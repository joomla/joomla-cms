<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

class JControllerCancel extends JControllerAdministrate
{
	public function execute()
	{
		$config = $this->config;
		$this->setReturn(base64_decode($config['return']));
		return $this->executeController();
	}
}