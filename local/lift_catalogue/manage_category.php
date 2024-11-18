<?php
require_once('../../config.php');
require_once('locallib.php');
require_once($CFG->dirroot . '/local/lift_catalogue/forms/category_form.php');
require_once($CFG->dirroot . '/local/lift_catalogue/forms/course_form.php');

require_login();

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/local/lift_catalogue/manage_category.php');
$PAGE->set_title(get_string('managecategories', 'local_lift_catalogue'));
//$PAGE->set_heading(get_string('managecategories', 'local_lift_catalogue'));

$category_id = optional_param('category_id', 0, PARAM_INT);
$editcategory_id = optional_param('edit', null, PARAM_INT);
$delete_category_id = optional_param('delete_category_id', null, PARAM_INT);

$course_id = optional_param('course_id', 0, PARAM_INT);
$editcourse_id = optional_param('editcourse', null, PARAM_INT);
$delete_course_id = optional_param('delete_course_id', null, PARAM_INT);

//Delete category
$deletecategory = null;
if ($delete_category_id) {
    if ($DB->record_exists('lift_catalogue_categories', ['id' => $delete_category_id])) {
        $DB->delete_records('lift_catalogue_categories', ['id' => $delete_category_id]);
        redirect(new moodle_url('/local/lift_catalogue/manage_category.php'));
    }
}

//Delete course
$deletecourse = null;
if ($delete_course_id) {
    if ($DB->record_exists('lift_catalogue_courses', ['id' => $delete_course_id])) {
        $DB->delete_records('lift_catalogue_courses', ['id' => $delete_course_id]);
        redirect(new moodle_url('/local/lift_catalogue/manage_category.php', ['category_id' => $category_id]), get_string('courseupdated', 'local_lift_catalogue'));
    }
}

// Fetch category for editing if edit param is provided
$editcategory = null;
if ($editcategory_id) {
    $editcategory = $DB->get_record('lift_catalogue_categories', array('id' => $editcategory_id), '*', MUST_EXIST);
}
$categoryform = new category_form(null, ['editcategory' => $editcategory]);

// Handle category form submission
if ($categoryform->is_cancelled()) {
    redirect(new moodle_url('/local/lift_catalogue/manage_category.php'));
} else if ($data = $categoryform->get_data()) {
    $record = new stdClass();
    $record->name = $data->name;
    $record->parent_id = $data->parentid ?? null;
    $record->status = 1; // Default status

    if (!empty($data->categoryid)) {
        // Editing existing category
        $record->id = $data->categoryid;
        $record->timemodified = time();
        $DB->update_record('lift_catalogue_categories', $record);
        redirect(new moodle_url('/local/lift_catalogue/manage_category.php'), get_string('categoryupdated', 'local_lift_catalogue'));
    } else {
        // Adding a new category
        $record->timecreated = time();
        $DB->insert_record('lift_catalogue_categories', $record);
        redirect(new moodle_url('/local/lift_catalogue/manage_category.php'), get_string('categoryadded', 'local_lift_catalogue'));
    }
}
///---------------------------------------------------//

// Fetch course for editing if the edit param is provided
$editcourse = null;
if ($editcourse_id) {
    $editcourse = $DB->get_record('lift_catalogue_courses', array('id' => $editcourse_id), '*', MUST_EXIST);
}
$courseform = new course_form(null, ['editcourse' => $editcourse, 'category_id' => $category_id]);
// Handle course form submission
if ($courseform->is_cancelled()) {
    redirect(new moodle_url('/local/lift_catalogue/manage_category.php'));
} else if ($data = $courseform->get_data()) {
    $record = new stdClass();
    $record->name = $data->name;
    $record->description = $data->description;
    $record->duration = $data->duration;
    $record->code = $data->code;
    $record->pin = $data->pin;
    $record->type = $data->type;
    $record->status = $data->status;
    $record->category_id = $data->category_id;

    if (!empty($data->courseid)) {
        // Editing existing course
        $record->id = $data->courseid;
        $record->timemodified = time();
        $DB->update_record('lift_catalogue_courses', $record);
    } else {
        // Adding a new course
        $record->timecreated = time();
        $record->timemodified = time(); // Ensure 'timemodified' is set
        $data->courseid = $DB->insert_record('lift_catalogue_courses', $record);
    }

    // Adding new course photo.
    if (!empty($data->image)) {
        $draftitemid = file_get_submitted_draft_itemid('image');

        file_save_draft_area_files($draftitemid, $context->id, 'local_lift_catalogue', 'course_images', $data->courseid);

        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'local_lift_catalogue', 'course_images', $data->courseid, false , false);
        if ($files) {
            $file = reset($files); // Get the first file (assuming only one file is saved)
            $filename = $file->get_filename();
        }

        $data->id = $data->courseid;
        $data->image = $filename;

        $DB->update_record('lift_catalogue_courses', $data);
    }

    if (!empty($data->courseid)) {
        redirect(new moodle_url('/local/lift_catalogue/manage_category.php', ['category_id' => $record->category_id]), get_string('courseupdated', 'local_lift_catalogue'));
    } else redirect(new moodle_url('/local/lift_catalogue/manage_category.php', ['category_id' => $record->category_id]), get_string('courseadded', 'local_lift_catalogue'));
    
}

echo $OUTPUT->header();
//echo $OUTPUT->heading(get_string('managecategories', 'local_lift_catalogue'));

echo html_writer::start_tag('div', array('class' => 'columns-2 viewmode-combined grid-start grid-row-r d-flex flex-wrap row'));

    display_category_catalogue($category_id);
    display_course_catalogue($category_id);

echo html_writer::end_tag('div');

//////////////////////////////////////////////////////

echo html_writer::start_tag('div', ['class' => 'row mt-3']);

    // Output category management section (Add/Edit)
    echo html_writer::start_tag('div', ['class' => 'col-12 col-lg-6']);
        $categoryform->display();
    echo html_writer::end_tag('div');

    echo html_writer::start_tag('div', ['class' => 'col-12 col-lg-6']);
    // Instantiate course form
        $courseform->display();
    echo html_writer::end_tag('div');

echo html_writer::end_tag('div');

echo $OUTPUT->footer();
?>
