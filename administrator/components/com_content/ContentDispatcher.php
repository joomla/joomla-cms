<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use JoomlaCMS\Component\AbstractDispatcher;

/**
 * Component dispatcher for frontend com_content component
 */
class ContentDispatcher extends AbstractDispatcher
{
	/**
	 * {@inheritdoc}
	 */
	public function boot()
	{
		if (!JFactory::getUser()->authorise('core.manage', 'com_content'))
		{
			throw new RuntimeException(JText::_('JERROR_ALERTNOAUTHOR'), 404);
		}

		JHtml::_('behavior.tabstate');

		JLoader::register('ContentHelper', __DIR__ . '/helpers/content.php');
	}
}
