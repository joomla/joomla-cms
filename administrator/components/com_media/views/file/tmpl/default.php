<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Add javascripts
JHtml::_('behavior.core');
JHtml::_('behavior.formvalidator');
JHtml::_('bootstrap.framework');

JHtml::_('script', 'com_media/edit-images.js', array('version' => 'auto', 'relative' => true)); // @TODO logic to load plugins per media type

$params = JComponentHelper::getParams('com_media');

/**
 * @var JForm $form
 */
$form = $this->form;

$tmpl = JFactory::getApplication()->input->getCmd('tmpl');

// Load the toolbar when we are in an iframe
if ($tmpl == 'component')
{
	echo JToolbar::getInstance('toolbar')->render();

	$tmpl = '&tmpl=' . $tmpl;
}

// Populate the media config
$config = [
	'apiBaseUrl'              => JUri::root() . 'administrator/index.php?option=com_media&format=json',
	'csrfToken'               => JSession::getFormToken(),
	'filePath'                => $params->get('file_path', 'images'),
	'fileBaseUrl'             => JUri::root() . $params->get('file_path', 'images'),
	'uploadPath'              => $this->file,
	'editViewUrl'             => JUri::root() . 'administrator/index.php?option=com_media&view=file' . $tmpl,
	'allowedUploadExtensions' => $params->get('upload_extensions', ''),
	'maxUploadSizeMb'         => $params->get('upload_maxsize', 10),
	'contents'                => base64_encode(file_get_contents(JPATH_ROOT . '/images' . $this->file)),
];

JFactory::getDocument()->addScriptOptions('com_media', $config);
JFactory::getDocument()->addStyleDeclaration("	.btn-group {
		display: block;
	}");

?>
<form action="#" method="post" name="adminForm" id="media-form" class="form-validate">
<?php
$fieldSets = $form->getFieldsets();

if ($fieldSets)
{
	echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'attrib-' . reset($fieldSets)->name));

	echo JLayoutHelper::render('joomla.edit.params', $this);

	echo JHtml::_('bootstrap.endTabSet');
}
?>
</form>
<div id="media-manager-edit-container"></div>
