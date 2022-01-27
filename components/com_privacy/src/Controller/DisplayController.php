<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Privacy\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

/**
 * Privacy Controller
 *
 * @since  3.9.0
 */
class DisplayController extends BaseController
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  $this
	 *
	 * @since   3.9.0
	 */
	public function display($cachable = false, $urlparams = [])
	{
		$view = $this->input->get('view', $this->default_view);

		// Submitting information requests and confirmation through the frontend is restricted to authenticated users at this time
		if (in_array($view, ['confirm', 'request']) && $this->app->getIdentity()->guest)
		{
			$this->setRedirect(
				Route::_('index.php?option=com_users&view=login&return=' . base64_encode('index.php?option=com_privacy&view=' . $view), false)
			);

			return $this;
		}

		// Set a Referrer-Policy header for views which require it
		if (in_array($view, ['confirm', 'remind']))
		{
			$this->app->setHeader('Referrer-Policy', 'no-referrer', true);
		}

		return parent::display($cachable, $urlparams);
	}
}
