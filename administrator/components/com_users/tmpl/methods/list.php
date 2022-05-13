<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Prevent direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Users\Administrator\Model\MethodsModel;
use Joomla\Component\Users\Administrator\View\Methods\HtmlView;

// phpcs:ignoreFile
/** @var HtmlView $this */

HTMLHelper::_('bootstrap.tooltip', '.hasTooltip');

/** @var MethodsModel $model */
$model = $this->getModel();

?>
<div id="com-users-methods-list-container">
	<?php foreach($this->methods as $methodName => $method): ?>
		<div class="com-users-methods-list-method com-users-methods-list-method-name-<?php echo htmlentities($method['display'])?> <?php echo ($this->defaultMethod == $methodName) ? 'com-users-methods-list-method-default' : ''?> ">
			<div class="com-users-methods-list-method-header">
				<div class="com-users-methods-list-method-image">
					<img src="<?php echo Uri::root() . $method['image'] ?>" alt="<?php echo $this->escape($method['name']) ?>">
				</div>
				<div class="com-users-methods-list-method-title">
					<h4>
						<?php echo $method['display'] ?>
						<?php if ($this->defaultMethod == $methodName): ?>
							<span id="com-users-methods-list-method-default-tag" class="badge bg-info me-1">
							<?php echo Text::_('COM_USERS_LBL_LIST_DEFAULTTAG') ?>
							</span>
						<?php endif; ?>
					</h4>
				</div>
				<div class="com-users-methods-list-method-info">
					<span class="hasTooltip icon icon-info-circle icon-info-sign"
						  title="<?php echo $this->escape($method['shortinfo']) ?>"></span>
				</div>
			</div>

			<div class="com-users-methods-list-method-records-container">
				<?php if (count($method['active'])): ?>
					<div class="com-users-methods-list-method-records">
						<?php  foreach($method['active'] as $record): ?>
							<div class="com-users-methods-list-method-record">
								<div class="com-users-methods-list-method-record-info">

									<?php if ($methodName == 'backupcodes'): ?>
										<div class="alert alert-info">
											<h3 class="alert-heading">
												<span class="icon icon-info-circle icon-info-sign" aria-hidden="true"></span>
												<?php echo Text::sprintf('COM_USERS_LBL_OTEP_PRINT_PROMPT_HEAD', Route::_('index.php?option=com_users&task=method.edit&id=' . (int) $record->id . ($this->returnURL ? '&returnurl=' . $this->escape(urlencode($this->returnURL)) : '') . '&user_id=' . $this->user->id)) ?>
											</h3>
											<p class="text-muted">
												<?php echo Text::_('COM_USERS_LBL_OTEP_PRINT_PROMPT') ?>
											</p>
										</div>
									<?php else: ?>
										<div class="com-users-methods-list-method-record-title-container">
											<?php if ($record->default): ?>
												<span id="com-users-methods-list-method-default-badge-small" class="badge bg-info me-1 hasTooltip" title="<?php echo $this->escape(Text::_('COM_USERS_LBL_LIST_DEFAULTTAG')) ?>"><span class="icon icon-star"></span></span>
											<?php endif; ?>
											<span class="com-users-methods-list-method-record-title">
												<?php echo $this->escape($record->title); ?>
											</span>
										</div>
									<?php endif; ?>

									<div class="com-users-methods-list-method-record-lastused">
										<span class="com-users-methods-list-method-record-createdon">
											<?php echo Text::sprintf('COM_USERS_TFA_LBL_CREATEDON', $model->formatRelative($record->created_on)) ?>
										</span>
										<span class="com-users-methods-list-method-record-lastused-date">
											<?php echo Text::sprintf('COM_USERS_TFA_LBL_LASTUSED', $model->formatRelative($record->last_used)) ?>
										</span>
									</div>

								</div>

								<?php if ($methodName != 'backupcodes'): ?>
								<div class="com-users-methods-list-method-record-actions">
									<a class="com-users-methods-list-method-record-edit btn btn-secondary"
									   href="<?php echo Route::_('index.php?option=com_users&task=method.edit&id=' . (int) $record->id . ($this->returnURL ? '&returnurl=' . $this->escape(urlencode($this->returnURL)) : '') . '&user_id=' . $this->user->id)?>">
										<span class="icon icon-pencil"></span>
									</a>

									<?php if ($method['canDisable']): ?>
										<a class="com-users-methods-list-method-record-delete btn btn-danger"
										   href="<?php echo Route::_('index.php?option=com_users&task=method.delete&id=' . (int) $record->id . ($this->returnURL ? '&returnurl=' . $this->escape(urlencode($this->returnURL)) : '') . '&user_id=' . $this->user->id . '&' . Factory::getApplication()->getFormToken() . '=1')?>"
										><span class="icon icon-trash"></span></a>
									<?php endif; ?>
								</div>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if (empty($method['active']) || $method['allowMultiple']): ?>
					<div class="com-users-methods-list-method-addnew-container">
						<a href="<?php echo Route::_('index.php?option=com_users&task=method.add&method=' . $this->escape(urlencode($method['name'])) . ($this->returnURL ? '&returnurl=' . $this->escape(urlencode($this->returnURL)) : '') . '&user_id=' . $this->user->id)?>"
						   class="com-users-methods-list-method-addnew btn btn-primary"
						>
							<?php echo Text::sprintf('COM_USERS_LBL_LIST_ADD_A', $method['display']) ?>
						</a>
					</div>
				<?php endif; ?>
			</div>
		</div>
	<?php endforeach; ?>
</div>
