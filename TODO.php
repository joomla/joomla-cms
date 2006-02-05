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

defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<style type="text/css">
#notes { text-align: center; margin: auto 0; }

s { color: red; }
.todo {
	background-color: #F9F9F9;
	height: 300px;
	overflow: auto;
	color: black;
	border: 1px solid #999999;
	padding: 20px;
	display: block;
	text-align: left;
}
hr { 
	border: 1px dotted black; 
}
span.todotitle {
	font-weight: bold;
	color: black;
}
.highlight {
	font-weight: bold;
	color: red;
}
</style>
<div id="notes">
<h3>TESTER NOTES</h3>
<pre class="todo">
<span class="todotitle">STATUS</span>

<b>** Joomla! 1.1.0 Alpha Release2 [06-02-2006] **</b>

<span class="highlight">** This should NOT meant to be used for a `live` or `production` site **</span>
This Alpha version is for testing and development purposes, or internal use only.  Use at your own risk!

<hr/>
<span class="todotitle">BROKEN</span>

To be able to handle all languages correctly we are moving towards UTF-8. It's clear that
this transistion will go with a few roadbumps. We predict problems with upgrading from an
existing db, and the use of some string related PHP functions. We are aware of these issues.

The reason for the alpha is to get them identified. U can help us by clearly stating problems
u are experiencing. For now we advice u to stay away from upgrading an existing site.
Upgrading problems will be handled after we identified and fixed any problems with translations
and new installs.

<hr/>
<span class="todotitle">REPORTING</span>

- Bug and problems
Forum   : <a target="_blank" href="http://forum.joomla.org/index.php/board,179.0.html">Quality Assurance and Testing</a>
Tracker : <a target="_blank" href="http://developer.joomla.org/sf/tracker/do/listArtifacts/projects.joomla/tracker.bug_tracker_1_1_x">Bug Tracker</a>

- Feature requests
Forum   : <a target="_blank" href="http://forum.joomla.org/index.php/board,38.0.html">Wishlist and Feature requests</a>
Tracker : <a target="_blank" href="http://developer.joomla.org/sf/tracker/do/listArtifacts/projects.joomla/tracker.feature_requests">Feature Requests</a>

<hr/>
<span class="todotitle">CHANGES</span>

- Languages
	* Backend Translated
	* Frontend Translated
	* RTL language compilance
	* UTF-8 output
	
- Framework
	* Joomla! Framework introduced
	* FTP installer
	* New PDF library (TCPDF)

- New plugins
	* authentication
	* user
	* xml-rpc
	* syndicate

- Other
	* Changed CSS editor, can edit all css files now
	* Added statistics reset functions
	* Added new com_syndicate

- Removed files
	* /pathway.php
	* /mainbody.php
	* /offlinebar.php
	* /offline.php
	* /includes/metadata.php
	* /includes/sef.php
	* /includes/agent_browser
	* /includes/agent_os
	
- Removed templates
	* /templates/madeyourday/..
	* /templates/rhuk_solarflare_ii/..
	* /administrator/templates/mambo_admin/...
	* /administrator/templates/mambo_admin_blue/...
	
- Removed modules
	* /modules/mod_templatechooser
	* /modules/mod_rssfeed
	
- Removed components
	* /components/com_rss/..

<hr/>
<span class="todotitle">TODO</span>

- Languages
	* Component language installation

- Other
	* Feature requests on the tracker
	* Session timeout issues
	* Core extension refactoring
	
- User Interface
	* Usability changes
	* Media manager improvements

<hr/>

</pre>
</div>