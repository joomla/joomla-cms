<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Uri\Uri;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate')
	->useScript('com_media.edit-images')
	->useStyle('com_media.mediamanager');

$params = ComponentHelper::getParams('com_media');

/** @var \Joomla\CMS\Form\Form $form */
$form = $this->form;

$tmpl = Factory::getApplication()->input->getCmd('tmpl');

// Load the toolbar when we are in an iframe
if ($tmpl == 'component')
{
	echo '<div class="subhead noshadow">';
	echo Toolbar::getInstance('toolbar')->render();
	echo '</div>';
}

// Populate the media config
$config = [
	'apiBaseUrl'              => Uri::base() . 'index.php?option=com_media&format=json',
	'csrfToken'               => Session::getFormToken(),
	'uploadPath'              => $this->file->path,
	'editViewUrl'             => Uri::base() . 'index.php?option=com_media&view=file' . ($tmpl ? '&tmpl=' . $tmpl : ''),
	'allowedUploadExtensions' => $params->get('upload_extensions', ''),
	'maxUploadSizeMb'         => $params->get('upload_maxsize', 10),
	'contents'                => $this->file->content,
];

$this->document->addScriptOptions('com_media', $config);

$this->useCoreUI = true;
?>
<form action="#" method="post" name="adminForm" id="media-form" class="form-validate main-card media-form mt-3">
<?php $fieldSets = $form->getFieldsets(); ?>
<?php if ($fieldSets) : ?>
	<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'attrib-' . reset($fieldSets)->name)); ?>
	<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>
	<?php echo '<div id="media-manager-edit-container" class="media-manager-edit"></div>'; ?>
	<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
<?php endif; ?>
</form>
