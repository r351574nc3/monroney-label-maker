<?php

namespace labelgen;

require_once(LABEL_MAKER_ROOT.'/models/labelgen-user.php');
require_once(LABEL_MAKER_ROOT.'/models/label.php');


class label_controller {
    protected static $table = 'labelgen_labels';
    protected $fields;
    protected $wp_session;
    protected $api;
    
    public function __construct($api, $wp_session) {
        $this->wp_session = $wp_session;
        $this->api = $api;
    }

    protected function is_logged_in() {
        return is_null($this->wp_session['user']);
    }
 
    public function get($request, $verb, $args) {
        $user = $this->wp_session['user'];
        $table = self::$table;
		$fields = [ 'id', 'label_color', 'font_style', 'font_weight', 'font_family', 'dealership_name', 'dealership_logo_id', 'dealership_tagline', 'custom_image_id', 'user_id', 'name', 'display_logo' ];
        $conditions = [];
        
        if ($this->is_logged_in()) {
            $conditions['user_id'] = intval($user->get_id());
            
            if (array_key_exists('id', $request) && intval($request['id']) > 0) {
                $conditions['id'] = intval($request['id']);
            }               

            $results = $this->parse_get_request($table, $fields, $conditions);
            if ($results) {
                    
                foreach ($results as &$result) {
                    if (array_key_exists('id', $result)) {                
                        global $wpdb;                
                        $wpdb->query($wpdb->prepare('SELECT option_id, price FROM labelgen_option_relationships where label_id = %d', $result['id']));
                        $options = $wpdb->last_result;
         
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
            return [ 'success'=>true, 'message'=>'No label saved.' ];
        }
        else {
            return [ 'success'=>true, 'message'=>'No label saved.' ];
        }
    }
    
    public function post($input, $verb, $args) {
        $user = $this->wp_session['user'];
        $request = $this->parse_label_request($input);
        
        global $wpdb;
        $wpdb->query($wpdb->prepare('SELECT * FROM labelgen_labels WHERE user_id = %d AND name = %s', $user->get_id(), $request['name']));
        $result = $wpdb->last_result;
        if ($result) {
            $request['id'] = $result[0]->id;
            $pkg = $this->api->parse_put_request(self::$table, $request, $this->api->array_select($request, [ 'id' ]));
        } else {
            $pkg = $this->api->parse_post_request(self::$table, $request, false);
        }
        
        $this->set_label_options($pkg);
        $pkg['request_method'] = 'POST';
        
        return $this->api->win($pkg);
    }

    
    public function put($input) {
        $request = $this->parse_label_request($input);
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

	protected function set_label_options($request, &$pkg) {
		$option_ids = [];
		$prices = [];
        $user = $this->wp_session['user'];
        
		if ($this->is_logged_in()) {
			global $wpdb;
			$wpdb->query($wpdb->prepare('SELECT * FROM labelgen_option_relationships WHERE label_id = %d', $pkg['id']));
			$temp = $wpdb->last_result;
			$saved_options = [];
			$saved_prices = [];
			
			for ($i = 0; $i < count($temp); $i++) {
				$saved_options[$temp[$i]->option_id] = $temp[$i]->id;				
				$saved_prices[$temp[$i]->option_id] = $temp[$i]->price;
			}
			
			if (array_key_exists('option_ids', $request) && is_array($request['option_ids'])) {
				foreach($request['option_ids'] as $id) {
					$option_id = intval($id);
					$price = floatval($request['option_prices'][$option_id]);			
					if ($option_id) {
						$option_ids[] = $option_id;
						
						$prices[$option_id] = $price;
						
						if (!array_key_exists($option_id, $saved_options)) {
							$result = $this->api->parse_post_request(
								'labelgen_option_relationships', 
								array(
									'option_id'=>$option_id, 
									'label_id'=>$pkg['id'], 
									'price'=>$price
								)
							);
							if (!array_key_exists('id', $result)) {
								throw new Exception('Something went terribly wrong');	
							}				
						} else {
						
							if (array_key_exists('option_prices', $request) && array_key_exists($option_id, $request['option_prices'])) {
								//$prices[$option_id] = $request['option_prices'][$option_id];
								//$option_ids[] = $option_id;
								if ($request['option_prices'][$option_id] != $saved_prices[$option_id]) {
									$this->api->parse_put_request(
										'labelgen_option_relationships', 
										array(
											'price'=>$request['option_prices'][$option_id]	
										),
										array(
											'option_id'=>$option_id, 
											'label_id'=>$pkg['id'],
										) 
									);
								}
							}										
						}
					}				
				}						
				$pkg['option_ids'] = $option_ids;
				$pkg['price_ids'] = $prices;
			}
		} else {
			throw new Exception('No Label ID Available to Add Options to!');
		}
	}	

    protected function parse_label_request($request) {
        $user = $this->wp_session['user'];
        $retval = [];
        
		if ($this->is_logged_in()) {
			$retval['user_id'] = $user->get_id();
			$retval['id'] = $request['id'] ? intval($request['id']) : NULL;

			@ $retval['label_color'] = ($request['label_color']) ? (preg_match('/^#[a-zA-Z0-9]{6,8}$/', $request['label_color']) ? $request['label_color'] : '#234a8b') : '#234a8b';
			@ $retval['font_style'] = ($request['font_style']) ? (in_array(array('Italic', 'Normal'), $request['font_style']) ? $request['font_style'] : 'Normal') : 'Normal';
			@ $retval['font_weight'] = ($request['font_weight']) ? (in_array(array('Bold', 'Normal'), $request['font_weight']) ? $request['font_weight'] : 'Normal') : 'Normal';
			@ $retval['font_family'] = ($request['font_family']) ? (in_array(array('Sans Serif', 'Monospace', 'Serif'), $request['font_family']) ? $request['font_family'] : 'Sans Serif') : 'Sans Serif';
			@ $retval['dealership_name'] = $request['dealership_name'] ? sanitize_text_field($request['dealership_name']) : NULL;				
			@ $retval['dealership_logo_id'] = $request['dealership_logo_id'] ? intval($request['dealership_logo_id']) : NULL;				
			@ $retval['dealership_tagline'] = $request['dealership_tagline'] ? sanitize_text_field($request['dealership_tagline']) : NULL;				
			//$retval['dealership_info'] = $request['dealership_info'] ? sanitize_text_field($request['dealership_info']) : '';				
			@ $retval['custom_image_id'] = $request['custom_image_id'] ? intval($request['custom_image_id']) : NULL;
			@ $retval['name'] = $request['name'] ? sanitize_text_field($request['name']) : NULL;
			@ $retval['display_logo'] = $request['display_logo'] ? true : false;
			//$retval['make_id'] = $request['make_id'] ? intval($request['make_id']) : '';
			//$retval['model_id'] = $request['model_id'] ? intval($request['model_id']) : '';
			//$retval['year_id'] = $request['year_id'] ? intval($request['year_id']) : '';
			//$retval['vin'] = $request['vin'] ? intval($request['vin']) : '';
			//$retval['msrp'] = $request['msrp'] ? floatval($request['msrp']) : '';
			//$retval['trim'] = $request['trim'] ? sanitize_text_field($request['trim']) : '';
		}
        else {
			throw new Exception('Please log in or sign up to save your form.');
		}
	    return $retval;
	}
}

?>
