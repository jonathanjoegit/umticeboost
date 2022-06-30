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
use theme_config;


defined('MOODLE_INTERNAL') || die;

/**
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * @package    theme_umticeboost
 * @copyright  2022 Jonathan J.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_renderer extends \theme_boost\output\core_renderer {

    /*
     * Overriding the custom_menu function ensures the custom menu is
     * always shown, even if no menu items are configured in the global
     * theme settings page.
     */
    public function umboost_custom_menu($custommenuitems = '') {
        global $CFG;

        if (empty($custommenuitems) && !empty($CFG->custommenuitems)) {
            $custommenuitems = $CFG->custommenuitems;
        }
        $custommenu = new custom_menu($custommenuitems, current_language());

        // Add dahsboard and my courses access.
        $this->umboost_get_dashboard_for_custom_menu($custommenu);

        // Show the "course list" button in navbar.
        // Could be for everybody or just manager (depending the settings of the theme)
        // Get theme config.
        $theme = theme_config::load('umticeboost');
        // If setting for everybody OR manager we show the button.
        if (
            $theme->settings->navbar_course_list == "everybody"
            || (
                // We consider "manager" sombody with this capacities.
                has_capability('moodle/course:view', $this->page->context)
                && has_capability('moodle/course:viewhiddencourses', $this->page->context)
            )
        ) {
            $this->umboost_get_courselist_for_custom_menu($custommenu);
        }

        return $this->render_custom_menu($custommenu);
    }

    /**
     * OVERRIDE this render to not show the lang menu !
     */
    protected function render_custom_menu(custom_menu $menu) {

        $content = '';
        foreach ($menu->get_children() as $item) {
            $context = $item->export_for_template($this);
            $content .= $this->render_from_template('core/custom_menu_item', $context);
        }
        return $content;
    }

    /**
     * Add dashboard and my courses access to custom menu (all users).
     */
    protected function umboost_get_dashboard_for_custom_menu(custom_menu $menu) {
        global $CFG;

        // Add dashboard shortcut.
        $branchtitle = get_string('dashboard', 'theme_umticeboost');; // Title that we can use with CSS.
        $branchlabel = get_string('dashboard', 'theme_umticeboost');
        $branchurl   = new moodle_url('/');
        $branchsort  = 0;

        $branch = $menu->add($branchlabel, $branchurl, $branchtitle, $branchsort);

        // Add my courses shortcut.
        $mycourses = $this->page->navigation->get('mycourses');

        if (isloggedin() && $mycourses && $mycourses->has_children()) {
            $branchtitle = get_string('mycourses', 'theme_umticeboost' ); // Title that we can use with CSS.
            $branchlabel = get_string('mycourses', 'theme_umticeboost' );
            $branchurl   = new moodle_url('/course/index.php');
            $branchsort  = 1;

            $branch = $menu->add($branchlabel, $branchurl, $branchtitle, $branchsort);

            foreach ($mycourses->children as $coursenode) {
                $branch->add($coursenode->get_content(), $coursenode->action, $coursenode->get_title());
            }
        }
    }


    /**
     * add course list to custom menu.
     */
    protected function umboost_get_courselist_for_custom_menu($custommenu) {
        // Fetch courses.
        $branchtitle = get_string('courselist', 'theme_umticeboost'); // Title that we can use with CSS.
        $branchlabel = get_string('courselist', 'theme_umticeboost');
        $branchurl = new moodle_url('/course/index.php');
        $branchsort = 2;

        $custommenu->add($branchlabel, $branchurl, $branchtitle, $branchsort);
    }




    /**
     * Overriding: remove current langague (useless in footer and ugly).
     * -
     * We want to show the custom menus as a list of links in the footer on small screens.
     * Just return the menu object exported so we can render it differently.
     */
    public function custom_menu_flat() {
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
            $this->language = $custommenu; /* ADD JJUPIN: remove current langague (useless in footer and ugly). */
            foreach ($langs as $langtype => $langname) {
                $this->language->add($langname, new moodle_url($this->page->url, array('lang' => $langtype)), $langname);
            }
        }

        return $custommenu->export_for_template($this);
    }


    /* -- -- -- COURSE CUMSTOMISATION :  -- -- -- */

    /**
     * Returns HTML to display a "Turn editing on/off" button in a form.
     *
     * Note: Not called directly by theme but by core in its way of setting the 'page button'
     *       attribute.  This version needed for 'Edit button keep position' in adaptable.js.
     *
     * @param moodle_url $url The URL + params to send through when clicking the button
     * @return string HTML the button
     */
    public function edit_button(moodle_url $url) {
        $url->param('sesskey', sesskey());
        if ($this->page->user_is_editing()) {
            $url->param('edit', 'off');
            $btn = 'btn-danger';
            $title = get_string('turneditingoff');
            $icon = 'fa-power-off';
        } else {
            $url->param('edit', 'on');
            $btn = 'btn-success';
            $title = get_string('turneditingon');
            $icon = 'fa-edit';
        }

        $buttontitle = $title;

        return html_writer::tag('a', html_writer::tag('i', '', array('class' => $icon.' fa fa-fw')).
            $buttontitle, array('href' => $url, 'class' => 'btn '.$btn, 'title' => $title));
    }


   
   


    /**
     * OVERRIDE (check moodle 3.8 OK).
     * Add jjupin: searchcourses to custom menu (copy of build_action_menu_from_navigation).
     * @todo: use the parent function if possible.
     * Take a node in the nav tree and make an action menu out of it.
     * The links are injected in the action menu.
     *
     * @param action_menu $menu
     * @param navigation_node $node
     * @param boolean $indent
     * @param boolean $onlytopleafnodes
     * @return boolean nodesskipped - True if nodes were skipped in building the menu
     */
    protected function  build_action_menu_from_navigation(
        action_menu $menu,
        navigation_node $node,
        $indent = false,
        $onlytopleafnodes = false
    ) {
        $skipped = false;

        // Build an action menu based on the visible nodes from this navigation tree.
        foreach ($node->children as $menuitem) {

            // ADDJJUPIN: No displaying "outcomes / fr:objectifs".
            if ($menuitem->key == "outcomes") {
                continue;
            }

            if ($menuitem->display) {
                if ($onlytopleafnodes && $menuitem->children->count()) {
                    $skipped = true;
                    continue;
                }
                if ($menuitem->action) {
                    if ($menuitem->action instanceof action_link) {
                        $link = $menuitem->action;
                        // Give preference to setting icon over action icon.
                        if (!empty($menuitem->icon)) {
                            $link->icon = $menuitem->icon;
                        }
                    } else {
                        $link = new action_link($menuitem->action, $menuitem->text, null, null, $menuitem->icon);
                    }
                } else {
                    if ($onlytopleafnodes) {
                        $skipped = true;
                        continue;
                    }
                    $link = new action_link(new moodle_url('#'), $menuitem->text, null, ['disabled' => true], $menuitem->icon);
                }
                if ($indent) {
                    $link->add_class('ml-4');
                }
                if (!empty($menuitem->classes)) {
                    $link->add_class(implode(" ", $menuitem->classes));
                }

                $menu->add_secondary_action($link);
                $skipped = $skipped || $this->build_action_menu_from_navigation($menu, $menuitem, true);
            }

            // ADD JJUPIN: We display the custom menu after "turn editing" / add jjupin.
            if ($menuitem->key == "turneditingonoff") {
                $this->umboost_get_custom_action_menu_for_course_header($menu);
            }
        }
        return $skipped;
    }

    /**
     * Add custom items to the course settings menu.
     * - participation
     * - enrolmentmethods
     * - questionbank
     */
    protected function umboost_get_custom_action_menu_for_course_header($menu) {

        // Participants (if the user has the good capacity).
        if (has_capability('report/participation:view',  $this->page->context)) {
            $text = get_string('participants', 'core');
            $url = new moodle_url('/user/index.php', array('id' => $this->page->course->id));
            $customactionmenu = new action_link($url, $text, null, null, new pix_icon('t/cohort', ''));
            $customactionmenu->prioritise = true;
            $menu->add_secondary_action($customactionmenu);
        }
        // Méthode d'inscription.
        if (has_capability('moodle/course:enrolreview',  $this->page->context)) {
            $text = get_string('enrolmentmethods', 'core');
            $url = new moodle_url('/enrol/instances.php', array('id' => $this->page->course->id));
            $customactionmenu = new action_link($url, $text, null, null, new pix_icon('t/enrolusers', ''));
            $menu->add_secondary_action($customactionmenu);
        }
        // Banque de qestion.
        if (has_capability('moodle/question:add',  $this->page->context)) {
            $text = get_string('questionbank', 'question');
            $url = new moodle_url('/question/edit.php', array('courseid' => $this->page->course->id));
            $customactionmenu = new action_link($url, $text, null, null, new pix_icon('t/edit', ''));
            $menu->add_secondary_action($customactionmenu);
        }
    }


    /* -- -- -- LOGIN FORM CUSTOMISATION :  -- -- -- */

    /**
     * Renders the login form (to have the "CAS" or "NOCAS" value)
     *
     * @param \core_auth\output\login $form The renderable.
     * @return string
     */
    public function render_login(\core_auth\output\login $form) {

        global $CFG, $SITE;

        $context = $form->export_for_template($this);

        // Override because rendering is not supported in template yet.
        if ($CFG->rememberusername == 0) {
            $context->cookieshelpiconformatted = $this->help_icon('cookiesenabledonlysession');
        } else {
            $context->cookieshelpiconformatted = $this->help_icon('cookiesenabled');
        }
        $context->errorformatted = $this->error_text($context->error);
        $url = $this->get_logo_url();
        if ($url) {
            $url = $url->out(false);
        }
        $context->logourl = $url;
        $context->sitename = format_string(
            $SITE->fullname,
            true,
            ['context' => context_course::instance(SITEID), "escape" => false]
        );

        /* Add information about the CAS (from GET) CAS or NOCAS. */
        /* If we are in /login/ => we want CAS*/
        $cas = true;
        // If "NOCAS" => we want only manual login.
        if (isset($_GET['authCAS']) and $_GET['authCAS'] == 'NOCAS') {
            $cas = false;
        }
        $context->cas = $cas;

        // Create URL: CAS / NOCAS / Angers.
        $linkcas = new moodle_url(
            '/login/index.php',
            array('authCAS' => "CAS")
        );
        $context->linkcas = $linkcas;

        $linnocas = new moodle_url(
            '/login/index.php',
            array('authCAS' => "NOCAS")
        );
        $context->linknocas = $linnocas;

        // Get theme config.
        $theme = theme_config::load('umticeboost');
        // If config "login_connexion_angers_users", we will send the information.
        if ($theme->settings->login_connexion_angers_users) {
            // ISSUE WITH HTTPS: @todo, CHECK ALL THIS LATER !
            // We force https (so no: new moodle_url('/auth/shibboleth/index.php').
            $linkangers = new moodle_url('https://umtice.univ-lemans.fr/auth/shibboleth/index.php');
            $context->linkangers = $linkangers;
        }

        return $this->render_from_template('theme_umticeboost/loginform', $context);
    }
}
