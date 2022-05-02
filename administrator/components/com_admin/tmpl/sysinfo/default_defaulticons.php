<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/** @var \Joomla\Component\Admin\Administrator\View\Sysinfo\HtmlView $this */

?>
<div class="card mb-3 ">
	<div class="card-header">
		<h2>
			<span class="icon-joomla" aria-hidden="true"></span>
			<?php echo Text::_('COM_ADMIN_AVAILABLE_ICONS'); ?>
		</h2>
	</div>
	<div class="card-body">
		<nav class="quick-icons px-3 pb-3">
			<ul class="nav flex-wrap">
				<li class="quickicon quickicon-single">
					<a href="#">
						<div class="quickicon-info">
							<div class="quickicon-icon">
								<span class="icon-joomla" style="font-size: 48px;" aria-hidden="true"></span>
							</div>
						</div>
						<div class="quickicon-name d-flex align-items-end">
							Joomla
						</div>
					</a>
				</li>

				<?php foreach ($this->defaulticons as $item): ?>
					<li class="quickicon quickicon-single">
						<a href="#">
							<div class="quickicon-info">
								<div class="quickicon-icon">
									<i style="font-size: 48px;" class="fa fa-<?php echo $item; ?>"></i>

								</div>
							</div>
							<div class="quickicon-name d-flex align-items-end">
								<?php echo $item; ?>
							</div>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>
	</div>
</div>
