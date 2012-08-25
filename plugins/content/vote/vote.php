<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.vote
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
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
class plgContentVote extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @access      protected
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 * @since       1.5
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	/**
	* @since	1.6
	*/
	public function onContentBeforeDisplay($context, &$row, &$params, $page=0)
	{
		$html = '';

		if ($params->get('show_vote'))
		{
			$rating = (int) @$row->rating;
			$rating_count = (int) @$row->rating_count;

			$view = JFactory::getApplication()->input->getString('view', '');
			$img = '';

			// look for images in template if available
			$starImageOn = JHtml::_('image', 'system/rating_star.png', null, null, true);
			$starImageOff = JHtml::_('image', 'system/rating_star_blank.png', null, null, true);

			for ($i = 0; $i < $rating; $i++)
			{
				$img .= $starImageOn;
			}
			for ($i = $rating; $i < 5; $i++)
			{
				$img .= $starImageOff;
			}
			$html .= '<span class="content_rating">';
			$html .= JText::sprintf($img);

			if ($view == 'article' && $row->state == 1)
			{
				$uri = JURI::getInstance();
				$uri->setQuery($uri->getQuery().'&hitcount=0');

				$html .= '<form method="post" action="' . $uri->toString() . '">';
				$html .= '<span class="content_vote">';
//				$html .= JText::_( 'PLG_VOTE_POOR' );
				$html .= ' <input type="radio" title="'.JText::sprintf('PLG_VOTE_VOTE', '1').'" name="user_rating" value="1" /> ';
				$html .= '<input type="radio" title="'.JText::sprintf('PLG_VOTE_VOTE', '2').'" name="user_rating" value="2" /> ';
				$html .= '<input type="radio" title="'.JText::sprintf('PLG_VOTE_VOTE', '3').'" name="user_rating" value="3" /> ';
				$html .= '<input type="radio" title="'.JText::sprintf('PLG_VOTE_VOTE', '4').'" name="user_rating" value="4" /> ';
				$html .= '<input type="radio" title="'.JText::sprintf('PLG_VOTE_VOTE', '5').'" name="user_rating" value="5" checked="checked" /> ';
//				$html .= JText::_( 'PLG_VOTE_BEST' );
				$html .= '&#160;<input class="btn btn-mini" type="submit" name="submit_vote" value="' . JText::_('PLG_VOTE_RATE') . '" />';
				$html .= '<input type="hidden" name="task" value="article.vote" />';
				$html .= '<input type="hidden" name="hitcount" value="0" />';
				$html .= '<input type="hidden" name="url" value="'.  $uri->toString() .'" />';
				$html .= JHtml::_('form.token');
				$html .= '</span>';
				$html .= '</form>';
			}
		}

		return $html;
	}
}
