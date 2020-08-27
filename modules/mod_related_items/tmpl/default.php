<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_related_items
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>
<ul class="mod-relateditems relateditems mod-list">
<?php foreach ($list as $item) : ?>
<li>
	<a href="<?php echo $item->route; ?>">
		<?php if ($showDate) echo HTMLHelper::_('date', $item->created, Text::_('DATE_FORMAT_LC4')) . ' - '; ?>
		<?php echo $item->title; ?></a>
</li>
<?php endforeach; ?>
</ul>
