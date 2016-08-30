<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors_Buttons.JDragDrop
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * JDragDrop Editors_Buttons Plugin
 *
 * @since  1.0
 */
class PlgEditors_ButtonsJdragdrop extends JPlugin
{
/**
 * Plugin that extends TinyMCE-Editor with JDragDrop
 *
 * @return string
 *
 * @since version
 */
	public function onLoadTinymcePlugin()
	{
		$allowImgPaste = "false";
		$user = JFactory::getUser();

		if ($user->authorise('core.create', 'com_media'))
		{
			$allowImgPaste = "true";
			$isSubDir = '';
			$session = JFactory::getSession();
			$uploadUrl = JUri::base() . 'index.php?option=com_media&task=file.upload&tmpl=component&'
			. $session->getName() . '=' . $session->getId()
			. '&' . JSession::getFormToken() . '=1'
			. '&asset=image&format=json';

			if (JFactory::getApplication()->isSite())
			{
			$uploadUrl = htmlentities($uploadUrl, null, 'UTF-8', null);
			}

			// Is Joomla installed in subdirectory
			if (JUri::root(true) != '/')
			{
			$isSubDir = JUri::root(true);
			}

			// Get specific path
			$tempPath = $this->params->get('path', '');

			if (!empty($tempPath))
			{
				$tempPath = rtrim($tempPath, '/');
				$tempPath = ltrim($tempPath, '/');
			}

			JText::script('PLG_TINY_ERR_UNSUPPORTEDBROWSER');
			JFactory::getDocument()->addScriptDeclaration(
				"
		var setCustomDir    = '" . $isSubDir . "';
		var mediaUploadPath = '" . $tempPath . "';
		var uploadUri       = '" . $uploadUrl . "';
			"
		);
			$content_css = '';
			$script = '';

			// Layout
			$script .= "
			$content_css
		document_base_url : \"" . JUri::root() . "\",
				paste_data_images: $allowImgPaste,
		";
		}

return 'jdragdrop';
	}
}
