<?php
/**
 * @version $Id$
 * @package Joomla
 * @subpackage Menus
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights
 * reserved.
 * @license GNU/GPL, see LICENSE.php
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
class JMenuHelperUrl extends JWizardHelper
{
	var $_helperContext	= 'menu';

	var $_helperName	= 'url';

	var $_type = null;

	/**
	 * Initializes the helper class with the wizard object and loads the wizard xml.
	 *
	 * @param object JWizard
	 */
	function init(&$wizard)
	{
		global $mainframe;
		parent::init( $wizard );

		$this->_type = $mainframe->getUserStateFromRequest('menuwizard.menutype', 'menutype');
	}

	/**
	 * @param string A params string
	 * @param string The option
	 */
	function &getConfirmation()
	{
		$values	=& $this->_wizard->getConfirmation();

		$final['type']	= 'url';
		$final['menu_type']	= $this->_type;

		return $final;
	}

	/**
	 * @param string A params string
	 * @param string The option
	 */
	function &getEditFields()
	{
		$fields = array();

		return $fields;
	}

	function getDetails()
	{
		$details[] = array('label' => JText::_('Type'), 'name' => JText::_('URL'), 'key' => 'type', 'value' => 'url');
		return $details;
	}

	function getStateXML()
	{
		// load the xml metadata
		$src = dirname(__FILE__).DS.'xml/url.xml';
		$path = 'state';
		return array('path' => $src, 'xpath' => $path);
	}

	function &prepForEdit(&$item) {
		return $item;
	}

	/**
	 * Prepares data before saving
	 * @param	array	A named array of values
	 * @return	array	The prepared array
	 */
	function prepForStore(&$values) {
		$values['componentid']	= 0;
		$values['control']		= '';
		return $values;
	}
}
?>