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
 * @author Andrew Eddie
 */
class JContentModelWizard extends JWizardModel
{
	function init($type='')
	{
		global $mainframe;

		$type = $mainframe->getUserStateFromRequest('contentwizard.tool', 'tool', $type );
		// Create the JWizard object

		// Include and create the helper object
		if ($type) {
			require_once(JPATH_COMPONENT.DS.'helpers'.DS.$type.'.php');
			$class = 'JContentHelper'.ucfirst($type);
			$this->_helper = new $class($this);
			$this->_helper->setXmlPath( JPATH_COMPONENT.DS.'helpers'.DS.'xml' );
			$name = $this->_helper->getWizardName();
		} else {
			$name = 'content';
		}

		// Instantiate wizard
		$this->_wizard = new JWizard($mainframe, $name);

		// Load the XML if helper is set
		if (isset($this->_helper)) {
			$this->_helper->init($this->_wizard);
		}
	}

	function getItems( $ids=array() )
	{
		$n = count( $ids );
		for ($i = 0; $i < $n; $i++)
		{
			$ids[$i] = (int) $ids[$i];
		}
		if ($n < 1) {
			$result = array();
		} else {
			$query = 'SELECT id, title' .
					' FROM #__content' .
					' WHERE id = ' . implode( ' OR id = ', $ids );
			$db =& $this->getDBO();
			$db->setQuery( $query );
			$result = $db->loadObjectList();
		}
		return $result;
	}
}
?>