<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Application\ApplicationHelper;

$plugin = PluginHelper::getPlugin('installer', 'override');
$params = new Registry($plugin->params);
$result = json_decode($params->get('overridefiles'), JSON_HEX_QUOT);
?>

<div class="row">
	<div class="col-md-12">
		<table class="table table-striped">
			<thead>
				<tr>
					<th style="width:25%">
						<?php echo Text::_('COM_TEMPLATES_OVERRIDE_TEMPLATE_FILE'); ?>
					</th>
					<th>
						<?php echo Text::_('COM_TEMPLATES_OVERRIDE_MODIFIED_DATE'); ?>
					</th>
					<th>
						<?php echo Text::_('COM_TEMPLATES_OVERRIDE_ACTION'); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php $flag = 0; ?>
				<?php foreach ($result as $values) : ?>
					<?php foreach ($values as $value) : ?>
						<?php $client = ApplicationHelper::getClientInfo($value['client']); ?>
						<?php $path = $client->path . '/templates/' . $value['template'] . base64_decode($value['id']); ?>
						<?php if (file_exists($path) && $this->template->extension_id === $value['extension_id']) : ?>
							<?php $flag = 1; ?>
							<tr>
								<td>
									<a class="hasTooltip" href="<?php echo Route::_('index.php?option=com_templates&view=template&id=' . (int) $value['extension_id'] . '&file=' . $value['id']); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?>"><?php echo base64_decode($value['id']); ?></a>
								</td>
								<td>
									<?php if (empty($value['modifiedDate'])) : ?>
										<span class="badge badge-warning"><?php echo Text::_('COM_TEMPLATES_OVERRIDE_CORE_REMOVED'); ?></span>
									<?php else : ?>
										<?php echo $value['modifiedDate']; ?>
									<?php endif; ?>
								</td>
								<td>
									<span class="badge badge-info"><?php echo $value['action']; ?></span>
								</td>
							</tr>
						<?php endif; ?>
					<?php endforeach; ?>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php if(!$flag || count($result) === 0) : ?>
			<joomla-alert type="success" role="alert" class="joomla-alert--show">
				<span class="icon-info" aria-hidden="true"></span>
				<?php echo Text::sprintf('COM_TEMPLATES_OVERRIDE_UPTODATE', $params->get('numupdate')); ?>
			</joomla-alert>
		<?php endif; ?>
	</div>
</div>
