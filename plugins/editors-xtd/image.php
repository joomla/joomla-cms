<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.event.plugin');

/**
 * Editor Image buton
 *
 * @author Johan Janssens <johan.janssens@joomla.org>
 * @package Editors-xtd
 * @since 1.5
 */
class plgButtonImage extends JPlugin
{

	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @param object $params  The object that holds the plugin parameters
	 * @since 1.5
	 */
	function plgButtonImage(& $subject, $params) {
		parent::__construct($subject, $params);
	}

	/**
	 * Display the button
	 *
	 * @return array A two element array of ( imageName, textToInsert )
	 */
	function onDisplay($name)
	{
		global $mainframe;

		$doc 		=& JFactory::getDocument();
		$template 	= $mainframe->getTemplate();

		$url 		= $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();

		$link = 'index.php?option=com_media&amp;task=imgManager&amp;tmpl=component';
		$css = "\t.button1-left .image { background: url($url/plugins/editors-xtd/image.gif) 100% 0 no-repeat; }";
		$doc->addStyleDeclaration($css);

		JHTML::_('behavior.modal');

		$button = new JObject();
		$button->set('modal', true);
		$button->set('link', $link);
		$button->set('text', JText::_('Image'));
		$button->set('name', 'image');
		$button->set('options', "{handler: 'iframe', size: {x: 570, y: 400}}");

		return $button;
	}
}
?>