<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
?>

<div class="com-cpanel-help">
	<h4 class="com-cpanel-help__header"><?php echo Text::_('MOD_MENU_HELP'); ?></h4>
	<ul class="list-group list-group-flush">
		<?php foreach ($this->links as $link) : ?>
			<li class="list-group-item">
				<span class="item-title"><a href="<?php echo $link['link']; ?>"><?php echo Text::_($link['label']); ?></a></span>
			</li>
		<?php endforeach; ?>
	</ul>
</div>