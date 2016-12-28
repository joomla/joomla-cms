<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.contact
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Editor Contact buton
 *
 * @since  3.7.0
 */
class PlgButtonContact extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.7.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Display the button
	 *
	 * @param   string  $name  The name of the button to add
	 *
	 * @return  JObject  The button options as JObject
	 *
	 * @since   3.7.0
	 */
	public function onDisplay($name)
	{
		/*
		 * Javascript to insert the link
		 * View element calls jSelectContact when a contact is clicked
		 * jSelectContact creates the link tag, sends it to the editor,
		 * and closes the select frame.
		 */
		$js = "
		function jSelectContact(id, title, catid, object, link, lang)
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

		JFactory::getDocument()->addScriptDeclaration($js);

		/*
		 * Use the built-in element view to select the contact.
		 * Currently uses blank class.
		 */
		$link = 'index.php?option=com_contact&amp;view=contacts&amp;layout=modal&amp;tmpl=component&amp;' . JSession::getFormToken() . '=1';

		$button = new JObject;
		$button->modal   = true;
		$button->class   = 'btn';
		$button->link    = $link;
		$button->text    = JText::_('PLG_EDITORS-XTD_CONTACT_BUTTON_CONTACT');
		$button->name    = 'address';
		$button->options = "{handler: 'iframe', size: {x: 800, y: 500}}";

		return $button;
	}
}
