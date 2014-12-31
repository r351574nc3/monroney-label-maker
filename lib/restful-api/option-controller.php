<?php

namespace labelgen;

require_once(LABEL_MAKER_ROOT.'/models/option.php');

class option_controller {
    protected $wp_session;
    protected $api;

    public function __construct($api, $wp_session) {
        $this->api = $api;
        $this->wp_sesison = $wp_session;
    }


    public function get($request, $verb, $args) {
        $user = $this->wp_session['user'];
        
        $conditions = $verb ? [ 'location' => array_pop($args) ] : [];
        $fields = [ 'id', 'option_name', 'owner', 'price', 'location' ];
        if (!is_null($user)) {
            global $wpdb;
            $wpdb->query(
                $wpdb->prepare(
                    'SELECT id, price, option_name FROM labelgen_options lo
                     INNER JOIN labelgen_user_relationships lur ON lur.item_id = lo.id
                     INNER JOIN labelgen_users lu ON lur.user_id = lu.id
                     WHERE lu.id = %d AND lo.location = %s',
                    $this->user_id, $conditions['location']
                )
            );
            $results[$conditions['location'] + '_options'] = $wpdb->last_result;
            $results['success'] = true;
            return $results;                
        } else {
           return $this->api->parse_get_request($table, $fields, $conditions);
        }
    }

    public function post($request, $verb, $args) {
        $location = array_pop($args);
        $user = $this->wp_session['user'];

        echo "Got here";
        if (isset($request['option_name']) && isset($location)) {
            echo "Inserting option";
            $request['option_name'] = sanitize_text_field($request['option_name']);
            $request['price'] = floatval($request['price']);  
            $request['location'] = $location;
            $request['owner'] = $user->get_id();

            $result = $this->api->parse_post_request($table, $request);        
            $this->api->user_relationships($table, $result['id']);
            return $result;
        } else {
            throw new Exception('Fields Not Set');
        }
    }

    public function delete($request, $verb, $args) {
        global $wpdb;
        $id = intval($args[0]);
        
        if ($this->user_id == 0 && !current_user_can('manage_options'))
            throw new Exception("Permission Denied");
        
        if ($id) {
            $wpdb->query($wpdb->prepare('SELECT * FROM labelgen_options WHERE owner = %d AND id = %d', array($this->user_id, $id)));

            if (!$wpdb->last_result) {
                throw new Exception('You do not have permission to delete this option.');
            }
            json_encode("");
            $this->parse_delete_request($table, ["id"=>$id, "owner"=>$this->user_id]);
            $this->parse_delete_request('labelgen_user_relationships', array('item_id'=>$id, 'user_id'=>$this->user_id, 'table_name'=>$table));
            $this->parse_delete_request('labelgen_option_relationships', array('option_id'=>$id));
            
            return array('success'=>true);
        } else {
            throw new Exception('Unable to process request!');                
        }       
    }
}
?>
