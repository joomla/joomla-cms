<?php
/**
 * @version $Id: $
 * @package Joomla.Framework
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

 jimport( 'joomla.utilities.date' );
 
 /**
 * JFeedRSS is a feed that implements RSS 2.0 Specification
 *
 * @author	Johan Janssens <johan.janssens@joomla.org>
 *
 * @package 	Joomla.Framework
 * @subpackage 	Document
 * @see http://www.rssboard.org/rss-specification
 * @since	1.5
 */

class JDocumentRenderer_RSS extends JDocumentRenderer
{
	//$this->_mime    = "application/rss+xml";
	
	/**
	 * Render the feed
	 * 
	 * @access public
	 * @return	string
	 */
	function render() 
	{
		$now  = new JDate();
		$data =& $this->_doc;
		
		$feed = "<rss version=\"2.0\">\n"; 
		$feed.= "	<channel>\n";
		$feed.= "		<title>".$data->title."</title>\n";
		$feed.= "		<description>".$data->description."</description>\n";
		$feed.= "		<link>".$data->link."</link>\n";
		$feed.= "		<lastBuildDate>".htmlspecialchars($now->toRFC822())."</lastBuildDate>\n";
		$feed.= "        <generator>".$data->getGenerator()."</generator>\n";

		if ($data->image!=null) 
		{
			$re.= "		<image>\n";
			$feed.= "			<url>".$data->image->url."</url>\n";
			$feed.= "			<title>".htmlspecialchars($data->image->title)."</title>\n";
			$feed.= "			<link>".$data->image->link."</link>\n";
			if ($data->image->width != "") {
				$feed.= "			<width>".$data->image->width."</width>\n";
			}
			if ($data->image->height!="") {
				$feed.= "			<height>".$data->image->height."</height>\n";
			}
			if ($data->image->description!="") {
				$feed.= "			<description>".$data->image->description."</description>\n";
			}
			$feed.= "		</image>\n";
		}
		if ($data->language!="") {
			$feed.= "		<language>".$data->language."</language>\n";
		}
		if ($data->copyright!="") {
			$feed.= "		<copyright>".htmlspecialchars($data->copyright)."</copyright>\n";
		}
		if ($data->editor!="") {
			$feed.= "		<managingEditor>".htmlspecialchars($data->editor)."</managingEditor>\n";
		}
		if ($data->webmaster!="") {
			$feed.= "		<webMaster>".htmlspecialchars($data->webmaster)."</webMaster>\n";
		}
		if ($data->pubDate!="") {
			$pubDate = new JDate($data->pubDate);
			$feed.= "		<pubDate>".htmlspecialchars($pubDate->toRFC822())."</pubDate>\n";
		}
		if ($data->category!="") {
			$feed.= "		<category>".htmlspecialchars($data->category)."</category>\n";
		}
		if ($data->docs!="") {
			$feed.= "		<docs>".htmlspecialchars($data->docs)."</docs>\n";
		}
		if ($data->ttl!="") {
			$feed.= "		<ttl>".htmlspecialchars($data->ttl)."</ttl>\n";
		}
		if ($data->rating!="") {
			$feed.= "        <rating>".htmlspecialchars($data->rating)."</rating>\n";
		}
		if ($data->skipHours!="") {
			$feed.= "		<skipHours>".htmlspecialchars($data->skipHours)."</skipHours>\n";
		}
		if ($data->skipDays!="") {
			$feed.= "		<skipDays>".htmlspecialchars($data->skipDays)."</skipDays>\n";
		}

		for ($i=0; $i<count($data->items); $i++) 
		{
			$feed.= "		<item>\n";
			$feed.= "			<title>".htmlspecialchars(strip_tags($data->items[$i]->title))."</title>\n";
			$feed.= "			<link>".htmlspecialchars($data->items[$i]->link)."</link>\n";
			$feed.= "			<description>".$data->items[$i]->description."</description>\n";

			if ($data->items[$i]->author!="") {
				$feed.= "			<author>".htmlspecialchars($data->items[$i]->author)."</author>\n";
			}
			/*
			// on hold
			if ($data->items[$i]->source!="") {
					$data.= "			<source>".htmlspecialchars($data->items[$i]->source)."</source>\n";
			}
			*/
			if ($data->items[$i]->category!="") {
				$feed.= "			<category>".htmlspecialchars($data->items[$i]->category)."</category>\n";
			}
			if ($data->items[$i]->comments!="") {
				$feed.= "			<comments>".htmlspecialchars($data->items[$i]->comments)."</comments>\n";
			}
			if ($data->items[$i]->date!="") {
			$itemDate = new JDate($data->items[$i]->date);
				$feed.= "			<pubDate>".htmlspecialchars($itemDate->toRFC822())."</pubDate>\n";
			}
			if ($data->items[$i]->guid!="") {
				$feed.= "			<guid>".htmlspecialchars($data->items[$i]->guid)."</guid>\n";
			}
			if ($data->items[$i]->enclosure != NULL)
			{
					$feed.= "            <enclosure url=\"";
				    $feed.= $data->items[$i]->enclosure->url;
				    $feed.= "\" length=\"";
				    $feed.= $data->items[$i]->enclosure->length;
				    $feed.= "\" type=\"";
				    $feed.= $data->items[$i]->enclosure->type;
				    $feed.= "\"/>\n";
			}
            	
			$feed.= "		</item>\n";
		}
		$feed.= "	</channel>\n";
		$feed.= "</rss>\n";
		return $feed;
	}
}
?>