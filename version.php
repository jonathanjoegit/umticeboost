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
 *  Version
 *
 * @package    theme_umticeboost
 * @copyright  2022 Jonathan J. - Le Mans Université
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Version (YYYYMMDDrr where rr is the release number).
$plugin->version   = 2022063000;

$plugin->requires  = 2019121200;
$plugin->component = 'theme_umticeboost';
$plugin->release  = 'Theme UMTICEBOOST v2 Moodle 3.10';

$plugin->dependencies = array(
    'theme_boost'  => 2020110900
);

/*
* TIP:
* When upgrade think about desintall if problem.
* When changing name of plugin, think about renaming the lang files.
*/
