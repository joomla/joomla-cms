<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.beez3
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$params =& $this->item->params;
$app = JFactory::getApplication();

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');

?>

<div class="items-more">
<h3><?php echo JText::_('COM_CONTENT_MORE_ARTICLES'); ?></h3>

<ol>

<?php
	foreach ($this->link_items as &$item) :
?>
		 <li>
		  		<a href="<?php echo JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid)); ?>">
			<?php echo $item->title; ?></a>
		</li>
<?php endforeach; ?>
	</ol>
</div>


