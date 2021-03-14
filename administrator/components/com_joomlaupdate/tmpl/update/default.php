<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('core')
	->useScript('jquery')
	->useScript('com_joomlaupdate.encryption')
	->useScript('com_joomlaupdate.update')
	->useScript('com_joomlaupdate.admin-update');

$password = Factory::getApplication()->getUserState('com_joomlaupdate.password', null);
$filesize = Factory::getApplication()->getUserState('com_joomlaupdate.filesize', null);
$ajaxUrl = Uri::base() . 'components/com_joomlaupdate/restore.php';
$returnUrl = 'index.php?option=com_joomlaupdate&task=update.finalise&' . Factory::getSession()->getFormToken() . '=1';

$this->document->addScriptOptions(
	'joomlaupdate',
	[
		'password' => $password,
		'totalsize' => $filesize,
		'ajax_url' => $ajaxUrl,
		'return_url' => $returnUrl,
	]
);
?>

<p class="nowarning"><?php echo Text::_('COM_JOOMLAUPDATE_VIEW_UPDATE_INPROGRESS'); ?></p>

<div id="update-progress">
	<div id="extprogress">
		<div id="progress" class="progress">
			<div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
		</div>
		<div class="extprogrow">
			<span class="extlabel"><?php echo Text::_('COM_JOOMLAUPDATE_VIEW_UPDATE_PERCENT'); ?></span>
			<span class="extvalue" id="extpercent" aria-live="polite"></span>
		</div>
		<div class="extprogrow">
			<span class="extlabel"><?php echo Text::_('COM_JOOMLAUPDATE_VIEW_UPDATE_BYTESREAD'); ?></span>
			<span class="extvalue" id="extbytesin"></span>
		</div>
		<div class="extprogrow">
			<span class="extlabel"><?php echo Text::_('COM_JOOMLAUPDATE_VIEW_UPDATE_BYTESEXTRACTED'); ?></span>
			<span class="extvalue" id="extbytesout"></span>
		</div>
		<div class="extprogrow">
			<span class="extlabel"><?php echo Text::_('COM_JOOMLAUPDATE_VIEW_UPDATE_FILESEXTRACTED'); ?></span>
			<span class="extvalue" id="extfiles"></span>
		</div>
	</div>
</div>
