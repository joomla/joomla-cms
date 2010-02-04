<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	Contact
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<?php if ($this->params->get('show_articles')) : ?>
<div class="jcontact-articles">
	<h4>
		<?php echo JText::_('Com_Contact_Contact_Articles_Heading'); ?>
	</h4>
	<ol>
		<?php foreach ($this->contact->articles as $article) :	?>
			<li>
				<a href="<?php $article->link = JRoute::_('index.php?option=com_content&view=article&id='.$article->id)?>">
				<?php echo $article->text = htmlspecialchars($article->title, ENT_COMPAT, 'UTF-8'); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ol>
</div>
<?php endif; ?>