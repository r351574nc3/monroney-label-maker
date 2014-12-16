<?php

namespace labelgen;


require_once(LABEL_MAKER_ROOT.'/models/make.php');

class make_controller {


    public function __construct($request, $session, $origin) {
        
        $table = 'labelgen_makes';      
        $request = array();
    }


    public function get() {
        $fields = array('id', 'make');
        return $this->parse_get_request($table, $fields);
    }

    public function post() {
        if (isset($this->request['make'])) {
            $request['make'] = sanitize_text_field($this->request['make']);
            global $wpdb;
            $wpdb->query($wpdb->prepare('SELECT * FROM labelgen_makes WHERE make = %s', $request['make']));
            $result = $wpdb->last_result;
            if($result) {               
                throw new Exception(array('message'=>'Already Added', 'id'=>$result[0]->id));
            } else {
                $result = $this->parse_post_request($table, $request);          
                $this->user_relationships($table, $result['id']);
                return $result;
            }
        } else {
            throw new Exception('Fields Not Set');          
        }
    }

    public function delete() {
        
        $id = intval($this->request['id']);         
        return $this->parse_delete_request($table, ["id"=>$id]);
    }
}
?>
