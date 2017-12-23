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
 * @since  __DEPLOY_VERSION__
 */
class ContentDispatcher extends Dispatcher
{
	/**
	 * The extension namespace
	 *
	 * @var    string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $namespace = 'Joomla\\Component\\Content';

	/**
	 * Dispatch a controller task. Redirecting the user if appropriate.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function dispatch()
	{
		if ($this->input->get('view') === 'article' && $this->input->get('layout') === 'pagebreak')
		{
			if (!$this->app->getIdentity()->authorise('core.create', 'com_content'))
			{
				$this->app->enqueueMessage(\JText::_('JERROR_ALERTNOAUTHOR'), 'warning');

				return;
			}
		}
		elseif ($this->input->get('view') === 'articles' && $this->input->get('layout') === 'modal')
		{
			if (!$this->app->getIdentity()->authorise('core.create', 'com_content'))
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
