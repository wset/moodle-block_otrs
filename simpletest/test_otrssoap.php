<?php

/**
 * unit tests for OTRS Soap
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden');
}

require_once( "{$CFG->dirroot}/blocks/otrs/otrssoap.class.php" );

class otrssoap_test extends UnitTestCase {

    function test_usercreate() {
        $this->fail();
    }
}
