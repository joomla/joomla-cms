<?php
/**
 * @version $Id: admin.menus.php 3504 2006-05-15 05:25:43Z eddieajau $
 * @package Joomla
 * @subpackage Menus
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights
 * reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

jimport('joomla.presentation.wizard');

/**
 * @package Joomla
 * @subpackage Menus
 * @author Louis Landry <louis.landry@joomla.org>
 */
class JMenuHelperMenulink extends JWizardHelper
{
	var $_helperContext	= 'menu';

	var $_helperName	= 'menulink';

	var $_type = null;

	/**
	 * Initializes the helper class with the wizard object and loads the wizard xml.
	 * 
	 * @param object JWizard
	 */
	function init(&$wizard)
	{
		parent::init( $wizard );
		$app =& $this->_parent->getApplication();
		$this->_type = $app->getUserStateFromRequest('menuwizard.menutype', 'menutype');
		$this->_wizard->_registry->set('menu_type', $app->getUserStateFromRequest('menuwizard.menulink.menu', 'menu'));
	}

	/**
	 * @param string A params string
	 * @param string The option
	 */
	function &getConfirmation()
	{
		$values	=& $this->_wizard->getConfirmation();

		$final['type']	= 'menulink';
		$final['menu_item']	= $values['menu_item'];
		$final['menu_type']	= $this->_type;

		return $final;
	}

	function getStateXML()
	{
		// load the xml metadata
		$src = dirname(__FILE__).DS.'xml/menulink.xml';
		$path = 'state';
		return array('path' => $src, 'xpath' => $path);
	}
}
?>