<?php
include(LABEL_MAKER_ROOT.'/models/labelgen-user.php');

require_once 'restful-api.php';
require_once 'backbone-controller.php';
require_once 'logo-controller.php';
require_once 'make-controller.php';
require_once 'model-controller.php';
require_once 'year-controller.php';
require_once 'image-controller.php';
require_once 'option-controller.php';
require_once 'label-controller.php';
require_once 'user-controller.php';

// Apply namespaces
use \labelgen;

class labelgen_api extends restful_api {
	
	protected $username;
    protected $wp_session;
	
    public function __construct($request, $session, $origin) {
        parent::__construct($request);

        $this->wp_session = $session;

        
        if ($this->is_user_logged_in()) {
            $this->user = new User($this->request);
            // $this->get_user_id_from_secret($this->request['secret'], $this->verb);
        }

        if (isset($this->request['loginstate'])) {
                unset($this->wp_session['user']);
                unset($_SESSION['wp_user_name']);
                unset($_SESSION['wp_user_id']);
                echo 'logout';
        }
        
        if ($_FILES) {
            switch($this->endpoint) {
                case('images'): 
                    $this->file_dir = 'images'; 
                    break;
                case('logos'): 
                    $this->file_dir = 'logos'; 
                    break;                      
                default: 
                    throw new Exception('Are you sure you want to do that?');
            }

            $this->pathname = WP_CONTENT_DIR.'/uploads/label-maker/user_data/'.$this->file_dir.'/';
            $this->baseurl = content_url('uploads/label-maker/user_data/'.$this->file_dir.'/');
            $this->allowed_exts = array("image"=>array("gif", "jpeg", "jpg", "pjpeg", "x-png", "bmp", "tiff", "png"));
        
            if (!is_dir(WP_CONTENT_DIR.'/uploads/label-maker')) {
                mkdir(WP_CONTENT_DIR.'/uploads/label-maker');
            }
            
            if (!is_dir(WP_CONTENT_DIR.'/uploads/label-maker/user_data')) {
                mkdir(WP_CONTENT_DIR.'/uploads/label-maker/user_data');
            }
                        
            if (!is_dir($this->pathname)) {
                mkdir($this->pathname);
            }

            if (!is_dir("{$this->pathname}{$this->username}")) {
                mkdir("{$this->pathname}{$this->username}");
            }
            
            $this->baseurl = "{$this->baseurl}{$this->username}";
            $this->pathname = "{$this->pathname}{$this->username}";
        }
    }
    
    /**
     * Determines if the current user is logged in.
     */
    protected function is_user_logged_in() {
        return isset($wp_session['user']);
    }

    public function user_relationships($item_table, $item_id) {
        global $wpdb;
        $table = 'labelgen_user_relationships';
        $wpdb->insert($table, array( 
            'user_id'=>$this->user_id, 
            'table_name'=> $item_table, 
            'item_id'=>$item_id, 
            'time'=>current_time('mysql')
            )
        );
        if ($wpdb->insert_id) {
            return;
        } else {
            throw new \Exception($this->db_values());
        }
    }

    protected function parse_args() {
        //echo json_encode(array('args'=>$this->args, 'endpoint'=>$this->endpoint, 'verb'=>$this->verb));
        //exit;     
    }
    
    
    
    protected function parse_label_request() {
        if ($this->user_id) {
            $request = array();
            $request['user_id'] = $this->user_id;
            $request['id'] = $this->request['id'] ? intval($this->request['id']) : NULL;

            @ $request['label_color'] = ($this->request['label_color']) ? (preg_match('/^#[a-zA-Z0-9]{6,8}$/', $this->request['label_color']) ? $this->request['label_color'] : '#234a8b') : '#234a8b';
            @ $request['font_style'] = ($this->request['font_style']) ? (in_array(array('Italic', 'Normal'), $this->request['font_style']) ? $this->request['font_style'] : 'Normal') : 'Normal';
            @ $request['font_weight'] = ($this->request['font_weight']) ? (in_array(array('Bold', 'Normal'), $this->request['font_weight']) ? $this->request['font_weight'] : 'Normal') : 'Normal';
            @ $request['font_family'] = ($this->request['font_family']) ? (in_array(array('Sans Serif', 'Monospace', 'Serif'), $this->request['font_family']) ? $this->request['font_family'] : 'Sans Serif') : 'Sans Serif';
            @ $request['dealership_name'] = $this->request['dealership_name'] ? sanitize_text_field($this->request['dealership_name']) : NULL;              
            @ $request['dealership_logo_id'] = $this->request['dealership_logo_id'] ? intval($this->request['dealership_logo_id']) : NULL;              
            @ $request['dealership_tagline'] = $this->request['dealership_tagline'] ? sanitize_text_field($this->request['dealership_tagline']) : NULL;             
            //$request['dealership_info'] = $this->request['dealership_info'] ? sanitize_text_field($this->request['dealership_info']) : '';                
            @ $request['custom_image_id'] = $this->request['custom_image_id'] ? intval($this->request['custom_image_id']) : NULL;
            @ $request['name'] = $this->request['name'] ? sanitize_text_field($this->request['name']) : NULL;
            @ $request['display_logo'] = $this->request['display_logo'] ? true : false;
            //$request['make_id'] = $this->request['make_id'] ? intval($this->request['make_id']) : '';
            //$request['model_id'] = $this->request['model_id'] ? intval($this->request['model_id']) : '';
            //$request['year_id'] = $this->request['year_id'] ? intval($this->request['year_id']) : '';
            //$request['vin'] = $this->request['vin'] ? intval($this->request['vin']) : '';
            //$request['msrp'] = $this->request['msrp'] ? floatval($this->request['msrp']) : '';
            //$request['trim'] = $this->request['trim'] ? sanitize_text_field($this->request['trim']) : '';
            return $request;
        } else {
            throw new Exception('Please log in or sign up to save your form.');
        }
    }

    protected function get_location($loc) {
        switch ($loc) {
            case ("interior"): 
                return 'interior'; 
            case ("exterior"): 
                return 'exterior'; 
            default: 
                throw new Exception('Not a valid location!'); 
        }

    }
            
    protected function users($action, $args) {
        $method = strtolower($this->method);
        $controller = new \labelgen\user_controller($this, $this->wp_session);
        return $controller->{$method}($this->request, $action, $args);
    }

    protected function backbone($action, $args) {
        $method = strtolower($this->method);
        $controller = new \labelgen\backbone_controller($this->wp_session);
        return $controller->{$method}($this->request, $action, args);       
    }
    
    protected function labels($action, $args) {
        $method = strtolower($this->method);
        $controller = new \labelgen\label_controller($this, $this->wp_session);
        return $controller->{$method}($this->request, $action, $args);
    }
    
    protected function discounts($action, $args) {
        $method = strtolower($this->method);
        $controller = new \labelgen\discount_controller($this->wp_session);
        return $controller->{$method}($this->request, $action, $args);
    }
    
    protected function options($action, $args) {
        $method = strtolower($this->method);
        $controller = new \labelgen\option_controller($this, $this->wp_session);
        return $controller->{$method}($this->request, $action, $args);
    }

    protected function logos($action, $args) {
        $method = strtolower($this->method);
        $controller = new \labelgen\logo_controller($this, $this->wp_session);
        return $controller->{$method}($this->request, $action, $args);
    }
    
    protected function images($action, $args) {
        $method = strtolower($this->method);
        $controller = new \labelgen\image_controller($this, $this->wp_session);
        return $controller->{$method}($this->request, $action, $args);
    }

    protected function models($action, $args) {
        $method = strtolower($this->method);
        $controller = new \labelgen\model_controller($this, $this->wp_session);
        return $controller->{$method}($this->request, $action, $args);
    }
     
    protected function makes($action, $args) {
        $method = strtolower($this->method);
        $controller = new \labelgen\make_controller($this, $this->wp_session);
        return $controller->{$method}($this->request, $action, $args);
    }

    protected function years($action, $args) {
        $method = strtolower($this->method);
        $controller = new \labelgen\year_controller($this, $this->wp_session);
        return $controller->{$method}($this->request, $action, $args); 
   }
     
}