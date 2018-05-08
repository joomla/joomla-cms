<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Workflow\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Factory;

/**
 * The first example class, this is in the same
 * package as declared at the start of file but
 * this example has a defined subpackage
 *
 * @since  __DEPLOY_VERSION__
 */
class WorkflowHelper extends ContentHelper
{
	/**
	 * Configure the Linkbar. Must be implemented by each extension.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public static function addSubmenu($vName)
	{
		$extension = Factory::getApplication()->input->getCmd('extension');

		$parts = explode('.', $extension);

		$component = reset($parts);

		$eName = ucfirst(str_replace('com_', '', $component));
		$cName = $eName . 'Helper';

		$class = '\\Joomla\\Component\\' . $eName . '\\Administrator\\Helper\\' . $cName;

		if (class_exists($class) && is_callable([$class, 'addSubmenu']))
		{
			$lang = Factory::getLanguage();

			// Loading language file from the administrator/language directory then
			// loading language file from the administrator/components/*extension*/language directory
			$lang->load($component, JPATH_BASE, null, false, true)
			|| $lang->load($component, \JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $component), null, false, true);

			call_user_func([$class, 'addSubmenu'], $vName);
		}
	}
}
