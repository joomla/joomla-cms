<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Quickicon.Joomlaupdate
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Joomla! update notification plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  Quickicon.Joomlaupdate
 * @since       2.5
 */
class PlgQuickiconJoomlaupdate extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * This method is called when the Quick Icons module is constructing its set
	 * of icons. You can return an array which defines a single icon and it will
	 * be rendered right after the stock Quick Icons.
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
		if ($context != $this->params->get('context', 'mod_quickicon') || !JFactory::getUser()->authorise('core.manage', 'com_installer'))
		{
			return;
		}

		JHtml::_('jquery.framework');

		$cur_template = JFactory::getApplication()->getTemplate();
		$url = JUri::base() . 'index.php?option=com_joomlaupdate';
		$ajax_url = JUri::base() . 'index.php?option=com_installer&view=update&task=update.ajax';
		$script = array();
		$script[] = 'var plg_quickicon_joomlaupdate_url = \'' . $url . '\';';
		$script[] = 'var plg_quickicon_joomlaupdate_ajax_url = \'' . $ajax_url . '\';';
		$script[] = 'var plg_quickicon_jupdatecheck_jversion = \'' . JVERSION . '\'';
		$script[] = 'var plg_quickicon_joomlaupdate_text = {'
			. '"UPTODATE" : "' . JText::_('PLG_QUICKICON_JOOMLAUPDATE_UPTODATE', true) . '",'
			. '"UPDATEFOUND": "' . JText::_('PLG_QUICKICON_JOOMLAUPDATE_UPDATEFOUND', true) . '",'
			. '"UPDATEFOUND_MESSAGE": "' . JText::_('PLG_QUICKICON_JOOMLAUPDATE_UPDATEFOUND_MESSAGE', true) . '",'
			. '"UPDATEFOUND_BUTTON": "' . JText::_('PLG_QUICKICON_JOOMLAUPDATE_UPDATEFOUND_BUTTON', true) . '",'
			. '"ERROR": "' . JText::_('PLG_QUICKICON_JOOMLAUPDATE_ERROR', true) . '",'
			. '};';
		$script[] = 'var plg_quickicon_joomlaupdate_img = {'
			. '"UPTODATE" : "' . JUri::base(true) . '/templates/' . $cur_template . '/images/header/icon-48-jupdate-uptodate.png",'
			. '"UPDATEFOUND": "' . JUri::base(true) . '/templates/' . $cur_template . '/images/header/icon-48-jupdate-updatefound.png",'
			. '"ERROR": "' . JUri::base(true) . '/templates/' . $cur_template . '/images/header/icon-48-deny.png",'
			. '};';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));
		JHtml::_('script', 'plg_quickicon_joomlaupdate/jupdatecheck.js', false, true);

		return array(
			array(
				'link' => 'index.php?option=com_joomlaupdate',
				'image' => 'joomla',
				'icon' => 'header/icon-48-download.png',
				'text' => JText::_('PLG_QUICKICON_JOOMLAUPDATE_CHECKING'),
				'id' => 'plg_quickicon_joomlaupdate',
				'group' => 'MOD_QUICKICON_MAINTENANCE'
			)
		);
	}
}
