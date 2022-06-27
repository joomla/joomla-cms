<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;

/**
 * Installer Warnings Model
 *
 * @since  1.6
 */
class WarningsModel extends ListModel
{
	/**
	 * Extension Type
	 * @var	string
	 */
	public $type = 'warnings';

	/**
	 * Return the byte value of a particular string.
	 *
	 * @param   string  $val  String optionally with G, M or K suffix
	 *
	 * @return  integer   size in bytes
	 *
	 * @since 1.6
	 */
	public function return_bytes($val)
	{
		if (empty($val))
		{
			return 0;
		}

		$val = trim($val);

		preg_match('#([0-9]+)[\s]*([a-z]+)#i', $val, $matches);

		$last = '';

		if (isset($matches[2]))
		{
			$last = $matches[2];
		}

		if (isset($matches[1]))
		{
			$val = (int) $matches[1];
		}

		switch (strtolower($last))
		{
			case 'g':
			case 'gb':
				$val *= (1024 * 1024 * 1024);
				break;
			case 'm':
			case 'mb':
				$val *= (1024 * 1024);
				break;
			case 'k':
			case 'kb':
				$val *= 1024;
				break;
		}

		return (int) $val;
	}

	/**
	 * Load the data.
	 *
	 * @return  array  Messages
	 *
	 * @since   1.6
	 */
	public function getItems()
	{
		static $messages;

		if ($messages)
		{
			return $messages;
		}

		$messages = [];

		// 16MB
		$minLimit = 16 * 1024 * 1024;

		$file_uploads = ini_get('file_uploads');

		if (!$file_uploads)
		{
			$messages[] = [
				'message'     => Text::_('COM_INSTALLER_MSG_WARNINGS_FILEUPLOADSDISABLED'),
				'description' => Text::_('COM_INSTALLER_MSG_WARNINGS_FILEUPLOADISDISABLEDDESC'),
			];
		}

		$upload_dir = ini_get('upload_tmp_dir');

		if (!$upload_dir)
		{
			$messages[] = [
				'message'     => Text::_('COM_INSTALLER_MSG_WARNINGS_PHPUPLOADNOTSET'),
				'description' => Text::_('COM_INSTALLER_MSG_WARNINGS_PHPUPLOADNOTSETDESC'),
			];
		}
		elseif (!is_writable($upload_dir))
		{
			$messages[] = [
				'message'     => Text::_('COM_INSTALLER_MSG_WARNINGS_PHPUPLOADNOTWRITEABLE'),
				'description' => Text::sprintf('COM_INSTALLER_MSG_WARNINGS_PHPUPLOADNOTWRITEABLEDESC', $upload_dir),
			];
		}

		$tmp_path = Factory::getApplication()->get('tmp_path');

		if (!$tmp_path)
		{
			$messages[] = [
				'message'     => Text::_('COM_INSTALLER_MSG_WARNINGS_JOOMLATMPNOTSET'),
				'description' => Text::_('COM_INSTALLER_MSG_WARNINGS_JOOMLATMPNOTSETDESC'),
			];
		}
		elseif (!is_writable($tmp_path))
		{
			$messages[] = [
				'message'     => Text::_('COM_INSTALLER_MSG_WARNINGS_JOOMLATMPNOTWRITEABLE'),
				'description' => Text::sprintf('COM_INSTALLER_MSG_WARNINGS_JOOMLATMPNOTWRITEABLEDESC', $tmp_path),
			];
		}

		$memory_limit = $this->return_bytes(ini_get('memory_limit'));

		if ($memory_limit > -1)
		{
			if ($memory_limit < $minLimit)
			{
				// 16MB
				$messages[] = [
					'message'     => Text::_('COM_INSTALLER_MSG_WARNINGS_LOWMEMORYWARN'),
					'description' => Text::_('COM_INSTALLER_MSG_WARNINGS_LOWMEMORYDESC'),
				];
			}
			elseif ($memory_limit < ($minLimit * 1.5))
			{
				// 24MB
				$messages[] = [
					'message'     => Text::_('COM_INSTALLER_MSG_WARNINGS_MEDMEMORYWARN'),
					'description' => Text::_('COM_INSTALLER_MSG_WARNINGS_MEDMEMORYDESC'),
				];
			}
		}

		$post_max_size       = $this->return_bytes(ini_get('post_max_size'));
		$upload_max_filesize = $this->return_bytes(ini_get('upload_max_filesize'));

		if ($post_max_size > 0 && $post_max_size < $upload_max_filesize)
		{
			$messages[] = [
				'message'     => Text::_('COM_INSTALLER_MSG_WARNINGS_UPLOADBIGGERTHANPOST'),
				'description' => Text::_('COM_INSTALLER_MSG_WARNINGS_UPLOADBIGGERTHANPOSTDESC'),
			];
		}

		if ($post_max_size > 0 && $post_max_size < $minLimit)
		{
			$messages[] = [
				'message'     => Text::_('COM_INSTALLER_MSG_WARNINGS_SMALLPOSTSIZE'),
				'description' => Text::_('COM_INSTALLER_MSG_WARNINGS_SMALLPOSTSIZEDESC'),
			];
		}

		if ($upload_max_filesize > 0 && $upload_max_filesize < $minLimit)
		{
			$messages[] = [
				'message'     => Text::_('COM_INSTALLER_MSG_WARNINGS_SMALLUPLOADSIZE'),
				'description' => Text::_('COM_INSTALLER_MSG_WARNINGS_SMALLUPLOADSIZEDESC'),
			];
		}

		return $messages;
	}
}
