<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

/**
 * Editor Readmore buton
 *
 * @package Editors-xtd
 * @since 1.5
 */
class plgButtonReadmore extends JPlugin
{
	/**
	 * readmore button
	 * @return array A two element array of (imageName, textToInsert)
	 */
	function onDisplay($name)
	{
		$app = JFactory::getApplication();

		$doc 		= &JFactory::getDocument();
		$template 	= $app->getTemplate();

		// button is not active in specific content components

		$getContent = $this->_subject->getContent($name);
		$present = JText::_('ALREADY_EXISTS', true) ;
		$js = "
			function insertReadmore(editor) {
				var content = $getContent
				if (content.match(/<hr\s+id=(\"|')system-readmore(\"|')\s*\/*>/i)) {
					alert('$present');
					return false;
				} else {
					jInsertEditorText('<hr id=\"system-readmore\" />', editor);
				}
			}
			";

		$doc->addScriptDeclaration($js);

		$button = new JObject;
		$button->set('modal', false);
		$button->set('onclick', 'insertReadmore(\''.$name.'\');return false;');
		$button->set('text', JText::_('Readmore'));
		$button->set('name', 'readmore');
		// TODO: The button writer needs to take into account the javascript directive
		//$button->set('link', 'javascript:void(0)');
		$button->set('link', '#');

		return $button;
	}
}