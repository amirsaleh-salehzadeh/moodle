<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $setting = new admin_setting_configtext(
        'theme_echealth/departmentname',
        get_string('settingdepartmentname', 'theme_echealth'),
        get_string('settingdepartmentname_desc', 'theme_echealth'),
        get_string('departmentname', 'theme_echealth'),
        PARAM_TEXT
    );
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    $setting = new admin_setting_configtext(
        'theme_echealth/portalname',
        get_string('settingportalname', 'theme_echealth'),
        get_string('settingportalname_desc', 'theme_echealth'),
        get_string('portalname', 'theme_echealth'),
        PARAM_TEXT
    );
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    $setting = new admin_setting_configstoredfile(
        'theme_echealth/logo',
        get_string('settinglogo', 'theme_echealth'),
        get_string('settinglogo_desc', 'theme_echealth'),
        'logo',
        0,
        ['accepted_types' => ['.png', '.jpg', '.jpeg']]
    );
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);
}
