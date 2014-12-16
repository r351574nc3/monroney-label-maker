<?php

namespace labelgen;

require_once(LABEL_MAKER_ROOT.'/models/image.php');

class image_controller {


    public function __construct($request, $session, $origin) {
        $table = 'labelgen_images';
        $conditions = array();
    }


    public function get() {
        if (isset($id)) 
            $condition['id'] = intval($id);
        $fields = array('id', 'guid', 'caption', 'owner');
        return $this->parse_get_request($table, $fields, $conditions);
    }

    public function post() {
        
        $request['owner'] = $this->user_id;
        $request['guid'] = $this->process_user_upload();
        $result = $this->parse_post_request($table, $request);          
        $this->user_relationships($table, $result['id']);
        return $result;
    }

    public function delete() {
        $id = intval($id);
        
        if ($this->user_id == 0 && !current_user_can('manage_options'))
            throw new Exception("Permission Denied");

        if ($id) {
            global $wpdb;
            
            $wpdb->query($wpdb->prepare('SELECT * FROM labelgen_images WHERE owner = %d AND id = %d', array($this->user_id, $id)));

            if (!$wpdb->last_result) {
                throw new Exception('You do not have permission to delete this image.');
            }

            
            $wpdb->query($wpdb->prepare("SELECT guid FROM {$table} WHERE id = %d", $id));
            $result = $wpdb->last_result;
            $guid = $result[0]->guid;
            
            preg_match('/(wp\-content\/.*$)/', $guid, $matches);
            $filepath = $_SERVER['DOCUMENT_ROOT']."/".$matches[0];
            
            //echo json_encode(array("filepath"=>$filepath));
            //exit;         
                        
            if (!$filepath) {
                throw new Exception('No record of image on file.');
            } else {
                if (file_exists($filepath)) {
                    if (!unlink($filepath)) {
                throw new Exception('There was a problem removing your file. If the problem persists please contact the system administrator');
                    }
                } else {
                    //throw new Exception('Image Does Not Exist.');
                }
            }
            
            $this->parse_delete_request($table, array('id'=>$id));
            $this->parse_delete_request('labelgen_user_relationships', array('item_id'=>$id, 'user_id'=>$this->user_id, 'table_name'=>$table));
            $wpdb->query($wpdb->prepare('UPDATE labelgen_labels SET custom_image_id = NULL WHERE custom_image_id = %d', $id));
            
            return array('success'=>true);
        } else {
            throw new Exception('Unable to process request!');                  
        }
             
   }
}
?>
