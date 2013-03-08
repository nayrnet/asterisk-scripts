asterisk-scripts by Ryan Hunt
All licensed CC-BY-SA
Used on Asterisk 1.8.x

- gvoicemail-mwi.php (Requires GoogleVoice.php)
	This CLI script is ran in crontab to create fake voice mail messages when you
	have messages in your google voice mailbox.
	It supports multiple users, it is used to trigger MWI indicators when you
	have a google voice mail. Enable polling in voicemail.conf

- gvcontact-sync.php
	This CLI script fetches google contacts and stores them in a local mysql db.
	It supports multiple users, I use this data for phone book and cnam queries.

- sccp-dst.sh
	This shell script I run in crontab on every sunday; it updates the DST and
	resets my phones when needed. For chan_sccp-b realtime setup.

- cnam-lookup.php
	This CLI script queries multiple databases of Google Contacts first for CNAM
	info, with priority to the user being called.
	If the name is not found it uses the OpenCNAM database and caches it. 
	Also shows state call originates from.

- GoogleVoice.php
	This is a library for gvoicemail-mwi.php

