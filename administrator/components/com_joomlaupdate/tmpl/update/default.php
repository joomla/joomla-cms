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
use Joomla\CMS\Help\Help;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('core')
	->useScript('com_joomlaupdate.admin-update')
	->useScript('bootstrap.modal');

Text::script('COM_JOOMLAUPDATE_ERRORMODAL_HEAD_FORBIDDEN');
Text::script('COM_JOOMLAUPDATE_ERRORMODAL_BODY_FORBIDDEN');
Text::script('COM_JOOMLAUPDATE_ERRORMODAL_HEAD_SERVERERROR');
Text::script('COM_JOOMLAUPDATE_ERRORMODAL_BODY_SERVERERROR');
Text::script('COM_JOOMLAUPDATE_ERRORMODAL_HEAD_GENERIC');
Text::script('COM_JOOMLAUPDATE_ERRORMODAL_BODY_INVALIDLOGIN');
Text::script('COM_JOOMLAUPDATE_UPDATING_FAIL');
Text::script('COM_JOOMLAUPDATE_UPDATING_COMPLETE');
Text::script('JLIB_SIZE_BYTES');
Text::script('JLIB_SIZE_KB');
Text::script('JLIB_SIZE_MB');
Text::script('JLIB_SIZE_GB');
Text::script('JLIB_SIZE_TB');
Text::script('JLIB_SIZE_PB');
Text::script('JLIB_SIZE_EB');
Text::script('JLIB_SIZE_ZB');
Text::script('JLIB_SIZE_YB');

$password = Factory::getApplication()->getUserState('com_joomlaupdate.password', null);
$filesize = Factory::getApplication()->getUserState('com_joomlaupdate.filesize', null);
$ajaxUrl = Uri::base() . 'components/com_joomlaupdate/extract.php';
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

$helpUrl = Help::createUrl('JHELP_COMPONENTS_JOOMLA_UPDATE', false);
?>

<div class="modal fade"
	 id="errorDialog"
	 tabindex="-1"
	 role="dialog"
	 aria-labelledby="errorDialogLabel"
	 aria-hidden="true"
>
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title" id="errorDialogLabel"></h3>
				<button type="button" class="btn-close novalidate" data-bs-dismiss="modal"
						aria-label="<?php echo Text::_('JLIB_HTML_BEHAVIOR_CLOSE') ?>">
			</div>
			<div class="modal-body p-3">
				<div id="errorDialogMessage"></div>
				<div>
					<a href="<?php echo $helpUrl ?>"
					   target="_blank"
					   class="btn btn-info">
						<span class="fa fa-info-circle" aria-hidden="true"></span>
						<?php echo Text::_('COM_JOOMLAUPDATE_ERRORMODAL_BTN_HELP') ?>
					</a>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="px-4 py-5 my-5 text-center">
	<span class="fa-8x mb-4 icon-loop joomlaupdate" aria-hidden="true"></span>
	<h1 class="display-5 fw-bold"><?php echo Text::_('COM_JOOMLAUPDATE_UPDATING_HEAD') ?></h1>
	<div class="col-lg-6 mx-auto">
		<p class="lead mb-4" id="update-title">
			<?php echo Text::_('COM_JOOMLAUPDATE_UPDATING_INPROGRESS'); ?>
		</p>
		<div id="progress" class="progress my-3">
			<div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated"
				 aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
			</div>
		</div>
		<div id="update-progress" class="container text-muted my-3">
			<div class="row">
				<div class="col">
					<span class="fa fa-file-archive" aria-hidden="true"></span>
					<span class="visually-hidden"><?php echo Text::_('COM_JOOMLAUPDATE_VIEW_UPDATE_BYTESREAD'); ?></span>
					<span id="extbytesin"></span>
				</div>
				<div class="col">
					<span class="fa fa-hdd" aria-hidden="true"></span>
					<span class="visually-hidden"><?php echo Text::_('COM_JOOMLAUPDATE_VIEW_UPDATE_BYTESEXTRACTED'); ?></span>
					<span id="extbytesout"></span>
				</div>
				<div class="col">
					<span class="fa fa-copy" aria-hidden="true"></span>
					<span class="visually-hidden"><?php echo Text::_('COM_JOOMLAUPDATE_VIEW_UPDATE_FILESEXTRACTED'); ?></span>
					<span id="extfiles"></span>
				</div>
			</div>
		</div>
		<div class="d-none justify-content-sm-center" id="update-help">
			<a href="<?php echo $helpUrl; ?>" target="_blank"
			   class="btn btn-outline-secondary btn-lg px-4"><?php echo Text::_('JGLOBAL_LEARN_MORE'); ?></a>
		</div>
	</div>
</div>
