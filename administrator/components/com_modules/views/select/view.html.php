<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML View class for the Modules component
 *
 * @package		Joomla.Administrator
 * @subpackage	com_modules
 * @since		1.6
 */
class ModulesViewSelect extends JView
{
	protected $state;
	protected $items;

	/**
	 * Display the view
	 */
	function display($tpl = null)
	{
		$this->state	= $this->get('State');
		$this->items	= $this->get('Items');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		// Group the items list by the client (site/admin).
		$groups = array(
			0 => array(),
			1 => array(),
		);

		foreach ($this->items as &$item)
		{
			// Preprocess the XML description.
			if (isset($item->xml)) {
				$item->description = trim($item->xml->description);
			}
			else {
				$item->description = JText::_('COM_MODULES_NODESCRIPTION');
			}

			$groups[$item->client_id][] = &$item;
		}

		$this->groups = &$groups;

		parent::display($tpl);
	}
}
