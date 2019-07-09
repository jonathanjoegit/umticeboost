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
* @copyright  2019 Jonathan J.
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

        // Umticeboost custom menu.
        if (isloggedin() && !isguestuser() ) {

            // add dahsboard and my courses access :
            $this->umticeboost_get_dashboard_for_custom_menu($custommenu);
            // add courses seach:
            $this->umticeboost_get_searchcourses_for_custom_menu($custommenu);

        }
        return parent::render_custom_menu($custommenu);
    }

    /**
    * add dashboard and my courses access to custom menu.
    */
    protected function umticeboost_get_dashboard_for_custom_menu(custom_menu $custommenu) {
        global $CFG;

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
                } else if (has_capability('moodle/course:viewhiddencourses', context_course::instance($course->id))) {
                    $branchtitle = format_string($course->shortname);
                    $branchlabel = '<span class="dimmed_text">'.format_string($course->fullname) . '</span>';
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

    }

    /**
    * add searchcourses to custom menu.
    */
    protected function umticeboost_get_searchcourses_for_custom_menu(custom_menu $custommenu) {
        // fetch courses :
        $branchtitle = $branchlabel = get_string('recherchecours', 'theme_umticeboost');
        $branchurl = new moodle_url('/course/index.php');
        $branchsort = 2;

        $custommenu->add($branchlabel, $branchurl, $branchtitle, $branchsort);
    }

    /**
    * We want to show the custom menus as a list of links in the footer on small screens.
    * Just return the menu object exported so we can render it differently.
    */
    public function umticeboost_custom_menu_flat() {
        global $CFG;
        $custommenuitems = '';

        if (empty($custommenuitems) && !empty($CFG->custommenuitems)) {
            $custommenuitems = $CFG->custommenuitems;
        }
        $custommenu = new custom_menu($custommenuitems, current_language());
        $langs = get_string_manager()->get_list_of_translations();
        $haslangmenu = $this->lang_menu() != '';

        if ($haslangmenu) {
            $strlang = get_string('language');
            $currentlang = current_language();
            if (isset($langs[$currentlang])) {
                $currentlang = $langs[$currentlang];
            } else {
                $currentlang = $strlang;
            }
            $this->language = $custommenu;
            foreach ($langs as $langtype => $langname) {
                $this->language->add($langname, new moodle_url($this->page->url, array('lang' => $langtype)), $langname);
            }
        }

        return $custommenu->export_for_template($this);
    }

    /**
    * Wrapper for header elements.
    *
    * @return string HTML to display the main header.
    */
    public function umticeboost_full_header() {
        global $PAGE;

        $header = new stdClass();
        $header->settingsmenu = $this->umticeboost_context_header_settings_menu();
        $header->contextheader = $this->context_header();
        $header->hasnavbar = empty($PAGE->layout_options['nonavbar']);
        $header->navbar = $this->navbar();
        $header->pageheadingbutton = $this->page_heading_button();
        $header->courseheader = $this->course_header();

        $header->editbutton = $this->umticeboost_edit_button();

        return $this->render_from_template('theme_umticeboost/header', $header);
    }


    /**
    * Editing button in a course
    *
    * @return string the editing button
    */
    public function umticeboost_edit_button() {
        global $PAGE, $COURSE;

        if (!$PAGE->user_allowed_editing() || $COURSE->id <= 1) {
            return '';
        }
        if ($PAGE->pagelayout == 'course') {
            $url = new moodle_url($PAGE->url);
            $url->param('sesskey', sesskey());
            if ($PAGE->user_is_editing()) {
                $url->param('edit', 'off');
                $btn = 'btn-danger editingbutton';
                $title = get_string('turneditingoff', 'core');
                $icon = 'fa-power-off';
            } else {
                $url->param('edit', 'on');
                $btn = 'btn-success editingbutton';
                $title = get_string('turneditingon', 'core');
                $icon = 'fa-edit';
            }
            return html_writer::tag('a', html_writer::start_tag('i', array(
                'class' => $icon . ' fa fa-fw'
            )) . html_writer::end_tag('i') . $title , array(
                'href' => $url,
                'class' => 'btn edit-btn ' . $btn,
                'data-tooltip' => "tooltip",
                'data-placement' => "bottom",
                'title' => $title,
            ));
        }
    }

    /**
    * This is an optional menu that can be added to a layout by a theme. It contains the
    * menu for the course administration, only on the course main page.
    *
    * @return string
    */
    public function umticeboost_context_header_settings_menu() {
        $context = $this->page->context;
        $menu = new action_menu();

        $items = $this->page->navbar->get_items();
        $currentnode = end($items);

        $showcoursemenu = false;
        $showfrontpagemenu = false;
        $showusermenu = false;

        // We are on the course home page.
        if (($context->contextlevel == CONTEXT_COURSE) &&
        !empty($currentnode) &&
        ($currentnode->type == navigation_node::TYPE_COURSE || $currentnode->type == navigation_node::TYPE_SECTION)) {
            $showcoursemenu = true;
        }

        $courseformat = course_get_format($this->page->course);
        // This is a single activity course format, always show the course menu on the activity main page.
        if ($context->contextlevel == CONTEXT_MODULE &&
        !$courseformat->has_view_page()) {

            $this->page->navigation->initialise();
            $activenode = $this->page->navigation->find_active_node();
            // If the settings menu has been forced then show the menu.
            if ($this->page->is_settings_menu_forced()) {
                $showcoursemenu = true;
            } else if (!empty($activenode) && ($activenode->type == navigation_node::TYPE_ACTIVITY ||
            $activenode->type == navigation_node::TYPE_RESOURCE)) {

                // We only want to show the menu on the first page of the activity. This means
                // the breadcrumb has no additional nodes.
                if ($currentnode && ($currentnode->key == $activenode->key && $currentnode->type == $activenode->type)) {
                    $showcoursemenu = true;
                }
            }
        }

        // This is the site front page.
        if ($context->contextlevel == CONTEXT_COURSE &&
        !empty($currentnode) &&
        $currentnode->key === 'home') {
            $showfrontpagemenu = true;
        }

        // This is the user profile page.
        if ($context->contextlevel == CONTEXT_USER &&
        !empty($currentnode) &&
        ($currentnode->key === 'myprofile')) {
            $showusermenu = true;
        }

        if ($showfrontpagemenu) {
            $settingsnode = $this->page->settingsnav->find('frontpage', navigation_node::TYPE_SETTING);
            if ($settingsnode) {
                // Build an action menu based on the visible nodes from this navigation tree.
                $skipped = $this->build_action_menu_from_navigation($menu, $settingsnode, false, true);

                // We only add a list to the full settings menu if we didn't include every node in the short menu.
                if ($skipped) {
                    $text = get_string('morenavigationlinks');
                    $url = new moodle_url('/course/admin.php', array('courseid' => $this->page->course->id));
                    $link = new action_link($url, $text, null, null, new pix_icon('t/edit', ''));
                    $menu->add_secondary_action($link);
                }
            }
        } else if ($showcoursemenu) {
            $settingsnode = $this->page->settingsnav->find('courseadmin', navigation_node::TYPE_COURSE);
            if ($settingsnode) {
                // Build an action menu based on the visible nodes from this navigation tree.
                $skipped = $this->build_action_menu_from_navigation($menu, $settingsnode, false, true);

                $quetionnode = $settingsnode->find('question', navigation_node::TYPE_COURSE);


                // Add some important pages in the course setting menu in a course. Add JJUPIN.
                $this->umticeboost_get_custom_action_menu($menu);


                // We only add a list to the full settings menu if we didn't include every node in the short menu.
                if ($skipped) {
                    $text = get_string('morenavigationlinks');
                    $url = new moodle_url('/course/admin.php', array('courseid' => $this->page->course->id));
                    $link = new action_link($url, $text, null, null, new pix_icon('t/edit', ''));
                    $menu->add_secondary_action($link);
                }
            }
        } else if ($showusermenu) {
            // Get the course admin node from the settings navigation.
            $settingsnode = $this->page->settingsnav->find('useraccount', navigation_node::TYPE_CONTAINER);
            if ($settingsnode) {
                // Build an action menu based on the visible nodes from this navigation tree.
                $this->build_action_menu_from_navigation($menu, $settingsnode);
            }
        }

        return $this->render($menu);
    }

    /**
    * add searchcourses to custom menu.
    */
    protected function umticeboost_get_custom_action_menu(action_menu $menu) {

        //TODO: CHECK PERMISSIONS !!!!

        // Participants :
        $text = get_string('participants', 'core');
        $url = new moodle_url('/user/index.php', array('id'=>$this->page->course->id));
        $link = new action_link($url, $text, null, null, new pix_icon('t/cohort', ''));
        $menu->add_secondary_action($link);

        // Méthode d'inscription :
        $text = get_string('enrolmentmethods', 'core');
        $url = new moodle_url('/enrol/instances.php', array('id'=>$this->page->course->id));
        $link = new action_link($url, $text, null, null, new pix_icon('t/enrolusers', ''));
        $menu->add_secondary_action($link);

        // Banque de qestion :
        $text = get_string('questionbank', 'question');
        $url = new moodle_url('/question/edit.php', array('courseid'=>$this->page->course->id));
        $link = new action_link($url, $text, null, null, new pix_icon('t/edit', ''));
        $menu->add_secondary_action($link);


    }


}
