<?php
/**
 * OTRS Integration Block
 *
 * @author Howard Miller
 * @version  See version in block_otrs.php
 * @copyright Copyright (c) 2011-2014 E-Learn Design Limited
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package block_otrs
 */


require_once( dirname(__FILE__).'/otrslib.class.php' );

class block_otrs extends block_base {
    function init() {
        $this->title = get_string('pluginname','block_otrs');
    }

    function applicable_formats() {
        return array('all' => true);
    }

    function has_config() {
        return true;
    }

    function get_content () {
        global $USER, $CFG, $COURSE;

        if ($this->content !== NULL) {
            return $this->content;
        }

        // some basics
        $courseid = $this->page->course->id;

        // context
        $id = $this->instance->id;
        $context = context_block::instance( $id );

        $cmid = null;
        if($this->page->cm){
            $cmid=$this->page->cm->id;
        }
        
        // in case it goes all wrong
        $this->content = new stdClass;
        $this->content->footer = '';
        $this->content->text = '';

        // are we allowed to view this block
        if (!has_capability( 'block/otrs:view', $context )) {
            return $this->content;
        }

        // search for current user's tickets
        $Tickets = otrslib::getBlockTickets( $USER );

        // display tickets
        $renderer = $this->page->get_renderer('block_otrs');
        if ($Tickets) {
            $this->content->text .= $renderer->BlockTicketList( $Tickets, $id, $courseid );
        }

        // button to add new report
        if (has_capability( 'block/otrs:create', $context )) {
            $this->content->text .= $renderer->newTicketButton( $id, $courseid, $cmid );
        }

        // button to view others
        if (has_capability( 'block/otrs:viewothers', $context )) {
            $this->content->text .= $renderer->userTicketsButton( $id, $courseid );
        }

        return $this->content;
    }

    /**
     * The cron process is no longer directly
     * linked to Moodle.
     * You have to set up your own
     */
    function cron() {
        return true;
    }

    /**
     * the cron process will grab all users
     * and sync them with OTRS
     * TODO: think of some way to only sync new users
     */
    function clicron() {
        global $DB;

        mtrace( 'OTRS: starting sync' );

        // get all ids for active users
        // just ids to keep memory down
        $userids = $DB->get_records( 'user', array('deleted'=>0), '', 'id' );
        mtrace( 'processing users - '.count($userids) );

        // run through users
        echo "\n";
        foreach ($userids as $userid) {
            mtrace( 'Updating '.$userid);
            otrslib::userupdate($userid);
        }

        return true;
    }
}

?>
