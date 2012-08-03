<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML View class for the WebLinks component
 *
 * @package		Joomla.Site
 * @subpackage	com_weblinks
 * @since		1.5
 */
class WeblinksViewWeblink extends JViewLegacy
{
	protected $state;
	protected $item;

	function display($tpl = null)
	{
		$app		= JFactory::getApplication();
		$params		= $app->getParams();

		// Get some data from the models
		$state		= $this->get('State');
		$item		= $this->get('Item');
		$category	= $this->get('Category');

		if ($this->getLayout() == 'edit') {
			$this->_displayEdit($tpl);
			return;
		}

		if ($item->url) {
			// redirects to url if matching id found
			$app->redirect($item->url);
		} else {
			//TODO create proper error handling
			$app->redirect(JRoute::_('index.php'), JText::_('COM_WEBLINKS_ERROR_WEBLINK_NOT_FOUND'), 'notice');
		}
	}
}
