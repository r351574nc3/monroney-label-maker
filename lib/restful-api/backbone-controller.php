<?php

namespace labelgen;

require_once(LABEL_MAKER_ROOT.'/models/labelgen-user.php');
require_once(LABEL_MAKER_ROOT.'/models/image.php');
require_once(LABEL_MAKER_ROOT.'/models/logo.php');
require_once(LABEL_MAKER_ROOT.'/models/option.php');

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
                "options"          => $this->get_options(),
                "labelgen_labels"  => []
        ]);
    }

    protected function get_images() {
        return Image::get_all(0);
    }

    protected function get_logos() {
        return Logo::get_all(0);
    }

    protected function get_options() {
        return Option::get_all(0);
    }
}
?>
