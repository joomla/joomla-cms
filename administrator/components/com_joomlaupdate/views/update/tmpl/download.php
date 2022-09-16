<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

// Include jQuery.
JHtml::_('jquery.framework');

// Load the scripts
JHtml::_('script', 'com_joomlaupdate/json2.js', array('version' => 'auto', 'relative' => true));
JHtml::_('script', 'com_joomlaupdate/download.js', array('version' => 'auto', 'relative' => true));
JHtml::_('stylesheet', 'com_joomlaupdate/download.css', array('version' => 'auto', 'relative' => true));

JFactory::getDocument()->addScriptOptions(
	'com_joomlaupdate',
	array(
		'ajaxUrl' => JUri::base() . 'index.php?option=com_joomlaupdate&task=update.stepdownload&' . JFactory::getSession()->getFormToken() . '=1',
		'returnUrl' => JUri::base() . 'index.php?option=com_joomlaupdate&task=update.install&' . JFactory::getSession()->getFormToken() . '=1',
		'minTime' => \Joomla\CMS\Component\ComponentHelper::getParams('com_joomlaupdate')
				->get('min_chunk_wait', 3) * 1000,
	)
);

?>

<div id="download-progress" >
	<p class="nowarning">
		<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DOWNLOAD_INPROGRESS'); ?>
	</p>

	<div id="dlprogress" class="container">
		<div id="progress" class="progress progress-striped active row-fluid">
			<div id="progress-bar" class="bar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
		</div>
		<div class="extprogrow row-fluid">
			<div class="extlabel span3">
				<span class="icon-health" aria-hidden="true"></span>
				<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_UPDATE_PERCENT'); ?>
			</div>
			<div class="extvalue span9" id="dlpercent"></div>
		</div>
		<div class="extprogrow row-fluid">
			<div class="extlabel span3">
				<span class="icon-download" aria-hidden="true"></span>
				<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DOWNLOAD_BYTESDL'); ?>
			</div>
			<div class="extvalue span9" id="dlbytesin"></div>
		</div>
		<div class="extprogrow row-fluid">
			<div class="extlabel span3">
				<span class="icon-file" aria-hidden="true"></span>
				<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DOWNLOAD_BYTESTOTAL'); ?>
			</div>
			<div class="extvalue span9" id="dlbytestotal"></div>
		</div>
	</div>
</div>

<div id="download-error" class="alert alert-danger">
	<h3 class="alert-heading">
		<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DOWNLOAD_ERROR') ?>
	</h3>
	<p id="dlerror"></p>
	<hr/>
	<div>
		<button type="button"
				id="dlrestart"
				class="btn btn-primary">
			<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DOWNLOAD_RESTART') ?>
		</button>
		&nbsp;
		<button type="button"
				id="dlcancel"
				class="btn btn-danger">
			<?php echo Text::_('JCANCEL') ?>
		</button>
	</div>
</div>
