<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;

// Add javascripts
HTMLHelper::_('behavior.core');
HTMLHelper::_('behavior.formvalidator');

HTMLHelper::_('script', 'com_media/edit-images.js', array('version' => 'auto', 'relative' => true));
// @TODO logic to load plugins per media type

$params = JComponentHelper::getParams('com_media');

// Add stylesheets
HTMLHelper::_('stylesheet', 'media/com_media/css/mediamanager.css');

/**
 * @var JForm $form
 */
$form = $this->form;

$tmpl = Factory::getApplication()->input->getCmd('tmpl');

// Load the toolbar when we are in an iframe
if ($tmpl == 'component')
{
	echo Toolbar::getInstance('toolbar')->render();
}

// Populate the media config
$config = [
	'apiBaseUrl'              => Uri::root() . 'administrator/index.php?option=com_media&format=json',
	'csrfToken'               => Session::getFormToken(),
	'uploadPath'              => $this->file->path,
	'editViewUrl'             => Uri::root() . 'administrator/index.php?option=com_media&view=file' . (!empty($tmpl) ? ('&tmpl=' . $tmpl) : ''),
	'allowedUploadExtensions' => $params->get('upload_extensions', ''),
	'maxUploadSizeMb'         => $params->get('upload_maxsize', 10),
	'contents'                => $this->file->content,
];

Factory::getDocument()->addScriptOptions('com_media', $config);

$this->useCoreUI = true;
?>
<div class="row">
	<form action="#" method="post" name="adminForm" id="media-form" class="form-validate col-md-12">
	<?php $fieldSets = $form->getFieldsets(); ?>
	<?php if ($fieldSets) : ?>
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'attrib-' . reset($fieldSets)->name)); ?>
		<?php echo '<div id="media-manager-edit-container" class="media-manager-edit d-flex justify-content-around form-validate col-md-9 p-4"></div>'; ?>
		<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>
		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
	<?php endif; ?>
	</form>
</div>
