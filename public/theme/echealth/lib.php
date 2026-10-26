<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

defined('MOODLE_INTERNAL') || die();

/**
 * Serve administrator-uploaded logo files for the theme.
 *
 * @param stdClass $course
 * @param stdClass|null $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function theme_echealth_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []): bool {
    if ($context->contextlevel !== CONTEXT_SYSTEM || $filearea !== 'logo') {
        send_file_not_found();
    }

    $theme = theme_config::load('echealth');
    if (!array_key_exists('cacheability', $options)) {
        $options['cacheability'] = 'public';
    }
    return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
}
