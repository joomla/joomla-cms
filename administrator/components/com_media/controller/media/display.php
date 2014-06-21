<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Joomla.Media
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Base Display Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       3.5
 */
class MediaControllerMediaDisplay extends ConfigControllerDisplay
{
	/**
	 * Prefix for the view and model classes
	 *
	 * @var    string
	 * @since  3.5
	 */
	public $prefix = 'Media';

	/**
	 * Execute the controller.
	 *
	 * @return  mixed  A rendered view or true
	 *
	 * @since   3.5
	 */
	public function execute()
	{
		$this->input->set('view', 'media');

		parent::execute();
	}
}
