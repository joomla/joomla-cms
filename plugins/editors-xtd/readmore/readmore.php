<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.readmore
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Editor Readmore button
 *
 * @since  1.5
 */
class PlgButtonReadmore extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Readmore button
	 *
	 * @param   string  $name  The name of the button to add
	 *
	 * @return  JObject  The button options as JObject
	 *
	 * @since   1.5
	 */
	public function onDisplay($name)
	{
		JHtml::_('script', 'com_content/admin-article-readmore.min.js', array('version' => 'auto', 'relative' => true));

		// Pass some data to javascript
		JFactory::getDocument()->addScriptOptions(
			'xtd-readmore',
			array(
				'editor' => $this->_subject->getContent($name),
				'exists' => JText::_('PLG_READMORE_ALREADY_EXISTS', true),
			)
		);

		$button = new JObject;
		$button->modal   = false;
		$button->class   = 'btn';
		$button->onclick = 'insertReadmore(\'' . $name . '\');return false;';
		$button->text    = JText::_('PLG_READMORE_BUTTON_READMORE');
		$button->name    = 'arrow-down';
		$button->link    = '#';

		return $button;
	}
}
