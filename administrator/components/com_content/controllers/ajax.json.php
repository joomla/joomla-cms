<?php
/**
 * @package	 Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license	 GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The article controller for ajax requests
 *
 * @since  __DEPLOY_VERSION__
 */
class ContentControllerAjax extends JControllerLegacy
{
	/**
	 * Method to fetch associations of an article
	 *
	 * The method assumes that the following http parameters are passed in an Ajax Get request:
	 * token: the form token
	 * assocId: the id of the article whose associations are to be returned
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

			$associations = JLanguageAssociations::getAssociations('com_content', '#__content', 'com_content.item', (int) $assocId);

			unset($associations[$excludeLang]);

			// Add the title to each of the associated records
			$contentTable = JTable::getInstance('Content', 'JTable');

			foreach($associations as $lang => $association)
			{
				$contentTable->load($association->id);
				$associations[$lang]->title = $contentTable->title;
			}

			$message = JText::_('JGLOBAL_ASSOCIATIONS_PROPAGATE_MESSAGE');

			echo new JResponseJson($associations, $message);
		}
	}
}