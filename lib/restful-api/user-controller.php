<?php
// 
namespace labelgen;

require_once(LABEL_MAKER_ROOT.'/models/labelgen-user.php');
require_once(LABEL_MAKER_ROOT.'/models/label.php');

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

    protected function signup_user($request, $verb, $args) {
        $user = (new \labelgen\User\Builder())
                ->with_username(trim($request['signupName']))
                ->with_email($request['signupEmail'])
                ->from_password($request['signupPassword'])                
                ->build();

        $return = \labelgen\User::save_new($user);
        
        
        if ($return) {
            // $retval = $user->to_array();
            // $retval['success'] = true;
            $this->wp_session['user'] = $user;
            return $return;
        } else {
            throw new Exception('Something went wrong. We were not able to sign you up at this time.');             
        }
    }
    
    protected function login($request, $verb, $args) {
        $user = (new \labelgen\User\Builder())
                ->with_username($request['loginName'])
                ->from_password($request['loginPassword'])
                ->build();
        
        if ($user->auth()) {
            $this->wp_session['user'] = $user;

            $retval = $user->to_array();
            $retval['message'] = 'Login successful.';
            return $retval;
        }
        return json_encode([ 'success' => true ]);
    }

    protected function load_session() {
        $user = $this->wp_session['user'];

        if (!is_null($user)) {
            $retval = $user->to_array();
            $retval['success'] = true;
            return $retval;
        }
        return [ 'success' => false, 'status' => 403 ];
    }

    protected function labels($request, $verb, $args) {
        $user = $this->wp_session['user'];
    }

    protected function new_label($request, $verb, $args) {
        $user = $this->wp_session['user'];
        $label = (new \labelgen\Label\Builder())
                ->with_name($request['name'])
                ->with_color($request['label_color'])
                ->with_dealership($request['dealership_name'])
                ->with_dealership_tagline($request['dealership_tagline'])
                ->with_font_family($request['font_family'])
                ->with_font_style($request['font_style'])
                ->with_font_weight($request['font_weight'])
                ->with_logo_id($request['dealership_logo_id'])
                ->with_display_logo($request['display_logo'])
                ->with_image_id($request['custom_image_id'])
                ->with_user($user)->build();
        return \labelgen\Label::save_new($label);
    }

    public function get($request, $verb, $args) {
        $username = '';
        if ($verb == 'session') {
            return $this->load_session();
        }
        
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
            return $this->login($request, $verb, $args);
        }
        else if (array_key_exists('signupName', $request)) {
            return $this->signup_user($request, $verb, $args);
        }

        $action = array_shift($args);

        if ($action == 'labels') {
            return $this->new_label($request, $verb, $args);
        }
    }

    public function delete() {
    }

}
?>
