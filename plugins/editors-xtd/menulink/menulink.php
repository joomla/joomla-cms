<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.article
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Editor Article buton
 *
 * @since  1.5
 */
class PlgButtonMenulink extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Display the button
	 *
	 * @param   string  $name  The name of the button to add
	 *
	 * @return array A four element array of (menu_id, menu_title, link, language)
	 */
	public function onDisplay($name)
	{
		$app = JFactory::getApplication();

		/* Load plugin button only in the backend */
		if ($app->isSite())
		{
			return;
		}
		/*
		 * Javascript to insert the link
		 * View element calls jSelectArticle when an article is clicked
		 * jSelectArticle creates the link tag, sends it to the editor,
		 * and closes the select frame.
		 */
		$js = "
		function jSelectMenuitem(id, title, link, lang)
		{
			var hreflang = '';
			if (lang !== '')
			{
				var hreflang = ' hreflang = \"' + lang + '\"';
			}
			var tag = '<a' + hreflang + ' href=\"' + link + '\">' + title + '</a>';
			jInsertEditorText(tag, '" . $name . "');
			jModalClose();
		}";

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($js);

		/*
		 * Use the built-in element view to select the article.
		 * Currently uses blank class.
		 */
		$link = 'index.php?option=com_menus&amp;view=items&amp;layout=modal&amp;tmpl=component&amp;editor='
		. $name . '&amp;' . JSession::getFormToken() . '=1';

		$button = new JObject;
		$button->modal = true;
		$button->class = 'btn';
		$button->link = $link;
		$button->text = JText::_('PLG_MENULINK_BUTTON_MENU');
		$button->name = 'file-add';
		$button->options = "{handler: 'iframe', size: {x: 800, y: 500}}";

		return $button;
	}
}
