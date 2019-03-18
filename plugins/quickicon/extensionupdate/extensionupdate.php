<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Quickicon.Extensionupdate
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Joomla! update notification plugin
 *
 * @since  2.5
 */
class PlgQuickiconExtensionupdate extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Returns an icon definition for an icon which looks for extensions updates
	 * via AJAX and displays a notification when such updates are found.
	 *
	 * @param   string  $context  The calling context
	 *
	 * @return  array  A list of icon definition associative arrays, consisting of the
	 *                 keys link, image, text and access.
	 *
	 * @since   2.5
	 */
	public function onGetIcons($context)
	{
		if ($context !== $this->params->get('context', 'mod_quickicon') || !JFactory::getUser()->authorise('core.manage', 'com_installer'))
		{
			return;
		}

		JHtml::_('jquery.framework');

		$token    = JSession::getFormToken() . '=' . 1;
		$url      = JUri::base() . 'index.php?option=com_installer&view=update&task=update.find&' . $token;
		$ajax_url = JUri::base() . 'index.php?option=com_installer&view=update&task=update.ajax&' . $token;
		$script   = array();
		$script[] = 'var plg_quickicon_extensionupdate_url = \'' . $url . '\';';
		$script[] = 'var plg_quickicon_extensionupdate_ajax_url = \'' . $ajax_url . '\';';
		$script[] = 'var plg_quickicon_extensionupdate_text = {'
			. '"UPTODATE" : "' . JText::_('PLG_QUICKICON_EXTENSIONUPDATE_UPTODATE', true) . '",'
			. '"UPDATEFOUND": "' . JText::_('PLG_QUICKICON_EXTENSIONUPDATE_UPDATEFOUND', true) . '",'
			. '"UPDATEFOUND_MESSAGE": "' . JText::_('PLG_QUICKICON_EXTENSIONUPDATE_UPDATEFOUND_MESSAGE', true) . '",'
			. '"UPDATEFOUND_BUTTON": "' . JText::_('PLG_QUICKICON_EXTENSIONUPDATE_UPDATEFOUND_BUTTON', true) . '",'
			. '"ERROR": "' . JText::_('PLG_QUICKICON_EXTENSIONUPDATE_ERROR', true) . '",'
			. '};';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));
		JHtml::_('script', 'plg_quickicon_extensionupdate/extensionupdatecheck.js', array('version' => 'auto', 'relative' => true));

		return array(
			array(
				'link'  => 'index.php?option=com_installer&view=update&task=update.find&' . $token,
				'image' => 'asterisk',
				'icon'  => 'header/icon-48-extension.png',
				'text'  => JText::_('PLG_QUICKICON_EXTENSIONUPDATE_CHECKING'),
				'id'    => 'plg_quickicon_extensionupdate',
				'group' => 'MOD_QUICKICON_MAINTENANCE'
			)
		);
	}
}
