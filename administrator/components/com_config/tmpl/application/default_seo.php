<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('joomla.dialog-autocreate');

/** @var \Joomla\Component\Config\Administrator\View\Application\HtmlView $this */

$this->name = Text::_('COM_CONFIG_SEO_SETTINGS');
$this->description = Text::_('COM_CONFIG_SEO_SETTINGS_DESC');

if ($this->sefPluginId) {
    $popupOptions = [
        'popupType'  => 'iframe',
        'textHeader' => Text::_('COM_CONFIG_SEO_SETTINGS_SEF_PLUGIN'),
        'src'        => Route::_('index.php?option=com_plugins&client_id=0&task=plugin.edit&extension_id=' . $this->sefPluginId . '&tmpl=component&layout=modal', false),
    ];

    $link = HTMLHelper::_(
        'link',
        '#',
        Text::_('COM_CONFIG_SEO_SETTINGS_SEF_PLUGIN'),
        [
            'class'                 => 'alert-link',
            'data-joomla-dialog'    => $this->escape(json_encode($popupOptions, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)),
            'data-checkin-url'      => Route::_('index.php?option=com_plugins&task=plugins.checkin&format=json&cid[]=' . $this->sefPluginId),
            'data-close-on-message' => '',
        ],
    );

    $this->description = Text::sprintf('COM_CONFIG_SEO_SETTINGS_DESC', $link);
}

$this->fieldsname = 'seo';
$this->formclass = 'options-form';

echo LayoutHelper::render('joomla.content.options_default', $this);
