<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Config Component Dispatch Controller
 * @package     Joomla.Administrator
 * @subpackage  com_config
 */
class ConfigController extends JControllerDispatcher
{
	public function __construct(JInput $input, JApplicationBase $app = null, $config = array())
	{
		$component = $input->get('component');
		if(!empty($component))
		{
			$config['component'] = strtolower($component);
		}

		$config['return'] = $this->getDefaultConfig('return',$config, $input->get('return',base64_encode('index.php?Itemid=0'), 'base64'));

		// only allowing the fix method to executed via get
		$task = $input->get('task', 'display', 'CMD');
		if ($task === 'fix')
		{
			$config['task'] = $task;
		}

		parent::__construct($input,$app,$config);
	}
}
