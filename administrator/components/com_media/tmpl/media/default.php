<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Uri\Uri;

$app    = Factory::getApplication();
$params = ComponentHelper::getParams('com_media');
$input  = $app->getInput();
$user   = $app->getIdentity();

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useStyle('com_media.mediamanager')
    ->useScript('com_media.mediamanager');

// Populate the language
$this->loadTemplate('texts');

$tmpl = $input->getCmd('tmpl');

// Load the toolbar when we are in an iframe
if ($tmpl === 'component') {
    echo '<div class="subhead noshadow">';
    echo Toolbar::getInstance('toolbar')->render();
    echo '</div>';
}

$mediaTypes = '&mediatypes=' . $input->getString('mediatypes', '0,1,2,3');

// Populate the media config
$config = [
    'apiBaseUrl'          => Uri::base() . 'index.php?option=com_media&format=json' . $mediaTypes,
    'csrfToken'           => Session::getFormToken(),
    'filePath'            => $params->get('file_path', 'images'),
    'fileBaseUrl'         => Uri::root() . $params->get('file_path', 'images'),
    'fileBaseRelativeUrl' => $params->get('file_path', 'images'),
    'editViewUrl'         => Uri::base() . 'index.php?option=com_media&view=file' . ($tmpl ? '&tmpl=' . $tmpl : '')  . $mediaTypes,
    'imagesExtensions'    => array_map('trim', explode(',', $params->get('image_extensions', 'bmp,gif,jpg,jpeg,png,webp'))),
    'audioExtensions'     => array_map('trim', explode(',', $params->get('audio_extensions', 'mp3,m4a,mp4a,ogg'))),
    'videoExtensions'     => array_map('trim', explode(',', $params->get('video_extensions', 'mp4,mp4v,mpeg,mov,webm'))),
    'documentExtensions'  => array_map('trim', explode(',', $params->get('doc_extensions', 'doc,odg,odp,ods,odt,pdf,ppt,txt,xcf,xls,csv'))),
    'maxUploadSizeMb'     => $params->get('upload_maxsize', 10),
    'providers'           => (array) $this->providers,
    'currentPath'         => $this->currentPath,
    'isModal'             => $tmpl === 'component',
    'canCreate'           => $user->authorise('core.create', 'com_media'),
    'canEdit'             => $user->authorise('core.edit', 'com_media'),
    'canDelete'           => $user->authorise('core.delete', 'com_media'),
];
$this->document->addScriptOptions('com_media', $config);
?>
<div id="com-media"></div>
