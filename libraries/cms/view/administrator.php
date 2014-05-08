<?php
/**
 * @package   Joomla.Libraries
 * @subpackage View
 * 
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

 defined('JPATH_PLATFORM') or die;

abstract class JViewAdministrator extends JViewCms
{
	/**
	 * Method to add the toolbar to the view
	 * @param array $config configuration options
	 */
	protected function addToolbar($config = null)
	{
		if(is_null($config))
		{
			$config = $this->config;
		}

		$pageHeader = strtoupper($config['option'].'_header_'.$config['subject'].'_'.$config['layout']);
		$icon =  substr($config['option'], 4).'.png';

		JToolBarHelper::title(JText::_($pageHeader), $icon);
	}

	/**
	 * Method to add filters to the sidebar
	 */
	protected function addFilters()
	{

	}

	/**
	 * Method to get a list of sortable filter fields
	 * @return array
	 */
	protected function getSortFields()
	{
		return array();
	}
}