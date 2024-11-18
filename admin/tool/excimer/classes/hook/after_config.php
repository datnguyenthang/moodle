<?php

namespace tool_excimer\hooks;

defined('MOODLE_INTERNAL') || die();

class after_config {
    public static function execute() {
        // Start processor.
        $manager = \tool_excimer\manager::get_instance();
        $manager->start_processor();
    }
}

