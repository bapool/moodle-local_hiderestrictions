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
require_once('../../config.php');

$courseid = required_param('courseid', PARAM_INT);
$sesskey = required_param('sesskey', PARAM_RAW);

require_login($courseid);
require_sesskey();

$context = context_course::instance($courseid);
require_capability('moodle/course:update', $context);

// Get current preference (default is shown = 0)
$hidden = get_user_preferences('local_hiderestrictions_hidden', 0);

// Toggle the preference
$newhidden = $hidden ? 0 : 1;
set_user_preference('local_hiderestrictions_hidden', $newhidden);

// Redirect back to the course
redirect(new moodle_url('/course/view.php', ['id' => $courseid]));
