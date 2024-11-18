<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/filelib.php');

/**
 * Serve files from the local lift_catalogue plugin.
 *
 * @param string $filearea The file area (e.g., 'course_images').
 * @param int $courseid The course ID associated with the file.
 * @param int $itemid The ID of the item (course ID) that the file is related to.
 * @param string $filename The name of the file to be served.
 * @param string $filepath The file path.
 * @param array $options Additional options.
 * @return void
 */
function local_lift_catalogue_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    global $DB;

    // Ensure the context is correct (adjust if needed)
    if ($context->contextlevel != CONTEXT_SYSTEM) {
        return false;
    }

    // Check if the file area is valid
    if ($filearea !== 'course_images') {
        return false;
    }

    // Ensure the user is logged in
    require_login();

    // Get file storage instance
    $fs = get_file_storage();

    // Extract itemid, filepath, and filename from $args
    $itemid = array_shift($args); // The item ID (usually the course ID)
    $filepath = '/' . implode('/', array_slice($args, 0, -1)) . '/'; // Construct the filepath
    $filename = array_pop($args); // Get the filename

    // Retrieve the file from Moodle file storage
    $file = $fs->get_file($context->id, 'local_lift_catalogue', $filearea, $itemid, $filepath, $filename);

    // Check if the file exists
    if (!$file || $file->is_directory()) {
        send_file_not_found();
    }

    // Serve the file
    send_stored_file($file, 0, 0, $forcedownload, $options);
}

/**
 * Get the course image URL for a given course.
 *
 * @param int $courseid The course ID.
 * @return string The full URL to the course image.
 */
function get_course_image_url($courseid) {
    global $CFG, $USER;

    // Set the context (change if necessary)
    $context = context_system::instance(); // Use course context if applicable

    // Get the file storage instance
    $fs = get_file_storage();

    // Fetch the file record from the file storage
    $file = $fs->get_file($context->id, 'local_lift_catalogue', 'course_images', $courseid, '0,0', false);

    // Check if the file exists and is visible
    if ($file && $file->is_visible()) {
        // Return the full URL to the file
        return $CFG->wwwroot . '/pluginfile.php/' . $context->id . '/local_lift_catalogue/course_images/' . $courseid . '/' . $file->get_filename();
    }
}

function type_of_course(){
    return [
        1 => get_string('type_online', 'local_lift_catalogue'),
        2 => get_string('type_offline', 'local_lift_catalogue'),
        3 => get_string('type_blended', 'local_lift_catalogue')
    ];
}