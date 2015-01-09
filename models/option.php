<?php

namespace labelgen;

class Option {
    protected $id;
    protected $time;
    protected $name;
    protected $price;
    protected $location;
    protected $owner;

    protected static $table = 'labelgen_options';

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
                 WHERE ty.user_id = %d AND ty.table_name = %s", 
                 intval($id), $table
            )
        );
        $num_results = $wpdb->last_result;

        if ($num_results) {
            $results = $wpdb->last_result;
        
            if (is_array($results)) {
                foreach($results as &$ut) {       
                    $ut->id = $ut->item_id;
                    unset($ut->table_name);
                    unset($ut->item_id);
                    unset($ut->time);
                    $retval["{$ut->location}_options"][] = $ut;
                }
            }        
        }

        return $retval;
    }
}
?>
