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
 * Theme functions.
 *
 * @package    theme_eadumboost
 * @copyright  2020 Jonathan J. - Le Mans Université
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/**
 * Returns the main SCSS content.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_eadumboost_get_main_scss_content($theme) {
    global $CFG;

    $scss = '';
    $filename = !empty($theme->settings->preset) ? $theme->settings->preset : null;
    $fs = get_file_storage();

    $context = context_system::instance();
    if ($filename == 'default.scss') {
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss');
    } else if ($filename == 'plain.scss') {
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/plain.scss');
    } else if ($filename && ($presetfile = $fs->get_file($context->id, 'theme_boost', 'preset', 0, '/', $filename))) {
        $scss .= $presetfile->get_content();
    } else {
        // Safety fallback - maybe new installs etc.
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss');
    }

    // Add umtice scss.
    // Post (style.scss) CSS - this is loaded AFTER the main scss but before the extra scss from the setting.
    $post = file_get_contents($CFG->themedir . '/eadumboost/scss/styles.scss');

    // Add custom styles for Test & Pre-production environment (theme setting).
    /*$value = $theme->settings->platform_env;
    if ($value == "Pre-Production") {
        $post .= file_get_contents($CFG->themedir . '/eadumboost/scss/extra/env_preproduction.scss');
    } else if ($value == "Test") {
        $post .= file_get_contents($CFG->themedir . '/eadumboost/scss/extra/env_test.scss');
    }*/

    return $scss . "\n" . $post;
}


function theme_eadumboost_extend_navigation(global_navigation $navigation) {
    // Enlever "Home".
    if ($homenode = $navigation->find('home', global_navigation::TYPE_ROOTNODE)) {
        $homenode->showinflatnavigation = false;
    }
}
