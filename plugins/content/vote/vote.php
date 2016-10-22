<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.vote
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Vote plugin.
 *
 * @since  1.5
 */
class PlgContentVote extends JPlugin
{
	/**
	 * Application object
	 *
	 * @var    JApplicationCms
	 * @since  __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * Database object
	 *
	 * @var    JDatabaseDriver
	 * @since  __DEPLOY_VERSION__
	 */
	protected $db;

	/**
	 * Method to save a vote.
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception
	 */
	public function onAjaxVote()
	{
		// Check for request forgeries.
		if (!JSession::checkToken())
		{
			throw new Exception(JText::_('JINVALID_TOKEN'));
		}

		$user_rating = $this->app->input->getInt('user_rating', -1);

		if ($user_rating > -1)
		{
			$id = $this->app->input->getInt('id', 0);

			try
			{
				$this->storeVote($id, $user_rating);

				return [
					'success' => true,
					'message' => JText::_('COM_CONTENT_ARTICLE_VOTE_SUCCESS'),
				];
			}
			catch (Exception $e)
			{
				$this->app->getLogger()->warning(
					'Failed to save vote on content.',
					[
						'exception' => $e,
						'category'  => 'error',
					]
				);

				return [
					'success' => false,
					'message' => JText::_('COM_CONTENT_ARTICLE_VOTE_FAILURE'),
				];
			}
		}
	}

	/**
	 * Displays the voting area if in an article
	 *
	 * @param   string   $context  The context of the content being passed to the plugin
	 * @param   object   &$row     The article object
	 * @param   object   &$params  The article params
	 * @param   integer  $page     The 'page' number
	 *
	 * @return  mixed  html string containing code for the votes if in com_content else boolean false
	 *
	 * @since   1.6
	 */
	public function onContentBeforeDisplay($context, &$row, &$params, $page=0)
	{
		$parts = explode(".", $context);

		if ($parts[0] != 'com_content')
		{
			return false;
		}

		$html = '';

		if (!empty($params) && $params->get('show_vote', null))
		{
			// Load plugin language files only when needed (ex: they are not needed if show_vote is not active).
			$this->loadLanguage();

			$rating = (int) @$row->rating;

			$view = JFactory::getApplication()->input->getString('view', '');
			$img = '';

			// Look for images in template if available
			$starImageOn  = JHtml::_('image', 'system/rating_star.png', JText::_('PLG_VOTE_STAR_ACTIVE'), null, true);
			$starImageOff = JHtml::_('image', 'system/rating_star_blank.png', JText::_('PLG_VOTE_STAR_INACTIVE'), null, true);

			for ($i = 0; $i < $rating; $i++)
			{
				$img .= $starImageOn;
			}

			for ($i = $rating; $i < 5; $i++)
			{
				$img .= $starImageOff;
			}

			$html .= '<div class="content_rating" itemprop="aggregateRating" itemscope itemtype="https://schema.org/AggregateRating">';
			$html .= '<p class="unseen element-invisible">'
					. JText::sprintf('PLG_VOTE_USER_RATING', '<span itemprop="ratingValue">' . $rating . '</span>', '<span itemprop="bestRating">5</span>')
					. '<meta itemprop="ratingCount" content="' . (int) $row->rating_count . '" />'
					. '<meta itemprop="worstRating" content="0" />'
					. '</p>';
			$html .= $img;
			$html .= '</div>';

			if ($view == 'article' && $row->state == 1)
			{
				$uri = clone JUri::getInstance();
				$uri->setVar('hitcount', '0');

				// Create option list for voting select box
				$options = array();

				for ($i = 1; $i < 6; $i++)
				{
					$options[] = JHtml::_('select.option', $i, JText::sprintf('PLG_VOTE_VOTE', $i));
				}

				// Generate voting form
				$html .= '<form method="post" action="' . htmlspecialchars($uri->toString(), ENT_COMPAT, 'UTF-8') . '" class="form-inline">';
				$html .= '<span class="content_vote">';
				$html .= '<label class="unseen element-invisible" for="content_vote_' . $row->id . '">' . JText::_('PLG_VOTE_LABEL') . '</label>';
				$html .= JHtml::_('select.genericlist', $options, 'user_rating', null, 'value', 'text', '5', 'content_vote_' . $row->id);
				$html .= '&#160;<input class="btn btn-mini" type="submit" name="submit_vote" value="' . JText::_('PLG_VOTE_RATE') . '" />';
				$html .= '<input type="hidden" name="task" value="article.vote" />';
				$html .= '<input type="hidden" name="hitcount" value="0" />';
				$html .= '<input type="hidden" name="url" value="' . htmlspecialchars($uri->toString(), ENT_COMPAT, 'UTF-8') . '" />';
				$html .= JHtml::_('form.token');
				$html .= '</span>';
				$html .= '</form>';
			}
		}

		return $html;
	}

	/**
	 * Adds additional fields to supported forms
	 *
	 * @param   JForm  $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onContentPrepareForm($form, $data)
	{
		if (!($form instanceof JForm))
		{
			throw new RuntimeException(JText::_('JERROR_NOT_A_FORM'), 500);
		}

		// Each form has different handling procedures
		switch ($form->getName())
		{
			case 'com_config.component':
				// Component configuration, check via the request whether we're manipulating com_content
				if ($this->app->input->getCmd('component') !== 'com_content')
				{
					return true;
				}

				$this->loadLanguage();

				// Create a SimpleXMLElement for the component config's fieldset
				$element = new SimpleXMLElement(file_get_contents(__DIR__ . '/voting/component.xml'));

				// Add the field
				$form->setField($element);

				return true;

			case 'com_content.article':
				// Add the voting fields to the form.
				JForm::addFormPath(__DIR__ . '/voting');
				$form->loadFile('content', false);

				return true;

			case 'com_menus.item':
				// Here we'll have to switch based on the chosen view
				$xml = $form->getXml();

				switch (strtoupper($xml->metadata->layout->attributes()->option))
				{
					case 'COM_CONTENT_ARTICLE_VIEW_DEFAULT_OPTION':
						// Add the voting fields to the form.
						JForm::addFormPath(__DIR__ . '/voting');
						$form->loadFile('menu_single_article', false);

						return true;

					case 'COM_CONTENT_CATEGORIES_VIEW_DEFAULT_OPTION':
						// Add the voting fields to the form.
						JForm::addFormPath(__DIR__ . '/voting');
						$form->loadFile('menu_categories', false);

						return true;

					case 'COM_CONTENT_CATEGORY_VIEW_BLOG_OPTION':
						// Add the voting fields to the form.
						JForm::addFormPath(__DIR__ . '/voting');
						$form->loadFile('menu_category_blog', false);

						return true;

					case 'COM_CONTENT_CATEGORY_VIEW_DEFAULT_OPTION':
						// Add the voting fields to the form.
						JForm::addFormPath(__DIR__ . '/voting');
						$form->loadFile('menu_category_default', false);

						return true;

					case 'COM_CONTENT_FEATURED_VIEW_DEFAULT_OPTION':
						// Add the voting fields to the form.
						JForm::addFormPath(__DIR__ . '/voting');
						$form->loadFile('menu_featured_article', false);

						return true;

					default:
						// Unsupported option
						return true;
				}

			default:
				// Unsupported form
				return true;
		}
	}

	/**
	 * Save user vote on article
	 *
	 * @param   integer  $pk    Article ID
	 * @param   integer  $rate  Voting rate
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function storeVote($pk = 0, $rate = 0)
	{
		if ($rate >= 1 && $rate <= 5 && $pk > 0)
		{
			$userIP = $this->app->input->server->getString('REMOTE_ADDR');

			// Initialize variables.
			$db    = $this->db;
			$query = $db->getQuery(true);

			// Query for an existing rating for this item
			$query->select('*')
				->from($db->quoteName('#__content_rating'))
				->where($db->quoteName('content_id') . ' = ' . (int) $pk);

			// Set the query and load the result.
			$rating = $db->setQuery($query)->loadObject();

			// If there are no ratings yet, insert a new record
			if (!$rating)
			{
				$query = $db->getQuery(true);

				$columnList = [$db->quoteName('content_id'), $db->quoteName('lastip'), $db->quoteName('rating_sum'), $db->quoteName('rating_count')];
				$valueList  = [(int) $pk, $db->quote($userIP), (int) $rate, 1];

				$query->clear()
					->insert($db->quoteName('#__content_rating'))
					->columns($columnList)
					->values(implode(', ', $valueList));

				$db->setQuery($query)->execute();
			}
			// If there is already a rating, add the new vote unless the previous voter's IP address matches the current address
			elseif ($userIP != ($rating->lastip))
			{
				$query->clear()
					->update($db->quoteName('#__content_rating'))
					->set($db->quoteName('rating_count') . ' = rating_count + 1')
					->set($db->quoteName('rating_sum') . ' = rating_sum + ' . (int) $rate)
					->set($db->quoteName('lastip') . ' = ' . $db->quote($userIP))
					->where($db->quoteName('content_id') . ' = ' . (int) $pk);

				$db->setQuery($query)->execute();
			}
		}

		throw new RuntimeException(JText::sprintf('COM_CONTENT_INVALID_RATING', $rate), __METHOD__ . '()');
	}
}
