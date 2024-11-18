<?php
defined('MOODLE_INTERNAL') || die();

class block_lift_hotnews extends block_base {

    /**
     * Initializes block settings, including the block title.
     */
    public function init() {
        $this->title = get_string('lift_hotnews', 'block_lift_hotnews');
    }

    /**
     * Generates the block content displayed on the page.
     */
    public function get_content() {
        global $CFG, $DB, $OUTPUT, $PAGE;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        // Get current time to filter hot news items.
        $now = time();
        $newsitems = $DB->get_records_select('lift_hotnews', 'startdate <= ? AND enddate >= ?', [$now, $now]);

        // Add custom JavaScript for the block.
        $PAGE->requires->js_call_amd('block_lift_hotnews/modal_init', 'init');

        // Check if there are news items.
        if (!empty($newsitems)) {
            $this->content->text .= '<div class="box-widget"><ul class="list-unstyled">';
            foreach ($newsitems as $newsitem) {
                $shortContent = shorten_text(format_string($newsitem->content), 120);
            
                $this->content->text .= '
                <li class="border-bottom mt-3">
                    <div class="overflow-auto">
                        <img class="ic-new align-middle" src="' . $CFG->wwwroot . '/blocks/lift_hotnews/images/new.svg" height="25" width="25" loading="lazy" />
                        <h6 class="d-inline align-middle">
                            <a href="#" class="d-inline align-middle" 
                                data-modal-title="' . format_string($newsitem->title) . '" 
                                data-modal-content="' . htmlspecialchars(format_text($newsitem->content, FORMAT_HTML), ENT_QUOTES) . '">
                                ' . format_string($newsitem->title) . '
                            </a>
                        </h6>
                        <div class="date small">' . $shortContent . '</div>
                    </div>
                </li>';
            }
            
            $this->content->text .= '</ul></div>';
        } else {
            // If no news items, display a message.
            $this->content->text .= '<p>' . get_string('no_hotnews', 'block_lift_hotnews') . '</p>';
        }

        // Add "Manage Hot News" button for users with the required capability.
        if (has_capability('block/lift_hotnews:manage', $this->context)) {
            $editurl = new moodle_url('/blocks/lift_hotnews/manage_hotnews.php');
            $this->content->footer = $OUTPUT->single_button($editurl, get_string('manage_hotnews', 'block_lift_hotnews'), 'get');
        }

        return $this->content;
    }

    /**
     * Allows multiple instances of the block on the same page.
     */
    public function instance_allow_multiple() {
        return true;
    }

    /**
     * Indicates whether the block has global configuration.
     */
    public function has_config() {
        return false;
    }
}
