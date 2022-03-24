<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<ol class="nav nav-tabs nav-stacked">
<?php foreach ($displayData->get('link_items') as $item) : ?>
	<li>
		<?php echo JHtml::_('link', JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language)), $item->title); ?>
	</li>
<?php endforeach; ?>
</ol>
