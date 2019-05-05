<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

?>

<div class="com-cpanel-help">
	<div class="mb-3 col-lg-3 col-md-4 col-12">
		<div class="card">
			<h2 class="com-cpanel-help__header card-header">
				<span class="fa fa-info-circle" aria-hidden="true"></span>
				<?php echo Text::_('MOD_MENU_HELP'); ?>
			</h2>
			<ul class="list-group list-group-flush">
				<?php foreach ($this->links as $link) : ?>
					<?php if ($link['link']) : ?>
						<li class="list-group-item">
							<span class="item-title"><a href="<?php echo $link['link']; ?>"><?php echo Text::_($link['label']); ?></a></span>
						</li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</div>
