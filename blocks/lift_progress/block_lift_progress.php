<?php
// This file is part of My Progress block for Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * My Progress block definition class
 *
 * @package    block_lift_progress
 * @copyright  2023 e-Learning – Conseils & Solutions <http://www.luiggisansonetti.fr/conseils>
 * @author     Willian Mano <willianmanoaraujo@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_lift_progress extends block_base {
    /**
     * Sets the block title
     *
     * @return void
     *
     * @throws coding_exception
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_lift_progress');
    }

    /**
     * Controls the block title based on instance configuration
     *
     * @return bool
     */
    public function specialization() {
        $title = isset($this->config->title) ? trim($this->config->title) : '';
        if (!empty($title)) {
            $this->title = format_string($this->config->title);
        }
    }

    /**
     * Creates the blocks main content
     *
     * @return \stdClass
     *
     * @throws coding_exception
     * @throws dml_exception
     */
    public function get_content() {
        global $USER, $DB, $OUTPUT;

        if ($this->content !== null) {
            return $this->content;
        }

        // Initialize the block content
        $this->content = new stdClass();
        $this->content->text = '';

        // Fetch enrolled courses with their progress for the logged-in user
        $userid = $USER->id;
        $progressData = $this->get_user_course_progress($userid);

        // Build the HTML content
        $html = '
        <div class="box-widget card-body p-0">
            <div class="position-relative">
                <div class="filters mb-3 row">

                        <div class="form-group col-md-6">
                            <label for="course-type">'.get_string('coursetype', 'block_lift_progress').'</label>
                            <select id="courseTypeFilter" name="type" class="form-select">
                                <option value="">'.get_string('all').'</option>
                                <option value="mandatory">'.get_string('mandatory', 'block_lift_progress').'</option>
                                <option value="optional">'.get_string('optional', 'block_lift_progress').'</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="sort-order">'.get_string('sortbycompletion', 'block_lift_progress').'</label>
                            <select id="sortCompletion" name="sort" class="form-select">
                                <option value="asc">'.get_string('ascending', 'block_lift_progress').'</option>
                                <option value="desc">'.get_string('descending', 'block_lift_progress').'</option>
                            </select>
                        </div>
                </div>
                <div id="courseListProgress">
                    <!-- Course items will be inserted here by JavaScript -->
                </div>
            ';
/*
        foreach ($progressData as $course) {
            $completion_percentage = round($course->progress / 10) * 10;
            $enddate = $course->enddate ? date('d/m/Y', $course->enddate) : get_string('no');

            // Determine the border class for the left and right parts based on the completion percentage
            $circle_left_class = '';
            $circle_right_class = '';

            if ($completion_percentage <= 10) {
                $circle_left_class = 'border-danger'; // Red
                $circle_right_class = 'border-danger'; // Red
            } elseif ($completion_percentage <= 20) {
                $circle_left_class = 'border-warning'; // Orange
                $circle_right_class = 'border-warning'; // Orange
            } elseif ($completion_percentage <= 30) {
                $circle_left_class = 'border-warning'; // Orange
                $circle_right_class = 'border-warning'; // Orange
            } elseif ($completion_percentage <= 40) {
                $circle_left_class = 'border-warning'; // Yellow-Orange
                $circle_right_class = 'border-warning'; // Yellow-Orange
            } elseif ($completion_percentage <= 50) {
                $circle_left_class = 'border-warning'; // Yellow
                $circle_right_class = 'border-warning'; // Yellow
            } elseif ($completion_percentage <= 60) {
                $circle_left_class = 'border-success'; // Yellow-Green
                $circle_right_class = 'border-success'; // Yellow-Green
            } elseif ($completion_percentage <= 70) {
                $circle_left_class = 'border-success'; // Green-Yellow
                $circle_right_class = 'border-success'; // Green-Yellow
            } elseif ($completion_percentage <= 80) {
                $circle_left_class = 'border-success'; // Green
                $circle_right_class = 'border-success'; // Green
            } elseif ($completion_percentage <= 90) {
                $circle_left_class = 'border-success'; // Bright Green
                $circle_right_class = 'border-success'; // Bright Green
            } else {
                $circle_left_class = 'border-success'; // Light Green
                $circle_right_class = 'border-success'; // Light Green
            }

            $html .= '
            <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                <div>
                    <a href="course/view.php?id=' . $course->id . '" class="mb-1 fs-3 fw-semibold">' . format_string($course->fullname) . '</a>
                    <p class="small mb-0"><em class="fw-semibold">(' . format_string($course->categoryname) . ')</em></p>
                    <p class="date small mb-0">' . get_string('enddate') . ': ' . $enddate . '</p>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <div class="progress circle" data-percentage="' . $completion_percentage . '">
                        <span class="progress-left">
                            <span class="progress-bar ' . $circle_left_class . '"></span>
                        </span>
                        <span class="progress-right">
                            <span class="progress-bar ' . $circle_right_class . '"></span>
                        </span>
                        <div class="progress-value">
                            <span>' . $course->progress . '%</span>
                        </div>
                    </div>
                </div>
            </div>';
        }
*/
        $html .= '
            </div>
        </div>
    ';

    $html .= '
        <script>
            const progressData = ' . json_encode($progressData) . ';

            function getClassByCompletion(completion_percentage) {
                let circle_class = "";
                if (completion_percentage <= 10) {
                    circle_class  = "border-danger";
                } else if (completion_percentage <= 20) {
                    circle_class  = "border-warning";
                } else if (completion_percentage <= 30) {
                    circle_class  = "border-warning";
                } else if (completion_percentage <= 40) {
                    circle_class  = "border-warning";
                } else if (completion_percentage <= 50) {
                    circle_class  = "border-warning";
                } else if (completion_percentage <= 60) {
                    circle_class  = "border-success";
                } else if (completion_percentage <= 70) {
                    circle_class  = "border-success";
                } else if (completion_percentage <= 80) {
                    circle_class  = "border-success";
                } else if (completion_percentage <= 90) {
                    circle_class  = "border-success";
                } else {
                    circle_class  = "border-success";
                }
                return circle_class;
            }

            function renderCourses(courses) {
                const courseList = document.getElementById("courseListProgress");
                courseList.innerHTML = "";

                courses.forEach(course => {
                    const completion_percentage = Math.round(course.progress / 10) * 10;
                    const circle_class = getClassByCompletion(completion_percentage);

                    const end_date_text = "' . get_string('enddate') . '";
                    const end_date = new Date(course.enddate * 1000).toLocaleDateString(); ;

                    const courseHtml = `
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                            <div>
                                <a href="course/view.php?id=${course.id}" class="mb-1 fs-3 fw-semibold">${course.fullname}</a>
                                <p class="small mb-0"><em class="fw-semibold">(${course.categoryname})</em></p>
                                <p class="date small mb-0">${end_date_text}: ${end_date}</p>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="progress circle" data-percentage="${completion_percentage}">
                                    <span class="progress-left">
                                        <span class="progress-bar ${circle_class}"></span>
                                    </span>
                                    <span class="progress-right">
                                        <span class="progress-bar ${circle_class}"></span>
                                    </span>
                                    <div class="progress-value">
                                        <span>${course.progress}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                    courseList.innerHTML += courseHtml;
                });
            }

            function filterAndSortCourses() {
                const courseTypeFilter = document.getElementById("courseTypeFilter").value;
                const sortCompletion = document.getElementById("sortCompletion").value;

                let filteredCourses = progressData;

                if (courseTypeFilter !== "all") {
                    filteredCourses = filteredCourses.filter(course => course.type === courseTypeFilter);
                }

                if (sortCompletion === "asc") {
                    filteredCourses.sort((a, b) => a.progress - b.progress);
                } else if (sortCompletion === "desc") {
                    filteredCourses.sort((a, b) => b.progress - a.progress);
                }

                renderCourses(filteredCourses);
            }

            document.getElementById("courseTypeFilter").addEventListener("change", filterAndSortCourses);
            document.getElementById("sortCompletion").addEventListener("change", filterAndSortCourses);

            // Initial render
            renderCourses(progressData);
        </script>
    ';

        $this->content->text = $html;

        return $this->content;
    }

    /**
     * Get user's enrolled courses and their completion progress using Moodle's built-in completion API.
     */
    private function get_user_course_progress($userid) {
        global $DB;

        $courses = enrol_get_users_courses($userid, true, ['id', 'shortname', 'fullname', 'category', 'enddate']);
        $courseProgressData = [];

        foreach ($courses as $course) {
            $category = $DB->get_record('course_categories', array('id' => $course->category));
            // Get the course completion data using Moodle's core completion API
            $completion = new completion_info($course);
            if ($completion->is_enabled()) {
                $progress = \core_completion\progress::get_course_progress_percentage($course, $userid);

                // Add course data to the array
                $courseProgressData[] = (object)[
                    'id' => $course->id,
                    'fullname' => $course->fullname,
                    'shortname' => $course->shortname,
                    'progress' => $progress ? round($progress) : 0,
                    'enddate' => $course->enddate,
                    'categoryname' => $category->name
                ];
            }
        }

        return $courseProgressData;
    }
}
