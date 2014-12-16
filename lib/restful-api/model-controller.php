<?php

namespace labelgen;

require_once(LABEL_MAKER_ROOT.'/models/model.php');

class model_controller {


    public function __construct($request, $session, $origin) {        
        $table = 'labelgen_models';         
    }


    public function get() {
        $fields = array('id', 'model', 'make_id');
        //conditions = array('make_id'=>$this->request['make_id']);
        return $this->parse_get_request($table, $fields);
    }

    public function post() {
        if (isset($this->request['make_id']) && isset($this->request['model'])) {
            //$request['id'] = intval($this->request['id']);
            $request['model'] = sanitize_text_field($this->request['model']);
            global $wpdb;
            $wpdb->query($wpdb->prepare('SELECT * FROM labelgen_models WHERE model = %s', $request['model']));
            $result = $wpdb->last_result;
    
            if($result) {               
                throw new Exception('Already Added ' . $result[0]->id);
            } else {
                $request['make_id'] = intval($this->request['make_id']);    
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
