<?php

require_once(__DIR__ . '/../../../config.php');

$message = optional_param('message', null, PARAM_TEXT);

$PAGE->set_url('/admin/tool/registrationrules/error.php', ['message' => $message]);
$PAGE->set_context(context_system::instance());

echo $OUTPUT->header();


echo "Error: You can't create an account with this data.";

if ($message !== null) {
    echo " Reason: $message";
}

echo $OUTPUT->footer();
