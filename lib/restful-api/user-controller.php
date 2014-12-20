<?php

namespace labelgen;

require_once(LABEL_MAKER_ROOT.'/models/labelgen-user.php');

class user_controller {
    protected $wp_session;

    public function __construct($wp_session) {
        $this->wp_session = $wp_session;
    }


    public function get($request, $verb, $args) {
        echo json_encode(["verb" => $verb, "args" => $args]); 
        if (isset($verb)
            && array_key_exists('loginPassword', $request)) {
            // User is logging in
            echo "User is logging in\n";
            // $this->get_user_id_from_password($this->request['loginPassword'])
        }
           /*
        
        $this->get_user_id_from_secret();
        $this->username = $this->verb;
        $this->endpoint = array_shift($this->args);
        if ($this->args) {
            $this->verb = array_shift($this->args);
        }
        else {
            $this->verb = NULL;
        }           

        $conditions = array();
        if (ctype_alnum($this->verb)
            && array_key_exists('loginPassword', $this->request) 
            && $this->request['loginPassword']) 
        {
            $conditions['name'] = $this->verb;
        } else if (@ $this->user_id == 0) {
            $conditions['id'] = 0;
        } else {
            throw new Exception('You do not have the authorization to perform this action!');   
        }
        
        $results = $this->parse_get_request($table, $fields, $conditions);
        
        if (isset($this->request['loginPassword'])) {
            if (!$this->decrypt($this->request['loginPassword'], $results[0]['password'])) {
        throw new Exception('Incorrect Password!');
            }
        }
        
        $id = $results[0]['id'];
        $secret = $results[0]['secret'];
        $user = array('success'=>true, 'name'=>$results[0]['name'], 'id'=>$id, 'secret'=>$secret);

        global $wpdb;
        $tables = array("labelgen_images", "labelgen_logos", "labelgen_makes", "labelgen_models", "labelgen_years", "labelgen_options", "labelgen_discounts");
        foreach($tables as $tbl) {          
        }
                
        if (isset($user['labelgen_options']) && is_array($user['labelgen_options'])) {
            foreach($user['labelgen_options'] as $option) {
        $user["{$option->location}_options"][] = $option;
            }        
                
        }
        
        $wpdb->query($wpdb->prepare("SELECT * FROM labelgen_labels WHERE user_id = %d", $this->user_id));               
        $user['labelgen_labels'] = $wpdb->last_result; 

        if (is_array($user['labelgen_labels'])) {
            foreach($user['labelgen_labels'] as &$label) {
        $wpdb->query($wpdb->prepare("SELECT * FROM labelgen_option_relationships WHERE label_id = %d", $label->id));        
        $options = $wpdb->last_result;
        if ($options) {
            $label->options = $options;
        }               
            }
        }
        */
            
        // return $user;

    }

    public function post() {
        return $this->signup_user($table, $fields);
    }

    public function delete() {
    }

    
    /**
     * 
     */                             
    protected function get_user_id_from_secret() {
        $auth_args = explode(":", $_SERVER['HTTP_AUTHENTICATION']);
        $user = substr($auth_args[0], 5, strlen($auth_args[0]));
        $this->username = $user;
        $nonce = $auth_args[1];
        $input_digest = $auth_args[2];
        
        global $wpdb;
        $wpdb->query($wpdb->prepare('SELECT secret, id FROM labelgen_users WHERE name = %s', array($this->verb)));
        $results = $wpdb->last_result;
        
        
        if ($results) {
            $id = $results[0]->id;
            
            $PROTOCOL = ($_SERVER['HTTPS']) ? 'https://' : 'http://';
            $path = $PROTOCOL.$_SERVER['HTTP_HOST'].$_SERVER['REDIRECT_URL'];       
            $msg = "{$this->method}+{$path}+{$nonce}";
            $hash = hash_hmac('sha1', $msg, $results[0]->secret, true);
            $saved_digest = base64_encode($hash);

            //echo json_encode(array('message'=>$msg, 'server_authentication'=>$_SERVER['HTTP_AUTHENTICATION'], 'saved_digest'=>$saved_digest, 'input_digest'=>$input_digest));
            //exit;

            if ($saved_digest == $input_digest) {
        $this->user_id = $id;
            } else {
        throw new Exception("Are you sure you want to do that?");
            }               
        } else {
            throw new Exception("No user by that name exists.");
        }               
    }
    
    private function signup_user($table, $fields) {
        //First Check that all the appropriate fields have been filled in
        if ($this->request['signupEmail'] && $this->request['signupPassword'] && $this->request['signupName']) {
            
            $request['email'] = is_email($this->request['signupEmail']) ? $this->request['signupEmail'] : NULL;
            if (is_null($request['email']))
        throw new Exception('Not a valid email address!');

            
            $request['name'] = trim($this->request['signupName']);
            
        
            if ($request['name']) {
        if (!ctype_alnum($request['name'])) throw new Exception(INVALID_CHARACTERS_IN_NAME);
        
        global $wpdb;
        $wpdb->query(
            $wpdb->prepare(
        "SELECT email, name FROM labelgen_users WHERE email = %s OR name = %s", 
        array($request['email'], $request['name'])
            )
        );
        $result = $wpdb->last_result[0];
        if ($result) {
            if ($result->email == $request['email']) {
        throw new Exception(EMAIL_ALREADY_REGISTERED);
            } else if ($result->name == $request['name']) {
        throw new Exception(NAME_ALREADY_REGISTERED);
            }
        }
            } else {
        throw new Exception(INVALID_USER_NAME);
            }
            
            $request['password'] = $this->encrypt(trim($this->request['signupPassword']));
            $request['secret'] = sha1(microtime(true).mt_rand(22222,99999));
            
        } else {
            throw new Exception('Missing Vital Sign Up Information.');
        }
        
        $return = $this->parse_post_request($table, $request, true);
        
        if ($return) {
            
            return array('success'=>true, 'id'=>$return['id'], 'secret'=>$return['secret'], 'email'=>$return['email'], 'name'=>$return['name']);
        } else {
            throw new Exception('Something went wrong. We were not able to sign you up at this time.');             
        }

    }
    
}
?>
