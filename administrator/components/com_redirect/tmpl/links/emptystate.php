<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Redirect\Administrator\Helper\RedirectHelper;

/** @var \Joomla\Component\Redirect\Administrator\View\Links\HtmlView $this */

$displayData = [
    'textPrefix' => 'COM_REDIRECT',
    'formURL'    => 'index.php?option=com_redirect&view=links',
    'helpURL'    => 'https://docs.joomla.org/Special:MyLanguage/Help5.x:Redirects:_Links',
    'icon'       => 'icon-map-signs redirect',
];

$app  = Factory::getApplication();
$user = $this->getCurrentUser();

if ($user->authorise('core.create', 'com_redirect')) {
    $displayData['createURL'] = 'index.php?option=com_redirect&task=link.add';
}

if (
    $user->authorise('core.create', 'com_redirect')
    && $user->authorise('core.edit', 'com_redirect')
    && $user->authorise('core.edit.state', 'com_redirect')
) {
    $displayData['formAppend'] = '<template id="joomla-dialog-batch">' . $this->loadTemplate('batch_body') . '</template>';
}

$collectUrlsEnabled = RedirectHelper::collectUrlsEnabled();
$pluginEnabled      = PluginHelper::isEnabled('system', 'redirect');
$redirectPluginId   = $this->redirectPluginId;

// Show messages about the enabled plugin and if the plugin should collect URLs
if ($pluginEnabled && $collectUrlsEnabled) {
    $app->enqueueMessage(Text::sprintf('COM_REDIRECT_COLLECT_URLS_ENABLED', Text::_('COM_REDIRECT_PLUGIN_ENABLED')), 'notice');
} else {
    /** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
    $wa = $this->getDocument()->getWebAssetManager();
    $wa->useScript('joomla.dialog-autocreate');

    $popupOptions = [
        'popupType'  => 'iframe',
        'textHeader' => Text::_('COM_REDIRECT_EDIT_PLUGIN_SETTINGS'),
        'src'        => Route::_('index.php?option=com_plugins&client_id=0&task=plugin.edit&extension_id=' . $redirectPluginId . '&tmpl=component&layout=modal', false),
    ];
    $link = HTMLHelper::_(
        'link',
        '#',
        Text::_('COM_REDIRECT_SYSTEM_PLUGIN'),
        [
            'class'                 => 'alert-link',
            'data-joomla-dialog'    => $this->escape(json_encode($popupOptions, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)),
            'data-checkin-url'      => Route::_('index.php?option=com_plugins&task=plugins.checkin&format=json&cid[]=' . $redirectPluginId),
            'data-close-on-message' => '',
            'data-reload-on-close'  => '',
        ],
    );

    if ($pluginEnabled && !$collectUrlsEnabled) {
        $app->enqueueMessage(
            Text::sprintf('COM_REDIRECT_COLLECT_MODAL_URLS_DISABLED', Text::_('COM_REDIRECT_PLUGIN_ENABLED'), $link),
            'notice'
        );
    } else {
        $app->enqueueMessage(Text::sprintf('COM_REDIRECT_PLUGIN_MODAL_DISABLED', $link), 'error');
    }
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
