<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

/**
 * Vote plugin.
 *
 * @package		Joomla
 * @subpackage	plg_vote
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
			$rating = intval(@$row->rating);
			$rating_count = intval(@$row->rating_count);

			$view = JRequest::getString('view', '');
			$img = '';

			// look for images in template if available
			$starImageOn = JHTML::_('image','system/rating_star.png', NULL, NULL, true);
			$starImageOff = JHTML::_('image','system/rating_star_blank.png', NULL, NULL, true);

			for ($i=0; $i < $rating; $i++) {
				$img .= $starImageOn;
			}
			for ($i=$rating; $i < 5; $i++) {
				$img .= $starImageOff;
			}
			$html .= '<span class="content_rating">';
			$html .= JText::_( 'PLG_VOTE_USER_RATING' ) .':'. $img .'&#160;/&#160;';
			$html .= $rating_count;
			$html .= "</span>\n<br />\n";

			if ( $view == 'article' && $row->state == 1)
			{
				$uri = &JFactory::getURI();
				$uri->setQuery($uri->getQuery().'&hitcount=0');

				$html .= '<form method="post" action="' . $uri->toString() . '">';
				$html .= '<span class="content_vote">';
				$html .= JText::_( 'PLG_VOTE_POOR' );
				$html .= '<input type="radio" alt="vote 1 star" name="user_rating" value="1" />';
				$html .= '<input type="radio" alt="vote 2 star" name="user_rating" value="2" />';
				$html .= '<input type="radio" alt="vote 3 star" name="user_rating" value="3" />';
				$html .= '<input type="radio" alt="vote 4 star" name="user_rating" value="4" />';
				$html .= '<input type="radio" alt="vote 5 star" name="user_rating" value="5" checked="checked" />';
				$html .= JText::_( 'PLG_VOTE_BEST' );
				$html .= '&#160;<input class="button" type="submit" name="submit_vote" value="'. JText::_( 'PLG_VOTE_RATE' ) .'" />';
				$html .= '<input type="hidden" name="task" value="vote" />';
				$html .= '<input type="hidden" name="hitcount" value="0" />';
				$html .= '<input type="hidden" name="url" value="'.  $uri->toString() .'" />';
				$html .= '</span>';
				$html .= '</form>';
			}
		}

		return $html;
	}
}
