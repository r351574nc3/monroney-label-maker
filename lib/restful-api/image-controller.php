<?php

namespace labelgen;

require_once(LABEL_MAKER_ROOT.'/models/image.php');
require_once(LABEL_MAKER_ROOT.'/models/logo.php');

class image_controller {
    protected $wp_session;
    protected $api;

    protected static $table = "labelgen_images";

    public function __construct($api, $wp_session) {
        $this->api = $api;
        $this->wp_session = $wp_session;
    }


    public function get($request, $verb, $args) {
        $id = intval(array_pop($args));
        if (isset($id)) 
            $condition['id'] = intval($id);
        $fields = [ 'id', 'guid', 'caption', 'owner' ];
        return $this->api->parse_get_request($table, $fields, $conditions);
    }

    public function post($request, $verb, $args) {
        $user = $this->wp_session['user'];

        $request['owner'] = $user->get_id();
        $request['guid'] = $this->api->process_user_upload();
        $result = $this->api->parse_post_request(self::$table, $request);
        $this->api->user_relationships(self::$table, $result['id']);
        return $result;
    }

    public function delete($request, $verb, $args) {
        $id = intval(array_pop($args));
        $user = $this->wp_session['user'];
        $table = self::$table;
        
        if ($user->is_admin() && !current_user_can('manage_options'))
            throw new \Exception("Permission Denied");

        if ($id) {
            global $wpdb;
            
            $wpdb->query($wpdb->prepare('SELECT * FROM labelgen_images WHERE owner = %d AND id = %d', array($this->user_id, $id)));

            if (!$wpdb->last_result) {
                throw new \Exception('You do not have permission to delete this image.');
            }

            
            $wpdb->query($wpdb->prepare("SELECT guid FROM {$table} WHERE id = %d", $id));
            $result = $wpdb->last_result;
            $guid = $result[0]->guid;
            
            preg_match('/(wp\-content\/.*$)/', $guid, $matches);
            $filepath = $_SERVER['DOCUMENT_ROOT']."/".$matches[0];

            if (!$filepath) {
                throw new \Exception('No record of image on file.');
            } else {
                if (file_exists($filepath)) {
                    if (!unlink($filepath)) {
                throw new \Exception('There was a problem removing your file. If the problem persists please contact the system administrator');
                    }
                } else {
                    //throw new Exception('Image Does Not Exist.');
                }
            }
            
            $this->parse_delete_request($table, [ 'id' => $id ]);
            $this->parse_delete_request('labelgen_user_relationships', [ 'item_id' => $id, 'user_id' => $user->get_id(), 'table_name' => $table ]);
            $wpdb->query($wpdb->prepare('UPDATE labelgen_labels SET custom_image_id = NULL WHERE custom_image_id = %d', $id));
            
            return array('success'=>true);
        } else {
            throw new \Exception('Unable to process request!');                  
        }
             
   }
}

class logo_controller extends image_controller {
    protected $wp_session;
    protected $api;

    protected static $table = "labelgen_logos";


    public function __construct($api, $wp_session) {
        parent::__construct($api, $wp_session); 
    }

    public function delete() {
        $id = intval(array_pop($args));
        $user = $this->wp_session['user'];
        $table = self::$table;

        if ($id) {
            global $wpdb;           
            $wpdb->query($wpdb->prepare('SELECT * FROM labelgen_logos WHERE owner = %d AND id = %d',[ $user->get_id(), $id ]));

            if (!$wpdb->last_result) {
                throw new \Exception('You do not have permission to delete this logo.');
            }

            return $this->api->parse_delete_request($table, $id, $user->get_id());
        } else {
            throw new \Exception('Unable to process request!');                  
        }
    }
}
?>
