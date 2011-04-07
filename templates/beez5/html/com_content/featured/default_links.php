<?php
/**
 * @version		$Id: default_links.php 17017 2010-05-13 10:48:48Z eddieajau $
 * @package		Joomla.Site
 * @subpackage	Templates.beez5
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
$app = JFactory::getApplication();
$templateparams =$app->getTemplate(true)->params;
if ($templateparams->get('html5')!=1)
{
	require(JPATH_BASE.'/components/com_content/views/featured/tmpl/default_links.php');
	//evtl. ersetzen durch JPATH_COMPONENT.'/views/...'
} else {
JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers');
?>

<h3><?php echo JText::_('COM_CONTENT_MORE_ARTICLES'); ?></h3>

<ol class="links">
<?php foreach ($this->link_items as &$item) : ?>
	<li>
		<a href="<?php echo JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug)); ?>">
			<?php echo $item->title; ?></a>
	</li>
<?php endforeach; ?>
</ol>
<?php } ?>