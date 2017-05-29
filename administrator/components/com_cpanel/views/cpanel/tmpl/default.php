<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cpanel
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

$user = JFactory::getUser();
?>
<div class="row-fluid">
	<?php $iconmodules = JModuleHelper::getModules('icon');
	if ($iconmodules) : ?>
		<div class="span3">
			<div class="cpanel-links">
				<?php
				// Display the submenu position modules
				foreach ($iconmodules as $iconmodule)
				{
					echo JModuleHelper::renderModule($iconmodule);
				}
				?>
			</div>
		</div>
	<?php endif; ?>
	<div class="span<?php echo ($iconmodules) ? 9 : 12; ?>">
		<?php if ($user->authorise('core.manage', 'com_postinstall') && $this->postinstall_message_count) : ?>
			<div class="row-fluid">
				<div class="alert alert-info">
					<h4>
						<?php echo JText::_('COM_CPANEL_MESSAGES_TITLE'); ?>
					</h4>
					<p>
						<?php echo JText::_('COM_CPANEL_MESSAGES_BODY_NOCLOSE'); ?>
					</p>
					<p>
						<?php echo JText::_('COM_CPANEL_MESSAGES_BODYMORE_NOCLOSE'); ?>
					</p>
					<p>
						<a href="index.php?option=com_postinstall&amp;eid=700" class="btn btn-primary">
							<?php echo JText::_('COM_CPANEL_MESSAGES_REVIEW'); ?>
						</a>
					</p>
				</div>
			</div>
		<?php endif; ?>
		<div class="row-fluid">
			<?php
			$spans = 0;

			foreach ($this->modules as $module)
			{
				// Get module parameters
				$params = new Registry($module->params);
				$bootstrapSize = $params->get('bootstrap_size');
				if (!$bootstrapSize)
				{
					$bootstrapSize = 12;
				}
				$spans += $bootstrapSize;
				if ($spans > 12)
				{
					echo '</div><div class="row-fluid">';
					$spans = $bootstrapSize;
				}
				echo JModuleHelper::renderModule($module, array('style' => 'well'));
			}
			?>
		</div>
	</div>
</div>
