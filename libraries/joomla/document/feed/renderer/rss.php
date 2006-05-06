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

 /**
 * JFeedRSS is a feed that implements RSS 2.0 Specification
 *
 * @author	Johan Janssens <johan.janssens@joomla.org>
 *
 * @package 	Joomla.Framework
 * @subpackage 	Feed
 * @see http://www.rssboard.org/rss-specification
 * @since	1.5
 */

class JDocumentRenderer_RSS extends JDocumentRenderer
{
	var $namespaces;
	
	//$this->_mime    = "application/rss+xml";
	//$this->_charset = "utf-8";
	
	/**
	 * Render the feed
	 * 
	 * @access public
	 * @return	string
	 */
	function render(&$feed) 
	{
		$result = "<?xml version=\"1.0\" encoding=\"".$this->_charset."\"?>\n";
		$result.= $feed->_createGeneratorComment();
		$result.= $feed->_createStylesheetReferences();
		$result.= "<rss version=\"2.0\"".$this->_getNameSpaces().">\n"; 
		$result.= "	<channel>\n";
		$result.= "		<title>".$feed->title."</title>\n";
		$result.= "		<description>".$feed->description."</description>\n";
		$result.= "		<link>".$feed->link."</link>\n";
		$now = new JFeedDate();
		$result.= "		<lastBuildDate>".htmlspecialchars($now->rfc822())."</lastBuildDate>\n";
		$result.= "        <generator>".$feed->generator."</generator>\n";

		if ($feed->image!=null) {
			$result.= "		<image>\n";
			$result.= "			<url>".$feed->image->url."</url>\n";
			$result.= "			<title>".htmlspecialchars($feed->image->title)."</title>\n";
			$result.= "			<link>".$feed->image->link."</link>\n";
			if ($feed->image->width!="") {
				$result.= "			<width>".$feed->image->width."</width>\n";
			}
			if ($feed->image->height!="") {
				$result.= "			<height>".$feed->image->height."</height>\n";
			}
			if ($feed->image->description!="") {
				$result.= "			<description>".$feed->image->getDescription()."</description>\n";
			}
			$result.= "		</image>\n";
		}
		if ($feed->language!="") {
			$result.= "		<language>".$this->language."</language>\n";
		}
		if ($feed->copyright!="") {
			$result.= "		<copyright>".htmlspecialchars($feed->copyright)."</copyright>\n";
		}
		if ($feed->editor!="") {
			$result.= "		<managingEditor>".htmlspecialchars($feed->editor)."</managingEditor>\n";
		}
		if ($feed->webmaster!="") {
			$result.= "		<webMaster>".htmlspecialchars($feed->webmaster)."</webMaster>\n";
		}
		if ($feed->pubDate!="") {
			$pubDate = new JFeedDate($feed->pubDate);
			$result.= "		<pubDate>".htmlspecialchars($pubDate->rfc822())."</pubDate>\n";
		}
		if ($feed->category!="") {
			$result.= "		<category>".htmlspecialchars($feed->category)."</category>\n";
		}
		if ($feed->docs!="") {
			$result.= "		<docs>".htmlspecialchars($feed->docs)."</docs>\n";
		}
		if ($feed->ttl!="") {
			$result.= "		<ttl>".htmlspecialchars($feed->ttl)."</ttl>\n";
		}
		if ($feed->rating!="") {
			$result.= "        <rating>".htmlspecialchars($feed->rating)."</rating>\n";
		}
		if ($feed->skipHours!="") {
			$result.= "		<skipHours>".htmlspecialchars($feed->skipHours)."</skipHours>\n";
		}
		if ($feed->skipDays!="") {
			$result.= "		<skipDays>".htmlspecialchars($feed->skipDays)."</skipDays>\n";
		}
		$result.= $feed->_createAdditionalElements($feed->additionalElements, "	");
		$result.= $feed->additionalMarkup;

		for ($i=0;$i<count($feed->items);$i++) {
			$result.= "		<item>\n";
			$result.= "			<title>".htmlspecialchars(strip_tags($feed->items[$i]->title))."</title>\n";
			$result.= "			<link>".htmlspecialchars($feed->items[$i]->link)."</link>\n";
			$result.= "			<description>".$feed->items[$i]->description."</description>\n";

			if ($feed->items[$i]->author!="") {
				$result.= "			<author>".htmlspecialchars($feed->items[$i]->author)."</author>\n";
			}
			/*
			// on hold
			if ($this->items[$i]->source!="") {
					$feed.= "			<source>".htmlspecialchars($this->items[$i]->source)."</source>\n";
			}
			*/
			if ($feed->items[$i]->category!="") {
				$result.= "			<category>".htmlspecialchars($feed->items[$i]->category)."</category>\n";
			}
			if ($feed->items[$i]->comments!="") {
				$result.= "			<comments>".htmlspecialchars($feed->items[$i]->comments)."</comments>\n";
			}
			if ($feed->items[$i]->date!="") {
			$itemDate = new JFeedDate($feed->items[$i]->date);
				$result.= "			<pubDate>".htmlspecialchars($itemDate->rfc822())."</pubDate>\n";
			}
			if ($feed->items[$i]->guid!="") {
				$result.= "			<guid>".htmlspecialchars($feed->items[$i]->guid)."</guid>\n";
			}
			$result.= $feed->_createAdditionalElements($feed->items[$i]->additionalElements, "		");
			$result.= $feed->items[$i]->additionalMarkup;
			if ($feed->items[$i]->enclosure != NULL)
			{
					$result.= "            <enclosure url=\"";
				    $result.= $feed->items[$i]->enclosure->url;
				    $result.= "\" length=\"";
				    $result.= $feed->items[$i]->enclosure->length;
				    $result.= "\" type=\"";
				    $result.= $feed->items[$i]->enclosure->type;
				    $result.= "\"/>\n";
			}
            	
			$result.= "		</item>\n";
		}
		$result.= "	</channel>\n";
		$result.= "</rss>\n";
		return $result;
	}
	
	function _getNameSpaces() 
	{
		if (!is_array($this->namespaces)) return "";
		
		$output = "";
		foreach ($this->namespaces as $namespace=>$dtd) {
			$output .= " ".$namespace."=\"".$dtd."\"";
		}
		
		return $output;
	}
	
	function addNameSpace($namespace,$dtd) {
		$this->namespaces[$namespace] = $dtd;
	}
}
?>