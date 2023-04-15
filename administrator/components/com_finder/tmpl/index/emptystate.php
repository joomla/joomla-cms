<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

$displayData = [
    'textPrefix' => 'COM_FINDER',
    'formURL'    => 'index.php?option=com_finder&view=index',
    'helpURL'    => 'https://docs.joomla.org/Special:MyLanguage/Smart_Search_quickstart_guide',
    'icon'       => 'icon-search-plus finder',
    'content'    => Text::_('COM_FINDER_INDEX_NO_DATA') . '<br>' . Text::_('COM_FINDER_INDEX_TIP'),
    'title'      => Text::_('COM_FINDER_HEADING_INDEXER'),
    'createURL'  => "javascript:document.getElementsByClassName('button-archive')[0].click();",
];

echo LayoutHelper::render('joomla.content.emptystate', $displayData);

if ($this->finderPluginId) : ?>
    <?php $link = Route::_('index.php?option=com_plugins&client_id=0&task=plugin.edit&extension_id=' . $this->finderPluginId . '&tmpl=component&layout=modal'); ?>
    <?php echo HTMLHelper::_(
        'bootstrap.renderModal',
        'plugin' . $this->finderPluginId . 'Modal',
        [
            'url'         => $link,
            'title'       => Text::_('COM_FINDER_EDIT_PLUGIN_SETTINGS'),
            'height'      => '400px',
            'width'       => '800px',
            'bodyHeight'  => '70',
            'modalWidth'  => '80',
            'closeButton' => false,
            'backdrop'    => 'static',
            'keyboard'    => false,
            'footer'      => '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"'
                . ' onclick="Joomla.iframeButtonClick({iframeSelector: \'#plugin' . $this->finderPluginId . 'Modal\', buttonSelector: \'#closeBtn\'})">'
                . Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>'
                . '<button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="Joomla.iframeButtonClick({iframeSelector: \'#plugin' . $this->finderPluginId . 'Modal\', buttonSelector: \'#saveBtn\'})">'
                . Text::_("JSAVE") . '</button>'
                . '<button type="button" class="btn btn-success" onclick="Joomla.iframeButtonClick({iframeSelector: \'#plugin' . $this->finderPluginId . 'Modal\', buttonSelector: \'#applyBtn\'}); return false;">'
                . Text::_("JAPPLY") . '</button>'
        ]
    ); ?>
<?php endif;
