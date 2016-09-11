<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The article controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_content
 * @since       _DEPLOY_VERSION_
 */
class ContentControllerArticle extends JControllerLegacy
{
	/**
	 * Method to generate and store share token.
	 *
	 * @return  boolean   True if token successfully stored, false otherwise and internal error is set.
	 *
	 * @since   _DEPLOY_VERSION_
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
