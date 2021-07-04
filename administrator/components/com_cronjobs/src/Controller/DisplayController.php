<?php
/**
 * Declares the default display controller for com_cronjobs
 *
 * @package     Joomla.Administrator
 * @subpackage  com_cronjobs
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Cronjobs\Administrator\Controller;

// Restrict direct access
\defined('_JEXEC_') or die;

use Exception;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Controller\ControllerInterface;


/**
 * Default controller for com_cronjobs
 *
 * @since __DEPLOY_VERSION__
 */
class DisplayController extends BaseController
{
	/**
	 * ! View 'cronjobs' has not been implemented yet
	 *
	 * @var string
	 * @since __DEPLOY_VERSION__
	 */
	protected $default_view = 'cronjobs';

	/**
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link InputFilter::clean()}.
	 *
	 * @return BaseController
	 *
	 * @throws Exception
	 * @since __DEPLOY_VERSION__
	 */
	public function display($cachable = false, $urlparams = array()) : ControllerInterface
	{
		// ! TODO
		return parent::display($cachable, $urlparams);
	}
}
