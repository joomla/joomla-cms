<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML View class for the Frontend component
 *
 * @since  3.5
 */
class MediaViewHtml extends ConfigViewCmsHtml
{

	public $item;

	public function render()
	{
		$app	= JFactory::getApplication();

		$lang	= JFactory::getLanguage();

		$session	= JFactory::getSession();
		$state		= $this->model->getState();

		$this->id		= $app->input->get('id');
		$this->item		= $this->model->getItem($this->id);

		return parent::render();
	}
}
