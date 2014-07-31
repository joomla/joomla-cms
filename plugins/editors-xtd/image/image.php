<?php
/**
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * Editor Image buton
 *
 * @package		Joomla.Plugin
 * @subpackage	Editors-xtd.image
 * @since 1.5
 */
class plgButtonImage extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @access      protected
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
	function onDisplay($name, $asset, $author)
	{
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_media');
		$user = JFactory::getUser();
		$extension = JRequest::getCmd('option');
		if ($asset == ''){
			$asset = $extension;
		}
		if (	$user->authorise('core.edit', $asset)
			||	$user->authorise('core.create', $asset)
			||	(count($user->getAuthorisedCategories($asset, 'core.create')) > 0)
			||	($user->authorise('core.edit.own', $asset) && $author == $user->id)
			||	(count($user->getAuthorisedCategories($extension, 'core.edit')) > 0)
			||	(count($user->getAuthorisedCategories($extension, 'core.edit.own')) > 0 && $author == $user->id)
		)
		{
			$link = 'index.php?option=com_media&amp;view=images&amp;tmpl=component&amp;e_name=' . $name . '&amp;asset=' . $asset . '&amp;author=' . $author;
			JHtml::_('behavior.modal');
			$button = new JObject;
			$button->set('modal', true);
			$button->set('link', $link);
			$button->set('text', JText::_('PLG_IMAGE_BUTTON_IMAGE'));
			$button->set('name', 'image');
			$button->set('options', "{handler: 'iframe', size: {x: 800, y: 500}}");
			return $button;
		}
				else
		{
			return false;
		}
	}
}
