<?php
require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot . "/local/lift_catalogue/lib.php");
class local_lift_catalogue_external extends external_api {

    // Define the function parameters.
    public static function get_courses_parameters() {
        return new external_function_parameters(
            array(
                'page' => new external_value(PARAM_INT, 'Page number', VALUE_DEFAULT, 0),
                'limit' => new external_value(PARAM_INT, 'Number of courses per page', VALUE_DEFAULT, 12),
                'category' => new external_value(PARAM_INT, 'Category of courses '),
                'coursetype' => new external_value(PARAM_INT, 'Type of course 1:online, 2:offline, 3:pledge'),
            )
        );
    }

    // The function itself.
    public static function get_courses($page, $limit, $category, $coursetype) {
        global $DB, $CFG;

        $params = self::validate_parameters(self::get_courses_parameters(), array(
            'page' => $page,
            'limit' => $limit,
            'category' => $category,
            'coursetype' => $coursetype,
        ));

        $offset = $params['page'] * $params['limit'];
        
        // Build SQL conditions and parameters array
        $conditions = [];
        $sqlparams = [];

        // Add conditions for category and coursetype if specified
        if ($params['category'] != 0) {
            $conditions[] = 'category_id = :category';
            $sqlparams['category'] = $params['category'];
        }
        if ($params['coursetype'] != 0) {
            $conditions[] = 'type = :coursetype';
            $sqlparams['coursetype'] = $params['coursetype'];
        }
        $sqlwhere = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        // Get total courses.
        $totalcourses = $DB->count_records_select('lift_catalogue_courses', implode(' AND ', $conditions), $sqlparams);

        // Get the courses for this page.
        $courses = $DB->get_records_sql(
            "SELECT * FROM {lift_catalogue_courses} $sqlwhere ORDER BY id ASC",
            $sqlparams,
            $offset,
            $limit
        );

        $coursesData = [];
        foreach ($courses as $course) {
            $coursesData[] = [
                'id' => $course->id,
                'imageurl' => $course->image ? $CFG->wwwroot . '/pluginfile.php/' . context_system::instance()->id . '/local_lift_catalogue/course_images/' . $course->id . '/' . $course->image : 'https://dummyimage.com/250x250',
                'name' => $course->name,
                'code' => $course->code,
                'duration' => $course->duration,
                'type' => type_of_course()[$course->type],
                'link' => (new moodle_url('/course/view.php', ['id' => $course->id]))->out(),
            ];
        }

        // Calculate total pages.
        $totalpages = ceil($totalcourses / $params['limit']);

        return array(
            'courses' => $coursesData,
            'totalpages' => $totalpages,
            'currentpage' => $params['page'],
        );
    }

    // Define the return values.
    public static function get_courses_returns() {
        return new external_single_structure(
            array(
                'courses' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'Course id'),
                            'imageurl' => new external_value(PARAM_URL, 'Course image URL'),
                            'name' => new external_value(PARAM_TEXT, 'Course name'),
                            'code' => new external_value(PARAM_TEXT, 'Course code'),
                            'duration' => new external_value(PARAM_TEXT, 'Course duration'),
                            'type' => new external_value(PARAM_TEXT, 'Course type'),                            
                            'link' => new external_value(PARAM_URL, 'Course link'),
                        )
                    )
                ),
                'totalpages' => new external_value(PARAM_INT, 'Total pages'),
                'currentpage' => new external_value(PARAM_INT, 'Current page'),
            )
        );
    }
}
