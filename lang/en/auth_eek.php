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

$string['pluginname'] = 'EEK Sync';
$string['auth_eekdescription'] = 'EEK Sync description';
$string['auth_description'] = 'EEK Sync description';
$string['auth_eek_course_missing'] = 'Course does not exist!';
$string['auth_eek_email_missing'] = 'User {$a->firstname} {$a->lastname} e-mail is missing!';
$string['auth_eek_email_in_use'] = 'User {$a->firstname} {$a->lastname} e-mail is already used in Moodle, skipping!';
$string['auth_eek_user_created'] = 'User {$a->firstname} {$a->lastname} has been created!';
$string['auth_eek_user_creation_failed'] = 'Failed to create user {$a->firstname} {$a->lastname}.';
$string['auth_eek_user_updated'] = 'User {$a->firstname} {$a->lastname} has been updated!';
$string['auth_eek_user_update_failed'] = 'Failed to update user {$a->firstname} {$a->lastname} details!';
$string['auth_eek_user_enrolled'] = 'User {$a->firstname} {$a->lastname} enrolled to course.';
$string['auth_eek_user_unenrolled'] = 'User {$a->firstname} {$a->lastname} unenrolled from course.';
$string['auth_eek_user_missing'] = 'User with ID {$a->idnumber} is missing from Moodle!';
$string['auth_eek_group_created'] = 'Group {$a->name} has been created!';
$string['auth_eek_group_creation_failed'] = 'Failed to create group {$a->name}';
$string['auth_eek_group_deleted'] = 'Group {$a->name} has been deleted!';
$string['auth_eek_group_deletion_failed'] = 'Failed to delete group {$a->name}';
$string['auth_eek_group_user_add'] = 'User {$a->firstname} {$a->lastname} has been added to group {$a->group}.';
// Config form strings.
$string['auth_dbdebugautheek'] = 'Debug ADOdb';
$string['auth_dbdebugautheekhelp'] = 'Debug ADOdb connection to external database - use when getting empty page during login. Not suitable for production sites.';
$string['auth_dbhost'] = 'The computer hosting the database server. Use a system DSN entry if using ODBC.';
$string['auth_dbhost_key'] = 'Host';
$string['auth_dbtype'] = 'The database type (See the <a href="http://phplens.com/adodb/supported.databases.html" target="_blank">ADOdb documentation</a> for details)';
$string['auth_dbtype_key'] = 'Database';
$string['auth_dbname'] = 'Name of the database itself. Leave empty if using an ODBC DSN.';
$string['auth_dbname_key'] = 'DB name';
$string['auth_dbuser'] = 'Username with read access to the database';
$string['auth_dbuser_key'] = 'DB user';
$string['auth_dbpass'] = 'Password matching the above username';
$string['auth_dbpass_key'] = 'Password';
$string['auth_dbtable'] = 'Name of the table in the database';
$string['auth_dbtable_key'] = 'Table';
