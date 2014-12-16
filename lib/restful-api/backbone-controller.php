<?php

namespace labelgen;

require_once(LABEL_MAKER_ROOT.'/models/labelgen-user.php');

class backbone_controller {
    protected $wp_session;

    public function __construct($wp_session) {
        $this->wp_session = $wp_session;
    }


    public function get($request, $verb, $args) {
        echo json_encode([
                "success" => true,
                "name"    => "",
                "id"      => "0",
                "secret"  => ""
        ]);
        return json_encode([
                "success" => true,
                "name"    => "",
                "id"      => "0",
                "secret"  => "",
                "labelgen_images"  => get_images(),
                "labelgen_logos"   => get_logos(),
                "labelgen_options" => get_options(),
                "interior_options" => get_interior_options(),
                "labelgen_labels"  => []
        ]);
    }

    protected function get_images() {
        return [];
    }

    protected function get_logos() {
        return [];
    }

    protected function get_options() {
        return [];
    }

    protected function get_interior_options() {
        return [];
    }
}
?>
