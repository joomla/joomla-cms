<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var \Joomla\Component\Finder\Administrator\View\Index\HtmlView $this */

$displayData = [
    'textPrefix' => 'COM_FINDER',
    'formURL'    => 'index.php?option=com_finder&view=index',
    'helpURL'    => 'https://docs.joomla.org/Special:MyLanguage/Smart_Search_quickstart_guide',
    'icon'       => 'icon-search-plus finder',
    'content'    => Text::_('COM_FINDER_INDEX_NO_DATA') . '<br>' . Text::_('COM_FINDER_INDEX_TIP'),
    'title'      => Text::_('COM_FINDER_HEADING_INDEXER'),
    'createURL'  => "javascript:document.getElementsByClassName('button-index')[0].click();",
];

echo LayoutHelper::render('joomla.content.emptystate', $displayData);

// Show warning that the content - finder plugin is disabled
if ($this->finderPluginId) {
    /** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
    $wa = $this->getDocument()->getWebAssetManager();
    $wa->useScript('joomla.dialog-autocreate');

    $popupOptions = [
        'popupType'  => 'iframe',
        'textHeader' => Text::_('COM_FINDER_EDIT_PLUGIN_SETTINGS'),
        'src'        => Route::_('index.php?option=com_plugins&client_id=0&task=plugin.edit&extension_id=' . $this->finderPluginId . '&tmpl=component&layout=modal', false),
    ];
    $link = HTMLHelper::_(
        'link',
        '#',
        Text::_('COM_FINDER_CONTENT_PLUGIN'),
        [
            'class'                 => 'alert-link',
            'data-joomla-dialog'    => $this->escape(json_encode($popupOptions, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)),
            'data-checkin-url'      => Route::_('index.php?option=com_plugins&task=plugins.checkin&format=json&cid[]=' . $this->finderPluginId),
            'data-close-on-message' => '',
            'data-reload-on-close'  => '',
        ],
    );
    Factory::getApplication()->enqueueMessage(Text::sprintf('COM_FINDER_INDEX_PLUGIN_CONTENT_NOT_ENABLED_LINK', $link), 'warning');
}
