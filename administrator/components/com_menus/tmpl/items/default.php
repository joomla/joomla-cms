<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns')
    ->useScript('multiselect');

$user      = Factory::getUser();
$app       = Factory::getApplication();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$ordering  = ($listOrder == 'a.lft');
$saveOrder = ($listOrder == 'a.lft' && strtolower($listDirn) == 'asc');
$menuType  = (string) $app->getUserState('com_menus.items.menutype', '');

if ($saveOrder && $menuType && !empty($this->items)) {
    $saveOrderingUrl = 'index.php?option=com_menus&task=items.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
    HTMLHelper::_('draggablelist.draggable');
}

$assoc   = Associations::isEnabled() && $this->state->get('filter.client_id') == 0;

?>
<?php // Set up the filter bar. ?>
<form action="<?php echo Route::_('index.php?option=com_menus&view=items&menutype='); ?>" method="post" name="adminForm"
      id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this, 'options' => ['selectorFieldName' => 'menutype']]); ?>
                <?php if (!empty($this->items)) : ?>
                    <table class="table" id="menuitemList">
                        <caption class="visually-hidden">
                            <?php echo Text::_('COM_MENUS_ITEMS_TABLE_CAPTION'); ?>,
                            <span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
                            <span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
                        </caption>
                        <thead>
                        <tr>
                            <td class="w-1 text-center">
                                <?php echo HTMLHelper::_('grid.checkall'); ?>
                            </td>
                            <?php if ($menuType) : ?>
                                <th scope="col" class="w-1 text-center d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', '', 'a.lft', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-sort'); ?>
                                </th>
                            <?php endif; ?>
                            <th scope="col" class="w-1 text-center">
                                <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
                            </th>
                            <th scope="col" class="title">
                                <?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
                            </th>
                            <th scope="col" class="w-10 d-none d-md-table-cell">
                                <?php echo HTMLHelper::_('searchtools.sort', 'COM_MENUS_HEADING_MENU', 'menutype_title', $listDirn, $listOrder); ?>
                            </th>
                            <?php if ($this->state->get('filter.client_id') == 0) : ?>
                                <th scope="col" class="w-10 text-center d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_MENUS_HEADING_HOME', 'a.home', $listDirn, $listOrder); ?>
                                </th>
                            <?php endif; ?>
                            <?php if ($this->state->get('filter.client_id') == 0) : ?>
                                <th scope="col" class="w-10 d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
                                </th>
                            <?php endif; ?>
                            <?php if ($assoc) : ?>
                                <th scope="col" class="w-10 d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_MENUS_HEADING_ASSOCIATION', 'association', $listDirn, $listOrder); ?>
                                </th>
                            <?php endif; ?>
                            <?php if (($this->state->get('filter.client_id') == 0) && (Multilanguage::isEnabled())) : ?>
                                <th scope="col" class="w-10 d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
                                </th>
                            <?php endif; ?>
                            <th scope="col" class="w-5 d-none d-md-table-cell">
                                <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                            </th>
                        </tr>
                        </thead>
                        <tbody <?php if ($saveOrder && $menuType) :
                            ?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="false"<?php
                               endif; ?>>
                        <?php
                        foreach ($this->items as $i => $item) :
                            $orderkey = array_search($item->id, $this->ordering[$item->parent_id]);
                            $canCreate = $user->authorise('core.create', 'com_menus.menu.' . $item->menutype_id);
                            $canEdit = $user->authorise('core.edit', 'com_menus.menu.' . $item->menutype_id);
                            $canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $user->get('id') || is_null($item->checked_out);
                            $canChange = $user->authorise('core.edit.state', 'com_menus.menu.' . $item->menutype_id) && $canCheckin;

                            // Get the parents of item for sorting
                            if ($item->level > 1) {
                                $parentsStr       = '';
                                $_currentParentId = $item->parent_id;
                                $parentsStr       = ' ' . $_currentParentId;

                                for ($j = 0; $j < $item->level; $j++) {
                                    foreach ($this->ordering as $k => $v) {
                                        $v = implode('-', $v);
                                        $v = '-' . $v . '-';

                                        if (strpos($v, '-' . $_currentParentId . '-') !== false) {
                                            $parentsStr .= ' ' . $k;
                                            $_currentParentId = $k;
                                            break;
                                        }
                                    }
                                }
                            } else {
                                $parentsStr = '';
                            }
                            ?>
                            <tr class="row<?php echo $i % 2; ?>" data-draggable-group="<?php echo $item->parent_id; ?>"
                                data-item-id="<?php echo $item->id; ?>" data-parents="<?php echo $parentsStr; ?>"
                                data-level="<?php echo $item->level; ?>">
                                <td class="text-center">
                                    <?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->title); ?>
                                </td>
                                <?php if ($menuType) : ?>
                                    <td class="text-center d-none d-md-table-cell">
                                        <?php
                                        $iconClass = '';

                                        if (!$canChange) {
                                            $iconClass = ' inactive';
                                        } elseif (!$saveOrder) {
                                            $iconClass = ' inactive" title="' . Text::_('JORDERINGDISABLED');
                                        }
                                        ?>
                                        <span class="sortable-handler<?php echo $iconClass ?>">
                                            <span class="icon-ellipsis-v" aria-hidden="true"></span>
                                        </span>
                                        <?php if ($canChange && $saveOrder) : ?>
                                            <input type="text" class="hidden" name="order[]" size="5"
                                                   value="<?php echo $orderkey + 1; ?>">
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                                <td class="text-center">
                                    <?php if ($item->type === 'component' && !$item->enabled) : ?>
                                        <span class="icon-warning" aria-hidden="true"></span>
                                        <div role="tooltip" id="warning<?php echo $item->id; ?>">
                                            <?php echo Text::_($item->enabled === null ? 'JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND' : 'COM_MENUS_LABEL_DISABLED'); ?>
                                        </div>
                                    <?php else : ?>
                                        <?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'items.', $canChange, 'cb', $item->publish_up, $item->publish_down); ?>
                                    <?php endif; ?>
                                </td>
                                <th scope="row">
                                    <?php $prefix = LayoutHelper::render('joomla.html.treeprefix', ['level' => $item->level]); ?>
                                    <?php echo $prefix; ?>
                                    <?php if ($item->checked_out) : ?>
                                        <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'items.', $canCheckin); ?>
                                    <?php endif; ?>
                                    <?php if ($canEdit && !$item->protected) : ?>
                                        <a href="<?php echo Route::_('index.php?option=com_menus&task=item.edit&id=' . (int) $item->id); ?>"
                                           title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape($item->title); ?>">
                                            <?php echo $this->escape($item->title); ?></a>
                                    <?php else : ?>
                                        <?php echo $this->escape($item->title); ?>
                                    <?php endif; ?>
                                    <?php echo HTMLHelper::_('menus.visibility', $item->params); ?>
                                    <div>
                                        <?php echo $prefix; ?>
                                        <span class="small">
                                            <?php if ($item->type != 'url') : ?>
                                                <?php if (empty($item->note)) : ?>
                                                    <?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
                                                <?php else : ?>
                                                    <?php echo Text::sprintf('JGLOBAL_LIST_ALIAS_NOTE', $this->escape($item->alias), $this->escape($item->note)); ?>
                                                <?php endif; ?>
                                            <?php elseif ($item->type == 'url' && $item->note) : ?>
                                                <?php echo Text::sprintf('JGLOBAL_LIST_NOTE', $this->escape($item->note)); ?>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <div title="<?php echo $this->escape($item->path); ?>">
                                        <?php echo $prefix; ?>
                                        <span class="small"
                                              title="<?php echo isset($item->item_type_desc) ? htmlspecialchars($this->escape($item->item_type_desc), ENT_COMPAT, 'UTF-8') : ''; ?>">
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
                                    <?php echo $this->escape($item->menutype_title ?: ucwords($item->menutype)); ?>
                                </td>
                                <?php if ($this->state->get('filter.client_id') == 0) : ?>
                                    <td class="text-center d-none d-md-table-cell">
                                        <?php if ($item->type == 'component') : ?>
                                            <?php if ($item->language == '*' || $item->home == '0') : ?>
                                                <?php echo HTMLHelper::_('jgrid.isdefault', $item->home, $i, 'items.', ($item->language != '*' || !$item->home) && $canChange && !$item->protected, 'cb', null, 'icon-home', 'icon-circle'); ?>
                                            <?php elseif ($canChange) : ?>
                                                <a href="<?php echo Route::_('index.php?option=com_menus&task=items.unsetDefault&cid[]=' . $item->id . '&' . Session::getFormToken() . '=1'); ?>">
                                                    <?php if ($item->language_image) : ?>
                                                        <?php echo HTMLHelper::_('image', 'mod_languages/' . $item->language_image . '.gif', $item->language_title, ['title' => Text::sprintf('COM_MENUS_GRID_UNSET_LANGUAGE', $item->language_title)], true); ?>
                                                    <?php else : ?>
                                                        <span class="badge bg-secondary"
                                                              title="<?php echo Text::sprintf('COM_MENUS_GRID_UNSET_LANGUAGE', $item->language_title); ?>"><?php echo $item->language; ?></span>
                                                    <?php endif; ?>
                                                </a>
                                            <?php else : ?>
                                                <?php if ($item->language_image) : ?>
                                                    <?php echo HTMLHelper::_('image', 'mod_languages/' . $item->language_image . '.gif', $item->language_title, ['title' => $item->language_title], true); ?>
                                                <?php else : ?>
                                                    <span class="badge bg-secondary"
                                                          title="<?php echo $item->language_title; ?>"><?php echo $item->language; ?></span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                                <?php if ($this->state->get('filter.client_id') == 0) : ?>
                                    <td class="small d-none d-md-table-cell">
                                        <?php echo $this->escape($item->access_level); ?>
                                    </td>
                                <?php endif; ?>
                                <?php if ($assoc) : ?>
                                    <td class="small d-none d-md-table-cell">
                                        <?php if ($item->association) : ?>
                                            <?php echo HTMLHelper::_('menus.association', $item->id); ?>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                                <?php if ($this->state->get('filter.client_id') == 0 && Multilanguage::isEnabled()) : ?>
                                    <td class="small d-none d-md-table-cell">
                                        <?php echo LayoutHelper::render('joomla.content.language', $item); ?>
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

                    <?php // Load the batch processing form if user is allowed ?>
                    <?php if ($user->authorise('core.create', 'com_menus') || $user->authorise('core.edit', 'com_menus')) : ?>
                        <?php echo HTMLHelper::_(
                            'bootstrap.renderModal',
                            'collapseModal',
                            [
                                'title'  => Text::_('COM_MENUS_BATCH_OPTIONS'),
                                'footer' => $this->loadTemplate('batch_footer')
                            ],
                            $this->loadTemplate('batch_body')
                        ); ?>
                    <?php endif; ?>
                <?php endif; ?>

                <input type="hidden" name="task" value="">
                <input type="hidden" name="boxchecked" value="0">
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>
