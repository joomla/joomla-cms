<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Quickicon.Extensionupdate
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\CMSPlugin;

/**
 * Joomla! update notification plugin
 *
 * @since  2.5
 */
class PlgQuickiconExtensionupdate extends CMSPlugin
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
		if ($context !== $this->params->get('context', 'mod_quickicon') || !Factory::getUser()->authorise('core.manage', 'com_installer'))
		{
			return array();
		}

		$token    = Session::getFormToken() . '=1';
		$options  = array(
			'url' => Uri::base() . 'index.php?option=com_installer&view=update&task=update.find&' . $token,
			'ajaxUrl' => Uri::base() . 'index.php?option=com_installer&view=update&task=update.ajax&' . $token,
		);

		Factory::getDocument()->addScriptOptions('js-extensions-update', $options);

		Text::script('PLG_QUICKICON_EXTENSIONUPDATE_UPTODATE', true);
		Text::script('PLG_QUICKICON_EXTENSIONUPDATE_UPDATEFOUND', true);
		Text::script('PLG_QUICKICON_EXTENSIONUPDATE_UPDATEFOUND_MESSAGE', true);
		Text::script('PLG_QUICKICON_EXTENSIONUPDATE_UPDATEFOUND_BUTTON', true);
		Text::script('PLG_QUICKICON_EXTENSIONUPDATE_ERROR', true);

		HTMLHelper::_('behavior.core');
		HTMLHelper::_('script', 'plg_quickicon_extensionupdate/extensionupdatecheck.min.js', array('version' => 'auto', 'relative' => true));

		return array(
			array(
				'link'  => 'index.php?option=com_installer&view=update&task=update.find&' . $token,
				'image' => 'fa fa-star-o',
				'icon'  => '',
				'text'  => Text::_('PLG_QUICKICON_EXTENSIONUPDATE_CHECKING'),
				'id'    => 'plg_quickicon_extensionupdate',
				'group' => 'MOD_QUICKICON_MAINTENANCE'
			)
		);
	}
}
