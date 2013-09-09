<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML View class for the Ajax
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       3.2
 */
class MediaViewAjaxRaw extends JViewBase
{
	public function render()
	{
		$input = JFactory::getApplication()->input;
		$operation = $input->get('operation', '', 'STRING');
		$a = array();
		$a['operation'] = $operation;
		$a['newimage'] = JURI::root() . "media/media/tmp/" . $input->get('newimage', '', 'STRING');
		$a['duplicatePath'] = $this->fixPath($input->get('duplicatePath', '', 'STRING'));
		if ($a['duplicatePath'] == 'false')
		{
			$a['message'] = JText::_('COM_MEDIA_EDITOR_FILE_NAME_EXISTS');
		}
		echo json_encode($a);
	}

	public function fixPath($path)
	{
		$fh = fopen('E:/logs/log.txt', 'a');
		$path = str_replace('/', '\\', $path);
		fwrite($fh,$path);
			$path = str_replace(".\\", '', $path);
		fwrite($fh, $path);
		return $path;
	}
}
