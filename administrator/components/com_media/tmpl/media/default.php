<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

$doc    = JFactory::getDocument();
$params = JComponentHelper::getParams('com_media');

// Make sure core.js is loaded before media scripts
JHtml::_('behavior.core');
JHtml::_('behavior.keepalive');

// Add javascripts
JHtml::_('script', 'media/com_media/js/mediamanager.js');

// Add stylesheets
JHtml::_('stylesheet', 'media/com_media/css/mediamanager.css');

// Populate the language
$this->loadTemplate('texts');

$tmpl = JFactory::getApplication()->input->getCmd('tmpl');

// Load the toolbar when we are in an iframe
if ($tmpl == 'component')
{
	echo JToolbar::getInstance('toolbar')->render();
}

// Populate the media config
$config = array(
	'apiBaseUrl'              => JUri::root() . 'administrator/index.php?option=com_media&format=json',
	'csrfToken'               => JSession::getFormToken(),
	'filePath'                => $params->get('file_path', 'images'),
	'fileBaseUrl'             => JUri::root() . $params->get('file_path', 'images'),
	'fileBaseRelativeUrl'     => $params->get('file_path', 'images'),
	'editViewUrl'             => JUri::root() . 'administrator/index.php?option=com_media&view=file' . $tmpl,
	'allowedUploadExtensions' => $params->get('upload_extensions', ''),
	'maxUploadSizeMb'         => $params->get('upload_maxsize', 10),
	'providers'               => (array) $this->providers,
	'currentPath'             => $this->currentPath,
	'isModal'                 => JFactory::getApplication()->input->getCmd('tmpl', '') === 'component' ? true : false,
);
$doc->addScriptOptions('com_media', $config);
?>
<div id="com-media"></div>
