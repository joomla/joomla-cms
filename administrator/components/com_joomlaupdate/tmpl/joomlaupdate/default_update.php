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

/** @var \Joomla\Component\Joomlaupdate\Administrator\View\Joomlaupdate\Html $this */
?>

<fieldset>
	<legend>
		<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_UPDATEFOUND'); ?>
	</legend>
	<p>
		<?php echo Text::sprintf($this->langKey, $this->updateSourceKey); ?>
	</p>

	<table class="table">
		<tbody>
			<tr>
				<td>
					<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_INSTALLED'); ?>
				</td>
				<td>
					<?php echo $this->updateInfo['installed']; ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_LATEST'); ?>
				</td>
				<td>
					<?php echo $this->updateInfo['latest']; ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_PACKAGE'); ?>
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
					<button class="btn btn-primary" type="submit">
						<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_INSTALLUPDATE'); ?>
					</button>
				</td>
			</tr>
		</tfoot>
	</table>
</fieldset>
