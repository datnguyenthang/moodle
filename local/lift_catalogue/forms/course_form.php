<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/lift_catalogue/lib.php');
class course_form extends moodleform {
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

        $mform->addElement('hidden', 'courseid', $editcourse ? $editcourse->id : '');
        $mform->setType('courseid', PARAM_INT);

        // Course name field
        $mform->addElement('text', 'name', get_string('coursename', 'local_lift_catalogue'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

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

        // Course pin (true/false value)
        $mform->addElement('selectyesno', 'pin', get_string('coursepin', 'local_lift_catalogue'));
        $mform->setDefault('pin', 1);

        // Course type (three types: Online, Offline, Pledge)
        $mform->addElement('select', 'type', get_string('coursetype', 'local_lift_catalogue'), type_of_course());
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

        $mform->addElement('filemanager', 'image', get_string('courseimage', 'local_lift_catalogue'), null, array(
            'subdirs' => 0,
            'maxbytes' => 10485760, // 10MB
            'areamaxbytes' => 10485760, // 10MB
            'maxfiles' => 1,
            'accepted_types' => array('image')
        ));

        if ($editcourse) {
            // Retrieve existing image
            $context = context_system::instance();
            $fs = get_file_storage();
            $file_records = $fs->get_area_files($context->id, 'local_lift_catalogue', 'course_images', $editcourse->id, false, false);
        
            if ($file_records) {
                // There's at least one file, retrieve the first one
                $file_record = reset($file_records);
                
                if ($file_record) {
                    // Populate filepicker with existing image
                    $draftitemid = file_get_submitted_draft_itemid('image');
                    
                    file_prepare_draft_area($draftitemid, $context->id, 'local_lift_catalogue', 'course_images', $editcourse->id, ['maxbytes' => 10485760, 'accepted_types' => ['image']]);
                    
                    // Set the draft item id to the filepicker
                    $mform->getElement('image')->setValue($draftitemid);
                }
            }
        }
        

        if ($editcourse) {
            // Set default values from the course record
            $mform->setDefault('name', $editcourse->name);
            $mform->setDefault('description', $editcourse->description);
            $mform->setDefault('duration', $editcourse->duration);
            $mform->setDefault('code', $editcourse->code);
            $mform->setDefault('pin', $editcourse->pin);
            $mform->setDefault('type', $editcourse->type);
            $mform->setDefault('status', $editcourse->status);
            $mform->setDefault('category_id', $editcourse->category_id);
        }

        // Add action buttons
        $this->add_action_buttons(true, $buttonlabel);
    }

    // Custom validation
    function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
    
        // Check for unique course code
        $course_code = $data['code'];
        $existing_course = $DB->get_record('lift_catalogue_courses', ['code' => $course_code]);

        // Validate only when adding a new course or updating a course with a different code
        if ($existing_course) {
            if (empty($data['courseid']) || ($existing_course->id != $data['courseid'])) {
                $errors['code'] = get_string('coursecodeexists', 'local_lift_catalogue');
            }
        }

        return $errors;
    }
    
}
