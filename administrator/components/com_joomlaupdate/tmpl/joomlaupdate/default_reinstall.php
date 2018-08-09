<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Updater\Update;

/** @var JoomlaupdateViewDefault $this */
?>
<fieldset>
	<legend><?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_NOUPDATES'); ?></legend>
	<p><?php echo Text::sprintf($this->langKey, $this->updateSourceKey); ?></p>

	<joomla-alert type="success"><?php echo Text::sprintf('COM_JOOMLAUPDATE_VIEW_DEFAULT_NOUPDATESNOTICE', JVERSION); ?></joomla-alert>

	<?php if (is_object($this->updateInfo['object']) && ($this->updateInfo['object'] instanceof Update)) : ?>
		<table class="table">
			<tbody>
			<tr>
				<td>
					<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_PACKAGE_REINSTALL'); ?>
				</td>
				<td>
					<a href="<?php echo $this->updateInfo['object']->downloadurl->_data; ?>">
						<?php echo $this->updateInfo['object']->downloadurl->_data; ?>
					</a>
				</td>
			</tr>
			<?php if (isset($this->updateInfo['object']->get('infourl')->_data)
				&& isset($this->updateInfo['object']->get('infourl')->title)) : ?>
				<tr>
					<td>
						<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_INFOURL'); ?>
					</td>
					<td>
						<a href="<?php echo $this->updateInfo['object']->get('infourl')->_data; ?>">
							<?php echo $this->updateInfo['object']->get('infourl')->title; ?>
						</a>
					</td>
				</tr>
			<?php endif; ?>
			</tbody>
			<tfoot>
			<tr>
				<td>&nbsp;</td>
				<td>
					<button class="btn btn-warning" type="submit">
						<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_INSTALLAGAIN'); ?>
					</button>
				</td>
			</tr>
			</tfoot>
		</table>
	<?php endif; ?>

</fieldset>
