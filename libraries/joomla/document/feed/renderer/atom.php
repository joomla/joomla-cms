<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Document
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// No direct access
defined('JPATH_BASE') or die;



/**
 * JDocumentRenderer_Atom is a feed that implements the atom specification
 *
 * Please note that just by using this class you won't automatically
 * produce valid atom files. For example, you have to specify either an editor
 * for the feed or an author for every single feed item.
 *
 * @package 	Joomla.Framework
 * @subpackage	Document
 * @see http://www.atomenabled.org/developers/syndication/atom-format-spec.php
 * @since	1.5
 */

 class JDocumentRendererAtom extends JDocumentRenderer
 {
	/**
	 * Document mime type
	 *
	 * @var		string
	 * @access	private
	 */
	 var $_mime = "application/atom+xml";

	/**
	 * Render the feed
	 *
	 * @access public
	 * @return string
	 */
	function render()
	{
		$now	= &JFactory::getDate();
		$data	= &$this->_doc;

		$uri = &JFactory::getURI();
		$url = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
		$syndicationURL = &JRoute::_('&format=feed&type=atom');

		$feed = "<feed xmlns=\"http://www.w3.org/2005/Atom\" ";
		if ($data->language!="") {
			$feed.= " xml:lang=\"".$data->language."\"";
		}
		$feed.= ">\n";
		$feed.= "	<title type=\"text\">".htmlspecialchars($data->title, ENT_COMPAT, 'UTF-8')."</title>\n";
		$feed.= "	<subtitle type=\"text\">".htmlspecialchars($data->description, ENT_COMPAT, 'UTF-8')."</subtitle>\n";
		$feed.= "	<link rel=\"alternate\" type=\"text/html\" href=\"".$url."\"/>\n";
		$feed.= "	<id>".str_replace(' ','%20',$data->getBase())."</id>\n";
		$feed.= "	<updated>".htmlspecialchars($now->toISO8601(), ENT_COMPAT, 'UTF-8')."</updated>\n";
		if ($data->editor!="") {
			$feed.= "	<author>\n";
			$feed.= "		<name>".$data->editor."</name>\n";
			if ($data->editorEmail!="") {
				$feed.= "		<email>".htmlspecialchars($data->editorEmail, ENT_COMPAT, 'UTF-8')."</email>\n";
			}
			$feed.= "	</author>\n";
		}
		$feed.= "	<generator uri=\"http://joomla.org\" version=\"1.5\">".$data->getGenerator()."</generator>\n";
		$feed.= '<link rel="self" type="application/atom+xml" href="'.str_replace(' ','%20',$url.$syndicationURL). "\" />\n";

		for ($i=0;$i<count($data->items);$i++)
		{
			$feed.= "	<entry>\n";
			$feed.= "		<title>".htmlspecialchars(strip_tags($data->items[$i]->title), ENT_COMPAT, 'UTF-8')."</title>\n";
			$feed.= '		<link rel="alternate" type="text/html" href="'.$url.$data->items[$i]->link."\"/>\n";

			if ($data->items[$i]->date=="") {
				$data->items[$i]->date = $now->toUnix();
			}
			$itemDate = &JFactory::getDate($data->items[$i]->date);
			$feed.= "		<published>".htmlspecialchars($itemDate->toISO8601(), ENT_COMPAT, 'UTF-8')."</published>\n";
			$feed.= "		<updated>".htmlspecialchars($itemDate->toISO8601(),ENT_COMPAT, 'UTF-8')."</updated>\n";
			$feed.= "		<id>".str_replace(' ', '%20', $url.$data->items[$i]->link)."</id>\n";

			if ($data->items[$i]->author!="")
			{
				$feed.= "		<author>\n";
				$feed.= "			<name>".htmlspecialchars($data->items[$i]->author, ENT_COMPAT, 'UTF-8')."</name>\n";
				if ($data->items[$i]->authorEmail!="") {
					$feed.= "		<email>".htmlspecialchars($data->items[$i]->authorEmail, ENT_COMPAT, 'UTF-8')."</email>\n";
				}
				$feed.= "		</author>\n";
			}
			if ($data->items[$i]->description!="") {
				$feed.= "		<summary type=\"html\">".htmlspecialchars($data->items[$i]->description, ENT_COMPAT, 'UTF-8')."</summary>\n";
				$feed.= "		<content type=\"html\">".htmlspecialchars($data->items[$i]->description, ENT_COMPAT, 'UTF-8')."</content>\n";
			}
			if ($data->items[$i]->enclosure != NULL) {
			$feed.="		<link rel=\"enclosure\" href=\"". $data->items[$i]->enclosure->url ."\" type=\"". $data->items[$i]->enclosure->type."\"  length=\"". $data->items[$i]->enclosure->length . "\" />\n";
			}
			$feed.= "	</entry>\n";
		}
		$feed.= "</feed>\n";
		return $feed;
	}
}
