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
 * JFeedAtom is a feed that implements the atom specification
 * 
 * Please note that just by using this class you won't automatically
 * produce valid atom files. For example, you have to specify either an editor
 * for the feed or an author for every single feed item.
 *
 * @author	Johan Janssens <johan.janssens@joomla.org>
 *
 * @package 	Joomla.Framework
 * @subpackage 	Feed
 * @see http://www.atomenabled.org/developers/syndication/atom-format-spec.php
 * @since	1.5
 */

 class JDocmuemnetRenderer_Atom extends JDocumentRenderer
 {
	//$this->contentType 	= "application/atom+xml";
	//$this->encoding 	= "utf-8";

	/**
	 * Render the feed
	 *
	 * @access public
	 * @return string
	 */
	function render(&$feed) 
	{
		$feed = "<?xml version=\"1.0\" encoding=\"".$this->encoding."\"?>\n";
		$feed.= $this->_createGeneratorComment();
		$feed.= $this->_createStylesheetReferences();
		$feed.= "<feed xmlns=\"http://www.w3.org/2005/Atom\"";
		if ($this->language!="") {
			$feed.= " xml:lang=\"".$this->language."\"";
		}
		$feed.= ">\n"; 
		$feed.= "    <title>".htmlspecialchars($this->title)."</title>\n";
		$feed.= "    <subtitle>".htmlspecialchars($this->description)."</subtitle>\n";
		$feed.= "    <link rel=\"alternate\" type=\"text/html\" href=\"".htmlspecialchars($this->link)."\"/>\n";
		$feed.= "    <id>".htmlspecialchars($this->link)."</id>\n";
		$now = new JFeedDate();
		$feed.= "    <updated>".htmlspecialchars($now->iso8601())."</updated>\n";
		if ($this->editor!="") {
			$feed.= "    <author>\n";
			$feed.= "        <name>".$this->editor."</name>\n";
			if ($this->editorEmail!="") {
				$feed.= "        <email>".$this->editorEmail."</email>\n";
			}
			$feed.= "    </author>\n";
		}
		$feed.= "    <generator>".FEEDCREATOR_VERSION."</generator>\n";
		$feed.= "<link rel=\"self\" type=\"application/atom+xml\" href=\"". $this->syndicationURL . "\" />\n";
		$feed.= $this->_createAdditionalElements($this->additionalElements, "    ");
		for ($i=0;$i<count($this->items);$i++) {
			$feed.= "    <entry>\n";
			$feed.= "        <title>".htmlspecialchars(strip_tags($this->items[$i]->title))."</title>\n";
			$feed.= "        <link rel=\"alternate\" type=\"text/html\" href=\"".htmlspecialchars($this->items[$i]->link)."\"/>\n";
			if ($this->items[$i]->date=="") {
				$this->items[$i]->date = time();
			}
			$itemDate = new JFeedDate($this->items[$i]->date);
			$feed.= "        <published>".htmlspecialchars($itemDate->iso8601())."</published>\n";
			$feed.= "        <updated>".htmlspecialchars($itemDate->iso8601())."</updated>\n";
			$feed.= "        <id>".htmlspecialchars($this->items[$i]->link)."</id>\n";
			$feed.= $this->_createAdditionalElements($this->items[$i]->additionalElements, "        ");
			if ($this->items[$i]->author!="") {
				$feed.= "        <author>\n";
				$feed.= "            <name>".htmlspecialchars($this->items[$i]->author)."</name>\n";
				$feed.= "        </author>\n";
			}
			if ($this->items[$i]->description!="") {
				$feed.= "        <summary>".htmlspecialchars($this->items[$i]->description)."</summary>\n";
			}
			if ($this->items[$i]->enclosure != NULL) {
			$feed.="        <link rel=\"enclosure\" href=\"". $this->items[$i]->enclosure->url ."\" type=\"". $this->items[$i]->enclosure->type."\"  length=\"". $this->items[$i]->enclosure->length . "\" />\n";
			}
			$feed.= "    </entry>\n";
		}
		$feed.= "</feed>\n";
		return $feed;
	}

	
}
?>