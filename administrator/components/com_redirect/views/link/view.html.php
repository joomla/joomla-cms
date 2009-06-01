<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_redirect
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Invalid Request.');

jimport('joomla.application.component.view');

/**
 * The HTML Redirect link view.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_redirect
 * @version		1.6
 */
class RedirectViewLink extends JView
{
	/**
	 * Display the view
	 *
	 * @return	void
	 * @since	1.6
	 */
	function display($tpl = null)
	{
		$state	= $this->get('State');
		$item	= $this->get('Item');
		$form	= $this->get('Form');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		// Bind the item data to the form object.
		if ($item) {
			$form->bind($item);
		}

		$this->assignRef('state',	$state);
		$this->assignRef('item',	$item);
		$this->assignRef('form',	$form);

		parent::display($tpl);
	}

	/**
	 * Build the default toolbar.
	 *
	 * @access	protected
	 * @return	void
	 * @since	1.6
	 */
	function buildDefaultToolBar()
	{
		if (is_object($this->item)) {
			$isNew = ($this->item->id == 0);
		}
		else {
			$isNew = true;
		}

		JToolBarHelper::title('Redirect: '.($isNew ? 'Add Link' : 'Edit Link'), 'generic');
		JToolBarHelper::custom('link.save2new', 'new.png', 'new_f2.png', 'Save and New', false);
		JToolBarHelper::save('link.save');
		JToolBarHelper::apply('link.apply');
		JToolBarHelper::cancel('link.cancel');
	}
}