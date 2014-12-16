<?php

namespace labelgen;

require_once(LABEL_MAKER_ROOT.'/models/option.php');

class option_controller {


    public function __construct($request, $session, $origin) {
        $table = 'labelgen_options'; 
        $conditions = array();
    }


    public function get() {
        $conditions = $this->verb ? [ 'location' => $this->location($this->verb) ] : [];
        $fields = [ 'id', 'option_name', 'owner', 'price', 'location' ];
        if ($this->user_id) {
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
           return $this->parse_get_request($table, $fields, $conditions);
        }
    }

    public function post() {
        if (isset($this->request['option_name']) && isset($location)) {
            $request['option_name'] = sanitize_text_field($this->request['option_name']);
            $request['price'] = floatval($this->request['price']);  
            $request['location'] = $this->get_location($location);                        
            $request['owner'] = $this->user_id;

            $result = $this->parse_post_request($table, $request);        
            $this->user_relationships($table, $result['id']);
            return $result;
        } else {
            throw new Exception('Fields Not Set');
        }
    }

    public function delete() {
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
