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
	->useStyle('com_media.mediamanager');

$script = $wa->getAsset('script', 'com_media.edit-images')->getUri(true);

$params = ComponentHelper::getParams('com_media');
$input  = Factory::getApplication()->input;

/** @var \Joomla\CMS\Form\Form $form */
$form = $this->form;

$tmpl = $input->getCmd('tmpl');

$input->set('hidemainmenu', true);

// Load the toolbar when we are in an iframe
if ($tmpl == 'component') {
	echo '<div class="subhead noshadow">';
	echo Toolbar::getInstance('toolbar')->render();
	echo '</div>';
}

$mediaTypes = $input->getString('mediatypes', '0');

// Populate the media config
$config = [
	'apiBaseUrl'         => Uri::base() . 'index.php?option=com_media&format=json' . '&mediatypes=' . $mediaTypes,
	'csrfToken'          => Session::getFormToken(),
	'uploadPath'         => $this->file->path,
	'editViewUrl'        => Uri::base() . 'index.php?option=com_media&view=file' . ($tmpl ? '&tmpl=' . $tmpl : '') . '&mediatypes=' . $mediaTypes,
	'imagesExtensions'   => explode(',', $params->get('image_extensions', 'bmp,gif,jpg,jpeg,png,webp')),
	'audioExtensions'    => explode(',', $params->get('audio_extensions', 'mp3,m4a,mp4a,ogg')),
	'videoExtensions'    => explode(',', $params->get('video_extensions', 'mp4,mp4v,mpeg,mov,webm')),
	'documentExtensions' => explode(',', $params->get('doc_extensions', 'doc,odg,odp,ods,odt,pdf,ppt,txt,xcf,xls,csv')),
	'maxUploadSizeMb'    => $params->get('upload_maxsize', 10),
	'contents'           => $this->file->content,
];

$this->document->addScriptOptions('com_media', $config);

$this->useCoreUI = true;
?>
<form action="#" method="post" name="adminForm" id="media-form" class="form-validate main-card media-form mt-3">
	<?php $fieldSets = $form->getFieldsets(); ?>
	<?php if ($fieldSets) : ?>
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'attrib-' . reset($fieldSets)->name, 'breakpoint' => 768]); ?>
		<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>
		<?php echo '<div id="media-manager-edit-container" class="media-manager-edit"></div>'; ?>
		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
	<?php endif; ?>
	<input type="hidden" name="mediatypes" value="<?php echo $mediaTypes; ?>">
</form>
<script type="module" src="<?php echo $script . '?' . $this->document->getMediaVersion(); ?>"></script>
