<?php

/**
 * @version		
 * @package		Joomla.Site
 * @subpackage	Contact
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<?php

if ($this->contact->params->get('show_articles')) :
	echo '<h3>';
	echo JText::_('Com_Contact_Contact_Articles_Heading') ; 
	echo '</h3>';
	echo '<ol>';
		foreach ($this->contact->articles as $article):	
			{
				$articlelink=
				'<li><a href="'
				. $article->link = JRoute::_('index.php?option=com_content&view=article&id='.$article->id)
				. '">'
				. $article->text = htmlspecialchars($article->title)
				. '</a></li>';
				echo $articlelink;
			};
		endforeach;
	echo '</ol>';	
endif;	
?>	
