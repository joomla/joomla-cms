<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.readmore
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Editor Readmore buton
 *
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.readmore
 * @since       1.5
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
	 * readmore button
	 * @return array A two element array of (imageName, textToInsert)
	 */
	public function onDisplay($name)
	{
		$doc = JFactory::getDocument();

		// button is not active in specific content components

		$getContent = $this->_subject->getContent($name);
		$present = JText::_('PLG_READMORE_ALREADY_EXISTS', true);
		$js = "
			function insertReadmore(editor)
			{
				var content = $getContent
				if (content.match(/<hr\s+id=(\"|')system-readmore(\"|')\s*\/*>/i))
				{
					alert('$present');
					return false;
				} else {
					jInsertEditorText('<hr id=\"system-readmore\" />', editor);
				}
			}
			";

		$doc->addScriptDeclaration($js);

		$button = new JObject;
		$button->modal = false;
		$button->class = 'btn';
		$button->onclick = 'insertReadmore(\''.$name.'\');return false;';
		$button->text = JText::_('PLG_READMORE_BUTTON_READMORE');
		$button->name = 'arrow-down';
		// TODO: The button writer needs to take into account the javascript directive
		//$button->link', 'javascript:void(0)');
		$button->link = '#';

		return $button;
	}
}
