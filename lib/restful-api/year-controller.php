<?php

namespace labelgen;

require_once(LABEL_MAKER_ROOT.'/models/year.php');

class year_controller {


    public function __construct($request, $session, $origin) {
        
        $table = 'labelgen_years';      
        $request = array();             
        if ($this->method == 'GET') {
        } elseif ($this->method == 'POST') {
            
        } elseif($this->method == 'DELETE') {
            
        } else {
            throw new Exception(sprintf('%s requests are not accepted at this time.', $this->method));
        }       
    }


    public function get() {
        $fields = array('id', 'make_id', 'model_id', 'year');
        //$conditions = array('make_id'=>$this->request['make_id'], 'model_id'=>$this->request['model_id']);
        return $this->parse_get_request($table, $fields);
    }

    public function post() {
        if (isset($this->request['year']) && isset($this->request['make_id']) && isset($this->request['model_id']) && strlen($this->request['year']) == 4) {
            $request['year'] = intval($this->request['year']);
            $request['make_id'] = intval($this->request['make_id']);
            $request['model_id'] = intval($this->request['model_id']);
        
            global $wpdb;
            $wpdb->query($wpdb->prepare('SELECT * FROM labelgen_years WHERE year = %d AND make_id = %d AND model_id = %d', $request['year'], $request['make_id'], $request['model_id']));
            $result = $wpdb->last_result;
            if($result) {               
                throw new Exception('Already Added ' . $result[0]->id);
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
