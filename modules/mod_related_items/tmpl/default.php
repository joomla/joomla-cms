<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_related_items
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<ul class="relateditems<?php echo $moduleclass_sfx; ?> mod-list">
<?php foreach ($list as $item) : ?>
<li>
	<a href="<?php echo $item->route; ?>">
		<?php if ($showDate) echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC4')) . ' - '; ?>
		<?php echo $item->title; ?></a>
</li>
<?php endforeach; ?>
</ul>
