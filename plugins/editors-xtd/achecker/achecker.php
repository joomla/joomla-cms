<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.achecker
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\CMSPlugin;

/**
 * Editor Achecker button
 *
 * @since  __deploy_version__
 */
class PlgButtonAchecker extends CMSPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  __deploy_version__
	 */
	protected $autoloadLanguage = true;

	/**
	 * Display the button
	 *
	 * @param   string  $name  The name of the button to add
	 *
	 * @return  CMSObject  The button options as JObject
	 *
	 * @since   __deploy_version__
	 */
	public function onDisplay($name)
	{
		$user  = Factory::getUser();

		// Can create in any category (component permission) or at least in one category
		$canCreateRecords = $user->authorise('core.create', 'com_content')
			|| count($user->getAuthorisedCategories('com_content', 'core.create')) > 0;

		// Instead of checking edit on all records, we can use **same** check as the form editing view
		$values = (array) Factory::getApplication()->getUserState('com_content.edit.article.id');
		$isEditingRecords = count($values);

		// This ACL check is probably a double-check (form view already performed checks)
		$hasAccess = $canCreateRecords || $isEditingRecords;
		if (!$hasAccess)
		{
			return;
		}

		Factory::getDocument()->addScriptOptions('xtd-pagebreak', array('editor' => $name));
		$link = 'index.php?option=com_content&amp;view=article&amp;layout=pagebreak&amp;tmpl=component&amp;e_name=' . $name;

		$button          = new CMSObject;
		$button->modal   = true;
		$button->link    = $link;
		$button->text    = Text::_('PLG_EDITORS-XTD_ACHECKER_BUTTON_ACCESSIBILITY');
		$button->name    = 'accessible';
		$button->iconSVG = '<svg viewBox="0 0 448 512" width="24" height="24"><path d="M423.9 255.8L411 413.1c-3.3 40.7-63.9 35.1-60.6-4.9l10-122.5-41.1 2.3c10.1 20.7 15.8 43.9 15.8 68.5 0 41.2-16.1 78.7-42.3 106.5l-39.3-39.3c57.9-63.7 13.1-167.2-74-167.2-25.9 0-49.5 9.9-67.2 26L73 243.2c22-20.7 50.1-35.1 81.4-40.2l75.3-85.7-42.6-24.8-51.6 46c-30 26.8-70.6-18.5-40.5-45.4l68-60.7c9.8-8.8 24.1-10.2 35.5-3.6 0 0 139.3 80.9 139.5 81.1 16.2 10.1 20.7 36 6.1 52.6L285.7 229l106.1-5.9c18.5-1.1 33.6 14.4 32.1 32.7zm-64.9-154c28.1 0 50.9-22.8 50.9-50.9C409.9 22.8 387.1 0 359 0c-28.1 0-50.9 22.8-50.9 50.9 0 28.1 22.8 50.9 50.9 50.9zM179.6 456.5c-80.6 0-127.4-90.6-82.7-156.1l-39.7-39.7C36.4 287 24 320.3 24 356.4c0 130.7 150.7 201.4 251.4 122.5l-39.7-39.7c-16 10.9-35.3 17.3-56.1 17.3z"></pa'
							. 'th></svg>';
		$button->options = [
			'height'     => '200px',
			'width'      => '400px',
			'bodyHeight' => '70',
			'modalWidth' => '80',
		];

		return $button;
	}
}
