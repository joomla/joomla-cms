<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contenthistory
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View class for a list of contenthistory.
 *
 * @since  1.5
 */
class ContenthistoryViewPreview extends JViewLegacy
{
	protected $items;

	protected $state;

	/**
	 * Method to display the view.
	 *
	 * @param   string  $tpl  A template file to load. [optional]
	 *
	 * @return  mixed  Exception on failure, void on success.
	 *
	 * @since   3.2
	 */
	public function display($tpl = null)
	{
		$this->state = $this->get('State');
		$this->item  = $this->get('Item');

		if (false === $this->item)
		{
			JFactory::getLanguage()->load('com_content', JPATH_SITE, null, true);

			JError::raiseError(404, JText::_('COM_CONTENT_ERROR_ARTICLE_NOT_FOUND'));

			return false;
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		return parent::display($tpl);
	}
}
