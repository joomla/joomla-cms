<?php
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
?>

Legend:

* -> Security Fix
# -> Bug Fix
+ -> Addition
^ -> Change
- -> Removed
! -> Note


07-Aug-2008 Andrew Eddie
 ! Commit to Joomla! development branch
 ! Rename to com_localise

----------

25-Feb-2008 RELEASE (J!1.5.1)
^ Change to allow languages in "xxx-XX" format
^ Updated Component Translation: German

04-Jan-2008 RELEASE (J!1.5 RC4)
# Changes to default language handling
# Changes to XML file parsing and format (mixed and lower case parameter names)
# Various small fixes following J! core modifications (mostly JHTML)
+ Improvements to Text Search
+ Included session tracking in forms
+ New Component Translations: Danish, Norwegian, Swedish

14-May-2007 RELEASE 7381 (J!1.5 Beta 2)
^ Change name from fftranslation to localise
# Fix JHTML refactoring and other changes in J! core
# Fix "Copy Reference Text" bug
# Fix Column Ordering  and Filtering Bugs

08-Mar-2007 RELEASE 6772
+ Add Component Translations: bg-BG, hu-HU, nl-NL
^ Change Installation XML
# Fix $mainframe->redirect ampersands
# Fix ampReplace() changes in J! core

20-Feb-2007
# Changes to element/fflanguages.php following core changes
+ Additions to INI file

19-Feb-2007
+ Add Language installation file packaging functionality
^ Restructure directory structure to standard joomla MVC

18-Feb-2007
^ Tooltips use only mootools
^ Moved Toolbars to individual layout/template files
^ Move CSS to localise.css and change layout/template files accordingly
^ Move JS to localise.js and change layout/template files accordingly

16-Feb-2007
# Fix to make configuration.php writable in TranslationsController::setDefault
# Fix trying to delete non-existent checkout file in TranslationsController::_multitask

14-Feb-2007
# Fix Select All checkbox bug

12-Feb-2007 RELEASE 6599
# Fix following rename of JToolbar::configuration to ::preferences in core
+ New tooltip functionality to handle mootools/overlib