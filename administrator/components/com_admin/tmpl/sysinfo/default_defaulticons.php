<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/** @var \Joomla\Component\Admin\Administrator\View\Sysinfo\HtmlView $this */

?>
<h1>
	<?php
	echo Text::_('COM_ADMIN_DEFAULT_ICONS'); ?>
</h1>

<div class="card-body">
	<nav class="quick-icons px-3 pb-3" aria-label="Quick Links System">
		<ul class="nav flex-wrap">

			<?php
			foreach ($this->defaulticons as $item): ?>

				<li class="quickicon quickicon-single">

					<div class="quickicon-info">
						<div class="quickicon-icon">
							<i style="font-size: 48px; color: Dodgerblue;" class="fa fa-<?php
							echo $item; ?>"></i>
						</div>
					</div>

					<div class="quickicon-name d-flex align-items-end">
						<?php
						echo $item; ?>                </div>

				</li>

			<?php
			endforeach; ?>

		</ul>
	</nav>
</div>

<pre>
<?php
foreach ($this->defaulticons as $item): ?>
	<div class="small-12 large-4 columns"><span class="fa fa-<?php echo $item; ?>">&nbsp;</span>&nbsp; <?php echo $item; ?></div>
<?php
endforeach;?>
</pre>
