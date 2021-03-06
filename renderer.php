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
 * Renderer for outputting the levels course format.
 *
 * @package format_levels
 * @since Moodle 2.3
 */


defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/course/format/topics/renderer.php');

/**
 * Basic renderer for levels format.
 *
 */
class format_levels_renderer extends format_topics_renderer
{

    /** @todo add multi language support */
    const GAME_SUGGESTIONS = [
        'Story/History' => 'Label, Page, File',
        'Game Rules' => 'Label, Page, File',
        'Challenge' => 'Page, File, Folder, URL, Book, Lesson, Assignment, Choice, Quiz, Glossary, Workshop, Wiki, Database, Forum, Chat, External tool, Survey',
        'Hidden Treasure' => 'Page, File, Folder, URL, Book, Lesson, Glossary, Forum, External tool',
        'Reward' => 'Page, File, Folder, URL, Book, Lesson, Glossary, Forum, External tool',
        'Combo' => 'Label, Page, File, Folder, Glossary, Database, Book, Lesson, Chat, External tool',
        'Badge' => 'Badge',
        'Socializing' => 'Forum, Chat',
    ];

    /**
     * get_level_section
     *
     * @param stdclass $course
     * @param string $name
     * @return string
     */
    protected function get_color_config($course, $name)
    {
        $return = false;
        if (isset($course->{$name})) {
            $color = str_replace('#', '', $course->{$name});
            $color = substr($color, 0, 6);
            if (preg_match('/^#?[a-f0-9]{6}$/i', $color)) {
                $return = '#' . $color;
            }
        }
        return $return;
    }

    /**
     * get_level_section
     *
     * @param stdclass $course
     * @param string $activesection
     * @return string
     */
    protected function get_level_section($course, $activesection)
    {
        global $PAGE;
        $html = '';
        $html .= $this->set_additional_css($course);
        $count = 1;
        $modinfo = get_fast_modinfo($course);
        $html .= html_writer::tag('div', '', ['class' => 'connecting-line']);
        $html .= html_writer::start_tag('div', ['class' => 'nav nav-tabs']);

        foreach ($modinfo->get_section_info_all() as $section => $thissection) {
            if ($section == 0) {
                continue;
            }
            if ($section > $course->numsections) {
                continue;
            }
            if ($course->hiddensections && !(int)$thissection->visible) {
                continue;
            }
            $html .= $this->get_circle_menu($section, $thissection, $course, $activesection, $PAGE->user_is_editing());
            $count++;
        }

        $html .= html_writer::end_tag('div');
        $html = html_writer::tag('div', $html, ['class' => 'wizard-inner']);
        $html = html_writer::tag('div', $html, ['class' => 'wizard']);
        if ($PAGE->user_is_editing()) { #when edit mode is ON
            $html .= html_writer::tag('div', get_string('editing', 'format_levels'), ['class' => 'alert alert-info alert-block fade in']);
        }
        return $html;
    }

    /**
     * @param $course
     * @return string
     */
    protected function set_additional_css($course)
    {
        $css = '';
        if ($colorhighlight = $this->get_color_config($course, 'colorhighlight')) {
            $css .=
                '.wizard div.current span.round-tab {
                color: ' . $colorhighlight . ';
                border-color:' . $colorhighlight . ';
            }
            ';
        }
        if ($colorcurrent = $this->get_color_config($course, 'colorcurrent')) {
            $css .=
                '.wizard div.activesection span.round-tab {
                 border: 2px solid ' . $colorcurrent . ';
                color: ' . $colorcurrent . ';
            }
            .wizard div.activesection:after {
            border-bottom-color: ' . $colorcurrent . ';
            }
            ';
        }
        if ($css) {
            return html_writer::tag('style', $css);
        }
        return '';
    }

    /**
     * @param $section
     * @param $thissection
     * @param $course
     * @param $activesection
     * @param $editing
     * @return mixed
     */
    private function get_circle_menu($section, $thissection, $course, $activesection, $editing)
    {
        $id = 'levelsection-' . $section;
        $name = $section;
        if ($course->sectiontype == 'alphabet' && is_numeric($name)) {
            $name = $this->number_to_alphabet($name);
        }
        if ($course->sectiontype == 'roman' && is_numeric($name)) {
            $name = $this->number_to_roman($name);
        }
        $class = 'levelsection';
        $onclick = 'M.format_levels.show(' . $section . ',' . $course->id . ')';
        if (!$thissection->available &&
            !empty($thissection->availableinfo)) {
            $class .= ' sectionhidden';
        } elseif (!$thissection->uservisible || !$thissection->visible) {
            $class .= ' sectionhidden';
            $onclick = false;
        }
        if ($course->marker == $section) {
            $class .= ' current';
        }
        if ($activesection == $section) {
            $class .= ' active';
        }
        if ($editing) {
            $onclick = false;
        }
        $html = html_writer::tag('span', $name, ['class' => 'round-tab']);

        return html_writer::tag('div', $html, ['class' => $class, 'title' => get_section_name($course, $section), 'onClick' => $onclick, 'id' => $id]);
    }

    /**
     * number_to_roman
     *
     * @param integer $number
     * @return string
     */
    protected function number_to_roman($number)
    {
        $number = intval($number);
        $return = '';
        $romanarray = [
            'M' => 1000,
            'CM' => 900,
            'D' => 500,
            'CD' => 400,
            'C' => 100,
            'XC' => 90,
            'L' => 50,
            'XL' => 40,
            'X' => 10,
            'IX' => 9,
            'V' => 5,
            'IV' => 4,
            'I' => 1
        ];
        foreach ($romanarray as $roman => $value) {
            $matches = intval($number / $value);
            $return .= str_repeat($roman, $matches);
            $number = $number % $value;
        }
        return $return;
    }

    /**
     * number_to_alphabet
     *
     * @param integer $number
     * @return string
     */
    protected function number_to_alphabet($number)
    {
        $number = $number - 1;
        $alphabet = range("A", "Z");
        if ($number <= 25) {
            return $alphabet[$number];
        } elseif ($number > 25) {
            $dividend = ($number + 1);
            $alpha = '';
            while ($dividend > 0) {
                $modulo = ($dividend - 1) % 26;
                $alpha = $alphabet[$modulo] . $alpha;
                $dividend = floor((($dividend - $modulo) / 26));
            }
            return $alpha;
        }
    }

    /**
     * start_section_list
     *
     * @return string
     */
    protected function start_section_list()
    {
        return html_writer::start_tag('ul', ['class' => 'levels']);
    }

    /**
     * section_header
     *
     * @param stdclass $section
     * @param stdclass $course
     * @param bool $onsectionpage
     * @param int $sectionreturn
     * @return string
     */
    protected function section_header($section, $course, $onsectionpage, $sectionreturn = null)
    {
        global $PAGE, $CFG;
        $o = '';
        $currenttext = '';
        $sectionstyle = '';
        if ($section->section != 0) {
            if (!$section->visible) {
                $sectionstyle = ' hidden';
            } elseif (course_get_format($course)->is_section_current($section)) {
                $sectionstyle = ' current';
            }
        }
        $o .= html_writer::start_tag('li', ['id' => 'section-' . $section->section,
            'class' => 'section main clearfix' . $sectionstyle,
            'role' => 'region', 'aria-label' => get_section_name($course, $section)]);
        $o .= html_writer::tag('span', $this->section_title($section, $course), ['class' => 'hidden sectionname']);
        $leftcontent = $this->section_left_content($section, $course, $onsectionpage);
        $o .= html_writer::tag('div', $leftcontent, ['class' => 'left side']);
        $rightcontent = $this->section_right_content($section, $course, $onsectionpage);
        $o .= html_writer::tag('div', $rightcontent, ['class' => 'right side']);
        $o .= html_writer::start_tag('div', ['class' => 'content']);
        $hasnamenotsecpg = (!$onsectionpage && ($section->section != 0 || !is_null($section->name)));
        $hasnamesecpg = ($onsectionpage && ($section->section == 0 && !is_null($section->name)));
        $classes = ' accesshide';
        if ($hasnamenotsecpg || $hasnamesecpg) {
            $classes = '';
        }
        $sectionname = html_writer::tag('span', $this->section_title($section, $course));
        if ($course->showdefaultsectionname) {
            $o .= $this->output->heading($sectionname, 3, 'sectionname' . $classes);
        }
        $o .= html_writer::start_tag('div', ['class' => 'summary']);
        $o .= $this->format_summary_text($section);
        $context = context_course::instance($course->id);
        $o .= html_writer::end_tag('div');
        $o .= $this->section_availability_message($section, has_capability('moodle/course:viewhiddensections', $context));
        return $o;
    }

    /**
     * print_multiple_section_page
     *
     * @param stdclass $course
     * @param array $sections (argument not used)
     * @param array $mods (argument not used)
     * @param array $modnames (argument not used)
     * @param array $modnamesused (argument not used)
     */
    public function print_multiple_section_page($course, $sections, $mods, $modnames, $modnamesused)
    {
        global $PAGE;
        $modinfo = get_fast_modinfo($course);
        $course = course_get_format($course)->get_course();
        $context = context_course::instance($course->id);
        $completioninfo = new completion_info($course);
        if (isset($_COOKIE['activesection_' . $course->id])) {
            $activesection = $_COOKIE['activesection_' . $course->id];
        } elseif ($course->marker > 0) {
            $activesection = $course->marker;
        } else {
            $activesection = 1;
        }
        $htmlsection = false;
        foreach ($modinfo->get_section_info_all() as $section => $thissection) {
            $htmlsection[$section] = '';
            if ($section == 0) {
                $section0 = $thissection;
                continue;
            }
            if ($section > $course->numsections) {
                continue;
            }
            /* if is not editing verify the rules to display the sections */
            if (!$PAGE->user_is_editing()) {
                if ($course->hiddensections && !(int)$thissection->visible) {
                    continue;
                }
                if (!$thissection->available && !empty($thissection->availableinfo)) {
                    $htmlsection[$section] .= $this->section_header($thissection, $course, false, 0);
                    continue;
                }
                if (!$thissection->uservisible || !$thissection->visible) {
                    $htmlsection[$section] .= $this->section_hidden($section, $course->id);
                    continue;
                }
            }
            $htmlsection[$section] .= $this->section_header($thissection, $course, false, 0);
            if ($thissection->uservisible) {
                $htmlsection[$section] .= $this->courserenderer->course_section_cm_list($course, $thissection, 0);
                $htmlsection[$section] .= $this->courserenderer->course_section_add_cm_control($course, $section, 0);
                $htmlsection[$section] .= $this->game_template_suggestions($course, $section, 0);
            }
            $htmlsection[$section] .= $this->section_footer();
        }
        if ($section0->summary || !empty($modinfo->sections[0]) || $PAGE->user_is_editing()) {
            $htmlsection0 = $this->section_header($section0, $course, false, 0);
            $htmlsection0 .= $this->courserenderer->course_section_cm_list($course, $section0, 0);
            $htmlsection0 .= $this->courserenderer->course_section_add_cm_control($course, 0, 0);
            $htmlsection0 .= $this->section_footer();
        }
        echo $completioninfo->display_help_icon();
        echo $this->output->heading($this->page_title(), 2, 'accesshide');
        echo $this->course_activity_clipboard($course, 0);
        echo $this->start_section_list();
        if ($course->sectionposition == 0 and isset($htmlsection0)) {
            echo html_writer::tag('span', $htmlsection0, ['class' => 'above']);
        }
        echo $this->get_level_section($course, $activesection);
        foreach ($htmlsection as $current) {
            echo $current;
        }
        if ($course->sectionposition == 1 and isset($htmlsection0)) {
            echo html_writer::tag('span', $htmlsection0, ['class' => 'below']);
        }
        if ($PAGE->user_is_editing() and has_capability('moodle/course:update', $context)) {
            foreach ($modinfo->get_section_info_all() as $section => $thissection) {
                if ($section <= $course->numsections or empty($modinfo->sections[$section])) {
                    continue;
                }
                echo $this->stealth_section_header($section);
                echo $this->courserenderer->course_section_cm_list($course, $thissection, 0);
                echo $this->stealth_section_footer();
            }
            echo $this->end_section_list();


            echo html_writer::start_tag('div', ['id' => 'changenumsections', 'class' => 'mdl-right']);
            $straddsection = get_string('increasesections', 'moodle');
            $url = new moodle_url('/course/changenumsections.php', ['courseid' => $course->id,
                'increase' => true, 'sesskey' => sesskey()]);
            $icon = $this->output->pix_icon('t/switch_plus', $straddsection);
            echo html_writer::link($url, $icon . get_accesshide($straddsection), ['class' => 'increase-sections']);
            if ($course->numsections > 0) {
                $strremovesection = get_string('reducesections', 'moodle');
                $url = new moodle_url('/course/changenumsections.php', ['courseid' => $course->id,
                    'increase' => false, 'sesskey' => sesskey()]);
                $icon = $this->output->pix_icon('t/switch_minus', $strremovesection);
                echo html_writer::link(
                    $url,
                    $icon . get_accesshide($strremovesection),
                    ['class' => 'reduce-sections']
                );
            }
            echo html_writer::end_tag('div');


        } else {
            echo $this->end_section_list();
        }
        echo html_writer::tag('style', '.course-content ul.levels #section-' . $activesection . ' { display: block; }');
        if (!$PAGE->user_is_editing()) {
            $PAGE->requires->js_init_call('M.format_levels.init', [$course->numsections]);
        }
    }

    protected function game_template_suggestions($course, $section, $sectionreturn = null, $displayoptions = array())
    {
        // check to see if user can add menus and there are modules to add
        if (!has_capability('moodle/course:manageactivities', context_course::instance($course->id))
            || !$this->page->user_is_editing()
            || !($modnames = get_module_types_names()) || empty($modnames)) {
            return '';
        }

        $html = html_writer::start_tag('div', ['class' => 'alert alert-secondary', 'role' => 'alert']);
        $html .= html_writer::tag('button', html_writer::tag('span', '&times;', ['aria-hidden' => true]), [
            'type' => 'button',
            'class' => 'close',
            'data-dismiss' => 'alert',
            'aria-label' => 'Close',
        ]);
        $html .= html_writer::tag('h4', get_string('game_template_header', 'format_levels'), ['class' => 'alert-heading']);
        $html .= html_writer::tag('p', get_string('game_template_description', 'format_levels'));
        $html .= '<hr>';

        $html .= html_writer::start_tag('ul', ['class' => 'list-group']);
        foreach (self::GAME_SUGGESTIONS as $game_element => $moodle_element) {
            $html .= html_writer::start_tag('li', ['class' => 'list-group-item']);
            $html .= html_writer::tag('strong', $game_element, []) . ' - ' . $moodle_element;
            $html .= html_writer::end_tag('li');
        }
        $html .= html_writer::end_tag('ul');

        $html .= html_writer::end_tag('div');

        return $html;

    }
}
