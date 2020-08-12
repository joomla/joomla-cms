<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\LanguageHelper;

/**
 * The article controller for ajax requests
 *
 * @since  3.9.0
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
	 * @since  3.9.0
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
				echo new JResponseJson(null, JText::sprintf('JLIB_FORM_VALIDATE_FIELD_INVALID', 'assocId'), true);

				return;
			}

			$excludeLang = $input->get('excludeLang', '', 'STRING');

			$associations = JLanguageAssociations::getAssociations('com_content', '#__content', 'com_content.item', (int) $assocId);

			unset($associations[$excludeLang]);

			// Add the title to each of the associated records
			$contentTable = JTable::getInstance('Content', 'JTable');

			foreach ($associations as $lang => $association)
			{
				$contentTable->load($association->id);
				$associations[$lang]->title = $contentTable->title;
			}

			$countContentLanguages = count(LanguageHelper::getContentLanguages(array(0, 1)));

			if (count($associations) == 0)
			{
				$message = JText::_('JGLOBAL_ASSOCIATIONS_PROPAGATE_MESSAGE_NONE');
			}
			elseif ($countContentLanguages > count($associations) + 2)
			{
				$tags    = implode(', ', array_keys($associations));
				$message = JText::sprintf('JGLOBAL_ASSOCIATIONS_PROPAGATE_MESSAGE_SOME', $tags);
			}
			else
			{
				$message = JText::_('JGLOBAL_ASSOCIATIONS_PROPAGATE_MESSAGE_ALL');
			}

			echo new JResponseJson($associations, $message);
		}
	}
}
