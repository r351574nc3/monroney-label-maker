<?php


namespace labelgen {

    //Define Exceptions
    define('INVALID_USER_NAME', 1);
    define('NAME_ALREADY_REGISTERED', 2);
    define('EMAIL_ALREADY_REGISTERED', 3);
    define('INVALID_CHARACTERS_IN_NAME', 4);


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

        protected static $SELECT_BY_ID_FORMAT       = "SELECT * from labelgen_users where id = %d";
        protected static $SELECT_BY_CONTENT_FORMAT  = "SELECT * from labelgen_users where name = %s AND email = %s";
        protected static $SELECT_LU_FORMAT          = "SELECT * from labelgen_user_relationships where id = %d";

        public function __construct() {
            $this->table = 'labelgen_users';
            $this->fields = array('email', 'password', 'name', 'id', 'secret');
        }
     
        public function is_admin() {
            return $this->id === 0;
        }


        public function set_id($id) {
            $this->id = $id;
        }

        public function get_id() {
            return $this->id;
        }

        public function set_key($key) {
            $this->key = $key;
        }

        public function set_username($username) {
            $this->username = $username;
        }

        public function get_username() {
            return $this->username;
        }

        public function set_password($password) {
            $this->password = $password;
        }

        public function get_secret() {
            return $this->secret;
        }

        public function set_secret($secret) {
            $this->secret = $secret;
        }

        public function set_email($email) {
           $this->email = $email;
           return $this;
        }

        public static function get_user_id_from($username) {
            global $wpdb;
            $table = self::$table;
            
            $wpdb->query($wpdb->prepare("SELECT * FROM {$table} WHERE name = %s", [ $username ]));
            $result = $wpdb->last_result;
            if ($result && is_array($result)) {
                return intval($result[0]->id);
            }
            return NULL;
        }

        public static function get_key_from($username, $key) {
            global $wpdb;
            $table = self::$table;
            
            $wpdb->query($wpdb->prepare("SELECT secret, id FROM {$table} WHERE name = %s", [ $this->username ]));
            $results = $wpdb->last_result;

            if ($results) {
                $hash = hash_hmac('sha1', $key, $results[0]->secret, true);
                return [ base64_encode($hash), $results[0]->secret ];
            }
            else {
                throw new \Exception("No user by that name exists.");
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
        public function auth() {
            global $wpdb;
            $table = self::$table;

            if (is_null($this->id)) {
               throw new \Exception("No such user exists.");
            }

            $wpdb->query($wpdb->prepare("SELECT * FROM {$table} WHERE name = %s", [ $this->username ]));
            $result = $wpdb->last_result;
            if ($result && is_array($result)) {
                if (self::decrypt($this->password, $result[0]->password)) {
                    return true;
                }
                else {
                    throw new \Exception("Either the name or password you entered is invalid");
                }
            } else {
                throw new \Exception("Invalid Password");
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

        protected function encrypt($passw, $cost = 10) {
            
            //Create initialization vector from random source, size 16
            $iv = mcrypt_create_iv(16, MCRYPT_DEV_URANDOM);
     
            // Create a random salt     
            $salt = strtr(base64_encode($iv), '+', '.');        
            
            // Prefix information about the hash so PHP knows how to verify it later.
            // "$2a$" Means we're using the Blowfish algorithm. The following two digits are the cost parameter.
            $prefixed_salt = sprintf("$2a$%02d$", $cost) . $salt;
            
            // Hash the password with the salt
            $hash = crypt($passw, $prefixed_salt);
     
            return $hash;
        }

        public function to_array() {
            $retval = [
                    'success' => true,
                    'name'    => $this->username,
                    'email'   => $this->email,
                    'id'      => $this->id,
                    'secret'  => $this->secret
            ];
            return $retval;
        }

        public function to_json() {
     
            return json_encode($this->to_array(), JSON_FORCE_OBJECT);
        }

        public static function insert($user) {
            if (is_null($user->username) || is_null($user->password) || is_nulL($user->email)) {
                throw new Exception('Missing Vital Sign Up Information.');
            }

            if (!is_email($user->email)) {
                throw new Exception('Not a valid email address!');
            }
     
            if (!ctype_alnum($user->username)) { throw new \Exception(INVALID_CHARACTERS_IN_NAME); }
            
            global $wpdb;
            $table = self::$table;
            $wpdb->query(
                $wpdb->prepare("SELECT * FROM ${table} WHERE email = %s OR name = %s", [ $user->email, $user->username ])
            );
            
            $result = $wpdb->last_result[0];
            if ($result) {
                if ($result->email == $request['email']) {
                    throw new \Exception(EMAIL_ALREADY_REGISTERED);
                } else if ($result->name == $request['name']) {
                    throw new \Exception(NAME_ALREADY_REGISTERED);
                }
                else {
                    throw new \Exception(INVALID_USER_NAME);
                }
            }
                
            $user->password = self::encrypt(trim($user->password));
            $user->secret = sha1(microtime(true).mt_rand(22222,99999));

    		$time = current_time('mysql');
        	$wpdb->insert(self::$table, [ 'id'        => $user->id, 
                                          'name'      => $user->username, 
                                          'email'     => $user->email, 
                                          'time'      => $time, 
                                          'password'  => $user->password, 
                                          'secret'    => $user->secret ]);
        	
        	$user->set_id($wpdb->insert_id);
     
            $retval = $user->to_array();
            $retval['success'] = true;
        	if ($retval['id']) {
                return $retval;
        	} 
            else {
        		throw new \Exception(json_encode(array('last_error'=>$wpdb->last_error, 'last_query'=>$wpdb->last_query)));
        	}
        }

        public static function update($user) {
            if (is_null($user->username) || is_null($user->password) || is_nulL($user->email)) {
                throw new Exception('Missing Vital Sign Up Information.');
            }

            if (!is_email($user->email)) {
                throw new Exception('Not a valid email address!');
            }
     
            if (!ctype_alnum($user->username)) { throw new \Exception(INVALID_CHARACTERS_IN_NAME); }
            
            global $wpdb;
            $table = self::$table;
            $wpdb->query(
                $wpdb->prepare("SELECT * FROM ${table} WHERE email = %s OR name = %s", [ $user->email, $user->username ])
            );
            
            $result = $wpdb->last_result[0];
            if ($result) {
                if ($result->email == $request['email']) {
                    throw new \Exception(EMAIL_ALREADY_REGISTERED);
                } else if ($result->name == $request['name']) {
                    throw new \Exception(NAME_ALREADY_REGISTERED);
                }
                else {
                    throw new \Exception(INVALID_USER_NAME);
                }
            }
                
            $user->password = self::encrypt(trim($user->password));
            $user->secret = sha1(microtime(true).mt_rand(22222,99999));

    		$time = current_time('mysql');
        	$wpdb->insert(self::$table, [ 'id'        => $user->id, 
                                          'name'      => $user->username, 
                                          'email'     => $user->email, 
                                          'time'      => $time, 
                                          'password'  => $user->password, 
                                          'secret'    => $user->secret ]);
        	
        	$user->set_id($wpdb->insert_id);
     
            $retval = $user->to_array();
            $retval['success'] = true;
        	if ($retval['id']) {
                return $retval;
        	} 
            else {
        		throw new \Exception(json_encode(array('last_error'=>$wpdb->last_error, 'last_query'=>$wpdb->last_query)));
        	}
        }		


    }
}

namespace labelgen\User {
    class Builder {

        protected $username;
        protected $password;
        protected $key;
        protected $secret;
        protected $email;

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

        public function with_email($email) {
           $this->email = $email;
           return $this;
        }

        public function build() {
            $retval = new \labelgen\User();
            $retval->set_username($this->username);
            $retval->set_password($this->password);
            $retval->set_email($this->email);
            
            $retval->set_id(\labelgen\User::get_user_id_from($this->username));
            return $retval;
        }
    }
}
?>
