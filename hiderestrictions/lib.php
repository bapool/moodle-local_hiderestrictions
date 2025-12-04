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
 * Hide Restrictions Plugin
 *
 * This plugin adds the ability for teachers to hide the course restrictions.
 * When enabled, the course restrictions do not show on the course pages.
 *
 * @package    local_hiderestrictions
 * @copyright  2025 Brian A. Pool, National Trail Local Schools
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Add the toggle button and CSS to hide restrictions
 */
function local_hiderestrictions_before_standard_html_head() {
    global $PAGE, $USER, $DB;
    
    // Only add on course pages and for users who can edit
    if ($PAGE->context->contextlevel != CONTEXT_COURSE) {
        return '';
    }
    
    // Exclude activity/module settings pages - only apply to main course view
    if ($PAGE->pagetype !== 'course-view-topics' && 
        $PAGE->pagetype !== 'course-view-weeks' && 
        $PAGE->pagetype !== 'course-view-singleactivity' &&
        strpos($PAGE->pagetype, 'course-view') !== 0) {
        return '';
    }
    
    $course = $PAGE->course;
    if (!has_capability('moodle/course:update', $PAGE->context)) {
        return '';
    }
    
    // Only show if completion tracking is enabled for this course
    if (empty($course->enablecompletion)) {
        return '';
    }
    
    // Get current preference (default is shown = 0)
    $hidden = get_user_preferences('local_hiderestrictions_hidden', 0);
    
    // Create the toggle URL
    $toggleurl = new moodle_url('/local/hiderestrictions/toggle.php', [
        'courseid' => $course->id,
        'sesskey' => sesskey()
    ]);
    
    // Button text is just "Restrictions" with appropriate icon
    $buttontext = get_string('restrictions', 'local_hiderestrictions');
    
    // Determine icon based on current state
    if ($hidden) {
        $iconclass = 'fa-eye';
        $title = get_string('showrestrictions', 'local_hiderestrictions');
    } else {
        $iconclass = 'fa-eye-slash';
        $title = get_string('hiderestrictions', 'local_hiderestrictions');
    }
    
    $output = '';
    
    // Add CSS to hide restrictions if preference is set
    if ($hidden) {
        $output .= '<style>
            /* Hide activity availability/restriction information on course page only */
            /* Target only the course content area, not settings forms */
            .course-content li.activity .text-muted,
            .course-content .activity-item .text-muted,
            .course-content .activity-availability,
            .course-content .availability-info,
            .course-content .availabilityinfo,
            .course-content .activity .badge,
            .course-content .activity-info .badge,
            #region-main .course-content [class*="availability"] {
                display: none !important;
            }
        </style>';
    }
    
    // Add floating toggle button
    $output .= '<style>
        .hiderestrictions-toggle-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
            background-color: #0f6cbf;
            color: white;
            border: none;
            border-radius: 50px;
            padding: 12px 20px;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .hiderestrictions-toggle-btn:hover {
            background-color: #0a5596;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
        }
        .hiderestrictions-toggle-btn i {
            font-size: 16px;
        }
    </style>';
    
    // Add the button HTML with title for tooltip
    $output .= '<a href="' . $toggleurl->out() . '" class="hiderestrictions-toggle-btn" title="' . $title . '">
        <i class="fa ' . $iconclass . '"></i>
        <span>' . $buttontext . '</span>
    </a>';
    
    return $output;
}

/**
 * Add link to the course administration menu (still available in More menu)
 */
function local_hiderestrictions_extend_navigation_course($navigation, $course, $context) {
    global $PAGE;
    
    // Only for users who can update courses
    if (!has_capability('moodle/course:update', $context)) {
        return;
    }
    
    // Only show if completion tracking is enabled for this course
    if (empty($course->enablecompletion)) {
        return;
    }
    
    // Get current preference (default is shown = 0)
    $hidden = get_user_preferences('local_hiderestrictions_hidden', 0);
    
    // Create the URL to toggle
    $url = new moodle_url('/local/hiderestrictions/toggle.php', [
        'courseid' => $course->id,
        'sesskey' => sesskey()
    ]);
    
    // Determine button text
    if ($hidden) {
        $text = get_string('showrestrictions', 'local_hiderestrictions');
        $icon = new pix_icon('t/hide', '');
    } else {
        $text = get_string('hiderestrictions', 'local_hiderestrictions');
        $icon = new pix_icon('t/show', '');
    }
    
    // Add to secondary navigation
    $node = navigation_node::create(
        $text,
        $url,
        navigation_node::TYPE_SETTING,
        null,
        'local_hiderestrictions',
        $icon
    );
    
    $navigation->add_node($node);
}
