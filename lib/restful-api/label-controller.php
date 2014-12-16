<?php

namespace labelgen;


class label_controller {

    public function __construct($request, $session, $origin) {
        $table = 'labelgen_labels'; 
        $fields = array('id', 'label_color', 'font_style', 'font_weight', 'font_family', 'dealership_name', 'dealership_logo_id', 'dealership_tagline', 'custom_image_id', 'user_id', 'name', 'display_logo');
        $conditions = array();
    }



    function get() {
        if ($this->user_id) {
            $conditions['user_id'] = intval($this->user_id);
            
            if (array_key_exists('id', $this->request) && intval($this->request['id']) > 0) {
                $conditions['id'] = intval($this->request['id']);
            }               

            $results = $this->parse_get_request($table, $fields, $conditions);
            if ($results) {
                    
                foreach ($results as &$result) {
                    if (array_key_exists('id', $result)) {
                
                global $wpdb;                
                $wpdb->query($wpdb->prepare('SELECT option_id, price FROM labelgen_option_relationships where label_id = %d', $result['id']));
                $options = $wpdb->last_result;
                //echo json_encode($options);
                //exit;

                if ($options) {
                    $result['option_ids'] = array();
                    $result['option_prices'] = array();
                    foreach ($options as $option) {
                        $result['option_ids'][] = $option->option_id;
                        $result['option_prices'][$option->option_id] = $option->price;
                    }
                }   
                    
                    }
                }
                return $results;        

            }
            return array('success'=>true, 'message'=>'No label saved.');
        } else {
            return array('success'=>true, 'message'=>'No label saved.');
        }
    }
    
    public function post() {
        $request = $this->parse_label_request();
        
        //echo json_encode($request);
        //exit;
        
        global $wpdb;
        $wpdb->query($wpdb->prepare('SELECT * FROM labelgen_labels WHERE user_id = %d AND name = %s', $this->user_id, $request['name']));
        $result = $wpdb->last_result;
        if ($result) {
            $request['id'] = $result[0]->id;
            $pkg = $this->parse_put_request($table, $request, $this->array_select($request, array('id')));          
        } else {
            $pkg = $this->parse_post_request($table, $request, false);
        }
        
        $this->set_label_options($pkg);
        $pkg['request_method'] = $this->method;
        
        return $this->win($pkg);
    }

    
    public function put() {
        $request = $this->parse_label_request();
        
        
        $pkg = $this->parse_put_request($table, $request, array('id'=>$this->user_id) );
        
        
        
        $this->set_label_options($pkg);
        return $this->win($pkg);
    }
    
    public function delete() {
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
