asterisk-scripts by Ryan Hunt
All licensed CC-BY-SA

-  gvoicemail-mwi.php (Requires GoogleVoice.php)
	This script is ran in crontab to create fake voice mail messages when you have
 	messages in your google voice mailbox.
	It supports multiple users, it is used to trigger MWI indicators when you have 
	a google voice mail.

- sccp-dst.sh
	This script I run in crontab on every sunday; it updates the DST and resets my 
	phones when needed.

- cnam-lookup.php
	This script queries multiple databases of Google Contacts first for CNAM info,
	with priority to the user being called.
	If the user is not found it uses the OpenCNAM database. Also shows state call
	originates from.

- GoogleVoice.php
	This is a library for gvoicemail-mwi.php

