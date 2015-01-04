<?php
// 
namespace labelgen;

require_once(LABEL_MAKER_ROOT.'/models/labelgen-user.php');
require_once(LABEL_MAKER_ROOT.'/models/label.php');

class user_controller {
    protected $wp_session;
    protected $api;

    public function __construct($api, $wp_session) {
        $this->wp_session = $wp_session;
        $this->api = $api;
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

        $return = \labelgen\User::insert($user);
        
        
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

            $retval['labelgen_labels'] = Label::query_for($user);
            $retval['labelgen_images'] = Image::query_for($user);
            $retval['labelgen_logos']  = Logo::query_for($user);

            $retval['message'] = 'Login successful.';
          
            return $retval;
        }
        return [ 'success' => false, 'message' => 'Login failed.' ];
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
        $controller = new \labelgen\label_controller($this->api, $this->wp_session);
        return $controller->get($request, $verb, $args);    
    }

    protected function post_labels($request, $verb, $args) {
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
        return \labelgen\Label::insert($label);
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
    }

    public function post($request, $verb, $args) {
        if (isset($verb)
            && array_key_exists('loginPassword', $request)) {
            // User is logging in
            $retval = $this->login($request, $verb, $args);
            if (!isset($retval['method'])) {
               $retval['method'] = $this->api->get_method();
            }
            return $retval;
        }
        else if (array_key_exists('signupName', $request)) {
            return $this->signup_user($request, $verb, $args);
        }

        $action = "post_" . array_shift($args);

        $retval = $this->{$action}($request, $verb, $args);
        if (!isset($retval['method'])) {
           $retval['method'] = $this->api->get_method();
        }
        return $retval;
    }

    protected function post_options($request, $verb, $args) {
        $controller = new \labelgen\option_controller($this->api, $this->wp_session);
        return $controller->post($request, $verb, $args);    
    }

    public function put($request, $verb, $args) {
        $action = "put_" . array_shift($args);
        $retval = $this->{$action}($request, $verb, $args);
        if (!isset($retval['method'])) {
           $retval['method'] = $this->api->get_method();
        }
        return $retval;
    }

    public function put_labels($request, $verb, $args) {
        $controller = new \labelgen\label_controller($this->api, $this->wp_session);
        return $controller->put($request, $verb, $args);    
    }

    public function delete() {
    }

}
?>    
