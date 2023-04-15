<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

$app = Factory::getApplication();

if ($app->isClient('site')) {
    Session::checkToken('get') or die(Text::_('JINVALID_TOKEN'));
}

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('com_menus.admin-items-modal');

$function  = $app->getInput()->get('function', 'jSelectMenuItem', 'cmd');
$editor    = $app->getInput()->getCmd('editor', '');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$link      = 'index.php?option=com_menus&view=items&layout=modal&tmpl=component&' . Session::getFormToken() . '=1';
$multilang = Multilanguage::isEnabled();

if (!empty($editor)) {
    // This view is used also in com_menus. Load the xtd script only if the editor is set!
    $this->document->addScriptOptions('xtd-menus', ['editor' => $editor]);
    $onclick = "jSelectMenuItem";
    $link    = 'index.php?option=com_menus&view=items&layout=modal&tmpl=component&editor=' . $editor . '&' . Session::getFormToken() . '=1';
}
?>
<div class="container-popup">
    <form action="<?php echo Route::_($link); ?>" method="post" name="adminForm" id="adminForm">
        <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this, 'options' => ['selectorFieldName' => 'menutype']]); ?>

        <?php if (empty($this->items)) : ?>
            <div class="alert alert-info">
                <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
            </div>
        <?php else : ?>
            <table class="table table-sm">
                <caption class="visually-hidden">
                    <?php echo Text::_('COM_MENUS_ITEMS_TABLE_CAPTION'); ?>,
                            <span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
                            <span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
                </caption>
                <thead>
                    <tr>
                        <th scope="col" class="w-1 text-center">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
                        </th>
                        <th scope="col" class="title">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
                        </th>
                        <th scope="col" class="d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_MENUS_HEADING_MENU', 'menutype_title', $listDirn, $listOrder); ?>
                        </th>
                        <th scope="col" class="w-5 text-center d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_MENUS_HEADING_HOME', 'a.home', $listDirn, $listOrder); ?>
                        </th>
                        <th scope="col" class="w-10 d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
                        </th>
                        <?php if ($multilang) : ?>
                            <th scope="col" class="w-15 d-none d-md-table-cell">
                                <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
                            </th>
                        <?php endif; ?>
                        <th scope="col" class="w-1 d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($this->items as $i => $item) : ?>
                    <?php if ($item->language && $multilang) {
                        if ($item->language !== '*') {
                            $language = $item->language;
                        } else {
                            $language = '';
                        }
                    } elseif (!$multilang) {
                        $language = '';
                    }
                    ?>
                    <tr class="row<?php echo $i % 2; ?>">
                        <td class="text-center">
                            <?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'items.', false, 'cb', $item->publish_up, $item->publish_down); ?>
                        </td>
                        <th scope="row">
                            <?php $prefix = LayoutHelper::render('joomla.html.treeprefix', ['level' => $item->level]); ?>
                            <?php echo $prefix; ?>
                            <a class="select-link" href="javascript:void(0)" data-function="<?php echo $this->escape($function); ?>" data-id="<?php echo $item->id; ?>" data-title="<?php echo $this->escape($item->title); ?>" data-uri="<?php echo 'index.php?Itemid=' . $item->id; ?>" data-language="<?php echo $this->escape($language); ?>">
                            <?php echo $this->escape($item->title); ?></a>
                            <?php echo HTMLHelper::_('menus.visibility', $item->params); ?>
                            <div>
                                <?php echo $prefix; ?>
                                <span class="small">
                                <?php if (empty($item->note)) : ?>
                                    <?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
                                <?php else : ?>
                                    <?php echo Text::sprintf('JGLOBAL_LIST_ALIAS_NOTE', $this->escape($item->alias), $this->escape($item->note)); ?>
                                <?php endif; ?>
                                </span>
                            </div>
                            <div title="<?php echo $this->escape($item->path); ?>">
                                <?php echo $prefix; ?>
                                <span class="small" title="<?php echo isset($item->item_type_desc) ? htmlspecialchars($this->escape($item->item_type_desc), ENT_COMPAT, 'UTF-8') : ''; ?>">
                                    <?php echo $this->escape($item->item_type); ?>
                                </span>
                            </div>
                            <?php if ($item->type === 'component' && !$item->enabled) : ?>
                                <div>
                                    <span class="badge bg-secondary">
                                        <?php echo Text::_($item->enabled === null ? 'JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND' : 'COM_MENUS_LABEL_DISABLED'); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </th>
                        <td class="small d-none d-md-table-cell">
                            <?php echo $this->escape($item->menutype_title); ?>
                        </td>
                        <td class="text-center d-none d-md-table-cell">
                            <?php if ($item->type == 'component') : ?>
                                <?php if ($item->language == '*' || $item->home == '0') : ?>
                                    <?php echo HTMLHelper::_('jgrid.isdefault', $item->home, $i, 'items.', ($item->language != '*' || !$item->home) && false && !$item->protected, 'cb', null, 'home', 'circle'); ?>
                                <?php else : ?>
                                    <?php if ($item->language_image) : ?>
                                        <?php echo HTMLHelper::_('image', 'mod_languages/' . $item->language_image . '.gif', $item->language_title, ['title' => $item->language_title], true); ?>
                                    <?php else : ?>
                                        <span class="badge bg-secondary" title="<?php echo $item->language_title; ?>"><?php echo $item->language; ?></span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td class="small d-none d-md-table-cell">
                            <?php echo $this->escape($item->access_level); ?>
                        </td>
                        <?php if ($multilang) : ?>
                            <td class="small d-none d-md-table-cell">
                                <?php if ($item->language == '') : ?>
                                    <?php echo Text::_('COM_MENUS_HOME'); ?>
                                <?php elseif ($item->language == '*') : ?>
                                    <?php echo Text::alt('JALL', 'language'); ?>
                                <?php else : ?>
                                    <?php echo LayoutHelper::render('joomla.content.language', $item); ?>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                        <td class="d-none d-md-table-cell">
                            <?php echo (int) $item->id; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <?php // load the pagination. ?>
            <?php echo $this->pagination->getListFooter(); ?>

        <?php endif; ?>

        <input type="hidden" name="task" value="">
        <input type="hidden" name="boxchecked" value="0">
        <input type="hidden" name="function" value="<?php echo $function; ?>">
        <input type="hidden" name="forcedLanguage" value="<?php echo $app->getInput()->get('forcedLanguage', '', 'cmd'); ?>">
        <?php echo HTMLHelper::_('form.token'); ?>

    </form>
</div>
