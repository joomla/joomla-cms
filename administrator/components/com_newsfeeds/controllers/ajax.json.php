<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The newsfeed controller for ajax requests
 *
 * @since  __DEPLOY_VERSION__
 */
class NewsfeedsControllerAjax extends JControllerLegacy
{
	/**
	 * Method to fetch associations of a newsfeed
	 *
	 * The method assumes that the following http parameters are passed in an Ajax Get request:
	 * token: the form token
	 * assocId: the id of the newsfeed whose associations are to be returned
	 * excludeLang: the association for this language is to be excluded
	 *
	 * @return  null
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function fetchAssociations()
	{
		if (!JSession::checkToken('get'))
		{
			echo new JResponseJson(null, JText::_('JINVALID_TOKEN'), true);
		}
		else
		{
			$input = JFactory::getApplication()->input;

			$assocId = $input->getInt('assocId', 0);

			if ($assocId == 0)
			{
				echo new JResponseJson(null, JText::_('JLIB_FORM_VALIDATE_FIELD_INVALID', "assocId"), true);

				return;
			}

			$excludeLang = $input->get('excludeLang', '', 'STRING');

			$associations = JLanguageAssociations::getAssociations('com_newsfeeds', '#__newsfeeds', 'com_newsfeeds.item', (int) $assocId);

			unset($associations[$excludeLang]);

			// Add the title to each of the associated records
			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_newsfeeds/tables');
			$newsfeedsTable = JTable::getInstance('Newsfeed', 'NewsfeedsTable');

			foreach ($associations as $lang => $association)
			{
				$newsfeedsTable->load($association->id);
				$associations[$lang]->title = $newsfeedsTable->name;
			}

			$message = JText::_('JGLOBAL_ASSOCIATIONS_PROPAGATE_MESSAGE');

			echo new JResponseJson($associations, $message);
		}
	}
}
