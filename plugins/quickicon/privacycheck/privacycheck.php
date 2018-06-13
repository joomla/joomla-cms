<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Quickicon.privacycheck
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Plugin to check privacy requests older than 14 days
 *
 * @since  3.9.0
 */
class PlgQuickiconPrivacyCheck extends JPlugin
{
	/**
	 * Load plugin language files automatically
	 *
	 * @var    boolean
	 * @since  3.7.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Check privacy requests older than 14 days.
	 *
	 * @param   string  $context  The calling context
	 *
	 * @return  void
	 *
	 * @since   3.9.0
	 */
	public function onGetIcons($context)
	{
		if ($context !== $this->params->get('context', 'mod_quickicon') || !JFactory::getUser()->authorise('core.manage', 'com_privacy'))
		{
			return;
		}

		JHtml::_('jquery.framework');

		$token    = JSession::getFormToken() . '=' . 1;
		$options  = array(
			'plg_quickicon_privacycheck_url'      => JUri::base() . 'index.php?option=com_privacy&' . $token,
			'plg_quickicon_privacycheck_ajax_url' => JUri::base() . 'index.php?option=com_privacy&task=ajax&' . $token,
			'plg_quickicon_privacycheck_text'     => array(
				"UPTODATE"            => JText::_('PLG_QUICKICON_PRIVACYCHECK_UPTODATE', true),
				"UPDATEFOUND"         => JText::_('PLG_QUICKICON_PRIVACYCHECK_UPDATEFOUND', true),
				"UPDATEFOUND_MESSAGE" => JText::_('PLG_QUICKICON_PRIVACYCHECK_UPDATEFOUND_MESSAGE', true),
				"UPDATEFOUND_BUTTON"  => JText::_('PLG_QUICKICON_PRIVACYCHECK_UPDATEFOUND_BUTTON', true),
				"ERROR"               => JText::_('PLG_QUICKICON_PRIVACYCHECK_ERROR', true),
			)
		);

		JFactory::getDocument()->addScriptOptions('js-privacy-check', $options);
		JHtml::_('script', 'plg_quickicon_privacycheck/privacycheck.js', array('version' => 'auto', 'relative' => true));

		return array(
			array(
				'link'  => 'index.php?option=com_privacy&view=requests&' . $token,
				'image' => 'users',
				'icon'  => 'header/icon-48-user.png',
				'text'  => JText::_('PLG_QUICKICON_PRIVACYCHECK_CHECKING'),
				'id'    => 'plg_quickicon_privacycheck',
				'group' => 'MOD_QUICKICON_MAINTENANCE'
			)
		);
	}
}
