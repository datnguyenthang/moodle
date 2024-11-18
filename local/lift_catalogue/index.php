<?php
require_once('../../config.php');
require_once('lib.php');
require_once('locallib.php');
require_login();

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/local/lift_catalogue/index.php');
$PAGE->set_title(get_string('pluginname', 'local_lift_catalogue'));
$PAGE->set_heading(get_string('pluginname', 'local_lift_catalogue'));

$PAGE->requires->js_call_amd('local_lift_catalogue/index', 'init');

$PAGE->set_pagelayout('course');
$PAGE->navbar->add(get_string('courses', 'local_lift_catalogue'), new moodle_url('/local/lift_catalogue/index.php'));


$courseType = [];
foreach (type_of_course() as $id => $label) { 
    $courseType[] = [ 'id' => $id, 'label' => $label ];
}
/*
$courses = $DB->get_records('lift_catalogue_courses', ['pin' => 1]);
$coursesData = [];
foreach ($courses as $course) {
    $coursesData[] = [
        'imageurl' => $course->image ? $CFG->wwwroot . '/pluginfile.php/' . $context->id . '/local_lift_catalogue/course_images/' . $course->id . '/' . $course->image : 'https://via.placeholder.com/250',
        'name' => $course->name,
        'code' => $course->code,
        'duration' => $course->duration,
        'type' => type_of_course()[$course->type],
        'link' => new moodle_url('/course/view.php', ['id' => $course->id]),
    ];
}
*/
$templatecontext = [
    'courseType' => $courseType,
    //'coursesData' => $coursesData,
    'category' => get_category_paths(),
];

// Output page header
echo $OUTPUT->header();

echo $OUTPUT->render_from_template('local_lift_catalogue/index', $templatecontext);

// Output page footer
echo $OUTPUT->footer();