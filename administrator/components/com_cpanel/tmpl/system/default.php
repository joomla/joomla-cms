<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/** @var  \Joomla\Component\Cpanel\Administrator\View\System\HtmlView  $this */
?>

<div class="com-cpanel-system">
	<?php foreach ($this->links as $section) : ?>
	<div class="com-cpanel-system__category">
		<h2 class="com-cpanel-system__header">
			<span class="fa fa-<?php echo $section->getIcon(); ?>" aria-hidden="true"></span>
			<?php echo Text::_($section->getTitle()); ?>
		</h2>
		<ul class="list-group list-group-flush">
			<?php foreach ($section->getItems() as $item) : ?>
				<li class="list-group-item">
					<a href="<?php echo $item->getLink(); ?>"><?php echo Text::_($item->getTitle()); ?>
					<?php if (!empty($item->getBadge())) : ?>
						<span class="pull-right badge badge-pill badge-warning">
							<?php echo '&#x200E;' . Text::_($item->getBadge()); ?>
						</span>
					<?php endif; ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php endforeach; ?>
</div>


