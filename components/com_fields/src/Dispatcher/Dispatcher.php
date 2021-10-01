<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_fields
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Fields\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Dispatcher\ComponentDispatcher;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

/**
 * ComponentDispatcher class for com_fields
 *
 * @since  4.0.0
 */
class Dispatcher extends ComponentDispatcher
{
	/**
	 * Method to check component access permission
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function checkAccess()
	{
		parent::checkAccess();

		if ($this->input->get('view') !== 'fields' || $this->input->get('layout') !== 'modal')
		{
			return;
		}

		$context = $this->app->getUserStateFromRequest('com_fields.fields.context', 'context', 'com_content.article', 'CMD');
		$parts   = FieldsHelper::extract($context);

		if (!$this->app->getIdentity()->authorise('core.create', $parts[0])
			|| !$this->app->getIdentity()->authorise('core.edit', $parts[0]))
		{
			throw new NotAllowed($this->app->getLanguage()->_('JERROR_ALERTNOAUTHOR'));
		}
	}
}
