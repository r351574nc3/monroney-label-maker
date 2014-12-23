<?php
// 
namespace labelgen;

require_once(LABEL_MAKER_ROOT.'/models/labelgen-user.php');

class user_controller {
    protected $wp_session;

    public function __construct($wp_session) {
        $this->wp_session = $wp_session;
    }

    protected function check_credentials($request, $verb, $args) {
        $user = $wp_session['user'];
        return json_encode([ 'success' => true,
                             'message' => (isset($user) && $user->is_admin()) ? current_user_can('manage_options') : true ]);
    }

    protected function get_unencrypted_secret() {
        $auth_args = explode(":", $_SERVER['HTTP_AUTHENTICATION']);
        $nonce = $auth_args[1];
        $input_digest = $auth_args[2];

        $PROTOCOL = ($_SERVER['HTTPS']) ? 'https://' : 'http://';
        $path = $PROTOCOL.$_SERVER['HTTP_HOST'].$_SERVER['REDIRECT_URL'];
        return "{$_SERVER['REQUEST_METHOD']}+{$path}+{$nonce}";
    }

    protected function login($request, $verb, $args) {
        $user = (new \labelgen\User\Builder())
                ->with_username($username)
                ->from_password($pw)
                ->build();
        
        if ($user->auth()) {
            $this->wp_session['user'] = $user;
            return $user->to_json();
        }

        return json_encode([ 'success' => true ]);
    }

    public function get($request, $verb, $args) {
        $username = '';
        if (isset($verb) && is_array($args)) {
            $action = array_pop($args);
            
            // If the action is a method, then evaluate it. If it is not, it must be a user.
            if (method_exists($this, $action) > 0) {
                return $this->{$action}($request, $verb, $args);
            }
            else {
                $username = $verb;
            }
        }

        if (isset($verb)
            && array_key_exists('loginPassword', $request)) {
            // User is logging in
            echo "User is logging in\n";
            // $this->get_user_id_from_password($this->request['loginPassword'])

            $user = (new \labelgen\User\Builder())
                    ->with_username($username)
                    ->from_password($pw)
                    ->build();
            
            if ($user->auth()) {
                $this->wp_session['user'] = $user;
                return $user->to_json();
            }
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

    public function post($request, $verb, $args) {
        if (isset($verb)
            && array_key_exists('loginPassword', $request)) {
            // User is logging in
            $this->login($request, $verb, $args);
        }
        else {
            // return $this->signup_user($table, $fields);
        }
    }

    public function delete() {
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
