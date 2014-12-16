<?php

namespace labelgen;

/**
 * 
 */
class User {

    protected $id;
    protected $name;
    protected $email;
    protected $secret;

    protected $table;
    protected $fields;

    public function __construct($request) {
        $this->table = 'labelgen_users';
        $this->fields = array('email', 'password', 'name', 'id', 'secret');
    }

    public function is_admin() {
        return $this->id === 0;
    }

    protected function get_user_id_from_password($pw) {
        global $wpdb;                           
        $wpdb->query($wpdb->prepare('SELECT * FROM labelgen_users WHERE name = %s', [ $this->verb ]));
        $result = $wpdb->last_result;
        if ($result && is_array($result)) {
            if ($this->decrypt($pw, $result[0]->password)) {
        $this->user_id = intval($result[0]->id);
            } else {
        throw new Exception("Either the name or password you entered is invalid");
            }
        } else {
            throw new Exception("Invalid Password.");           
        }
    }
}
?>
