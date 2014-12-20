<?php

namespace labelgen;

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
    protected $user_id;
    protected $name;
    protected $logo_id;
    protected $display_logo;

    protected static $table = 'labelgen_labels';

    public function __construct() {
    }

    public static function get_all($id) {
        global $wpdb;
        $retval = [];
        $table = self::$table;
        
        $wpdb->query(
            $wpdb->prepare(
                "SELECT * FROM {$table} tx 
                 INNER JOIN labelgen_user_relationships ty
                 ON tx.id = ty.item_id 
                 WHERE ty.user_id = %d OR ty.user_id = 0 AND ty.table_name = %s", 
                 intval($id), $table
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

}
?>
