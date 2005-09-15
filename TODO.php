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

<b>** Preparation for Joomla! 1.1.0 Alpha Release (no fixed date) **</b>

<hr/>
<span class="todotitle">MUST FIX BEFORE ALPHA</span>

- <s>{mospagebreak} mambot not working correctly: repeats text</s>

- <s>Global configuration under reconstruction
  * need to remove the 'tabs'</s>

<hr/>
<span class="todotitle">BROKEN</span>

- Help Popup [awaiting creation of help pages on help.mamboserver.com]

- Template editing

- Media Manager
  * Not showing 'generated code'
  * File upload
  * Display Icon of non-image files

- <s>Search Component</s>

<hr/>
<span class="todotitle">TODO</span>

Can be completed after Alpha release if necessary

- Remove ACL Hardcoding
  * change things like
  . "\n WHERE gid = 25"
  to use the acl api to be future proof

- Bugs, Testing & Documenation
  [All to Help]

- Caching
  * Implemented new caching system in frontend components
  <s>* Create backend caching manager</s>
  [Jinx]

- Module Assigment
  Improve way menu list for module assingment is displayed
  [Jinx]

- Language Manager
  Handle different encoding of languages within the manager while translating
  and make sure that the files are written in the correct encoding
  [Alex]

- Fieldsets, Tree Menus, Related Links
  Ensure consistency across all default components
  Look at all editXML pages
  [Tim and Andy to work on styling]

- Related Links for Manager pages
  Check all Related links
  Add Quick tips through language manager
  Check for missing boxes
  [Rey/Jick]

- Help KeyRef
  * Help support for frontend edit pages:
		e.g. edit/new content, weblinks submission, etc
  [Rey]

- Menu Items
  * New menu items for frontend submission:
		submit content, submit weblinks etc
  * Allow 'changing' of existing menu items, instead of having to delete and create new
  * <b>NEW</b> Rey, can we get a re-parent action, ie, select a group of menu item and
	select a new parent menu for them.  Maybe it can piggy-back off the 'move' action,
	eg, move to a new menu or a new parent (desirable but not critical for alpha)
  [Rey]

- <s>TinyMCE
  * Upgrade to Version 1.45
  [Rey]</s>

- <s>User Manager
  * Simple Bulk Users Creator
  [Rey]</s>

- Media Manager
  * <s>Tree</s>
  * Handle general 'docs' type of file
  * Add configuration interface to allow settings to be stored
	in the 'params' fields of the components table
	- add com_media to the table iscore=1
	- Don't just add configuration variables to configuration.php
  * Configuraton setting for allowed image and file types
  * Test folder delete
  * Test a variation of list_icons.html to shows details list
	(list_details.html) and add list style selection
  * Change trash can to checkbox and add delete icon
  * Add 'up directory' as first folder
  * Add safeguard for not going above valid root directories
  * Add configuration variable to allow 'other' root directories,
	for example docman's /dmdocuments

- Javascript behavior layer
  * <s>introduce unobstrusive toolbar functionality (mosToolbar prototype)</s>
  * <s>introduced javascript command (taskk) pattern for handling UI interaction</s>
  * <s>introduce unobstrusive list functionality (mosList prototype)</s>
  * <s>remove hardcoded javascript calls from menubar.html.php </s>
  * introduce unobstrisive dialog functionality (mosDialog prototype)
  * introduce basic AJAX functionality (mosAJAX)
  * introduce form handling functionality (mosForm)
  [Jinx]

- Improve the search component
  <s>Solve the "Page has expired" when hitting back</s>
  <s>Page the search results</s>
  Make blocked cookies safe
  <s>Make a parameter for results per page</s>
  [Emir]

- Administrator UI
  <s>Toolbar buttons too small for other langs.  Need to  modify to allow for
  larger text</s>
  Add button for Statistics "Reset" to toolbar
  <s>[Andy] - also fix logout button.</s>

- Template
  <s>Add a div to seperate content items in blog view [Andy]</s>
  <s>Tidy up offline bar css [Andy]</s>

- <s>Statistics
  Add functions to clear the tables for search, items and agents
  [Jick]</s>

- <s>Language Files
  Move to individual language folders,e g
  /language/english/*
  Check that the parent folder has write permission when creating a language
  Rework installer to install language files to separate directories
  [Alex]</s>

- <s>Popups
  Move all files in /administrator/popups to their
  respective components, running them through
  index3.php
  [Rey]</s>

- <s>Template Manager rework</s>

- <s>Language Installer Rework
  [Andrew]</s>

- <s>Mambot Installer Rework
  [Andrew]</s>

- <s>Module Installer Rework
  [Andrew]</s>

- <s>Component Installer Rework
  [Andrew]</s>

- <s>Parameters
  Allow for parameters handler to read files with &lt;mosparams&gt; root
  for files that are not the true install file (we have a number
  of these)
  [Andrew]</s>

- <s>Move contentTree template out of page.html into separate file
  (see editXML in Template Mgr for example of how this is done)
  [Rey]</s>


<hr/>
<span class="todotitle">DEFERRED TO NEXT VERSION/RELEASE</span>

- Update/Patch handler
  Extend the installer to handle 'element' level
  or whole-of-core patching, including XML Schema
  updates
  [Sam Moffat :: Mambo-Google SOC]

- Multi-installer
  Multi install will allow you to assemble multiple
  installable 'elements' in a single zip.  The
  format will simply be that each element is put in
  it's own folder.  The multi-install will work
  through each of the top level folders as if it
  was an individual element.  The installer class
  is being reworked to facilitate this functionality.
  Will be added to com_installer.  After the file
  is uploaded (or the directory selected) the user
  should be given a list of the elements that are
  available to install.
  [Andrew]

- ADODB XML Schema
  Allow web installer to update the schema using
  this functionality.
  [Mitch or Andrew]
  Allow main installer to install using this
  [Andrew]

- Easy Content Linking
  Create a content link popup editor extension.
  [Jinx]

- Preview module
  Preview live content edit (on edit) (needs mosAJAX functionality)


<hr/>