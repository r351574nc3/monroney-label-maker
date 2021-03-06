<?php

namespace labelgen;

require_once(LABEL_MAKER_ROOT.'/models/option.php');

class option_controller {
    protected $wp_session;
    protected $api;

    protected static $table = "labelgen_options";

    public function __construct($api, $wp_session) {
        $this->api = $api;
        $this->wp_session = $wp_session;
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
        $table = self::$table;

        if (is_null($user)) {
			throw new \Exception('Please log in or sign up to save your form.');
        }
        
        if (isset($request['option_name']) && isset($location)) {
            $request['option_name'] = sanitize_text_field($request['option_name']);
            $request['price'] = floatval($request['price']);  
            $request['location'] = $location;
            $request['owner'] = $user->get_id();

            $result = $this->api->parse_post_request($table, $request);        
            $this->api->user_relationships($table, $user->get_id(),$result['id']);
            return $result;
        } else {
            throw new \Exception('Fields Not Set');
        }
    }

    public function delete($request, $verb, $args) {
        global $wpdb;
        $id = intval(array_pop($args));
        $user = $this->wp_session['user'];
        
        if ($user->is_admin() && !current_user_can('manage_options'))
            throw new \Exception("Permission Denied");
        
        if ($id) {
            $wpdb->query($wpdb->prepare('SELECT * FROM labelgen_options WHERE owner = %d AND id = %d', [ $user->get_id(), $id ]));

            if (!$wpdb->last_result) {
                throw new \Exception('You do not have permission to delete this option.');
            }
            $this->api->parse_delete_request(self::$table, [ "id" => $id, "owner"=>$user->get_id() ]);
            $this->api->parse_delete_request('labelgen_user_relationships', [ 'item_id'=>$id, 'user_id'=>$user->get_id(), 'table_name' => self::$table ]);
            $this->api->parse_delete_request('labelgen_option_relationships', [ 'option_id' => $id ]);
            
            return array('success'=>true);
        } else {
            throw new \Exception('Unable to process request!');                
        }       
    }
}
?>
