<?php

namespace labelgen;

require_once(LABEL_MAKER_ROOT.'/models/logo.php');

class logo_controller {


    public function __construct($request, $session, $origin) {
        $table = 'labelgen_logos';
        $conditions = array();
    }


    public function get() {
        if (isset($id)) 
            $condition['id'] = intval($id);
        $fields = array('id', 'guid', 'owner');
        return $this->parse_get_request($table, $fields, $conditions);
    }

    public function post() {
        $request['owner'] = $this->user_id;        
        $request['guid'] = $this->process_user_upload();
        $result = $this->parse_post_request($table, $request);          
        $this->user_relationships($table, $result['id']);
        return $result;
    }

    public function delete() {
        $id = intval($id);
        if ($id) {
            global $wpdb;           
            $wpdb->query($wpdb->prepare('SELECT * FROM labelgen_logos WHERE owner = %d AND id = %d', array($this->user_id, $id)));

            if (!$wpdb->last_result) {
                throw new Exception('You do not have permission to delete this logo.');
            }

            //return $this->parse_delete_request($table, $id, $this->user_id);
        } else {
            throw new Exception('Unable to process request!');                  
        }
    }
}
?>
