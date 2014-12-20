<?php

namespace labelgen;

require_once(LABEL_MAKER_ROOT.'/models/labelgen-user.php');
require_once(LABEL_MAKER_ROOT.'/models/image.php');

class backbone_controller {
    protected $wp_session;

    public function __construct($wp_session) {
        $this->wp_session = $wp_session;
    }


    public function get($request, $verb, $args) {
        return json_encode([
                "success" => true,
                "name"    => "",
                "id"      => "",
                "secret"  => "",
                "labelgen_images"  => $this->get_images(),
                "labelgen_logos"   => $this->get_logos(),
                "labelgen_options" => $this->get_options(),
                "interior_options" => $this->get_interior_options(),
                "labelgen_labels"  => []
        ]);
    }

    protected function get_images() {
        return Image::get_all(0);
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
