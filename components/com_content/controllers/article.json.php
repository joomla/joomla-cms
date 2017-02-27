<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The article controller
 *
 * @since  __DEPLOY_VERSION__
 */
class ContentControllerArticle extends JControllerLegacy
{
	/**
	 * Method to generate and store share token.
	 *
	 * @return  string  Returns a JSON string.
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @throws  InvalidArgumentException
	 */
	public function shareDraft()
	{
		$app    = JFactory::getApplication();

		if (!JSession::checkToken('get'))
		{
			echo new JResponseJson(null, JText::_('JINVALID_TOKEN'), true);

			$app->close();
		}

		$return  = false;
		$error   = false;
		$message = JText::plural('COM_CONTENT_DRAFT_LINKS_N_ITEMS_SAVED', 1);

		try
		{
			$articleId = $this->input->get('articleId', false, 'int');
			$alias     = $this->input->get('alias', false, 'cmd');

			if (false === $articleId)
			{
				throw new InvalidArgumentException(JText::_('COM_CONTENT_INVALID_ARTICLE_ID'));
			}

			// Base this model on the backend version.
			static::addModelPath(JPATH_ADMINISTRATOR . '/components/com_content/models', 'ContentModel');

			// Get the model
			/** @var ContentModelArticle $model */
			$model  = $this->getModel('Article', 'ContentModel');
			$return = $model->createShareDraft($articleId, $alias);
		}
		catch (Exception $e)
		{
			$error = true;
			$message = JText::_('COM_CONTENT_DRAFT_LINKS_ERROR');
		}

		echo new JResponseJson($return, $message, $error);

		$app->close();
	}
}
