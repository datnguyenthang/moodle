<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class lift_course_form extends moodleform {
    protected function definition() {
        global $DB;

        $mform = $this->_form;
        $editcourse = $this->_customdata['editcourse'] ?? null;
        $category_id = $this->_customdata['category_id'] ?? 0;

        // Form heading
        $formheading = $editcourse ? get_string('editcourse', 'local_lift_catalogue') : get_string('addcourse', 'local_lift_catalogue');
        $buttonlabel = $editcourse ? get_string('updatecourse', 'local_lift_catalogue') : get_string('addcourse', 'local_lift_catalogue');

        // Form heading
        $mform->addElement('header', 'courseheader', $formheading);

        // Course name field
        $mform->addElement('text', 'coursename', get_string('coursename', 'local_lift_catalogue'));
        $mform->setType('coursename', PARAM_TEXT);
        $mform->addRule('coursename', null, 'required', null, 'client');

        // Course description field
        $mform->addElement('textarea', 'description', get_string('coursedescription', 'local_lift_catalogue'), 'wrap="virtual" rows="5" cols="50"');
        $mform->setType('description', PARAM_TEXT);

        // Course duration field (in hours)
        $mform->addElement('text', 'duration', get_string('courseduration', 'local_lift_catalogue') . ' (' . get_string('hours', 'local_lift_catalogue') . ')');
        $mform->setType('duration', PARAM_INT);

        // Course code (must be unique)
        $mform->addElement('text', 'code', get_string('coursecode', 'local_lift_catalogue'));
        $mform->setType('code', PARAM_TEXT);
        $mform->addRule('code', null, 'required', null, 'client');

        if ($editcourse) {
            $mform->addElement('hidden', 'courseid', $editcourse->id);
            $mform->setType('courseid', PARAM_INT);
        }

        // Course pin (true/false value)
        $mform->addElement('selectyesno', 'pin', get_string('coursepin', 'local_lift_catalogue'));
        $mform->setDefault('pin', 0);

        // Course type (three types: Online, Offline, Pledge)
        $mform->addElement('select', 'type', get_string('coursetype', 'local_lift_catalogue'), [
            1 => get_string('type_online', 'local_lift_catalogue'),
            2 => get_string('type_offline', 'local_lift_catalogue'),
            3 => get_string('type_pledge', 'local_lift_catalogue')
        ]);
        $mform->setType('type', PARAM_INT);

        // Course status
        $mform->addElement('selectyesno', 'status', get_string('coursestatus', 'local_lift_catalogue'));
        $mform->setDefault('status', 1);

        // Category ID dropdown using category paths
        $categories = get_category_paths();
        $category_options = ['' => get_string('selectcategory', 'local_lift_catalogue')];
        foreach ($categories as $category) {
            $category_options[$category->id] = $category->path;
        }
        $mform->addElement('select', 'category_id', get_string('coursecategory', 'local_lift_catalogue'), $category_options);
        $mform->setType('category_id', PARAM_INT);
        $mform->addRule('category_id', null, 'required', null, 'client');

        // Image upload
        $mform->addElement('filepicker', 'image', get_string('courseimage', 'local_lift_catalogue'), null, [
            'maxbytes' => 10485760,
            'accepted_types' => ['image']
        ]);

        // Add action buttons
        $this->add_action_buttons(true, $buttonlabel);
    }

    // Custom validation
    function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);

        // Check for unique course code
        $course_code = $data['code'];
        $existing_course = $DB->get_record('lift_catalogue_courses', ['code' => $course_code], 'id');
        if ($existing_course && (!$data['courseid'] || $existing_course->id != $data['courseid'])) {
            $errors['code'] = get_string('coursecodeexists', 'local_lift_catalogue');
        }

        return $errors;
    }
}
<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class lift_course_form extends moodleform {
    protected function definition() {
        global $DB;

        $mform = $this->_form;
        $editcourse = $this->_customdata['editcourse'] ?? null;
        $category_id = $this->_customdata['category_id'] ?? 0;

        // Form heading
        $formheading = $editcourse ? get_string('editcourse', 'local_lift_catalogue') : get_string('addcourse', 'local_lift_catalogue');
        $buttonlabel = $editcourse ? get_string('updatecourse', 'local_lift_catalogue') : get_string('addcourse', 'local_lift_catalogue');

        // Form heading
        $mform->addElement('header', 'courseheader', $formheading);

        // Course name field
        $mform->addElement('text', 'coursename', get_string('coursename', 'local_lift_catalogue'));
        $mform->setType('coursename', PARAM_TEXT);
        $mform->addRule('coursename', null, 'required', null, 'client');

        // Course description field
        $mform->addElement('textarea', 'description', get_string('coursedescription', 'local_lift_catalogue'), 'wrap="virtual" rows="5" cols="50"');
        $mform->setType('description', PARAM_TEXT);

        // Course duration field (in hours)
        $mform->addElement('text', 'duration', get_string('courseduration', 'local_lift_catalogue') . ' (' . get_string('hours', 'local_lift_catalogue') . ')');
        $mform->setType('duration', PARAM_INT);

        // Course code (must be unique)
        $mform->addElement('text', 'code', get_string('coursecode', 'local_lift_catalogue'));
        $mform->setType('code', PARAM_TEXT);
        $mform->addRule('code', null, 'required', null, 'client');

        if ($editcourse) {
            $mform->addElement('hidden', 'courseid', $editcourse->id);
            $mform->setType('courseid', PARAM_INT);
        }

        // Course pin (true/false value)
        $mform->addElement('selectyesno', 'pin', get_string('coursepin', 'local_lift_catalogue'));
        $mform->setDefault('pin', 0);

        // Course type (three types: Online, Offline, Pledge)
        $mform->addElement('select', 'type', get_string('coursetype', 'local_lift_catalogue'), [
            1 => get_string('type_online', 'local_lift_catalogue'),
            2 => get_string('type_offline', 'local_lift_catalogue'),
            3 => get_string('type_pledge', 'local_lift_catalogue')
        ]);
        $mform->setType('type', PARAM_INT);

        // Course status
        $mform->addElement('selectyesno', 'status', get_string('coursestatus', 'local_lift_catalogue'));
        $mform->setDefault('status', 1);

        // Category ID dropdown using category paths
        $categories = get_category_paths();
        $category_options = ['' => get_string('selectcategory', 'local_lift_catalogue')];
        foreach ($categories as $category) {
            $category_options[$category->id] = $category->path;
        }
        $mform->addElement('select', 'category_id', get_string('coursecategory', 'local_lift_catalogue'), $category_options);
        $mform->setType('category_id', PARAM_INT);
        $mform->addRule('category_id', null, 'required', null, 'client');

        // Image upload
        $mform->addElement('filepicker', 'image', get_string('courseimage', 'local_lift_catalogue'), null, [
            'maxbytes' => 10485760,
            'accepted_types' => ['image']
        ]);

        // Add action buttons
        $this->add_action_buttons(true, $buttonlabel);
    }

    // Custom validation
    function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);

        // Check for unique course code
        $course_code = $data['code'];
        $existing_course = $DB->get_record('lift_catalogue_courses', ['code' => $course_code], 'id');
        if ($existing_course && (!$data['courseid'] || $existing_course->id != $data['courseid'])) {
            $errors['code'] = get_string('coursecodeexists', 'local_lift_catalogue');
        }

        return $errors;
    }
}
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
$PAGE->set_heading(get_string('managecategories', 'local_lift_catalogue'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('managecategories', 'local_lift_catalogue'));

$course_id = optional_param('course_id', 0, PARAM_INT);
$category_id = optional_param('category_id', 0, PARAM_INT);
$editcategory_id = optional_param('edit', null, PARAM_INT);
$editcourse_id = optional_param('editcourse', null, PARAM_INT);

// Handle category form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['categoryname'])) {
    $data = new stdClass();
    $data->name = required_param('categoryname', PARAM_TEXT);
    $data->parent_id = optional_param('parentid', null, PARAM_INT);
    $data->status = 1;  // Default status

    // Check if we are editing an existing category
    $categoryid = optional_param('categoryid', null, PARAM_INT);
    if ($categoryid) {
        $data->id = $categoryid;
        $data->timemodified = time();
        $DB->update_record('lift_catalogue_categories', $data);
        redirect(new moodle_url('/local/lift_catalogue/manage_category.php'), get_string('categoryupdated', 'local_lift_catalogue'));
    } else {
        // Add a new category
        $data->timecreated = time();
        $DB->insert_record('lift_catalogue_categories', $data);
        redirect(new moodle_url('/local/lift_catalogue/manage_category.php'), get_string('categoryadded', 'local_lift_catalogue'));
    }
}

// Handle course form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['coursename'])) {
    $data = new stdClass();
    $data->name = required_param('coursename', PARAM_TEXT);
    $data->description = optional_param('description', '', PARAM_TEXT);
    $data->duration = optional_param('duration', '', PARAM_TEXT);
    $data->code = optional_param('code', '', PARAM_TEXT);
    $data->pin = required_param('pin', PARAM_INT);
    $data->type = required_param('type', PARAM_INT);
    $data->status = optional_param('status', 0, PARAM_INT);
    $data->category_id = required_param('category_id', PARAM_INT);

    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $image = file_save_draft_area_files(
            file_get_submitted_draft_itemid('image'),
            context_system::instance()->id,
            'local_lift_catalogue',
            'course_images',
            0,
            ['maxbytes' => 10485760, 'accepted_types' => ['image']]
        );
        $data->image = $image;
    }

    // Check if we are editing an existing course
    $courseid = optional_param('courseid', null, PARAM_INT);
    if ($courseid) {
        $data->id = $courseid;
        $data->timemodified = time();
        $DB->update_record('lift_catalogue_courses', $data);
        redirect(new moodle_url('/local/lift_catalogue/manage_category.php', ['category' => $data->category_id]), get_string('courseupdated', 'local_lift_catalogue'));
    } else {
        // Add a new course
        $data->timecreated = time();
        $DB->insert_record('lift_catalogue_courses', $data);
        redirect(new moodle_url('/local/lift_catalogue/manage_category.php', ['category' => $data->category_id]), get_string('courseadded', 'local_lift_catalogue'));
    }
}

echo html_writer::start_tag('div', array('class' => 'columns-2 viewmode-combined grid-start grid-row-r d-flex flex-wrap row'));

display_category_catalogue($category_id);
display_course_catalogue($category_id);

echo html_writer::end_tag('div');

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
    $record->name = $data->categoryname;
    $record->parent_id = $data->parentid;
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
$courseform = new lift_course_form(null, ['editcourse' => $editcourse, 'category_id' => $category_id]);
// Handle course form submission
if ($courseform->is_cancelled()) {
    redirect(new moodle_url('/local/lift_catalogue/manage_category.php'));
} else if ($data = $courseform->get_data()) {
    $record = new stdClass();
    $record->name = $data->coursename;
    $record->description = $data->description;
    $record->duration = $data->duration;
    $record->code = $data->code;
    $record->pin = $data->pin;
    $record->type = $data->type;
    $record->status = $data->status;
    $record->category_id = $data->category_id;

    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $image = file_save_draft_area_files(
            file_get_submitted_draft_itemid('image'),
            context_system::instance()->id,
            'local_lift_catalogue',
            'course_images',
            0,
            ['maxbytes' => 10485760, 'accepted_types' => ['image']]
        );
        $record->image = $image;
    }

    if (!empty($data->courseid)) {
        // Editing existing course
        $record->id = $data->courseid;
        $record->timemodified = time();
        $DB->update_record('lift_catalogue_courses', $record);
        redirect(new moodle_url('/local/lift_catalogue/manage_category.php', ['category' => $record->category_id]), get_string('courseupdated', 'local_lift_catalogue'));
    } else {
        // Adding a new course
        $record->timecreated = time();
        $DB->insert_record('lift_catalogue_courses', $record);
        redirect(new moodle_url('/local/lift_catalogue/manage_category.php', ['category' => $record->category_id]), get_string('courseadded', 'local_lift_catalogue'));
    }
}

echo html_writer::start_tag('div', ['class' => 'row']);

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
