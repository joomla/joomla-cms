<?php
/**
* version $Id$
* @package Joomla
* @subpackage Newsfeeds
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
*
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Newsfeeds component
 *
 * @static
 * @package Joomla
 * @subpackage Newsfeeds
 * @since 1.0
 */
class NewsfeedsViewCategories extends JView
{
	function display($tpl = null)
	{
		global $Itemid;

		for($i = 0; $i < count($this->categories); $i++)
		{
			$category =& $this->categories[$i];
			$category->link = sefRelToAbs('index.php?option=com_newsfeeds&amp;task=category&amp;catid='. $category->catid .'&amp;Itemid='. $Itemid);
		}

		// Define image tag attributes
		if ($this->params->get('image') != -1)
		{
			$attribs['align'] = '"'. $this->params->get('image_align').'"';
			$attribs['hspace'] = '"6"';

			// Use the static HTML library to build the image tag
			$this->data->image = JHTML::Image('/images/stories/'.$this->params->get('image'), JText::_('NEWS_FEEDS'), $attribs);
		}

		parent::display($tpl);
	}
}
?>