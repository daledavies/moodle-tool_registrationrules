<?php

require_once(__DIR__ . '/../../../config.php');

$ver = optional_param('ver', null, PARAM_TEXT);

$PAGE->set_url('/admin/tool/registrationrules/error.php');
$PAGE->set_context(context_system::instance());

echo $OUTPUT->header();

if ($ver === 'before') {
    \core\notification::warning(get_config('tool_registrationrules', 'generalbeforemessage'));
} else if ($ver === 'after') {
    \core\notification::warning(get_config('tool_registrationrules', 'generalaftermessage'));
}

echo $OUTPUT->footer();
