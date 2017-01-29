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
 * @since  __DEPLOY_VERSION__
 */
class ContentControllerArticle extends JControllerForm
{
	/**
	 * Method to generate and store share token.
	 *
	 * @return  void
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

			if (false === $articleId)
			{
				throw new InvalidArgumentException(JText::_('COM_CONTENT_INVALID_ARTICLE_ID'));
			}

			// Get the model
			/** @var ContentModelArticle $model */
			$model = $this->getModel();
			$return = $model->createShareDraft($articleId);
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
