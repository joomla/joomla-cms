<?php
/**
* @version $Id: TODO.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

defined( '_VALID_MOS' ) or die( 'Restricted access' );
?>
<span class="todotitle">STATUS</span>

<b>** Joomla! 1.1.0 Alpha Release [26-10-2005] **</b>

Alpha or beta versions are versions for testing purposes or internal use only. 
They are not meant to be used in for general or practical deployment.

<hr/>

<span class="todotitle">BROKEN</span>

To be able to handle all languages correctly we are moving towards UTF-8. It's clear that 
this transistion will go with a few roadbumps. We predict problems with upgrading from an 
existing db, and the use of some string related PHP functions. We are aware of these issues.

The reason for the alpha is to get them identified. U can help us by clearly stating problems 
u are experiencing. For now we advice u to stay away from upgrading an existing site. 
Upgrading problems will be handled after we identified and fixed any problems with translations 
and new installs.

<span class="todotitle">REPORTING</span>

- Bug and problems
Forum   : <a target="_new" href="http://forum.joomla.org/index.php/board,179.0.html">Quality Assurance and Testing</a>
Tracker : <a target="_new" href="http://developer.joomla.org/sf/tracker/do/listArtifacts/projects.joomla/tracker.bug_tracker_1_1_x">Bug Tracker</a>

- Feature requests
Forum   : <a target="_new" href="http://forum.joomla.org/index.php/board,38.0.html">Wishlist and Feature requests</a>
Tracker : <a target="_new" href="http://developer.joomla.org/sf/tracker/do/listArtifacts/projects.joomla/tracker.feature_requests">Feature Requests</a>

<hr/>
<span class="todotitle">CHANGES</span>

- Languages
	* Backend Translated
	* RTL language compilance
	* UTF-8 output
	
- Systembots
	* SSL + new mosLink function
	* SEF

- Userbots 

- Other 
	* Changed CSS editor, can edit all css files now
	* Added statistics reset functions
	
- Depreceated files
	* /pathway.php
	* /mainbody.php
	* /includes/metadata.php
	* /includes/sef.php
	* /administrator/templates/mambo_admin/...
	* /administrator/templates/mambo_admin_blue/...

<hr/>

<span class="todotitle">TODO</span>

- Languages
	* Language manager and installer
	* Installation translation
	
- Other
	* XML-RPC server
	* LDAP library class
	* Allow adming folder to reside elsewhere
	* Feature requests on the tracker

<hr/>