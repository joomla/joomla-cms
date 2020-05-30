<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Associations\Administrator\View\Association\HtmlView;

/** @var HtmlView $this */

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('jquery.framework');

HTMLHelper::_('script', 'com_associations/sidebyside.js', ['version' => 'auto', 'relative' => true]);
HTMLHelper::_('stylesheet', 'com_associations/sidebyside.css', ['version' => 'auto', 'relative' => true]);

$this->document->getWebAssetManager()
	->useScript('webcomponent.core-loader');

$options = [
	'layout'   => $this->app->input->get('layout', '', 'string'),
	'itemtype' => $this->itemType,
	'id'       => $this->referenceId,
];
?>
<button id="toogle-left-panel" class="btn btn-sm btn-secondary"
		data-show-reference="<?php echo Text::_('COM_ASSOCIATIONS_EDIT_SHOW_REFERENCE'); ?>"
		data-hide-reference="<?php echo Text::_('COM_ASSOCIATIONS_EDIT_HIDE_REFERENCE'); ?>"><?php echo Text::_('COM_ASSOCIATIONS_EDIT_HIDE_REFERENCE'); ?>
</button>

<form action="<?php echo Route::_('index.php?option=com_associations&view=association&' . http_build_query($options)); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" data-associatedview="<?php echo $this->typeName; ?>">
	<div class="sidebyside">
		<div class="outer-panel" id="left-panel">
			<div class="inner-panel">
				<h3><?php echo Text::_('COM_ASSOCIATIONS_REFERENCE_ITEM'); ?></h3>
				<iframe id="reference-association" name="reference-association" title="reference-association"
					src="<?php echo Route::_($this->editUri . '&task=' . $this->typeName . '.edit&id=' . (int) $this->referenceId); ?>"
					height="400" width="400"
					data-action="edit"
					data-item="<?php echo $this->typeName; ?>"
					data-id="<?php echo $this->referenceId; ?>"
					data-title="<?php echo $this->referenceTitle; ?>"
					data-title-value="<?php echo $this->referenceTitleValue; ?>"
					data-language="<?php echo $this->referenceLanguage; ?>"
					data-editurl="<?php echo Route::_($this->editUri); ?>">
				</iframe>
			</div>
		</div>
		<div class="outer-panel" id="right-panel">
			<div class="inner-panel">
				<div class="language-selector">
					<div class="clearfix">
						<h3 class="target-text"><?php echo Text::_('COM_ASSOCIATIONS_ASSOCIATED_ITEM'); ?></h3>
					</div>
					<div class="langtarget">
						<div class="sr-only">
							<?php echo $this->form->getLabel('itemlanguage'); ?>
						</div>
						<?php echo $this->form->getInput('itemlanguage'); ?>
					</div>
					<div class="modaltarget">
						<?php echo $this->form->getInput('modalassociation'); ?>
					</div>
				</div>
				<iframe id="target-association" name="target-association" title="target-association"
					src="<?php echo $this->defaultTargetSrc; ?>"
					height="400" width="400"
					data-action="<?php echo $this->targetAction; ?>"
					data-item="<?php echo $this->typeName; ?>"
					data-id="<?php echo $this->targetId; ?>"
					data-title="<?php echo $this->targetTitle; ?>"
					data-language="<?php echo $this->targetLanguage; ?>"
					data-editurl="<?php echo Route::_($this->editUri); ?>">
				</iframe>
			</div>
		</div>
	</div>

	<input type="hidden" name="task" value="">
	<input type="hidden" name="target-id" id="target-id" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
