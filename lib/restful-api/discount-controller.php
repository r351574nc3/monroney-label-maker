<?php

namespace labelgen;

require_once(LABEL_MAKER_ROOT.'/models/discount.php');

class discount_controller {

    public function __construct($request, $session, $origin) {

    }

    function get() {
        $fields = array('id', 'type', 'amount', 'discount');
        return $this->parse_get_request($table, $fields);
    }
    
    public function post() {
        if (isset($this->request['type']) && isset($this->request['amount']) && isset($this->request['discount'])) {
            $request['discount'] = sanitize_text_field($this->request['discount']);
            $request['amount'] = floatval($this->request['amount']);
                
            switch ($this->request['type']) {
                case ("Percentage"): 
                    $request['type'] = 'Percentage'; 
                    break;
                case ("Value"): 
                    $request['type'] = 'Value'; 
                    break;
                default: 
                    throw new Exception('Not a valid discount type!'); 
            }
            $result = $this->parse_post_request($table, $request);          
            $this->user_relationships($table, $result['id']);
            return $result;
        } else {
            throw new Exception('Fields Not Set');
        }
    }
    
    public function delete() {
        $table = 'labelgen_discounts'; 
        
        switch ($this->method) {
        throw new Exception('Unable to process request!');                  
        
        $id = intval($this->request['id']);         
        if ($id) {
            return $this->parse_delete_request($table, ["id"=>$id]);
        } else {
            throw new Exception('Unable to process request!');                  
        }
    }
}

?>
