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

/*
 * @package auth_eek
 * @copyright 2014 Codespot
 * @author Tõnis Tartes <tonis.tartes@gmail.com>
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); // It must be included from a Moodle page
}

require_once dirname(dirname(dirname(__FILE__))) . '/lib/authlib.php';
require_once dirname(dirname(dirname(__FILE__))) . '/grade/querylib.php';

class auth_plugin_eek extends auth_plugin_base {
    
    /*
     * Init
     */
    function auth_plugin_eek() {
        $this->authtype = 'eek';
        $this->config = get_config('auth/eek');
    }
    
    /*
     * This class aint intendend to login to moodle, must always come up as false
     */
    function user_login($username, $password) {
        return false; //must fail always
    }
    
    /*
     * Automatic user creation
     */
    function syncuser($isikid, $type, $username, $password, $firstname, $lastname, $email, $country, $city, $deleted = 0) {
        global $CFG, $DB;
        
        if (!$isikid || !$type || !$username || !$password || !$firstname || !$lastname || !$email) {
            return false;
        }
        
        if (!$DB->record_exists('user', array('username'=>$username, 'confirmed' => 1, 'deleted' => 0)) &&
        !$DB->record_exists('user', array('email'=>$email, 'confirmed' => 1, 'deleted' => 0)) &&
        !$DB->record_exists('user', array('idnumber'=>$isikid, 'confirmed' => 1, 'deleted' => 0))
        ) {
            $user = new Object();
            $user->auth = $type;
            $user->username = $username;
            $user->password = AUTH_PASSWORD_NOT_CACHED;
            $user->idnumber = $isikid; // This or some new field...
            $user->firstname = $firstname;
            $user->lastname = $lastname;
            $user->email = $email;
            $user->country = $country;
            $user->city = $city;
            $user->deleted = $deleted;
            $user->mnethostid = $CFG->mnet_localhost_id;
            $user->policyagreed = 1;
            $user->confirmed = 1;
            $user->timecreated = time();

            if ($user->id = $DB->insert_record('user', $user)) {
                //Success.. Maybe send an email notification upon creation?
                return true;
            } else {
                //Failed to create user
                return false;
            }
        } else {
            //Sorry.. duplicate user warning
            return false;
        }
    }
    
    /*
     * Synchronize course members
     */
    function synccoursemembers($courseid, $members, $group) {
        print_r($courseid);
        print_r($memebrs);
        return false;
    }
    
    /*
     * SSO..
     */
    function ssorequest($username) {
        //To-Do
    }
    
    /*
     * Get a users "Course Total" grade
     */
    function getoutcome($courseid, $isikid) {
        global $DB;
        
        $user = $DB->get_record('user', array('idnumber' => $isikid, 'deleted' => '0')); // Moodle user object
        
        $grade = grade_get_course_grade($user->id, $courseid); // User "Course Total" grade
        
        $usergrade = new Object();
        $usergrade->grade = $grade->grade;
        $usergrade->str_grade = $grade->str_grade;
        
        return $grade;
    }
    
    /*
     * Get all "Course Total" grades from a course
     */
    function getoutcomes($courseid) {
        global $DB;
        
        $course = $DB->get_record('course', array('id' => $courseid)); // Get course data

        if (!$context = get_context_instance(CONTEXT_COURSE, $course->id)) {
            return false;
        }

        $role = $DB->get_record('role', array('shortname'=>'student'), '*', MUST_EXIST);

        $params = array();
        $params['contextid'] = $context->id;
        $params['roleid'] = $role->id;

        $sql = "SELECT u.id, u.username, u.idnumber, u.firstname, u.lastname, u.email
                FROM {role_assignments} as ra
                LEFT JOIN
                    {user} as u
                ON
                    u.id = ra.userid
                WHERE ra.contextid = :contextid
                      AND ra.roleid = :roleid
                ORDER BY u.lastname ASC";

        $users = $DB->get_records_sql($sql, $params);
        
        $grades_array = array();
        
        foreach($users as $user) {
            
            $grade = grade_get_course_grade($user->id, $course->id); // User "Course Total" grade

            $grades = new Object();
            $grades->uid = $user->id;
            $grades->username = $user->username;
            $grades->isikid = $user->idnumber;
            $grades->email = $user->email;
            $grades->grade = $grade->grade;
            $grades->str_grade = $grade->str_grade;
            
            $grades_array[] = $grades;
        }
        
        return $grades_array;
    }
}