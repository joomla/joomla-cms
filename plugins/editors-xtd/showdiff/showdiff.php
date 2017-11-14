<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.showdiff
 * @copyright   Copyright (C)2017 - Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Model\Model;

JLoader::register('ContenthistoryHelper', JPATH_ADMINISTRATOR . '/components/com_contenthistory/helpers/contenthistory.php');

/**
 * Editor showdiff button
 *
 * @since  1.5
 */
class PlgButtonShowdiff extends JPlugin
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
	 * @return  JObject  The button options as JObject
	 *
	 * @since   1.5
	 */
	public function onDisplay($name)
	{
		$input  = JFactory::getApplication()->input;
		$itemId = $input->get('id');
		$user   = JFactory::getUser();

		if ($user->authorise('core.create', 'com_content')
			|| $user->authorise('core.edit', 'com_content')
			|| $user->authorise('core.edit.own', 'com_content'))
		{
			if (JUri::getInstance($_SERVER['HTTP_REFERER'])->getVar('option') === 'com_associations')
			{
				JFactory::getDocument()->addScriptOptions('xtd-showdiff', array('editor' => $name));

				$link = 'index.php?option=com_content&amp;view=article&amp;layout=showdiff&amp;tmpl=component&amp;e_name='
					. $name . '&amp;id=' . $itemId;

				$button          = new JObject;
				$button->modal   = true;
				$button->class   = 'btn';
				$button->link    = $link;
				$button->text    = JText::_('PLG_EDITORSXTD_SHOWDIFF_BUTTON_SHOWDIFF');
				$button->name    = 'shuffle';
				$button->options = array(
					'height'     => '450px',
					'width'      => '400px',
					'bodyHeight' => '70',
					'modalWidth' => '80',
				);

				return $button;
			}
		}

		// TODO Missing return statement?
	}
}
