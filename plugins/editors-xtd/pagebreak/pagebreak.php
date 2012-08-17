<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.pagebreak
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Editor Pagebreak buton
 *
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.pagebreak
 * @since       1.5
 */
class plgButtonPagebreak extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 * @since       1.5
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	/**
	 * Display the button
	 *
	 * @return array A two element array of (imageName, textToInsert)
	 */
	public function onDisplay($name)
	{
		$app = JFactory::getApplication();

		$doc = JFactory::getDocument();
		$template = $app->getTemplate();

		$link = 'index.php?option=com_content&amp;view=article&amp;layout=pagebreak&amp;tmpl=component&amp;e_name='.$name;

		JHtml::_('behavior.modal');

		$button = new JObject;
		$button->modal = true;
		$button->link  = $link;
		$button->text  = JText::_('PLG_EDITORSXTD_PAGEBREAK_BUTTON_PAGEBREAK');
		$button->name  = 'copy';
		$button->options = "{handler: 'iframe', size: {x: 500, y: 300}}";

		return $button;
	}
}
