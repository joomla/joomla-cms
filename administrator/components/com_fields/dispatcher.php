<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\Dispatcher;

/**
 * Dispatcher class for com_content
 *
 * @since  4.0.0
 */
class FieldsDispatcher extends Dispatcher
{
	/**
	 * The extension namespace
	 *
	 * @var    string
	 *
	 * @since  4.0.0
	 */
	protected $namespace = 'Joomla\\Component\\Fields';

	/**
	 * Method to check component access permission
	 *
	 * @since   4.0.0
	 *
	 * @return  void
	 */
	protected function checkAccess()
	{
		JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');

		$app       = JFactory::getApplication();
		$context   = $app->getUserStateFromRequest(
			'com_fields.groups.context',
			'context',
			$app->getUserStateFromRequest('com_fields.fields.context', 'context', 'com_content.article', 'CMD'),
			'CMD'
		);

		$parts = FieldsHelper::extract($context);

		if (!$parts || !$this->app->getIdentity()->authorise('core.manage', $parts[0]))
		{
			throw new \Joomla\CMS\Access\Exception\Notallowed($this->app->getLanguage()->_('JERROR_ALERTNOAUTHOR'), 403);
		}
	}
}
