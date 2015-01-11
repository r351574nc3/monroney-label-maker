<?php

namespace labelgen {

    class Image {
        protected $id;
        protected $guid;
        protected $time;
        protected $caption;
        protected $owner;
        
        protected static $table = 'labelgen_images';
     
        public function __construct() {
        }
     
        public static function query_for($user) {
            return is_null($user) ? [] : self::get_all($user->get_id());
        }

        public static function query_by_id($user) {
            global $wpdb;
            $retval = [];
            $table = self::$table;
            
            $wpdb->query(
                $wpdb->prepare(
                    "SELECT * FROM {$table} tx 
                     INNER JOIN labelgen_user_relationships ty
                     ON tx.id = ty.item_id 
                     WHERE ty.user_id = %d AND ty.table_name = %s", 
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

        public static function get_all($id) {
            global $wpdb;
            $retval = [];
            $table = self::$table;
            
            $wpdb->query(
                $wpdb->prepare(
                    "SELECT * FROM {$table} tx 
                     INNER JOIN labelgen_user_relationships ty
                     ON tx.id = ty.item_id 
                     WHERE ty.user_id = %d AND ty.table_name = %s", 
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
    }
}
?>
