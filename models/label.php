<?php

namespace labelgen {
    require_once(LABEL_MAKER_ROOT.'/models/labelgen-user.php');

    class Label {
        protected $id;
        protected $color;
        protected $font_style;
        protected $font_weight;
        protected $font_family;
        protected $dealership;
        protected $dealership_tagline;
        protected $image_id;
        protected $time;
        protected $user;
        protected $name;
        protected $logo_id;
        protected $display_logo;
     
        protected static $table = 'labelgen_labels';
        protected static $UPDATE_LABELS_FORMAT = <<<EOL
                UPDATE labelgen_labels SET
                        label_color = '%s',
                        font_style = '%s', 
                        font_weight = '%s', 
                        font_family = '%s', 
                        dealership_name = '%s', 
                        dealership_tagline = '%s', 
                        dealership_logo_id = '%d', 
                        custom_image_id = '%d', 
                        time = '%s', 
                        user_id = '%d', 
                        name = '%s', 
                        display_logo = '%s' 
                WHERE id = '%d'
EOL;
     
        public function __construct() {
        }

        public function set_id($id) {
            $this->id = $id;
        }

        public function set_color($color) {
            $this->color = $color;
        }
        public function set_user($user) {
            $this->user = $user;
        }
        public function set_name($name) {
            $this->name = $name;
        }
        public function set_logo_id($logo_id) {
            $this->logo_id = $logo_id;
        }

        public function set_font_style($font_style) {
            $this->font_style = $font_style;
        }
        public function set_font_weight($font_weight) {
            $this->font_weight = $font_weight;
        }
        public function set_font_family($font_family) {
            $this->font_family = $font_family;
        }
        public function set_dealership($dealership) {
            $this->dealership = $dealership;
        }
        public function set_dealership_tagline($dealership_tagline) {
            $this->dealership_tagline = $dealership_tagline;
        }
        public function set_image_id($image_id) {
            $this->image_id = $image_id;
        }
        public function set_display_logo($display_logo) {
            $this->display_logo = $display_logo;
        }

        public function get_id() {
            return $this->id;
        }
        public function get_color() {
            return $this->color;
        }
        public function get_user() {
            return $this->user;
        }
        public function get_name() {
            return $this->name;
        }
        public function get_logo_id() {
            return $this->logo_id;
        }

        public function get_font_style() {
            return $this->font_style;
        }
        public function get_font_weight() {
            return $this->font_weight;
        }
        public function get_font_family() {
            return $this->font_family;
        }
        public function get_dealership() {
            return $this->dealership;
        }
        public function get_dealership_tagline() {
            return $this->dealership_tagline;
        }
        public function get_image_id() {
            return $this->image_id;
        }
        public function get_display_logo() {
            return $this->display_logo;
        }
        
        public function set_time($time) {
            $this->time = $time;
        }

        public static function insert($label) {
            global $wpdb;
            $retval = [];
            $table = self::$table;
        
    		$time = current_time('mysql');
        	$wpdb->insert(self::$table, [ 'id'                   => $label->get_id(), 
                                          'name'                 => $label->get_name(),
                                          'label_color'          => $label->get_color(), 
                                          'time'                 => $time, 
                                          'font_style'           => $label->get_font_style(), 
                                          'font_family'          => $label->get_font_family(), 
                                          'font_weight'          => $label->get_font_weight(), 
                                          'dealership_name'      => $label->get_dealership(), 
                                          'dealership_tagline'   => $label->get_dealership_tagline(), 
                                          'custom_image_id'      => $label->get_image_id(), 
                                          'dealership_logo_id'   => $label->get_logo_id(), 
                                          'user_id'              => $label->get_user()->get_id(), 
                                          'display_logo'         => $label->get_display_logo ]);
        	$label->set_id(intval($wpdb->insert_id));

        	if (!$label->get_id()) {
        		throw new \Exception(json_encode(array('last_error'=>$wpdb->last_error, 'last_query'=>$wpdb->last_query)));
        	}


        	$wpdb->insert('labelgen_user_relationships',
                                        [ 'id'                   => NULL, 
                                          'table_name'           => self::$table, 
                                          'item_id'              => $label->get_id(), 
                                          'user_id'              => $label->get_user()->get_id() ]);

            $retval = $label->to_array();
            $retval['success'] = true;


        	if (intval($wpdb->insert_id)) {
                return $retval;
        	} 
            else {
        		throw new \Exception(json_encode(array('last_error'=>$wpdb->last_error, 'last_query'=>$wpdb->last_query)));
        	}

        }

        public static function update($label) {
            global $wpdb;
            $retval = [];
            $table = self::$table;
     
    		$time = current_time('mysql');

            $wpdb->query($wpdb->prepare(self::$UPDATE_LABELS_FORMAT, [
                $label->get_color(),
                $label->get_font_style(),
                $label->get_font_weight(),
                $label->get_font_family(),
                $label->get_dealership(),
                $label->get_dealership_tagline(),
                $label->get_logo_id(),
                $label->get_image_id(),
                $time,
                $label->get_user()->get_id(),
                $label->get_name(),
                $label->get_display_logo(),
                $label->get_id()
            ]));
     
            $retval = $label->to_array();
            $retval['success'] = true;
            return retval;
        }

        public static function query_for($user) {
            global $wpdb;
            $retval = [];
            $table = self::$table;
            
            $wpdb->query(
                $wpdb->prepare(
                    "SELECT * FROM {$table} tx 
                     INNER JOIN labelgen_user_relationships ty
                     ON tx.id = ty.item_id 
                     WHERE ty.user_id = %d OR ty.user_id = 0 AND ty.table_name = %s", 
                     intval($user->get_id()), $table
                )
            );

            $num_results = $wpdb->last_result;
                
            if ($num_results) {
                $retval = $wpdb->last_result;
            
                if (is_array($retval)) {
                    foreach($retval as &$ut) {       
                        $ut->id = $ut->item_id;
                        unset($ut->table_name);
                        unset($ut->item_id);
                        unset($ut->time);
                    }
                }        
            }
     
            return $retval;
        }
          
        protected function set_label_options(&$pkg) {
            $option_ids = array();
            $prices = array();
            if ($this->user_id === 0 || $this->user_id) {
                global $wpdb;
                $wpdb->query($wpdb->prepare('SELECT * FROM labelgen_option_relationships WHERE label_id = %d',$pkg['id']));                 
                $temp = $wpdb->last_result;
                $saved_options = array();
                $saved_prices = array();
                
                for ($i = 0; $i < count($temp); $i++) {
                    $saved_options[$temp[$i]->option_id] = $temp[$i]->id;               
                    $saved_prices[$temp[$i]->option_id] = $temp[$i]->price;
                }
                
                if (array_key_exists('option_ids', $this->request) && is_array($this->request['option_ids'])) {
                    foreach($this->request['option_ids'] as $id) {
                        $option_id = intval($id);
                        $price = floatval($this->request['option_prices'][$option_id]);         
                        if ($option_id) {
                            $option_ids[] = $option_id;
                            
                            $prices[$option_id] = $price;
                            
                            if (!array_key_exists($option_id, $saved_options)) {
                                $result = $this->parse_post_request(
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
                            
                                if (array_key_exists('option_prices', $this->request) && array_key_exists($option_id, $this->request['option_prices'])) {
                                    //$prices[$option_id] = $this->request['option_prices'][$option_id];
                                    //$option_ids[] = $option_id;
                                    if ($this->request['option_prices'][$option_id] != $saved_prices[$option_id]) {
                                        $this->parse_put_request(
                                            'labelgen_option_relationships', 
                                            array(
                                                'price'=>$this->request['option_prices'][$option_id]    
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

        public function to_array() {
            return [ 'id'                   => $label->id, 
                     'name'                 => $label->name, 
                     'label_color'          => $label->color, 
                     'time'                 => $time, 
                     'font_style'           => $label->font_style, 
                     'font_family'          => $label->font_family, 
                     'font_weight'          => $label->font_weight, 
                     'dealership_name'      => $label->dealership, 
                     'dealership_tagline'   => $label->dealership_tagline, 
                     'custom_image_id'      => $label->image_id, 
                     'dealership_logo_id'   => $label->logo_id, 
                     'user_id'              => $label->user->id, 
                     'display_logo'         => $label->display_logo ];
        }
    }
}

namespace labelgen\Label {

    class Builder {
        protected $id;
        protected $color;
        protected $font_style;
        protected $font_weight;
        protected $font_family;
        protected $dealership;
        protected $dealership_tagline;
        protected $image_id;
        protected $time;
        protected $user;
        protected $name;
        protected $logo_id;
        protected $display_logo;

        public function __construct() {
        
        }

        public function with_id($id) {
            $this->id = $id;
            return $this;
        }
        public function with_color($color) {
            $this->color = $color;
            return $this;
        }
        public function with_user($user) {
            $this->user = $user;
            return $this;
        }
        public function with_name($name) {
            $this->name = $name;
            return $this;
        }
        public function with_logo_id($logo_id) {
            $this->logo_id = $logo_id;
            return $this;
        }

        public function with_font_style($font_style) {
            $this->font_style = $font_style;
            return $this;
        }
        public function with_font_weight($font_weight) {
            $this->font_weight = $font_weight;
            return $this; 
        }
        public function with_font_family($font_family) {
            $this->font_family = $font_family;
            return $this;
        }
        public function with_dealership($dealership) {
            $this->dealership = $dealership;
            return $this;
        }
        public function with_dealership_tagline($dealership_tagline) {
            $this->dealership_tagline = $dealership_tagline;
            return $this;
        }
        public function with_image_id($image_id) {
            $this->image_id = $image_id;
            return $this;
        }
        public function with_display_logo($display_logo) {
            $this->display_logo = $display_logo;
            return $this;
        }
        public function with_time($time) {
            $this->time = $time;
            return $this;
        }

        public function build() {
            $label = new \labelgen\Label();
            $label->set_id($this->id);
            $label->set_color($this->color);
            $label->set_user($this->user);
            $label->set_image_id($this->image_id);
            $label->set_font_style($this->font_style);
            $label->set_font_weight($this->font_weight);
            $label->set_font_family($this->font_family);
            $label->set_time($this->time);
            $label->set_display_logo($this->display_logo);
            $label->set_logo_id($this->logo_id);
            $label->set_dealership($this->dealership);
            $label->set_dealership_tagline($this->dealership_tagline);
            $label->set_user($this->user);
            $label->set_name($this->name);
            return $label;
        }
    }
}
?>
