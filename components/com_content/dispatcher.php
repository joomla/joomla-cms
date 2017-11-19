<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
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
class ContentDispatcher extends Dispatcher
{
	/**
	 * The extension namespace
	 *
	 * @var    string
	 *
	 * @since  4.0.0
	 */
	protected $namespace = 'Joomla\\Component\\Content';

	/**
	 * Dispatch a controller task. Redirecting the user if appropriate.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function dispatch()
	{
		$checkCreateEdit = ($this->input->get('view') === 'articles' && $this->input->get('layout') === 'modal')
			|| ($this->input->get('view') === 'article' && $this->input->get('layout') === 'pagebreak');

		if ($checkCreateEdit)
		{
			// Can create in any category (component permission) or at least in one category
			$canCreateRecords = $this->app->getIdentity()->authorise('core.create', 'com_content')
				|| count($this->app->getIdentity()->getAuthorisedCategories('com_content', 'core.create')) > 0;

			// Instead of checking edit on all records, we can use **same** check as the form editing view
			$values = (array) $this->app->getUserState('com_content.edit.article.id');
			$isEditingRecords = count($values);
			$hasAccess = $canCreateRecords || $isEditingRecords;

			if (!$hasAccess)
			{
				$this->app->enqueueMessage(\JText::_('JERROR_ALERTNOAUTHOR'), 'warning');

				return;
			}
		}

		JLoader::register('ContentHelperRoute', JPATH_SITE . '/components/com_content/helpers/route.php');
		JLoader::register('ContentHelperQuery', JPATH_SITE . '/components/com_content/helpers/query.php');
		JLoader::register('ContentHelperAssociation', JPATH_SITE . '/components/com_content/helpers/association.php');

		parent::dispatch();
	}
}
