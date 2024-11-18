<?php
require_once("$CFG->libdir/formslib.php");

class hotnews_form extends moodleform {
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'hotnewsheader', get_string('addnews', 'block_lift_hotnews'));

        // Add hidden fields
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_RAW);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'action', 'add');
        $mform->setType('action', PARAM_RAW);

        // Add title field
        $mform->addElement('text', 'title', get_string('title', 'block_lift_hotnews'), array('size' => '80'));
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', null, 'required', null, 'client');

        // Add content field
        $mform->addElement('editor', 'content', get_string('content', 'block_lift_hotnews'));
        $mform->setType('content', PARAM_RAW);
        $mform->addRule('content', null, 'required', null, 'client');

        // Add start date field
        $mform->addElement('date_time_selector', 'startdate', get_string('startdate', 'block_lift_hotnews'));
        $mform->addRule('startdate', null, 'required', null, 'client');

        // Add end date field
        $mform->addElement('date_time_selector', 'enddate', get_string('enddate', 'block_lift_hotnews'));
        $mform->addRule('enddate', null, 'required', null, 'client');

        // Add submit button
        $this->add_action_buttons(false, get_string('save'));
    }
}
