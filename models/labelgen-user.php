<?php

namespace labelgen {

    /**
     *
     */
    class User {
     
        protected $id;
        protected $name;
        protected $email;
        protected $key;
        protected $secret;
     
        protected $fields;
     
        protected static $table = "labelgen_users";
     
        public function __construct($request) {
            $this->table = 'labelgen_users';
            $this->fields = array('email', 'password', 'name', 'id', 'secret');
        }
     
        public function is_admin() {
            return $this->id === 0;
        }


        public function set_id($id) {
            $this->id = $id;
        }

        public function set_key($key) {
            $this->key = $key;
        }

        public function set_secret($secret) {
            $this->secret = $secret;
        }

        public static function get_user_id_from($username, $password) {
            global $wpdb;
            $table = self::$table;
            
            $wpdb->query($wpdb->prepare('SELECT * FROM {$table} WHERE name = %s', [ $username ]));
            $result = $wpdb->last_result;
            if ($result && is_array($result)) {
                if (self::decrypt($password, $result[0]->password)) {
                    return intval($result[0]->id);
                }
                else {
                    throw new Exception("Either the name or password you entered is invalid");
                }
            } else {
                throw new Exception("Invalid Password.");           
            }
        }

        public static function get_key_from($username, $key) {
            global $wpdb;
            $table = self::$table;
            
            $wpdb->query($wpdb->prepare('SELECT secret, id FROM {$table} WHERE name = %s', [ $this->username ]));
            $results = $wpdb->last_result;

            if ($results) {
                $hash = hash_hmac('sha1', $key, $results[0]->secret, true);
                return [ base64_encode($hash), $results[0]->secret ];
            }
            else {
                throw new Exception("No user by that name exists.");
            }
        }

        /**
         * Used for users that are already logged in to verify if the user is the same user or if there is some kind of malicious
         * thing happening.
         */
        public function verify($input_digest, $key) {
            $saved_digest = \labelgen\User::get_key_from($this->username, $key);

            /*
            $retval->set_key($key_secret[0]);
            $retval->set_secret($key_secret[1]);
            */
            
            return $saved_digest == $input_digest;
        }

        /**
         * Validate authentication of a user by comparing password hashes.
         */
        protected function auth($password) {
            global $wpdb;
            $table = self::$table;

            $wpdb->query($wpdb->prepare('SELECT * FROM {$table} WHERE name = %s', [ $this->username ]));
            $result = $wpdb->last_result;
            if ($result && is_array($result)) {
                if (self::decrypt($password, $result[0]->password)) {
                    return true;
                }
                else {
                    throw new Exception("Either the name or password you entered is invalid");
                }
            } else {
                throw new Exception("Invalid Password.");
            }
        }

        protected static function decrypt($input, $hash) {      
            $hash = trim($hash);
            $input = trim($input);
            
            $result = crypt($input, $hash);
            $compare = strcmp($result, $hash);
     
            if ($compare === 0) {
                return true;
            } else {
                return false;
            }
        }

        public function to_json() {
            $retval = [
                    "success" => true,
                    "name"    => $this->username,
                    "id"      => $this->id,
                    "secret"  => $this->secret
            ];
     
            return json_encode($retval);
        }
    }
}

namespace User {
    class Builder {

        protected $username;
        protected $password;
        protected $key;
        protected $secret;

        public function __construct() {
        }

        public function with_username($username) {
            $this->username = $username;
            return $this;
        }

        public function from_password($password) {
           $this->password = $password;
           return $this;
        }

        public function build() {
            $retval = new User();
            $retval->set_username($this->username);
            $retval->set_password($this->password);
            
            $retval->set_id(\labelgen\User::get_user_id_from($this->username, $this->password));
        }
    }
}
?>
