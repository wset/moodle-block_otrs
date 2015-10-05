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

class add_comment_form extends moodleform {

    function definition() {
        global $CFG, $COURSE;

        // get custom data passed to constructor
        $customdata = $this->_customdata;
    
        // because it is
        $mform =& $this->_form;

        // create the form
        $mform->addElement( 'header', 'otrsheader', get_string( 'addcomment','block_otrs' ));

        $mform->addElement('html','<div class="otrsinstruct">'.get_string( 'addcommentinfo','block_otrs' ).'</div>' );

        $mform->addElement( 'text', 'subject', get_string( 'subject','block_otrs' ), 'size=50' );
        $mform->addRule( 'subject', null, 'required' );
        $mform->setDefault( 'subject',get_string( 're','block_otrs' ).' '.$customdata['title'] );
        $mform->setType( 'subject', PARAM_TEXT );

        $mform->addElement( 'editor', 'comment', get_string( 'comment','block_otrs' ),
            array(
                'canUseHtmlEditor'=>'detect',
                'rows'=>15,
                'cols'=>50,
                )
            );
        $mform->setType( 'comment', PARAM_RAW );
        $mform->addRule( 'comment', null, 'required' );

        // hidden field
        $mform->addElement( 'hidden','id',$customdata['id'] );
        $mform->setType('id', PARAM_INT);
        $mform->addElement( 'hidden','ticket',$customdata['ticket'] );
        $mform->setType('ticket', PARAM_INT);

        $this->add_action_buttons();
    }
}
