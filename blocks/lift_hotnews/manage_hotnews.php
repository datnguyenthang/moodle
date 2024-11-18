<?php
// This file is part of Moodle - http://moodle.org/
// Moodle is free software: you can redistribute it and/or modify it under the terms of the GNU GPL.

require_once('../../config.php'); // Always include the Moodle config file.
require_once('hotnews_form.php');
require_login(); // Ensure the user is logged in.

$context = context_system::instance();
require_capability('moodle/site:config', $context); // Ensure the user has the right capability to manage hot news.

// Page settings.
$PAGE->set_url(new moodle_url('/block/lift_hotnews/manage_hotnews.php'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('manage_hotnews', 'block_lift_hotnews'));
$PAGE->set_heading(get_string('manage_hotnews', 'block_lift_hotnews'));

// Handle form submission for adding/updating/deleting news.
if ($data = data_submitted() && confirm_sesskey()) {
    if (!empty($data->action)) {
        if ($data->action == 'add' || $data->action == 'edit') {    
            // Add or edit hot news.
            $newsdata = new stdClass();
            //$newsdata->id = optional_param('id', null, PARAM_INT);
            $newsdata->title = required_param('title', PARAM_TEXT);
            $newsdata->content = required_param('content', PARAM_RAW);
            $newsdata->startdate = strtotime(required_param('startdate', PARAM_TEXT));
            $newsdata->enddate = strtotime(required_param('enddate', PARAM_TEXT));

            if ($data->action == 'edit') {
                $newsdata->id = required_param('id', PARAM_INT);
                $DB->update_record('lift_hotnews', $newsdata);
            } else {
                $DB->insert_record('lift_hotnews', $newsdata);
            }
        } 
    }
}

// Output page.
echo $OUTPUT->header();

$mform = new hotnews_form();

// Check if we are editing an existing news item.
$action = optional_param('action', '', PARAM_ALPHA);
if ($action === 'edit') {
    $id = required_param('id', PARAM_INT);
    if ($newsitem = $DB->get_record('lift_hotnews', ['id' => $id])) {
        $newsitem->content = ['text' => $newsitem->content, 'format' => FORMAT_HTML];
        $newsitem->action = 'edit';
        $mform->set_data($newsitem);
    }
} elseif ($action == 'delete') {
    // Delete hot news.
    $id = required_param('id', PARAM_INT);
    $DB->delete_records('lift_hotnews', ['id' => $id]);
}

if ($mform->is_cancelled()) {
    // Handle form cancel operation.
} else if ($data = $mform->get_data()) {
    // Handle form submission.
    $record = new stdClass();
    $record->title = $data->title;
    $record->content = $data->content['text'];
    $record->startdate = $data->startdate;
    $record->enddate = $data->enddate;

    $DB->insert_record('lift_hotnews', $record);

    redirect(new moodle_url('/blocks/lift_hotnews/manage_hotnews.php'));
} else {
    // Display the form.
    $mform->display();
}

////////////////////////////////////////////////////////////////////////////////////////////////

// Fetch all news items.
$newsitems = $DB->get_records('lift_hotnews');

echo '<table class="table">
    <tr>
        <th>' . get_string('title', 'block_lift_hotnews') . '</th>
        <th>' . get_string('startdate', 'block_lift_hotnews') . '</th>
        <th>' . get_string('enddate', 'block_lift_hotnews') . '</th>
        <th>' . get_string('actions', 'block_lift_hotnews') . '</th>
    </tr>';

foreach ($newsitems as $newsitem) {
    echo '<tr>
        <td>' . format_string($newsitem->title) . '</td>
        <td>' . userdate($newsitem->startdate) . '</td>
        <td>' . userdate($newsitem->enddate) . '</td>
        <td>
            <a href="?id=' . $newsitem->id . '&action=edit">' . get_string('edit', 'block_lift_hotnews') . '</a>
            <a href="?id=' . $newsitem->id . '&action=delete" onclick="return confirm(\'' . get_string('confirmdelete', 'block_lift_hotnews') . '\');">' . get_string('delete', 'block_lift_hotnews') . '</a>
        </td>
    </tr>';
}

echo '</table>';

echo $OUTPUT->footer();
