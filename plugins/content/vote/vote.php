<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.vote
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Vote plugin.
 *
 * @package     Joomla.Plugin
 * @subpackage  Content.vote
 * @since       1.5
 */
class PlgContentVote extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Displays the voting area if in an article
	 *
	 * @param   string   $context  The context of the content being passed to the plugin
	 * @param   object   &$row     The article object
	 * @param   object   &$params  The article params
	 * @param   integer  $page     The 'page' number
	 *
	 * @return  string  html string containing code for the votes
	 *
	 * @since   1.6
	 */
	public function onContentBeforeDisplay($context, &$row, &$params, $page=0)
	{
		$html = '';

		if (!empty($params) && $params->get('show_vote', null))
		{
			$rating = (int) @$row->rating;

			$view = JFactory::getApplication()->input->getString('view', '');
			$img = '';

			// Look for images in template if available
			$starImageOn = JHtml::_('image', 'system/rating_star.png', JText::_('PLG_VOTE_STAR_ACTIVE'), null, true);
			$starImageOff = JHtml::_('image', 'system/rating_star_blank.png', JText::_('PLG_VOTE_STAR_INACTIVE'), null, true);

			for ($i = 0; $i < $rating; $i++)
			{
				$img .= $starImageOn;
			}

			for ($i = $rating; $i < 5; $i++)
			{
				$img .= $starImageOff;
			}

			$html .= '<div class="content_rating">';
			$html .= '<p class="unseen element-invisible">' . JText::sprintf('PLG_VOTE_USER_RATING', $rating, '5') . '</p>';
			$html .= $img;
			$html .= '</div>';

			if ($view == 'article' && $row->state == 1)
			{
				$uri = JUri::getInstance();
				$uri->setQuery($uri->getQuery() . '&hitcount=0');

				// Create option list for voting select box
				$options = array();

				for ($i = 1; $i < 6; $i++)
				{
					$options[] = JHTML::_('select.option', $i, JText::sprintf('PLG_VOTE_VOTE', $i));
				}

				// Generate voting form
				$html .= '<form method="post" action="' . htmlspecialchars($uri->toString()) . '" class="form-inline">';
				$html .= '<span class="content_vote">';
				$html .= '<label class="unseen element-invisible" for="content_vote_' . $row->id . '">' . JText::_('PLG_VOTE_LABEL') . '</label>';
				$html .= JHTML::_('select.genericlist', $options, 'user_rating', null, 'value', 'text', '5', 'content_vote_' . $row->id);
				$html .= '&#160;<input class="btn btn-mini" type="submit" name="submit_vote" value="' . JText::_('PLG_VOTE_RATE') . '" />';
				$html .= '<input type="hidden" name="task" value="article.vote" />';
				$html .= '<input type="hidden" name="hitcount" value="0" />';
				$html .= '<input type="hidden" name="url" value="' . htmlspecialchars($uri->toString()) . '" />';
				$html .= JHtml::_('form.token');
				$html .= '</span>';
				$html .= '</form>';
			}
		}

		return $html;
	}
}
