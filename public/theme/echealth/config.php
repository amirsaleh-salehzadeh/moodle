<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../boost/lib.php');

$THEME->name = 'echealth';
$THEME->parents = ['boost'];
$THEME->sheets = [];
$THEME->editor_sheets = [];
$THEME->rendererfactory = 'theme_overridden_renderer_factory';
$THEME->layouts['mydashboard'] = [
    'file' => 'drawers_dashboard.php',
    'regions' => ['side-post'],
    'defaultregion' => 'side-post',
    'options' => ['nonavbar' => true, 'langmenu' => true],
];
$THEME->scss = function($theme) {
    return theme_boost_get_main_scss_content($theme) . "\n" . file_get_contents(__DIR__ . '/scss/echealth.scss');
};
