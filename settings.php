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
 * @package    theme_umticeboost
 * @copyright  2022 Jonathan J. - Le Mans Université
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    // Boost provides a nice setting page which splits settings onto separate tabs. We want to use it here.
    $settings = new theme_boost_admin_settingspage_tabs('themesettingumticeboostboost', get_string('configtitle', 'theme_umticeboost'));


    /*
    * ----------------------
    * General settings tab
    * ----------------------
    */
    $page = new admin_settingpage('theme_umticeboost_general', get_string('general_settings', 'theme_umticeboost'));


    // Set plateform environment (to have extra CSS for test & pre prod).
    $name = 'theme_umticeboost/platform_env';
    $title = get_string('platform_env', 'theme_umticeboost');
    $description = get_string('platform_env_desc', 'theme_umticeboost');
    $default = 'Production';
    $choices = array(
        'Production' => 'Production',
        'Pre-Production' => 'Pre-Production',
        'Test-annualisation' => 'Annualisation',
        'Test' => 'Test'
    );
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Show a block for Angers Université users in the login page.
    $name = 'theme_umticeboost/login_connexion_angers_users';
    $title = get_string('title_angers_users', 'theme_umticeboost');
    $description = get_string('text_angers_user', 'theme_umticeboost');
    $default = 0;
    $choices = array(
        0 => "No",
        1 => "Yes"
    );
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Show "course list" in navbar for everybody or just admin/manager (UMTICE: all, umticeboost: manager).
    $name = 'theme_umticeboost/navbar_course_list';
    $title = get_string('navbar_course_list', 'theme_umticeboost');
    $description = get_string('navbar_text_course_list_navbar', 'theme_umticeboost');
    $default = 'manager';
    $choices = array(
        'everybody' => 'Everybody (UMTICE)',
        'manager' => 'Manager (EAD-UM)'
    );
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Add the page.
    $settings->add($page);


    /*
    * ----------------------
    * Course settings tab
    * ----------------------
    */
    $page = new admin_settingpage('theme_umticeboost_course', get_string('course_settings', 'theme_umticeboost'));

    // Simplify the nav-drawer in the context "course" (hide that is not connected with the course).
    // For now, it's a scss file : course_simplify_navdrawer.scss.
    $name = 'theme_umticeboost/course_simplify_navdrawer';
    $title = get_string('course_simplify_navdrawer', 'theme_umticeboost');
    $description = get_string('course_text_simplify_navdrawer', 'theme_umticeboost');
    $default = 0;
    $choices = array(
        0 => "No",
        1 => "Yes"
    );
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

     // Add acess to "rapport tuteur" in the nav-drawer.
     $name = 'theme_umticeboost/course_rapport_tuteur';
     $title = get_string('course_rapport_tuteur', 'theme_umticeboost');
     $description = get_string('course_text_rapport_tuteur', 'theme_umticeboost');
     $default = 0;
     $choices = array(
         0 => "No",
         1 => "Yes"
     );
     $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
     $setting->set_updatedcallback('theme_reset_all_caches');
     $page->add($setting);

     // Add acess to "editing mode" in the nav-drawer.
     $name = 'theme_umticeboost/course_editing_mode_navdrawer';
     $title = get_string('course_editing_mode_navdrawer', 'theme_umticeboost');
     $description = get_string('course_text_editing_mode_navdrawer', 'theme_umticeboost');
     $default = 0;
     $choices = array(
         0 => "No",
         1 => "Yes"
     );
     $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
     $setting->set_updatedcallback('theme_reset_all_caches');
     $page->add($setting);



     // Add the page.
     $settings->add($page);
}
