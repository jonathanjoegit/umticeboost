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

namespace theme_umticeboost\output;

use coding_exception;
use html_writer;
use tabobject;
use tabtree;
use custom_menu_item;
use custom_menu;
use block_contents;
use navigation_node;
use action_link;
use stdClass;
use moodle_url;
use preferences_groups;
use action_menu;
use help_icon;
use single_button;
use context_course;
use pix_icon;

defined('MOODLE_INTERNAL') || die;

/**
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * @package    theme_umticeboost
 * @copyright   2019 Jonathan J.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class core_renderer extends \theme_boost\output\core_renderer {

    /** @var custom_menu_item language The language menu if created */
    protected $language = null;


    /*
     * Overriding the custom_menu function ensures the custom menu is
     * always shown, even if no menu items are configured in the global
     * theme settings page.
     */
    public function umticeboost_custom_menu($custommenuitems = '') {
        global $CFG;

        if (empty($custommenuitems) && !empty($CFG->custommenuitems)) {
            $custommenuitems = $CFG->custommenuitems;
        }
        $custommenu = new custom_menu($custommenuitems, current_language());

// umticeboost custom menu :
 if (isloggedin() && !isguestuser() ) {
   // TDB + listes des cours :
        $branchtitle = $branchlabel = get_string('myhome');
        $branchurl = new moodle_url('');
        $branchsort = 1;

        $branch = $custommenu->add($branchlabel, $branchurl, $branchtitle, $branchsort);

        $hometext = get_string('myhome');
        $homelabel = html_writer::tag('i', '', array('class' => 'fa fa-home')).html_writer::tag('span', ' '.$hometext);
        $branch->add($homelabel, new moodle_url('/my/index.php'), $hometext);

        // Get 'My courses' sort preference from admin config.
        if (!$sortorder = $CFG->navsortmycoursessort) {
            $sortorder = 'sortorder';
        }

        // Retrieve courses and add them to the menu when they are visible.
        $numcourses = 0;
        //$hasdisplayhiddenmycourses = \theme_essential\toolbox::get_setting('displayhiddenmycourses');
        if ($courses = enrol_get_my_courses(null, $sortorder . ' ASC')) {
            foreach ($courses as $course) {
                if ($course->visible) {
                    $branch->add('<span class="fa fa-graduation-cap"></span>'.format_string($course->fullname),
                        new moodle_url('/course/view.php?id=' . $course->id), format_string($course->shortname));
                    $numcourses += 1;
                } else if (has_capability('moodle/course:viewhiddencourses', context_course::instance($course->id)) /*&& $hasdisplayhiddenmycourses*/) {
                    $branchtitle = format_string($course->shortname);
                    $branchlabel = '<span class="dimmed_text">'/*.$this->getfontawesomemarkup('eye-slash')*/.
                        format_string($course->fullname) . '</span>';
                    $branchurl = new moodle_url('/course/view.php', array('id' => $course->id));
                    $branch->add($branchlabel, $branchurl, $branchtitle);
                    $numcourses += 1;
                }
            }
        }
        if ($numcourses == 0 || empty($courses)) {
            $noenrolments = get_string('noenrolments', 'theme_umticeboost');
            $branch->add('<em>' . $noenrolments . '</em>', new moodle_url(''), $noenrolments);
        }

        // Recherche cours :
        $branchtitle = $branchlabel = get_string('recherchecours', 'theme_umticeboost');
        $branchurl = new moodle_url('/course/index.php');
        $branchsort = 2;
        $custommenu->add($branchlabel, $branchurl, $branchtitle, $branchsort);

  /*
        // Mail
        $branchtitle = $branchlabel = get_string('mail', 'theme_umticeboost');
        $branchurl = new moodle_url('http://webmail.univ-lemans.fr/');
        $branchsort = 3;
        $branch = $custommenu->add($branchlabel, $branchurl, $branchtitle, $branchsort);
*/

        // Aide :
      /*$branchtitle = $branchlabel = get_string('help');
        $branchurl = new moodle_url('');
        $branchsort = 4;
        $branch = $custommenu->add($branchlabel, $branchurl, $branchtitle, $branchsort);

        // Sous branche Enseignant :
        $SSbranchtitle = $SSbranchlabel = get_string('assistanceEns', 'theme_umticeboost');
        $SSbranchurl = new moodle_url('');
        $branchEnseignant = $branch->add($SSbranchlabel, $SSbranchurl, $SSbranchtitle);

        // sous sous branches :
        $SSbranchtitle = $SSbranchlabel = get_string('FAQ', 'theme_umticeboost');
        $SSbranchurl = new moodle_url('/course/view.php?id=2245');
        $branchEnseignant->add($SSbranchlabel, $SSbranchurl, $SSbranchtitle);

        $SSbranchtitle = $SSbranchlabel = get_string('creerespacecours', 'theme_umticeboost');
        $SSbranchurl = new moodle_url('http://coursumtice.univ-lemans.fr/');
        $branchEnseignant->add($SSbranchlabel, $SSbranchurl, $SSbranchtitle);

*/

}
        return parent::render_custom_menu($custommenu);
    }

    




}
