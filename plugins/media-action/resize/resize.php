<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Media-Action.resize
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use \Joomla\Image\Image;

/**
 * Media Manager Resize Action
 *
 * @since  4.0.0
 */
class PlgMediaActionResize extends \Joomla\Component\Media\Administrator\Plugin\MediaActionPlugin
{
	/**
	 * The save event.
	 *
	 * @param   string   $context  The context
	 * @param   object   $item     The item
	 * @param   boolean  $isNew    Is new item
	 * @param   array    $data     The validated data
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onContentBeforeSave($context, $item, $isNew, $data = array())
	{
		if ($context != 'com_media.file')
		{
			return;
		}

		if (!$this->params->get('batch_width') && !$this->params->get('batch_height'))
		{
			return;
		}

		if (!in_array($item->extension, ['jpg', 'jpeg', 'png', 'gif']))
		{
			return;
		}

		$imgObject = new Image(imagecreatefromstring($item->data));

		if ($imgObject->getWidth() < $this->params->get('batch_width', 0)
			&& $imgObject->getHeight() < $this->params->get('batch_height', 0))
		{
			return;
		}

		$imgObject->resize(
			$this->params->get('batch_width', 0),
			$this->params->get('batch_height', 0),
			false,
			Image::SCALE_INSIDE
		);

		$type = IMAGETYPE_JPEG;

		switch ($item->extension)
		{
			case 'gif':
				$type = IMAGETYPE_GIF;
				break;
			case 'png':
				$type = IMAGETYPE_PNG;
		}

		ob_start();
		$imgObject->toFile(null, $type);
		$item->data = ob_get_contents();
		ob_end_clean();
	}
}
