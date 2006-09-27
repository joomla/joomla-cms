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
class NewsfeedsViewCategory extends JView
{
	function display($tpl = null)
	{
		global $Itemid;
		
		$catid = $this->catid;

		//create pagination
		jimport('joomla.html.pagination');
		$this->pagination = new JPagination($this->total, $this->limitstart, $this->limit);

		$k = 0;
		for($i = 0; $i <  count($this->items); $i++)
		{
			$item =& $this->items[$i];

			$item->link =  sefRelToAbs('index.php?option=com_newsfeeds&amp;task=view&amp;feedid='. $item->id .'&amp;Itemid='. $Itemid);

			$item->odd   = $k;
			$item->count = $i;
			$k = 1 - $k;
		}

		// Define image tag attributes
		if (isset ($this->category->image))
		{
			$attribs['align'] = '"'.$this->category->image_position.'"';
			$attribs['hspace'] = '"6"';

			// Use the static HTML library to build the image tag
			$this->image = mosHTML::Image('/images/stories/'.$this->category->image, JText::_('News Feeds'), $attribs);
		}
		
		parent::display($tpl);
	}
}
?>