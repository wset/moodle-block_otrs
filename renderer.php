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

define( 'MAX_BLOCK_OPEN',5 );
define( 'MAX_BLOCK_CLOSED',5 );

class block_otrs_renderer extends plugin_renderer_base {

    /**
     * display a single Article
     */
    static function displayArticle( $Articlei, $Count ) {

        // start constructing html
        $html = '<div class="otrs_article">';

        // article headline
        


        // close div
        $html .= '</div>';
    }

    /**
     * display article table
     */
    static function ArticleTable( $Articles, $baseURL, $SelectedID ) {
  
        // start constructing html
        $table = new html_table();
        $table->attributes = array('class' => 'table');

        // table and headings
        $table->head = array(
            get_string( 'number','block_otrs' ),
            get_string( 'type','block_otrs' ),
            get_string( 'from','block_otrs' ),
            get_string( 'to','block_otrs' ),
            get_string( 'subject','block_otrs' ),
            get_string( 'created','block_otrs' ),
            '&nbsp',
        );

        // iterate over Article
        $count = 1;
        foreach ($Articles as $Article) {
            $link = "$baseURL&article={$Article->ArticleID}";
            if ($SelectedID == $Article->ArticleID) {
                $class = 'info';
            }
            else {
                $class = '';
            }
            $row = new html_table_row( array(
                $count,
                $Article->ArticleType,
                $Article->From,
                $Article->To,
                $Article->Subject,
                $Article->Created,
                "<a class=\"btn btn-info\" href=\"$link\">".get_string( 'view','block_otrs').'</a>',
            ));
            $table->data[] = $row;
            $table->rowclasses[] = $class;

            $count++;
        }

        return html_writer::table($table);
    }

    /**
     * display single article
     */
    static function SingleArticle( $Article, $format=FORMAT_HTML ) {
        global $OUTPUT;

        // layout
        $html = '<div class="alert alert-success">';
        $html .= "<h3>{$Article->Subject}</h3>";
        $html .= "<div class=\"otrs_article_body\">" . format_text($Article->Body, $format) . "</div>";
        $html .= '</div>';

        return $html;
    }

    /**
     * comment button
     */
    static function CommentButton($url) {
        return '<p><a class="btn" href="'.$url.'">'.get_string('addcomment', 'block_otrs').'</a></p>';
    }

    /**
     * list a single ticket (private for BlockTicketList)
     */
    private static function BlockTicketSingle( $Ticket, $id, $courseid ) {
        global $CFG;

        $link = new moodle_url("/blocks/otrs/view_ticket.php", array('id'=>$id, 'ticket'=>$Ticket->TicketID, 'courseid'=>$courseid));
        $class = "class=\"otrsstate_{$Ticket->State}\"";
        $html = "<li $class><a href=\"$link\">{$Ticket->Subject}</a></li>";

        return $html;
    }

    /**
     * ticket list in block
     */
    static function BlockTicketList( $Tickets, $id, $courseid ) {
        global $CFG;

        // section for open tickets
        $out = '<div id="otrs_block_open">';
        $out .= '<h4>'.get_string('activetickets','block_otrs').'</h4>';
        $out .= '<ul>';

        // show open and pending (not closed) tickets
        $count = 1;
        foreach ($Tickets as $Ticket) {
            if ((stripos($Ticket->State,'closed')===false) and ($count<=MAX_BLOCK_OPEN)) {
                $out .= self::BlockTicketSingle( $Ticket, $id, $courseid );
                $count++;
            } 
        }
        $out .= '</ul>';

        // did we display any
        if ($count==1) {
            $out .= '<center><p class="alert alert-warning">'.get_string('notickets','block_otrs').'</p></center>';
        }

        // show 'more' link if that wasn't all
        if ($count > MAX_BLOCK_OPEN) {
            $linkstr = get_string('moretickets','block_otrs');
        } else {
            $linkstr = get_string('fulltickets','block_otrs');
        }
        $link = new moodle_url('/blocks/otrs/list_tickets.php', array('id'=>$id, 'courseid'=>$courseid));
        $out .= "<p><a class=\"btn btn-default\" href=\"$link\">$linkstr</a></p>";            

        // end of open tickets bit
        $out .= '</div>';

        // section for closed tickets
        $out .= '<div id="otrs_block_open">';
        $out .= '<h4>'.get_string('closedtickets','block_otrs').'</h4>';
        $out .= '<ul>';

        // show open and pending (not closed) tickets
        $count = 1;
        foreach ($Tickets as $Ticket) {
            if ((stripos($Ticket->State,'closed')!==false) and ($count<=MAX_BLOCK_CLOSED)) {
                $out .= self::BlockTicketSingle( $Ticket, $id, $courseid );
                $count++;
            } 
        }
        $out .= '</ul>';

        // did we display any
        if ($count==1) {
            $out .= '<center><p class="alert alert-warning">'.get_string('notickets','block_otrs').'</p></center>';
        }

        // show 'more' link if that wasn't all
        if ($count > MAX_BLOCK_CLOSED) {
            $linkstr = get_string('moretickets','block_otrs');
            $link = new moodle_url('/blocks/otrs/list_tickets.php', array('id'=>$id, 'courseid'=>$courseid));
            $out .= "<p><a class=\"btn btn-default\" href=\"$link\">$linkstr</a></p>";            
        }

        // end of closed tickets bit
        $out .= '</div>';

        return $out;
    }

    /**
     * open/closed switch
     */
    static function OpenClosed( $baseurl, $state ) {
        global $OUTPUT;
    
        // build tabs
        $tabs = array();
        $tabs[] = new tabobject('all', "$baseurl&state=all", get_string('showall','block_otrs'));
        $tabs[] = new tabobject('open', "$baseurl&state=open", get_string('showopen','block_otrs'));
        $tabs[] = new tabobject('closed', "$baseurl&state=closed", get_string('showclosed','block_otrs'));

        return $OUTPUT->tabtree($tabs, $state);
    }

    /**
     * table of tickets (open or closed) for list page
     */
    static function listTickets( $Tickets, $id, $url, $open=true, $closed=true ) {
        global $CFG;

        // use html_table class
        $table = new html_table();

        // table headings
        $table->head = array(
            get_string( 'number','block_otrs' ),
            get_string( 'created','block_otrs' ),
            get_string( 'priority','block_otrs' ),
            get_string( 'subject','block_otrs' ),
            get_string( 'state','block_otrs' ),
            '&nbsp',
        );

        // iterate over tickets
        $count = 1;
        foreach ($Tickets as $Ticket) {

            //open/closed ?
            $foundclosed = stripos( $Ticket->State, 'closed' );
            if (!$open and ($foundclosed===false)) {
                continue;
            }
            if (!$closed and ($foundclosed !== false)) {
                continue;
            }

            // view ticket link
            $link = "$url&ticket={$Ticket->TicketID}";

            $table->data[] = array(
                $count,
                $Ticket->Created,
                $Ticket->Priority,
                $Ticket->Subject,
                $Ticket->State,
                "<a class=\"btn btn-info\" href=\"$link\">".get_string('view','block_otrs')."</a>",
            );
            $count++;
        }

        $html = '<div>' . html_writer::table($table);
        
        // check for nothing shown
        if ($count==1) {
            $html .= "<center><p class=\"btn btn-info\" href=\"$link\">".get_string('notickets','block_otrs')."</p></center>";
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * table of users with tickets
     */
    static function listUserTickets( $users, $id, $courseid ) {
        global $CFG;

        // build html
        $table = new html_table();

        // table headings
        $table->head = array(
            get_string( 'username','block_otrs' ),
            get_string( 'email','block_otrs' ),
            '&nbsp;',
        );

        // iterate over users
        $count = 1;
        foreach ($users as $user) {

            // view ticket link
            $link = new moodle_url('/blocks/otrs/list_tickets.php', array('id'=>$id, 'user'=>$user->id, 'courseid'=>$courseid));

            $table->data[] = array(
                fullname( $user ),
                $user->email,
                "<a class=\"btn btn-info\" href=\"$link\">".get_string('view','block_otrs')."</a>",
            );
            $count++;
        }

        $html = html_writer::table($table);
        
        // check for nothing shown
        if ($count==1) {
            $html .= "<center><p class=\"alert alert-danger\">".get_string('nousers','block_otrs')."</p></center>";
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * new ticket button
     */
    static function newTicketButton( $id, $courseid ) {
        global $CFG;

        $strcreateticket = get_string( 'createticket','block_otrs' );
        $url = new moodle_url('/blocks/otrs/create_ticket.php', array('id'=>$id, 'courseid'=>$courseid));
        $html = '<p><a class="btn btn-info" href="'.$url.'">'.$strcreateticket.'</a></p>';

        return $html;
    }

    /**
     * user tick list button
     */
    static function userTicketsButton( $id, $courseid ) {
        global $CFG;

        $strlistusertickets = get_string( 'listusertickets','block_otrs' );
        $url = new moodle_url('/blocks/otrs/list_users.php', array('id'=>$id, 'courseid'=>$courseid));
        $html = '<p><a class="btn btn-info" href="'.$url.'">'.$strlistusertickets.'</a></p>';

        return $html;
    }
}
