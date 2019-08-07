<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('jquery.framework');

HTMLHelper::_('script', 'com_associations/sidebysideupdate.js', ['version' => 'auto', 'relative' => true]);
HTMLHelper::_('stylesheet', 'com_associations/sidebyside.css', ['version' => 'auto', 'relative' => true]);

$options = array(
	'layout'   => $this->app->input->get('layout', '', 'string'),
	'itemtype' => $this->itemtype,
	'id'       => $this->referenceId,
	'targetId'   => $this->targetId
);
?>
<form action="<?php echo Route::_('index.php?option=com_associations&view=association&' . http_build_query($options)); ?>" method="post" name="adminForm" id="adminForm" data-associatedview="<?php echo $this->typeName; ?>">
	<div class="sidebyside">
		<div class="outer-panel" id="left-panel">
			<div class="inner-panel">
				<?php if (isset($this->referenceVersionIdNew)) : ?>
					<h3><?php echo Text::_('COM_ASSOCIATIONS_REFERENCE_ITEM_COMPARE_VIEW'); ?></h3>
					<iframe id="reference-association" name="reference-association" title="reference-association"
						src="<?php echo Route::_('index.php?option=com_contenthistory&view=compare&layout=compareassocparent&tmpl=component&' . Session::getFormToken() . '=1&id1=' . $this->referenceVersionIdNew . '&id2=' . $this->referenceVersionIdOld); ?>"
						height="400" width="400" >
					</iframe>
				<?php else : ?>
					<h3><?php echo Text::_('COM_ASSOCIATIONS_REFERENCE_ITEM'); ?></h3>
					<iframe id="reference-association" name="reference-association" title="reference-association"
						src="<?php echo Route::_($this->editUri . '&task=' . $this->typeName . '.edit&id=' . (int) $this->referenceId); ?>"
						height="400" width="400"
						data-action="edit"
						data-item="<?php echo $this->typeName; ?>"
						data-id="<?php echo $this->referenceId; ?>"
						data-title="<?php echo $this->referenceTitle; ?>"
						data-language="<?php echo $this->referenceLanguage; ?>"
						data-editurl="<?php echo Route::_($this->editUri); ?>">
					</iframe>
				<?php endif; ?>
			</div>
		</div>
		<div class="outer-panel" id="right-panel">
			<div class="inner-panel">
					<h3 class="target-text"><?php echo Text::_('COM_ASSOCIATIONS_ASSOCIATED_ITEM'); ?></h3>
				<iframe id="target-association" name="target-association" title="target-association"
						src="<?php echo Route::_($this->editUri . '&task=' . $this->typeName . '.edit&id=' . (int) $this->targetId); ?>"
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
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
