<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.article
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;

/**
 * Editor Article button
 *
 * @since  1.5
 */
class PlgButtonArticle extends CMSPlugin
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
	 * @return  CMSObject  The button options as JObject
	 *
	 * @since   1.5
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

		$link = 'index.php?option=com_content&amp;view=articles&amp;layout=modal&amp;tmpl=component&amp;'
			. Session::getFormToken() . '=1&amp;editor=' . $name;

		$button = new CMSObject;
		$button->modal   = true;
		$button->link    = $link;
		$button->text    = Text::_('PLG_ARTICLE_BUTTON_ARTICLE');
		$button->name    = $this->_type . '_' . $this->_name;
		$button->icon    = 'file-add';
		$button->iconSVG = '<svg viewBox="0 0 32 32" width="24" height="24"><path d="M28 24v-4h-4v4h-4v4h4v4h4v-4h4v-4zM2 2h18v6h6v10h2v-10l-8-'
								. '8h-20v32h18v-2h-16z"></path></svg>';
		$button->options = [
			'height'     => '300px',
			'width'      => '800px',
			'bodyHeight' => '70',
			'modalWidth' => '80',
		];

		return $button;
	}
}
