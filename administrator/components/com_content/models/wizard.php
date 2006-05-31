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
 * @author Andrew Eddie
 */
class JContentModelWizard extends JWizardModel
{
	function init($type='')
	{
		// Create the JWizard object
		$app =& $this->getApplication();
		$type = $app->getUserStateFromRequest('menuwizard.type', 'type', $type);
		$menutype = $app->getUserStateFromRequest('menuwizard.menutype', 'menutype');

		// Include and create the helper object
		if ($type) {
			require_once(COM_MENUS.'helpers'.DS.$type.'.php');
			$class = 'JMenuHelper'.ucfirst($type);
			$this->_helper =& new $class($this);
			$name = $this->_helper->getWizardName();
		} else {
			$name = 'menu';
		}

		// Instantiate wizard
		$this->_wizard =& new JWizard($app, $name);

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
		if ($n < 1)
		{
			$result = array();
		}
		else
		{
			$query = 'SELECT id, title' .
					' FROM #__content' .
					' WHERE id = ' . implode( ' OR id = ', $ids ); 
			$db = $this->getDBO();
			$db->setQuery( $query );
			$result = $db->loadObjectList();
		}
		return $result;
	}
}
?>