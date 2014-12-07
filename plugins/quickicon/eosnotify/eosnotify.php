<?php
/**
 * @package		Joomla.Plugin
 * @subpackage	Quickicon.Joomla
 *
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Joomla! End of Support Notification Plugin
 *
 * @since       2.5.28
 * @deprecated  3.0
 */
class PlgQuickiconEosnotify extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 *
	 * @since   2.5.28
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
	 * @param   $context  The calling context
	 *
	 * @return  array  A list of icon definition associative arrays, consisting of the
	 *				   keys link, image, text and access.
	 *
	 * @since   2.5.28
	 */
	public function onGetIcons($context)
	{
		if (!JFactory::getApplication()->isAdmin() || version_compare(JVERSION, '3.0', '>='))
		{
			return;
		}

		$text = JText::_('PLG_EOSNOTIFY_SUPPORT_ENDING');

		if (JFactory::getDate() >= '2015-01-01')
		{
			$text = JText::_('PLG_EOSNOTIFY_SUPPORT_ENDED');
		}

		if (JAdministratorHelper::findOption() == 'com_cpanel')
		{
			JFactory::getApplication()->enqueueMessage(JText::sprintf('PLG_EOSNOTIFY_CLICK_FOR_INFORMATION_WITH_LINK', $text), 'error');
		}
	}
}
