<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
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
	public function __construct(&$subject, $options = array()) {
		parent::__construct($subject, $options);
		$this->loadLanguage();
	}

	public function onBeforeDisplayContent(&$row, &$params, $page=0)
	{
		$uri = &JFactory::getURI();

		$id = $row->id;
		$html = '';

		if (isset($row->rating_count) && $params->get('show_vote') && !$params->get('popup'))
		{
			$html .= '<form method="post" action="' . $uri->toString() . '">';
			$img = '';

			// Look for images in template if available.
			$starImageOn = JHtml::_('image.site', 'rating_star.png', '/images/M_images/');
			$starImageOff = JHtml::_('image.site', 'rating_star_blank.png', '/images/M_images/');
			for ($i=0; $i < $row->rating; $i++) {
				$img .= $starImageOn;
			}
			for ($i=$row->rating; $i < 5; $i++) {
				$img .= $starImageOff;
			}
			$html .= '<span class="content_rating">';
			$html .= JText::_('User Rating') .':'. $img .'&nbsp;/&nbsp;';
			$html .= intval($row->rating_count);
			$html .= "</span>\n<br />\n";

			if (!$params->get('intro_only'))
			{
				$html .= '<span class="content_vote">';
				$html .= JText::_('Poor');
				$html .= '<input type="radio" alt="vote 1 star" name="user_rating" value="1" />';
				$html .= '<input type="radio" alt="vote 2 star" name="user_rating" value="2" />';
				$html .= '<input type="radio" alt="vote 3 star" name="user_rating" value="3" />';
				$html .= '<input type="radio" alt="vote 4 star" name="user_rating" value="4" />';
				$html .= '<input type="radio" alt="vote 5 star" name="user_rating" value="5" checked="checked" />';
				$html .= JText::_('Best');
				$html .= '&nbsp;<input class="button" type="submit" name="submit_vote" value="'. JText::_('Rate') .'" />';
				$html .= '<input type="hidden" name="task" value="vote" />';
				$html .= '<input type="hidden" name="option" value="com_content" />';
				$html .= '<input type="hidden" name="cid" value="'. $id .'" />';
				$html .= '<input type="hidden" name="url" value="'.  $uri->toString() .'" />';
				$html .= '</span>';
			}
			$html .= '</form>';
		}
		return $html;
	}
}