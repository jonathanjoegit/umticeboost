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
 * Theme settings.
 *
 * @package    theme_eadumboost
 * @copyright  2020 Jonathan J. - Le Mans Université
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings = new theme_boost_admin_settingspage_tabs('themesettingeadumboost', get_string('configtitle', 'theme_boost'));
    $page = new admin_settingpage('theme_boost_general', get_string('generalsettings', 'theme_boost'));


    // Set plateform environment (to have extra CSS for test & pre prod).
       $name = 'theme_eadumboost/platform_env';
       $title = get_string('platform_env', 'theme_eadumboost');
       $description = get_string('platform_env_desc', 'theme_eadumboost');
       $default = 'Production';
       $choices = array(
         'Production' => 'Production',
         'Pre-Production' => 'Pre-Production',
         'Test' => 'Test'
       );
       $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
       $setting->set_updatedcallback('theme_reset_all_caches');
       $page->add($setting);




    $settings->add($page);
}
