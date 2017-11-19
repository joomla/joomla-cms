<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Access\Exception\Notallowed;
use Joomla\CMS\Dispatcher\Dispatcher;
use Joomla\Component\Associations\Administrator\Helper\AssociationsHelper;

/**
 * Dispatcher class for com_associations
 *
 * @since  4.0.0
 */
class AssociationsDispatcher extends Dispatcher
{
	/**
	 * The extension namespace
	 *
	 * @var    string
	 *
	 * @since  4.0.0
	 */
	protected $namespace = 'Joomla\\Component\\Associations';

	/**
	 * Method to check component access permission
	 *
	 * @since   4.0.0
	 *
	 * @return  void
	 *
	 * @throws  Exception|Notallowed
	 */
	protected function checkAccess()
	{
		parent::checkAccess();

		// Check if user has permission to access the component item type.
		$itemType = $this->input->get('itemtype', '', 'string');

		if ($itemType !== '')
		{
			list($extensionName, $typeName) = explode('.', $itemType);

			if (!AssociationsHelper::hasSupport($extensionName))
			{
				throw new Exception(JText::sprintf('COM_ASSOCIATIONS_COMPONENT_NOT_SUPPORTED', JText::_($extensionName)), 404);
			}

			if (!JFactory::getUser()->authorise('core.manage', $extensionName))
			{
				throw new Notallowed($this->app->getLanguage()->_('JERROR_ALERTNOAUTHOR'), 403);
			}
		}
	}
}
