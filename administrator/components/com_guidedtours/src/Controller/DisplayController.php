<?php

/**
 * @package       Joomla.Administrator
 * @subpackage    com_guidedtours
 * @copyright (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Guidedtours\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Component Controller
 *
 * @since __DEPLOY_VERSION__
 */
class DisplayController extends BaseController
{
	/**
	 * The default view.
	 *
	 * @var   string
	 * @since __DEPLOY_VERSION__
	 */
	protected $default_view = 'tours';

	/**
	 * Method to display a view.
	 *
	 * @param   boolean $cachable  If true, the view output will be cached
	 * @param   array   $urlparams An array of safe URL parameters and their variable types, for valid values see {@link \JFilterInput::clean()}.
	 *
	 * @return static  This object to support chaining.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function display($cachable = false, $urlparams = array())
	{
		return parent::display();
	}
}
