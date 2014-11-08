<?php

/**
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Joomla! udpate notification plugin
 *
 * @package		Joomla.Plugin
 * @subpackage	Quickicon.Joomla
 * @since		2.5
 */
class plgQuickiconEosnotify extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 *
	 * @since   2.5
	 */
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);

		$this->loadLanguage();
	}

	/**
	 * This method is called when the Quick Icons module is constructing its set
	 * of icons. You can return an array which defines a single icon and it will
	 * be rendered right after the stock Quick Icons.
	 *
	 * @param  $context  The calling context
	 *
	 * @return array A list of icon definition associative arrays, consisting of the
	 *				 keys link, image, text and access.
	 *
	 * @since       2.5
	 */
	public function onGetIcons($context)
	{
		$text = JText::_('PLG_EOSNOTIFY_SUPPORT_ENDING');
		if(date('Ymd')>='20150101')
			$text = JText::_('PLG_EOSNOTIFY_SUPPORT_ENDED');
			
		if (JAdministratorHelper::findOption() == 'com_cpanel') { 
			$messtext = '<div style="background-color:#FFCFCF; font-size:16px; font-weight:bold; margin-bottom: 10px; padding: 10px; border-radius: 10px;">' . $text . ' ' . JText::_('PLG_EOSNOTIFY_CLICK_FOR_INFORMATION_WITH_LINK') . '</div>';
			echo("<script>document.getElementById('system-message-container').innerHTML = '" . $messtext . "';</script>");
		}
		
		return array(array(
			'link' => 'http://docs.joomla.org/Why_Migrate',
			'image' => JURI::root() . 'plugins/quickicon/eosnotify/stop15.png',
			'text' => '<span style="color:red;">' . $text . '<br />' . JText::_('PLG_EOSNOTIFY_CLICK_FOR_INFORMATION') . '.</span>',
			'id' => 'plg_quickicon_eos'
		));
	}
}
