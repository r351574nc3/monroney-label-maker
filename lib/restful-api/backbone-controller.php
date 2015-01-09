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
        $retval = [
                "success" => true,
                "name"    => is_null($user) ? '' : $user->get_username(),
                "id"      => is_null($user) ? '' : $user->get_id(),
                "secret"  => is_null($user) ? '' : $user->get_secret(),
                "labelgen_images"  => is_null($user) ? [] : $this->get_images($user),
                "labelgen_logos"   => is_null($user) ? [] : $this->get_logos($user),
                "labelgen_options" => [],
                "labelgen_labels"  => is_null($user) ? [] : $this->get_labels($user)
        ];

        if (!is_null($user)) {
            foreach ($this->get_options($user) as $key => $value) {
                $retval[$key] = $value;
                array_push($retval['labelgen_options'], $value);
            }
        }
        return $retval;
    }

    protected function get_labels($user) {
        return Label::query_for($user);
    }

    protected function get_images($user) {
        return Image::query_for($user);
    }

    protected function get_logos($user) {
        return Logo::query_for($user);
    }

    protected function get_options($user) {
        return Option::query_for($user);
    }
}
?>
