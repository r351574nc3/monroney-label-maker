<?php

namespace labelgen {


    class Logo {
        protected $id;
        protected $guid;
        protected $time;
        protected $owner;
     
        protected static $table = 'labelgen_logos';
     
        public function __construct() {
        }

        public static function query_for($user) {
            return is_null($user) ? [] : self::get_all($user->get_id());
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
                     WHERE (ty.user_id = %d OR ty.user_id = 0) AND ty.table_name = %s", 
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
