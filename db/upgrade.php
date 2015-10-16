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
defined('MOODLE_INTERNAL') || die;

function xmldb_block_otrs_upgrade($oldversion) {
    global $CFG;

    if($oldversion < 2015101500) {
        if(isset($CFG->block_otrs_queue)) {
            set_config('queue',$CFG->block_otrs_queue,'block_otrs');
            unset_config('block_otrs_queue');
        }
        if(isset($CFG->block_otrs_quiz_queue)) {
            set_config('quiz_queue',$CFG->block_otrs_quiz_queue,'block_otrs');
            unset_config('block_otrs_quiz_queue');
        }
        if(isset($CFG->block_otrs_completion_queue)) {
            set_config('completion_queue',$CFG->block_otrs_completion_queue,'block_otrs');
            unset_config('block_otrs_completion_queue');
        }
        if(isset($CFG->block_otrs_user_update_queue)) {
            set_config('user_update_queue',$CFG->block_otrs_user_update_queue,'block_otrs');
            unset_config('block_otrs_user_update_queue');
        }
        if(isset($CFG->block_otrs_course_dfield)) {
            set_config('course_dfield',$CFG->block_otrs_course_dfield,'block_otrs');
            unset_config('block_otrs_course_dfield');
        }
        if(isset($CFG->block_otrs_url)) {
            set_config('rpcurl',$CFG->block_otrs_url,'block_otrs');
            unset_config('block_otrs_url');
        }
        if(isset($CFG->block_otrs_otrsuser)) {
            set_config('rpcuser',$CFG->block_otrs_otrsuser,'block_otrs');
            unset_config('block_otrs_otrsuser');
        }
        if(isset($CFG->block_otrs_otrspassword)) {
            set_config('rpcpassword',$CFG->block_otrs_otrspassword,'block_otrs');
            unset_config('block_otrs_otrspassword');
        }
        if(isset($CFG->block_otrs_userfields)) {
            set_config('userfields',$CFG->block_otrs_userfields,'block_otrs');
            unset_config('block_otrs_userfields');
        }
        if(isset($CFG->block_otrs_giurl)) {
            set_config('giurl',$CFG->block_otrs_giurl,'block_otrs');
            unset_config('block_otrs_giurl');
        }
        if(isset($CFG->block_otrs_gins)) {
            set_config('gins',$CFG->block_otrs_gins,'block_otrs');
            unset_config('block_otrs_gins');
        }
        if(isset($CFG->block_otrs_giuser)) {
            set_config('giuser',$CFG->block_otrs_giuser,'block_otrs');
            unset_config('block_otrs_giuser');
        }
        if(isset($CFG->block_otrs_gipassword)) {
            set_config('gipassword',$CFG->block_otrs_gipassword,'block_otrs');
            unset_config('block_otrs_gipassword');
        }
        upgrade_block_savepoint(true, 2015101500, 'otrs');
    }
}