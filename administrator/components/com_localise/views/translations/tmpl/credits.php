<?php
/**
* @version 1.5
* @package com_localise
* @author Ifan Evans
* @copyright Copyright (C) 2008 Ifan Evans. All rights reserved.
* @license GNU/GPL
* @bugs - please report to post@ffenest.co.uk
*/

defined('_JEXEC') or die('Restricted access');

// TOOLBAR
// ! configure/preferences check will be deprecated
JToolbarHelper::title( JText::_( 'Translation Manager' ), 'langmanager.png' );
JToolbarHelper::custom('languages','upload.png','upload_f2.png','Languages',false);
JToolbarHelper::divider();
( is_callable( array('JToolbarHelper', 'preferences') ) ) ? JToolbarHelper::preferences('com_localise',400,600) : JToolbarHelper::configuration('com_localise',400,600);;
?>
<form action="index.php" method="post" name="adminForm">
	<input type="hidden" name="option" value="com_localise" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="1" />
</form>
<h1>Translation Manager Credits</h1>
<p>Version 1.5.1</p>
<p>This is an utility component which helps with creating and managing Joomla 1.5 translations<br />
(NB: It does not manage content, only the site language)</p>
<ul>
	<li>Manage site, admin and installation files</li>
	<li>Create, Edit, Delete Languages (XML files and folders)</li>
	<li>Create, Edit, Delete, Publish, Unpublish, Checkin, Checkout Translation Files (INI files)</li>
</ul>
<ul>
	<li>Customisable Reference Language (defaults to en-GB)
	<li>Shows translation progress of individual language files against the reference language</li>
	<li>Shows translation progress for entire client-language sets against the reference language</li>
	<li>Search all the files in a language set for phrase(s) with customisable CSS highlighting of matches</li>
	<li>Customisable AutoCorrect of text entry (handy for unusual characters/diacritics)</li>
	<li>Customisable change of single-quotes to backticks or vice-versa</li>
	<li>Customisable global replace of a key=phrase combination (handy when the same combo is in many language files)</li>
</ul>
<ul>
	<li>Automatically create missing INI files</li>
	<li>Highlight phrases in an INI file that have yet to be translated</li>
	<li>Highlight and create or delete extra phrases (ones that don't exist in the reference language)</li>
</ul>
<ul>
	<li>Create Langage Installation Zip Files from a directory</li>
	<li>Translate the Language Tag when creating Archive files</li>
</ul>
<ul>
	<li>Extensive Tooltip Help</li>
	<li>Configurable Preferences</li>
</ul>
<ul>
	<li>With thanks to component translators</li>
	<li>bg-BG: Иво Апостолов</li>
	<li>ca-AD: Manuel Soler</li>
	<li>cy-GB: Ifan Evans</li>
	<li>da-DK: Joomla! Project</li>
	<li>de-DE: René Serradeil</li>
	<li>es-ES: Manuel Soler</li>
	<li>hu-HU: Herczeg József Tamás</li>
	<li>nb-NO: Joomla! i Norge</li>
	<li>nl-NL: Joomla! Nederlands</li>
	<li>sv-SE: Swedish Translation Team</li>
</ul>
<p></p>
<p><b style="color:red">YOU SHOULD MAKE A BACKUP OF YOUR LANGUAGE FILES BEFORE USING THIS COMPONENT</b></p>
<p></p>
<hr>