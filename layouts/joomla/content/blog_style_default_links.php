<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;
?>
<ol class="nav nav-tabs nav-stacked">
<?php foreach ($displayData->get('link_items') as $item) : ?>
	<li>
		<?php echo JHtml::_('link', JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language)), $item->title); ?>
	</li>
<?php endforeach; ?>
</ol>
