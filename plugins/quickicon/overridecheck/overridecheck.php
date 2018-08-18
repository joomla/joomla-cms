<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Quickicon.Overridecheck
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
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
 * Joomla! template override notification plugin
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgQuickiconOverrideCheck extends CMSPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * Returns an icon definition for an icon which looks for overrides update
	 * via AJAX and displays a notification when such overrides are updated.
	 *
	 * @param   string  $context  The calling context
	 *
	 * @return  array  A list of icon definition associative arrays, consisting of the
	 *                 keys link, image, text and access.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onGetIcons($context)
	{
		if ($context !== $this->params->get('context', 'mod_quickicon') || !Factory::getUser()->authorise('core.manage', 'com_installer'))
		{
			return array();
		}

		$token    = Session::getFormToken() . '=1';
		$options  = array(
			'url' => Uri::base() . 'index.php?option=com_templates&view=templates',
			'ajaxUrl' => Uri::base() . 'index.php?option=com_templates&view=templates&task=template.ajax&' . $token,
		);

		Factory::getDocument()->addScriptOptions('js-override-check', $options);

		Text::script('PLG_QUICKICON_OVERRIDECHECK_ERROR', true);
		Text::script('PLG_QUICKICON_OVERRIDECHECK_ERROR_ENABLE', true);
		Text::script('PLG_QUICKICON_OVERRIDECHECK_UPTODATE', true);
		Text::script('PLG_QUICKICON_OVERRIDECHECK_OVERRIDEFOUND', true);

		HTMLHelper::_('behavior.core');
		HTMLHelper::_('script', 'plg_quickicon_overridecheck/overridecheck.js', array('version' => 'auto', 'relative' => true));

		return array(
			array(
				'link'  => 'index.php?option=com_templates&view=templates',
				'image' => 'fa fa-file',
				'icon'  => '',
				'text'  => Text::_('PLG_QUICKICON_OVERRIDECHECK_CHECKING'),
				'id'    => 'plg_quickicon_overridecheck',
				'group' => 'MOD_QUICKICON_MAINTENANCE'
			)
		);
	}
}
