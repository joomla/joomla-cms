<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Prevent direct access
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Users\Site\View\Method\HtmlView;

// phpcs:ignoreFile
/** @var  HtmlView  $this */

HTMLHelper::_('bootstrap.tooltip', '.hasTooltip');

$cancelURL = Route::_('index.php?option=com_users&task=methods.display&user_id=' . $this->user->id);

if (!empty($this->returnURL))
{
	$cancelURL = $this->escape(base64_decode($this->returnURL));
}

$recordId = (int) $this->record->id ?? 0;
$method   = $this->record->method ?? $this->getModel()->getState('method');
$userId   = (int) $this->user->id ?? 0;
?>
<div class="card card-body">
	<form action="<?php echo Route::_(sprintf("index.php?option=com_users&task=method.save&id=%d&method=%s&user_id=%d", $recordId, $method, $userId)) ?>"
		  class="form form-horizontal" id="com-users-method-edit" method="post">
		<?php echo HTMLHelper::_('form.token') ?>
		<?php if (!empty($this->returnURL)): ?>
		<input type="hidden" name="returnurl" value="<?php echo $this->escape($this->returnURL) ?>">
		<?php endif; ?>

		<?php if (!empty($this->renderOptions['hidden_data'])): ?>
		<?php foreach ($this->renderOptions['hidden_data'] as $key => $value): ?>
		<input type="hidden" name="<?php echo $this->escape($key) ?>" value="<?php echo $this->escape($value) ?>">
		<?php endforeach; ?>
		<?php endif; ?>

		<?php if (!empty($this->title)): ?>
		<?php if (!empty($this->renderOptions['help_url'])): ?>
		<span class="float-end">
			<a href="<?php echo $this->renderOptions['help_url'] ?>"
			   class="btn btn-sm btn-small btn-default btn-inverse btn-dark"
			   target="_blank"
			>
				<span class="icon icon-question-sign" aria-hidden="true"></span>
				<span class="visually-hidden"><?php echo Text::_('JHELP') ?></span>
			</a>
		</span>
		<?php endif;?>
		<h3 id="com-users-method-edit-head">
			<?php echo Text::_($this->title) ?>
		</h3>
		<?php endif; ?>

		<div class="row mb-3">
			<label class="col-sm-3 col-form-label hasTooltip"
				for="com-users-method-edit-title"
				title="<?php echo $this->escape(Text::_('COM_USERS_TFA_EDIT_FIELD_TITLE_DESC')) ?>">
				<?php echo Text::_('COM_USERS_TFA_EDIT_FIELD_TITLE'); ?>
			</label>
			<div class="col-sm-9">
				<input type="text"
						class="form-control"
						id="com-users-method-edit-title"
						name="title"
						value="<?php echo $this->escape($this->record->title) ?>"
						placeholder="<?php echo Text::_('COM_USERS_TFA_EDIT_FIELD_TITLE_DESC') ?>">
			</div>
		</div>

		<div class="row mb-3">
			<div class="col-sm-9 offset-sm-3">
				<div class="form-check">
					<input class="form-check-input" type="checkbox" id="com-users-is-default-method" <?php echo $this->record->default ? 'checked="checked"' : ''; ?> name="default">
					<label class="form-check-label" for="com-users-is-default-method">
						<?php echo Text::_('COM_USERS_TFA_EDIT_FIELD_DEFAULT'); ?>
					</label>
				</div>
			</div>
		</div>

		<?php if (!empty($this->renderOptions['pre_message'])): ?>
		<div class="com-users-method-edit-pre-message text-muted mt-4 mb-3">
			<?php echo $this->renderOptions['pre_message'] ?>
		</div>
		<?php endif; ?>

		<?php if (!empty($this->renderOptions['tabular_data'])): ?>
		<div class="com-users-method-edit-tabular-container">
			<?php if (!empty($this->renderOptions['table_heading'])): ?>
			<h4 class="h3 border-bottom mb-3">
				<?php echo $this->renderOptions['table_heading'] ?>
			</h4>
			<?php endif; ?>
			<table class="table table-striped">
				<tbody>
				<?php foreach ($this->renderOptions['tabular_data'] as $cell1 => $cell2): ?>
				<tr>
					<td>
						<?php echo $cell1 ?>
					</td>
					<td>
						<?php echo $cell2 ?>
					</td>
				</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php endif; ?>

		<?php if ($this->renderOptions['field_type'] == 'custom'): ?>
			<?php echo $this->renderOptions['html']; ?>
		<?php else: ?>
		<div class="row mb-3">
			<?php if ($this->renderOptions['label']): ?>
			<label class="col-sm-3 col-form-label hasTooltip" for="com-users-method-edit-code">
				<?php echo $this->renderOptions['label']; ?>
			</label>
			<?php endif; ?>
			<div class="col-sm-9" <?php echo $this->renderOptions['label'] ? '' : 'offset-sm-3' ?>>
				<input type="<?php echo $this->renderOptions['input_type']; ?>"
					   class="form-control" id="com-users-method-code"
					   name="code"
					   value="<?php echo $this->escape($this->renderOptions['input_value']) ?>"
					   placeholder="<?php echo $this->escape($this->renderOptions['placeholder']) ?>">
			</div>
		</div>
		<?php endif; ?>

		<div class="row mb-3">
			<div class="col-sm-9 offset-sm-3">
				<?php if ($this->renderOptions['show_submit'] || $this->isEditExisting): ?>
				<button type="submit" class="btn btn-primary me-3">
					<span class="icon icon-ok"></span>
					<?php echo Text::_('JSAVE'); ?>
				</button>
				<?php endif; ?>

				<a href="<?php echo $cancelURL ?>"
				   class="btn btn-small btn-sm btn-danger">
					<span class="icon icon-cancel-2"></span>
					<?php echo Text::_('JCANCEL'); ?>
				</a>
			</div>
		</div>

		<?php if (!empty($this->renderOptions['post_message'])): ?>
			<div class="com-users-method-edit-post-message text-muted">
				<?php echo $this->renderOptions['post_message'] ?>
			</div>
		<?php endif; ?>
	</form>
</div>
