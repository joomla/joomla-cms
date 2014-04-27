<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

abstract class JViewCms extends JViewLegacy
{
	/**
	 * Configuration options
	 * @var array
	 */
	protected $config = array();
	
	public function __construct($config = array())
	{
		parent::__construct($config);
		
		$this->config = $config;
	}
	
	/**
	 * Method to render a template script and return the output.
	 * @param   string  $tpl  The name of the template file to parse. Automatically searches through the template paths.
	 *
	 * @return  mixed $output A string or a ErrorException.
	 */
	public function render($tpl = null)
	{
		$output = $this->loadTemplate($tpl);
	
		return $output;
	}
	
	
	protected function canDo($action, $assetName = null)
	{
		$model = $this->getModel();
	
		return $model->allowAction($action, $assetName);
	}
}