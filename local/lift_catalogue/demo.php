<?php
require_once('../../config.php');
require_once($CFG->dirroot . '/local/table_sql/classes/local/demo/demo_table_form.php');

require_login();

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/local/lift_catalogue/table.php');
$PAGE->set_title(get_string('managecategories', 'local_lift_catalogue'));
$PAGE->set_heading(get_string('managecategories', 'local_lift_catalogue'));

$tableform = new \local_table_sql\local\demo\demo_table_form();

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('managecategories', 'local_lift_catalogue'));

$tableform->out(20, true);


echo $OUTPUT->footer();