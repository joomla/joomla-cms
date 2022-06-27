<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Contact\Site\Dispatcher;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcher;
use Joomla\CMS\Language\Text;

/**
 * ComponentDispatcher class for com_contact
 *
 * @since  4.0.0
 */
class Dispatcher extends ComponentDispatcher
{
	/**
	 * Dispatch a controller task. Redirecting the user if appropriate.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function dispatch()
	{
		if ($this->input->get('view') === 'contacts' && $this->input->get('layout') === 'modal')
		{
			if (!$this->app->getIdentity()->authorise('core.create', 'com_contact'))
			{
				$this->app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'warning');

				return;
			}

			$this->app->getLanguage()->load('com_contact', JPATH_ADMINISTRATOR);
		}

		parent::dispatch();
	}
}
