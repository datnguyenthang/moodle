<?php
// This file is part of Moodle - http://moodle.org/
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
 * Purity Course Intro block main class.
 */

defined('MOODLE_INTERNAL') || die();

class block_lift_hotnews extends block_base {

    /**
     * Adds title to block instance and initializes it.
     */
    public function init() {
        $this->title = get_string('lift_hotnews', 'block_lift_hotnews');
    }

    /**
     * Gets block instance content.
     */
    public function get_content() {
        global $CFG, $DB;

        if ($this->content !== null) {
            return $this->content;
        }
        $this->content = new stdClass();

        $now = time();
        $newsitems = $DB->get_records_select('lift_hotnews', 'startdate <= ? AND enddate >= ?', array($now, $now));

        $this->content->text = '<div class="box-widget"><ul class="list-unstyled">';
        foreach ($newsitems as $newsitem) {
            $this->content->text .= '
            <li class="border-bottom mt-3">
                <div class="overflow-auto">
                    <img class="ic-new align-middle" src="'.$CFG->wwwroot.'/blocks/lift_hotnews/images/new.svg" height="25" width="25" />
                    <h6 class="d-inline align-middle">
                        <a href="#" class="d-inline align-middle" data-toggle="modal" data-btarget="#hotNewsModal'.$newsitem->id.'">
                            ' . format_text($newsitem->title) . '
                        </a>
                    </h6>
                    <div class="date small">' . substr(format_text($newsitem->content), 0, 120) . '...</div>
                </div>
                <div class="modal fade" id="hotNewsModal'.$newsitem->id.'" tabindex="-1" aria-labelledby="hotNewsModalLabel'.$newsitem->id.'" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-color-green-light">
                                <h5 class="modal-title text-light" id="hotNewsModalLabel'.$newsitem->id.'">' . format_text($newsitem->title) . '</h5>
                                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                ' . format_text($newsitem->content) . '
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-primary fs-4" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </li>';
        }
        $this->content->text .= '</ul></div>';

        // If user has capability of editing, add button.
        if (has_capability('block/lift_hotnews:manage', $this->context)) {
            $editurl = new moodle_url('/blocks/lift_hotnews/manage_hotnews.php');
            $this->content->footer = html_writer::tag('a', get_string('manage_hotnews', 'block_lift_hotnews'), 
                array('href' => $editurl, 'class' => 'btn btn-primary'));
        }

        return $this->content;
    }

    /**
     * Allows multiple instances of the block.
     */
    public function instance_allow_multiple() {
        return true;
    }

    /**
     * Enables block global configuration.
     */
    public function has_config() {
        return false;
    }
}

