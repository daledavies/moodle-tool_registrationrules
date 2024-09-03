<?php

namespace tool_registrationrules\local;

class admin_page_rule_plugins extends \admin_externalpage {

    /** @var string the name of plugin subtype */
    private $subtype = '';

    /**
     * The constructor - calls parent constructor
     *
     * @param string $subtype
     */
    public function __construct($subtype) {
        $this->subtype = $subtype;
        $url = new \moodle_url('/admin/tool/registrationrules/managerules.php', array('subtype'=>$subtype));
        parent::__construct('manage' . $subtype . 'plugins',
                            get_string('manageregistrationruleplugins', 'tool_registrationrules'),
                            $url);
    }

    /**
     * Search plugins for the specified string
     *
     * @param string $query The string to search for
     * @return array
     */
    public function search($query) {
        if ($result = parent::search($query)) {
            return $result;
        }

        $found = false;

        foreach (\core_component::get_plugin_list($this->subtype) as $name => $notused) {
            if (strpos(\core_text::strtolower(get_string('pluginname', $this->subtype . '_' . $name)),
                    $query) !== false) {
                $found = true;
                break;
            }
        }
        if ($found) {
            $result = new \stdClass();
            $result->page     = $this;
            $result->settings = array();
            return array($this->name => $result);
        } else {
            return array();
        }
    }
}