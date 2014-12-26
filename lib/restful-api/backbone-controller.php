<?php

namespace labelgen;

require_once(LABEL_MAKER_ROOT.'/models/labelgen-user.php');
require_once(LABEL_MAKER_ROOT.'/models/image.php');
require_once(LABEL_MAKER_ROOT.'/models/logo.php');
require_once(LABEL_MAKER_ROOT.'/models/option.php');
require_once(LABEL_MAKER_ROOT.'/models/label.php');

class backbone_controller {
    protected $wp_session;

    public function __construct($wp_session) {
        $this->wp_session = $wp_session;
    }


    public function get($request, $verb, $args) {
        $user = $this->wp_session['user'];
        echo("null user? " . !is_null($user));
        $retval = [
                "success" => true,
                "name"    => "",
                "id"      => "",
                "secret"  => "",
                "labelgen_images"  => $this->get_images(),
                "labelgen_logos"   => $this->get_logos(),
                "labelgen_options" => [],
                "labelgen_labels"  => !is_null($user) ? [] : $this->get_labels($user)
        ];

        foreach ($this->get_options() as $key => $value) {
            $retval[$key] = $value;
            array_push($retval['labelgen_options'], $value);
        }

        return json_encode($retval);
    }

    protected function get_labels($user) {
         Label::query_for($user);
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
