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
<div class="sysinfo">
	<table class="table">
		<caption class="visually-hidden">
			<?php echo Text::_('COM_ADMIN_CONFIGURATION_FILE'); ?>
		</caption>
		<thead>
			<tr>
				<th scope="col" class="w-30">
					<?php echo Text::_('COM_ADMIN_SETTING'); ?>
				</th>
				<th scope="col">
					<?php echo Text::_('COM_ADMIN_VALUE'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($this->config as $key => $value) : ?>
				<tr>
					<th scope="row">
						<?php echo $key; ?>
					</th>
					<td>
						<?php echo HTMLHelper::_('configuration.value', $value); ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
