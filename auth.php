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

require_once($CFG->dirroot.'/grade/export/lib.php');
require_once dirname(dirname(dirname(__FILE__))) . '/lib/authlib.php';
require_once dirname(dirname(dirname(__FILE__))) . '/grade/querylib.php';

require_once $CFG->libdir.'/enrollib.php';
require_once $CFG->libdir.'/accesslib.php';
require_once dirname(dirname(dirname(__FILE__))) . '/enrol/eek/lib.php';

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
            $user->password = md5($password);
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
                return $user;
            } else {
                //Failed to create user
                return false;
            }
        } else {
            //Update existing user
            $user = $DB->get_record('user', array('idnumber' => $isikid, 'confirmed' => 1, 'deleted' => 0));
            if (!$user) {
                //If cant find user by idnumber
                $user = $DB->get_record('user', array('username' => $username, 'confirmed' => 1, 'deleted' => 0));
            }
            /*Fields to be updated..*/
            /*
            $user->firstname = $firstname;
            $user->lastname = $lastname;
            $user->country = $country;
            $user->city = $city;
            */
            return false;
        }
    }
    
    /*
     * Synchronize groups (add,delete)
     */
    function syncgroups($courseshortname, $groups) {
        
        $course = $DB->get_record('course', array('shortname' => $courseshortname)); //get course data

        //First we check existing groups and delete unused/empty OIS groups
        $allgroups = groups_get_all_groups($course->id, 0, 0, 'id, idnumber');

        foreach ($allgroups as $key => $value) {
            if (!empty($value->idnumber)) {
                if (!in_array($value->idnumber, array_keys($groups))) {
                    $group_members = groups_get_members($value->id);
                    if (count($group_members) < 1) {
                        groups_delete_group($value->id);
                    }
                }
            }
        }

        //Now lets create new groups if neccessary..
        foreach($groups as $key => $value) {
            
            cache_helper::invalidate_by_definition('core', 'groupdata', array(), array($course->id)); //Clear group cache
            
            $groupbyidnumber = groups_get_group_by_idnumber($course->id, $key);
            
            if (!$groupbyidnumber) {
                $data = new stdClass();
                $data->name = $value;
                $data->idnumber = $key;
                $data->courseid = $course->id;
                $data->descriptionformat = 1;
                $data->enrolmentkey = $value;
                groups_create_group($data, false, false);
            } else {
                continue;
            }            
        }
    }
    
    /*
     * Synchronize course members
     */
    function synccoursemembers($courseshortname, $members, $group = false) {
        global $DB;
        
        $members = unserialize($members);
        
        $course = $DB->get_record('course', array('shortname' => $courseshortname)); //get course data
        
        $eek_plugin = new enrol_eek_plugin();        
        if (!$DB->record_exists('enrol', array('courseid'=>$course->id, 'enrol'=>'eek'))) {
            // only one instance allowed, sorry
            $eek_plugin->add_instance($course);
        }

        $instance = $DB->get_record('enrol', array('courseid'=>$course->id, 'enrol'=>'eek'), '*', MUST_EXIST);
        $context = context_course::instance($instance->courseid);
        $role = $DB->get_record('role', array('shortname'=>'student'), '*', MUST_EXIST);        
        $processed_users = array();
        /*
        $enrol_eek = '';
        $instances = enrol_get_instances($course->id, true);
        foreach($instances as $instance) {
            if ($instance->enrol == 'eek') {
                $enrol_eek = $instance;
            }
        }
        */
        //Enrol users
        foreach($members as $key => $value) {
            $user = $DB->get_record('user', array('idnumber' => $value->idnumber, 'deleted' => '0'));            
            if (!$user) {
                if (empty($value->email)) {
                    //Email missing
                    //$error_text .= '<b>'.get_string('auth_ois_email_missing', $msg_lang, $str_obj).'</b><br />';
                    continue;                        
                } else if ($chk_email = $DB->get_record('user', array('email' => $value->email, 'deleted' => '0'))) {
                    //Preventing duplicate account creaton if email or identic idnumber(isikukood) already existing in moodle base
                    //$error_text .= '<b>'.get_string('auth_ois_email_in_use', $msg_lang, $str_obj).'</b><br />';
                    continue;
                }                
                $udata = $this->syncuser($value->idnumber, $value->type, $value->username, $value->password, $value->firstname, $value->lastname, $value->email, $value->country, $value->city, $value->deleted);
                
                if ($udata) {
                    $eek_plugin->enrol_user($instance, $udata->id, $role->id, 0, 0, ENROL_USER_ACTIVE);
                    //Add to group if required
                    if (!empty($group)) {
                        //add user to group if group exists
                        $groupinfo = groups_get_group_by_idnumber($course->id, $group);
                        if (!empty($groupinfo)) {
                            groups_add_member($groupinfo->id, $udata->id);
                        }
                    }
                } else {
                    //echo 'error creating user';
                    continue;
                }
            } else {                
                if (!is_enrolled($context, $user)) {
                    $eek_plugin->enrol_user($instance, $user->id, $role->id, 0, 0, ENROL_USER_ACTIVE);
                    //Add to group if required
                    if (!empty($group)) {
                        //add user to group if group exists
                        $groupinfo = groups_get_group_by_idnumber($course->id, $group);
                        if (!empty($groupinfo)) {
                            groups_add_member($groupinfo->id, $udata->id);
                        }
                    }
                }
            }
            array_push($processed_users, $value->idnumber);
        }

        //Unenrol and Sync users with SIS
        $enrolled_users = get_enrolled_users($context, '', '', 'u.id, u.idnumber, u.firstname, u.lastname');
        foreach ($enrolled_users as $key => $value) {            
            if (!in_array($value->idnumber, $processed_users)) {
                $eek_plugin->unenrol_user($instance, $value->id);
            }
        }
        
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
    function getoutcome($courseshortname, $isikid) {
        global $DB;
        
        $user = $DB->get_record('user', array('idnumber' => $isikid, 'deleted' => '0')); // Moodle user object
        $course = $DB->get_record('course', array('shortname' => $courseshortname)); //get course data
        
        $grade = grade_get_course_grade($user->id, $course->id); // User "Course Total" grade
        
        $usergrade = new Object();
        $usergrade->grade = $grade->grade;
        $usergrade->str_grade = $grade->str_grade;
        
        return $grade;
    }
    
    /*
     * Get all "Course Total" grades from a course
     */
    function getoutcomes($courseshortname) {
        global $DB;
        
        $course = $DB->get_record('course', array('shortname' => $courseshortname)); // Get course data

        if (!$context = context_course::instance($course->id)) {
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