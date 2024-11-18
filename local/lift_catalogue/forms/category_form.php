<?php

require_once($CFG->libdir . '/formslib.php');
class category_form extends moodleform {
    protected function definition() {
        global $DB;

        $mform = $this->_form;

        // Get data passed when creating the form
        $editcategory = $this->_customdata['editcategory'] ?? null;

        // Fetch all categories for the parent category dropdown
        $categories = get_category_paths();

        // Determine if it's an add or edit operation
        $formheading = $editcategory ? get_string('editcategory', 'local_lift_catalogue') : get_string('addcategory', 'local_lift_catalogue');
        $buttonlabel = $editcategory ? get_string('updatecategory', 'local_lift_catalogue') : get_string('addcategory', 'local_lift_catalogue');
        $categoryname = $editcategory ? $editcategory->name : '';
        $parentid = $editcategory ? $editcategory->parent_id : null;
        $categoryid = $editcategory ? $editcategory->id : '';

        // Form heading
        $mform->addElement('header', 'categoryheader', $formheading);

        // Hidden field for category ID (used for editing)
        $mform->addElement('hidden', 'categoryid', $categoryid);
        $mform->setType('categoryid', PARAM_INT);

        // Category name field
        $mform->addElement('text', 'name', get_string('categoryname', 'local_lift_catalogue'), ['size' => 70]);
        $mform->setType('name', PARAM_TEXT);
        $mform->setDefault('name', $categoryname);
        $mform->addRule('name', null, 'required', null, 'client');

        // Parent category dropdown
        $options = ['' => get_string('none', 'local_lift_catalogue')];
        foreach ($categories as $category) {
            $options[$category->id] = $category->path;
        }

        $mform->addElement('select', 'parentid', get_string('parentcategory', 'local_lift_catalogue'), $options);
        $mform->setType('parentid', PARAM_INT);
        $mform->setDefault('parentid', $parentid);

        // Add submit button
        $this->add_action_buttons(true, $buttonlabel);
    }

    // Custom validation
    function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);

        /*
        // Check if category name is unique
        $categoryname = $data['categoryname'];
        $categoryid = $data['categoryid'] ?? 0; // In case of new category, categoryid will be empty

        // Query to find existing category with the same name, excluding the current category (for edit)
        $conditions = ['name' => $categoryname];
        if ($categoryid) {
            $conditions['id'] = $categoryid;
        }

        // Check if a category with the same name exists
        $existing = $DB->get_record_select('lift_catalogue_categories', 'name = :name AND id <> :id', ['name' => $categoryname, 'id' => $categoryid]);
        if ($existing || $existing->id != $categoryid) {
            $errors['categoryname'] = get_string('categorynameexists', 'local_lift_catalogue');
        }
        */

        return $errors;
    }
}
