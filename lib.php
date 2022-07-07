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
 * @package    theme_umticeboost
 * @copyright  2022 Jonathan J. - Le Mans Université
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/theme/boost/lib.php');

/**
 * Returns the main SCSS content.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_umticeboost_get_main_scss_content($theme) {
    global $CFG;

    // Get the main css of the parent theme_boost.
    static $boosttheme = null;
    if (empty($boosttheme)) {
        $boosttheme = theme_config::load('boost'); // Needs to be the Boost theme so that we get its settings.
    }
    $scss = theme_boost_get_main_scss_content($boosttheme);

    // Add theme custom scss.
    $custom = file_get_contents($CFG->themedir . '/umticeboost/scss/styles.scss');

    // Add custom styles for Test & Pre-production environment (theme setting).
    $value = $theme->settings->platform_env;
    if ($value == "Pre-Production") {
        $custom .= file_get_contents($CFG->themedir . '/umticeboost/scss/extra/env_preproduction.scss');
    } else if ($value == "Test") {
        $custom .= file_get_contents($CFG->themedir . '/umticeboost/scss/extra/env_test.scss');
    } else if ($value == "Test-annualisation") {
        $custom .= file_get_contents($CFG->themedir . '/umticeboost/scss/extra/env_testannualisation.scss');
    }
    if ($theme->settings->course_simplify_navdrawer ) {
        // Theme setting: course_simplify_navdrawer => add scss.
        $custom .= file_get_contents($CFG->themedir . '/umticeboost/scss/extra/course_simplify_navdrawer.scss');
    }
    // Combine them together.
    return $scss . "\n" . $custom;
}

/**
 * Modification du Nav-drawer de Moodle (appelé dans les layouts), on étend ainsi la navigation
 * doc NAVIGATION: https://docs.moodle.org/dev/Navigation_API#How_the_navigation_works
 *
 * @param  navigation_node$navigation.
 */

function theme_umticeboost_extend_navigation($navigation) {
    global $PAGE, $CFG, $COURSE;
    require_once($CFG->libdir . '/completionlib.php');

    static $theme;
    if (empty($theme)) {
        $theme = theme_config::load('umticeboost');
    }

    // Enlever "Home" (accueil non connecté, utile vu qu'on ne cache des blocs qu'en CSS après).
    if ($homenode = $navigation->find('home', global_navigation::TYPE_ROOTNODE)) {
        $homenode->showinflatnavigation = false;
    }

    // Add plugin "tuteur" (setting course_rapport_tuteur).
    // Vérifier si l'user à le droit d'afficher le rapport Tuteur.
    $context = $PAGE->context;
    if (
        has_capability('report/tuteur:view', $context)
        && $theme->settings->course_rapport_tuteur
        ) {
        // S'il y a des activités.
        $completion = new completion_info($COURSE);
        $activities = $completion->get_activities();
        if (count($activities) > 0) {
            // On récupère le noeud du cours (cours + section + ...).
            $coursenode = $PAGE->navigation->find($COURSE->id, navigation_node::TYPE_COURSE);
            // Si la navigation contient des items.
            if ($coursenode && $coursenode->has_children()) {

                // On créer un noeud et on utilise le add de la classe navigation_node_collection pour le ranger.
                $url = new moodle_url($CFG->wwwroot.'/report/tuteur/index.php', array('course' => $COURSE->id));
                $nodereport = navigation_node::create(
                    "Rapport Tuteur",
                    $url,
                    navigation_node::TYPE_SETTING,
                    "rapporttuteur",
                    "rapporttuteur",
                    new pix_icon('i/report', 'rapporttuteur')
                );

                // Function signature.
                // Create($text, $action=null, $type=self::TYPE_CUSTOM, $shorttext=null, $key=null, pix_icon $icon=null).
                // On check s'il y a le noeud "grades", si oui on le met en dessous (sinon à la fin).
                if ($PAGE->navigation->find("grades", navigation_node::TYPE_SETTING)) {
                    $node = $coursenode->children->add($nodereport, "grades");
                } else { // Sinon à la fin du noeud.
                    $node = $coursenode->children->add($nodereport);
                }
            }
        }
    }

    // Add edition mode in nav drawer (setting : course_editing_mode_navdrawer).
    if (
        $PAGE->user_allowed_editing()
        && $PAGE->pagelayout == 'course'
        && $theme->settings->course_editing_mode_navdrawer
    ) {
        $url = new moodle_url($PAGE->url);
        $url->param('sesskey', sesskey());
        $title = get_string('turneditingoff', 'core');

        if ($PAGE->user_is_editing()) {
            $url->param('edit', 'off');
            $title = get_string('turneditingoff', 'core');
        } else {
            $url->param('edit', 'on');
            $title = get_string('turneditingon', 'core');
        }
        $nodeedit = navigation_node::create(
            $title,
            $url,
            navigation_node::TYPE_SETTING,
            $title,
            $title,
            new pix_icon('i/edit', 'turneditingon')
        );
        $coursenode = $PAGE->navigation->find($COURSE->id, navigation_node::TYPE_COURSE);
        $node = $coursenode->children->add($nodeedit);
    }
}
