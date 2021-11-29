<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contenthistory
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Contenthistory\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Input\Input;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/**
 * History manager master display controller.
 *
 * @since  4.0.0
 */
class DisplayController extends BaseController
{
	/**
	 * @param   array                     $config   An optional associative array of configuration settings.
	 * @param   MVCFactoryInterface|null  $factory  The factory.
	 * @param   CMSApplication|null       $app      The JApplication for the dispatcher
	 * @param   ?Input                    $input    Input
	 *
	 * @since   3.0
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
	{
		$config['base_path'] = JPATH_COMPONENT_ADMINISTRATOR;

		parent::__construct($config, $factory, $app, $input);
	}
}
