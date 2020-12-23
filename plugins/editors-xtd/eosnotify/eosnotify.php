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
		return array(array(
			'link' => 'http://www.google.com',
			'image' => JURI::root() . 'plugins/quickicon/eosnotify/stop15.png',
			'text' => '<span style="color:red;">Joomla 2.5 Support Has Ended!!<br />Click Here for More Information.</span>',
			'id' => 'plg_quickicon_eos'
		));
	}
}
