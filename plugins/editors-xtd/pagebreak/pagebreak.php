<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

/**
 * Editor Pagebreak buton
 *
 * @package Editors-xtd
 * @since 1.5
 */
class plgButtonPagebreak extends JPlugin
{
	/**
	 * Display the button
	 *
	 * @return array A two element array of (imageName, textToInsert)
	 */
	function onDisplay($name)
	{
		$app = JFactory::getApplication();

		$doc = & JFactory::getDocument();
		$template = $app->getTemplate();

		$link = 'index.php?option=com_content&amp;task=ins_pagebreak&amp;tmpl=component&amp;e_name='.$name;

		JHtml::_('behavior.modal');

		$button = new JObject;
		$button->set('modal', true);
		$button->set('link', $link);
		$button->set('text', JText::_('Pagebreak'));
		$button->set('name', 'pagebreak');
		$button->set('options', "{handler: 'iframe', size: {x: 400, y: 85}}");

		return $button;
	}
}