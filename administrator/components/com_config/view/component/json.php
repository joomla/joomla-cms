<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

require_once dirname(dirname(__DIR__)) . '/helper/component.php';

/**
 * View for the component configuration
 *
 * @package     Joomla.Administrator
 * @subpackage  com_config
 * @since       1.5
 */
class ConfigViewComponent extends JViewCms
{
	/**
	 * Display the view
	 * 
	 * @param   string  $tpl  Layout
	 * 
	 * @return  void
	 * 
	 */
	public function render()
	{
		echo json_encode('To be implemented');
	}

}
