<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Updater\Update;
use Joomla\Component\Joomlaupdate\Administrator\View\Joomlaupdate\HtmlView;

/** @var HtmlView $this */
?>
<fieldset class="options-form">
	<legend>
		<?php echo Text::_('COM_JOOMLAUPDATE_UPDATE_CHECK'); ?>
	</legend>
	<p class="alert alert-info">
		<span class="icon-info-circle" aria-hidden="true"></span>
		<span class="visually-hidden"><?php echo Text::_('NOTICE'); ?></span>
		<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_NOUPDATES'); ?>
	</p>
	<p><?php echo Text::sprintf($this->langKey, $this->updateSourceKey); ?></p>
	<p class="alert alert-success">
		<span class="icon-check-circle" aria-hidden="true"></span>
		<span class="visually-hidden"><?php echo Text::_('NOTICE'); ?></span>
		<?php echo Text::sprintf('COM_JOOMLAUPDATE_VIEW_DEFAULT_NOUPDATESNOTICE', '&#x200E;' . JVERSION); ?>
	</p>
	<?php if (is_object($this->updateInfo['object']) && ($this->updateInfo['object'] instanceof Update)) : ?>
		<div class="control-group">
			<div class="control-label">
				<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_PACKAGE_REINSTALL'); ?>
			</div>
			<div class="controls">
				<?php echo HTMLHelper::link(
					$this->updateInfo['object']->downloadurl->_data,
					$this->updateInfo['object']->downloadurl->_data,
					[
						'target' => '_blank',
						'rel'    => 'noopener noreferrer',
						'title'  => Text::sprintf('JBROWSERTARGET_NEW_TITLE', $this->updateInfo['object']->downloadurl->_data)
					]
				); ?>
			</div>
		</div>

		<?php if (isset($this->updateInfo['object']->get('infourl')->_data)
			&& isset($this->updateInfo['object']->get('infourl')->title)) : ?>
			<div class="control-group">
				<div class="control-label">
					<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_INFOURL'); ?>
				</div>
				<div class="controls">
					<?php echo HTMLHelper::link(
						$this->updateInfo['object']->get('infourl')->_data,
						$this->updateInfo['object']->get('infourl')->title,
						[
							'target' => '_blank',
							'rel'    => 'noopener noreferrer',
							'title'  => Text::sprintf('JBROWSERTARGET_NEW_TITLE', $this->updateInfo['object']->get('infourl')->title)
						]
					); ?>
				</div>
			</div>
		<?php endif; ?>

		<hr>

		<div class="control-group">
			<div class="controls">
				<button class="btn btn-warning" type="submit">
					<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_INSTALLAGAIN'); ?>
				</button>
			</div>
		</div>
	<?php endif; ?>

</fieldset>
