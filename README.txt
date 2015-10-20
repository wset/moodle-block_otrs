
OTRS Help Desk Block
====================

Uses OTRS's RPC connector to sync user details and the generic interface to view,
create and update tickets.

SETUP OTRS
==========

* Go to Admin > SysConfig > Framework > Core:Soap and set a username and password.
* Go to Admin > Web Services and create a SOAP web service with 4 operations 
  (TicketCreate, TicketGet, TicketSearch, TicketUpdate)
* Go to Admin > Agents and create a new agent for the web service just created.  
  They will need access to view and add to any queues you want to use in moodle.

ADD NEW CUSTOMER USER FIELDS
============================

* Open the database client
* Select OTRS database
* Source the file install/custom_otrs_fields.sql
* Add the text from install/Config.pm into your /opt/otrs/Kernel/Config.pm file under 
  "insert your own config settings here"

SETUP MOODLE
============

Place block files in /block/otrs within the moodle tree and install plugin in the usual 
way.

Go to the block setup (Site administration > Modules) and configure the block.
You need the path to the rpc.pl file and the username and password you set
up in the first step, as well as the path to the web service you setup in the second 
step and the username and password you setup in the third. You also need to name the 
OTRS queue  for new tickets (it's probably a good idea to create a queue called 'moodle' 
in OTRS)


* If it doesn't work check Log in OTRS Admin > System Log for rpc.pl errors or the 
debugging in  Admin > Web Services for the generic interface.
