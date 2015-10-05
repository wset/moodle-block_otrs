
SETUP OTRS
==========

* Go to Admin > SysConfig > Framework > Core:Soap and set a username and password.
* Go to Admin > Package manager and (if required) install the fixed version of the
  SOAP opm file (included)

ADD NEW CUSTOMER USER FIELDS
============================

* Open the database client
* Select OTRS database
* Source the file install/custom_otrs_fields.sql
* Add the text from install/Config.pm into your /opt/otrs/Kernel/Config.pm file under 
  "insert your own config settings here"

SETUP MOODLE
============

Go to the block setup (Site administration > Modules) and configure the block.
You need the path to the rpc.pl file and the username and password you set
up in the first step. You also need to name the OTRS queue for new tickets (it's
probably a good idea to create a queue called 'moodle' in OTRS)


* If it doesn't work check Log in OTRS Admin > System Log
