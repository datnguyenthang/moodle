<?php
// tool/excimer/db/hooks.php
defined('MOODLE_INTERNAL') || die();

$hooks = [
    'core\hook\after_config' => [
        'callback' => 'tool_excimer\hook\after_config'
    ]
];
