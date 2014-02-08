<?php
/**
 * @package    FrameworkOnFramework
 * @subpackage form
 * @copyright  Copyright (C) 2010 - 2012 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('_JEXEC') or die;

/**
 * Field header for Published (enabled) columns
 *
 * @package  FrameworkOnFramework
 * @since    2.0
 */
class FOFFormHeaderPublished extends FOFFormHeaderFieldselectable
{
	/**
	 * Create objects for the options
	 *
	 * @return  array  The array of option objects
	 */
	protected function getOptions()
	{
		$config = array(
			'published'		 => 1,
			'unpublished'	 => 1,
			'archived'		 => 0,
			'trash'			 => 0,
			'all'			 => 0,
		);

		$stack = array();

		if ($this->element['show_published'] == 'false')
		{
			$config['published'] = 0;
		}

		if ($this->element['show_unpublished'] == 'false')
		{
			$config['unpublished'] = 0;
		}

		if ($this->element['show_archived'] == 'true')
		{
			$config['archived'] = 1;
		}

		if ($this->element['show_trash'] == 'true')
		{
			$config['trash'] = 1;
		}

		if ($this->element['show_all'] == 'true')
		{
			$config['all'] = 1;
		}

		$options = JHtml::_('jgrid.publishedOptions', $config);

		reset($options);

		return $options;
	}
}
