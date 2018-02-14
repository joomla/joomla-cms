<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Add javascripts
JHtml::_('behavior.core');
JHtml::_('behavior.formvalidator');
JHtml::_('bootstrap.framework');

JHtml::_('script', 'com_media/edit-images.js', array('version' => 'auto', 'relative' => true));
// @TODO logic to load plugins per media type

$params = JComponentHelper::getParams('com_media');

// Add stylesheets
JHtml::_('stylesheet', 'media/com_media/css/mediamanager.css');

/**
 * @var JForm $form
 */
$form = $this->form;

$tmpl = JFactory::getApplication()->input->getCmd('tmpl', '');

// Populate the media config
$config = [
	'apiBaseUrl'              => JUri::root() . 'administrator/index.php?option=com_media&format=json',
	'csrfToken'               => JSession::getFormToken(),
	'uploadPath'              => $this->file->path,
	'editViewUrl'             => JUri::root() . 'administrator/index.php?option=com_media&view=file' . $tmpl,
	'allowedUploadExtensions' => $params->get('upload_extensions', ''),
	'maxUploadSizeMb'         => $params->get('upload_maxsize', 10),
	'contents'                => base64_encode(file_get_contents($this->file->localpath)),
];

JFactory::getDocument()->addScriptOptions('com_media', $config);

?>
<div class="row">
	<form action="#" method="post" name="adminForm" id="media-form" class="form-validate col-md-12">
	<?php $fieldSets = $form->getFieldsets(); ?>
	<?php if ($fieldSets) : ?>
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'attrib-' . reset($fieldSets)->name)); ?>
		<?php echo '<div id="media-manager-edit-container" class="media-manager-edit d-flex justify-content-around form-validate col-md-9 p-4"></div>'; ?>
		<?php echo JLayoutHelper::render('joomla.edit.params', $this); ?>
		<?php echo JHtml::_('bootstrap.endTabSet'); ?>
	<?php endif; ?>
	</form>
</div>
