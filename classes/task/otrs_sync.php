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
 *  OTRS Block - Scheduled Tasks
 *
 *  This filter will replace defined cloudfront URLs with signed
 *  URLs as described at http://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/private-content-signed-urls.html
 *
 * @package    block
 * @subpackage otrs
 * @copyright  2015 Owen Barritt, Wine & Spirit Education Trust
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 namespace block_otrs\task;

 require_once( dirname(__FILE__).'/../../otrslib.class.php' );
 
 class otrs_sync extends \core\task\scheduled_task {
    public function get_name() {
        // Shown in admin screens
        return get_string('sync_task', 'block_otrs');
    }

    public function execute() {
        global $DB;

        mtrace( 'OTRS: starting sync' );
        
        $lastruntime = $this->get_last_run_time();
        
        // get all ids for active users
        // just ids to keep memory down
        // limit to users with profiles updated since lastrun
        $sql = "select id from {user} as u ";
        $sql .= "where u.deleted=0 and timemodified>=?";
        
        $userids = $DB->get_records_sql( $sql, array($lastruntime) );
        mtrace( 'processing users - '.count($userids) );

        // run through users
        echo "\n";
        foreach ($userids as $userid) {
            mtrace( 'Updating '.$userid->id);
            \otrslib::userupdate($userid);
        }

        return true;
    }
}