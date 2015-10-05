<?php

/**
 * OTRS Integration Block
 *
 * @author Howard Miller
 * @version  See version in block_otrs.php
 * @copyright Copyright (c) 2011 E-Learn Design Limited
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package block_otrs
 */


require_once( $CFG->libdir.'/formslib.php');

class create_ticket_form extends moodleform {

    function definition() {
        global $CFG, $COURSE;

        // get custom data passed to constructor
        $customdata = $this->_customdata;
    
        // because it is
        $mform =& $this->_form;

        // create the form
        $mform->addElement( 'header', 'otrsheader', get_string( 'newticket','block_otrs' ));

        $mform->addElement('html','<div class="otrsinstruct">'.get_string( 'newticketinfo','block_otrs' ).'</div>' );

        $mform->addElement( 'text', 'subject', get_string( 'subject','block_otrs' ) );
        $mform->addRule( 'subject', null, 'required' );
        $mform->setType('subject', PARAM_TEXT);

        $mform->addElement( 'editor', 'description', get_string( 'description','block_otrs' ));
        $mform->setType( 'description', PARAM_RAW );
        $mform->setDefault( 'description', $customdata['description'] );
        $mform->addRule( 'description', null, 'required' );
        //$mform->addElement( 'format','format',get_string('format') );

        // hidden field
        $mform->addElement( 'hidden','id',$customdata['id'] );
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons();
    }
}
