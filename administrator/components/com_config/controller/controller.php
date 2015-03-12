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

		$config['return'] = $input->get('return','', 'base64');

		parent::__construct($input,$app,$config);
	}
}
