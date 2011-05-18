<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * JDocumentRenderer_Atom is a feed that implements the atom specification
 *
 * Please note that just by using this class you won't automatically
 * produce valid atom files. For example, you have to specify either an editor
 * for the feed or an author for every single feed item.
 *
 * @package     Joomla.Platform
 * @subpackage  Document
 * @see         http://www.atomenabled.org/developers/syndication/atom-format-spec.php
 * @since       11.1
 */

 class JDocumentRendererAtom extends JDocumentRenderer
 {
	/**
	 * Document mime type
	 *
	 * @var    string
	 */
	protected $_mime = "application/atom+xml";

	/**
	 * Render the feed
	 *
	 * @return  string
	 */
	public function render()
	{
		$app	= JFactory::getApplication();
		$now	= JFactory::getDate();
		$data	= &$this->_doc;

		$uri = JFactory::getURI();
		$url = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
		$syndicationURL = JRoute::_('&format=feed&type=atom');

		if ($app->getCfg('sitename_pagetitles', 0) == 1) {
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $data->title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
			$title = JText::sprintf('JPAGETITLE', $data->title, $app->getCfg('sitename'));
		}
		else {
			$title = $data->title;
		}
		
		$feed_title = htmlspecialchars($title, ENT_COMPAT, 'UTF-8');

		$feed = "<feed xmlns=\"http://www.w3.org/2005/Atom\" ";
		if ($data->language!="") {
			$feed.= " xml:lang=\"".$data->language."\"";
		}
		$feed.= ">\n";
		$feed.= "	<title type=\"text\">".$feed_title."</title>\n";
		$feed.= "	<subtitle type=\"text\">".htmlspecialchars($data->description, ENT_COMPAT, 'UTF-8')."</subtitle>\n";
		if (empty($data->category) === false) {
			if (is_array($data->category)) {
				foreach ($data->category as $cat) {
					$feed.= "	<category term=\"".htmlspecialchars($cat, ENT_COMPAT, 'UTF-8')."\" />\n";
				}
			}
			else {
				$feed.= "	<category term=\"".htmlspecialchars($data->category, ENT_COMPAT, 'UTF-8')."\" />\n";
			}
		}
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
		$feed.= "	<generator uri=\"http://joomla.org\" version=\"1.6\">".$data->getGenerator()."</generator>\n";
		$feed.= '	<link rel="self" type="application/atom+xml" href="'.str_replace(' ','%20',$url.$syndicationURL)."\"/>\n";

		for ($i = 0, $count = count($data->items); $i < $count; $i++)
		{
			$feed.= "	<entry>\n";
			$feed.= "		<title>".htmlspecialchars(strip_tags($data->items[$i]->title), ENT_COMPAT, 'UTF-8')."</title>\n";
			$feed.= '		<link rel="alternate" type="text/html" href="'.$url.$data->items[$i]->link."\"/>\n";

			if ($data->items[$i]->date=="") {
				$data->items[$i]->date = $now->toUnix();
			}
			$itemDate = JFactory::getDate($data->items[$i]->date);
			$feed.= "		<published>".htmlspecialchars($itemDate->toISO8601(), ENT_COMPAT, 'UTF-8')."</published>\n";
			$feed.= "		<updated>".htmlspecialchars($itemDate->toISO8601(),ENT_COMPAT, 'UTF-8')."</updated>\n";
			if (empty($data->items[$i]->guid) === true) {
				$feed.= "		<id>".str_replace(' ', '%20', $url.$data->items[$i]->link)."</id>\n";
			}
			else {
				$feed.= "		<id>".htmlspecialchars($data->items[$i]->guid, ENT_COMPAT, 'UTF-8')."</id>\n";
			}

			if ($data->items[$i]->author!="")
			{
				$feed.= "		<author>\n";
				$feed.= "			<name>".htmlspecialchars($data->items[$i]->author, ENT_COMPAT, 'UTF-8')."</name>\n";
				if ($data->items[$i]->authorEmail!="") {
					$feed.= "			<email>".htmlspecialchars($data->items[$i]->authorEmail, ENT_COMPAT, 'UTF-8')."</email>\n";
				}
				$feed.= "		</author>\n";
			}
			if ($data->items[$i]->description!="") {
				$feed.= "		<summary type=\"html\">".htmlspecialchars($data->items[$i]->description, ENT_COMPAT, 'UTF-8')."</summary>\n";
				$feed.= "		<content type=\"html\">".htmlspecialchars($data->items[$i]->description, ENT_COMPAT, 'UTF-8')."</content>\n";
			}
			if (empty($data->items[$i]->category) === false) {
				if (is_array($data->items[$i]->category)) {
					foreach ($data->items[$i]->category as $cat) {
						$feed.= "		<category term=\"".htmlspecialchars($cat, ENT_COMPAT, 'UTF-8')."\" />\n";
					}
				}
				else {
					$feed.= "		<category term=\"".htmlspecialchars($data->items[$i]->category, ENT_COMPAT, 'UTF-8')."\" />\n";
				}
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
