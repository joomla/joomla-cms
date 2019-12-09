<?php

/**
 * @package     acorn.Framework
 * @subpackage  acorn
 *
 * @copyright   Copyright (C) 2015 Troy T. Hall All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Path;

/**
 * ==================================================
 * Joomla Variables
 * ==================================================
 */
$app    = Factory::getApplication();
$doc    = Factory::getDocument();
$user   = Factory::getUser();
$lang   = Factory::getLanguage();
$menu   = Factory::getApplication()->getMenu();
$active = $app->getMenu()->getActive();
$params = $app->getTemplate(true)->params;

// Output as HTML5
$this->setHtml5(true);

// Get PageClass Suffix from menu item
$pageclass = '';
if (is_object($menu))
	$pageclass = $active->params->get('pageclass_sfx');

// Getting params for template
$template      = 'templates/' . $this->template;
$templatePath  = Path::clean(JPATH_THEMES . '/' . $this->template);
$frameworkPath = Path::clean($templatePath . '/framework/');
$backendPath   = Path::clean($frameworkPath . 'backend/');

// Detecting Active Variables
$option   = $app->input->getCmd('option', '');
$view     = ' view-' . $app->input->getCmd('view');
$layout   = $app->input->getCmd('layout', '');
$layout   = $layout ? ' layout-' . $layout : ' no-layout';
$task     = $app->input->getCmd('task', '');
$task     = $task ? ' task-' . $task : ' no-task';
$itemid   = ' itemid-' . $app->input->getCmd('Itemid');
$loggedin = $app->input->getCmd('loggedin', '');

// Since bodyclass can be defined during page load lets get all the body class stuff done ahead of time.
$mainbodyclass = 'site' . ' ' . $view . ' ' . $layout . ' ' . $task . ' ' . $itemid . ' ' . $pageclass . ' ' . $option . ' ' . ($this->direction == 'rtl' ? ' rtl' : '');

/**
 * ==================================================
 * Framework Variables
 * ==================================================
 */
$socialiconsFooter = false;
$socialiconsNav    = false;
$socialiconsHeader = false;
$socialIcons       = $this->params->get('socialIcons');
$sitename          = $app->get('sitename');

// Used with HTMLHelper::
$HTMLHelperDebug = array('version' => 'auto', 'relative' => true, 'detectDebug' => true);

//  Get position count for those that have multiple if's
$navbarBrand     = $this->countModules('navbar-brand');
$menu_module     = $this->countModules('menu');
$copyrightModule = $this->countModules('copyright');

// load functions
require_once $frameworkPath . 'functions.php';

// Load Template Layout
require_once $backendPath . 'layout.php';

/**
 * ==================================================
 * Frontpage check
 * ==================================================
 */
$isFrontpage = false;

// Single language sites
if (!JLanguageMultilang::isEnabled())
{
	if ($menu->getActive() == $menu->getDefault())
	{
		$isFrontpage = true;
	}
}
elseif ($menu->getActive() == $menu->getDefault($lang->get('tag')))
{
// Multilanguage sites
	$isFrontpage = true;
}
$frontpage = $isFrontpage ? 'frontpage' : '';


// Chromes
$top      = ($this->params->get('top') == 1) ? "Desktop" : "Mobile";
$standard = ($this->params->get('standard') == 1) ? "Desktop" : "Mobile";
$bottom   = ($this->params->get('bottom') == 1) ? "Desktop" : "Mobile";
$footer   = ($this->params->get('footer') == 1) ? "Desktop" : "Mobile";

if (Factory::getUser()->guest)
{
	$loggedin = ' loggedout';
}
else
{
	$loggedin = ' loggedin';
}

// END - Initialized variables

// Start Includes

// Load logo tab.
require_once $backendPath . 'logo.php';

// Load Main menu
include_once $backendPath . 'main-menu.php';

// Load Mobile menu
require_once $backendPath . 'mobile-menu.php';

// Load Header
//require_once $backendPath . 'header.php';


// Load Footer
//require_once $backendPath . 'footer.php';

// Load Copyright
require_once $backendPath . 'copyright.php';

// Load Miscellaneous
include_once $backendPath . 'miscellaneous.php';

// Load Custom Code.
require_once $backendPath . 'custom-code.php';

// Load Social Icons - Don't load social icons unless needed.
$socialIcons ? require_once $backendPath . 'social-icons.php' : false;

// We can process the header last so it has all possible information
include_once $frameworkPath . "head_include.php";
