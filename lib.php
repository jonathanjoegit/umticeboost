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

    // Add theme custom scss.
    $post = file_get_contents($CFG->themedir . '/eadumboost/scss/styles.scss');

    // Add custom styles for Test & Pre-production environment (theme setting).
    $value = $theme->settings->platform_env;
    if ($value == "Pre-Production") {
        $post .= file_get_contents($CFG->themedir . '/eadumboost/scss/extra/env_preproduction.scss');
    } else if ($value == "Test") {
        $post .= file_get_contents($CFG->themedir . '/eadumboost/scss/extra/env_test.scss');
    }

    // Combine them together.
    return $scss . "\n" . $post;
}



/**
 * Modification du Nav-drawer de Moodle (appelé dans les layouts), on étend ainsi la navigation
 * //doc NAVIGATION: https://docs.moodle.org/dev/Navigation_API#How_the_navigation_works
 */
function theme_eadumboost_extend_navigation($navigation) {
    global $PAGE, $CFG, $COURSE;
    require_once($CFG->libdir . '/completionlib.php');

    // Enlever "Home".
    if ($homenode = $navigation->find('home', global_navigation::TYPE_ROOTNODE)) {
        $homenode->showinflatnavigation = false;
    }
    // Enlever "Privat files".
    // Fait en CSS (display:none;) sinon c'est un peu galère (à voir plus tard).

    // Aajouter plugin "tuteur".
    // Vérifier si l'user à le droit d'afficher le rapport Tuteur.
    $context = $PAGE->context;
    if (has_capability('report/tuteur:view', $context)) {
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
                // Signature create($text, $action=null, $type=self::TYPE_CUSTOM, $shorttext=null, $key=null, pix_icon $icon=null).

                // On check s'il y a le noeud "grades", si oui on le met en dessous (sinon à la fin).
                if ($PAGE->navigation->find("grades", navigation_node::TYPE_SETTING)) {
                    $node = $coursenode->children->add($nodereport, "grades");
                } else { // Sinon à la fin du noeud.
                    $node = $coursenode->children->add($nodereport);
                }
            }
        }
    }

    // ISSUE : the page "enrol user" doesnt not exist any more (now only with a pop-up).
    // Ajouter "inscrire des utilisateurs" pour les admins.
    // Vérifier si l'user à le droit d'inscrire des utilisateurs (donc d'accèder à cette page).
/*
    $context = $PAGE->context;
    if (has_capability('enrol/manual:enrol', $context)) {
        // On récupère le noeud du cours (cours + section + ...).
        $coursenode = $PAGE->navigation->find($COURSE->id, navigation_node::TYPE_COURSE);
        // Si la navigation contient des items.
        if ($coursenode && $coursenode->has_children()) {
            // On créer un noeud et on utilise le add de la classe navigation_node_collection pour le ranger.
            $url = new moodle_url($CFG->wwwroot.'/enrol/users.php', array('id' => $COURSE->id));
            $newnode = navigation_node::create(
                get_string('enrolusers', 'enrol'),
                $url,
                navigation_node::TYPE_SETTING,
                "enrolusers",
                "enrolusers",
                new pix_icon('i/enrolusers', 'enrolusers')
            );

            // On check s'il y a le noeud "participants", si oui on le met en dessous (sinon à la fin).
            if ($PAGE->navigation->find("participants", navigation_node::TYPE_CONTAINER)) {
                $node = $coursenode->children->add($newnode, "participants");
            } else { // Sinon à la fin du noeud.
                $node = $coursenode->children->add($newnode);
            }
        }
    }*/
}
