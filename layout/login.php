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
 * A login page layout for the boost theme.
 *
 * @package   theme_eadumboost
 * @copyright  2020 Jonathan J.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 defined('MOODLE_INTERNAL') || die();

$bodyattributes = $OUTPUT->body_attributes();


/* ADD JJUPIN Check URL to get the CAS or manual form to connect*/
$authcas = optional_param('authCAS', '', PARAM_ALPHANUMEXT);
if ($authcas == 'NOCAS') {
    $cas = false;
} else {
    $cas = true;
}

$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
    'output' => $OUTPUT,
    'bodyattributes' => $bodyattributes,
    'authCAS' => $cas /* ADD JJUPIN.*/
];
// ADD JJUPIN: render with theme_eadumboost/login.
echo $OUTPUT->render_from_template('theme_eadumboost/login', $templatecontext);
