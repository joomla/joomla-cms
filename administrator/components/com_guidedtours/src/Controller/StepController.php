<?php
/**
 * @package       Joomla.Administrator
 * @subpackage    com_guidedtours
 * @copyright (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Guidedtours\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;

/**
 * Controller for a single tour
 *
 * @since __DEPLOY_VERSION__
 */
class StepController extends FormController
{

	public function cancel($key = null)
	{
		$this->setRedirect('index.php?option=com_guidedtours&view=steps');
	}
}
