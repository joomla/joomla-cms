<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

/** @var \Joomla\Component\Banners\Administrator\View\Clients\HtmlView $this */

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('table.columns')
    ->useScript('multiselect');

$purchaseTypes = [
    '1' => 'UNLIMITED',
    '2' => 'YEARLY',
    '3' => 'MONTHLY',
    '4' => 'WEEKLY',
    '5' => 'DAILY',
];

$user       = $this->getCurrentUser();
$userId     = $user->id;
$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
$params     = $this->state->get('params') ?? new Registry();
?>
<form action="<?php echo Route::_('index.php?option=com_banners&view=clients'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php
                // Search tools bar
                echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]);
                ?>
                <?php if (empty($this->items)) : ?>
                    <div class="alert alert-info">
                        <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                        <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                    </div>
                <?php else : ?>
                    <table class="table">
                        <caption class="visually-hidden">
                            <?php echo Text::_('COM_BANNERS_CLIENTS_TABLE_CAPTION'); ?>,
                            <span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
                            <span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
                        </caption>
                        <thead>
                            <tr>
                                <td class="w-1 text-center">
                                    <?php echo HTMLHelper::_('grid.checkall'); ?>
                                </td>
                                <th scope="col" class="w-1 text-center">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_BANNERS_HEADING_CLIENT', 'a.name', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="w-15 d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_BANNERS_HEADING_CONTACT', 'a.contact', $listDirn, $listOrder); ?>
                                </th>
                                <?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_published')) : ?>
                                    <th scope="col" class="w-10 text-center d-none d-md-table-cell">
                                        <span class="icon-check" aria-hidden="true"></span>
                                        <span class="d-none d-lg-inline"><?php echo Text::_('COM_BANNERS_COUNT_PUBLISHED_ITEMS'); ?></span>
                                    </th>
                                <?php endif; ?>
                                <?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_unpublished')) : ?>
                                    <th scope="col" class="w-10 text-center d-none d-md-table-cell">
                                        <span class="icon-times" aria-hidden="true"></span>
                                        <span class="d-none d-lg-inline"><?php echo Text::_('COM_BANNERS_COUNT_UNPUBLISHED_ITEMS'); ?></span>
                                    </th>
                                <?php endif; ?>
                                <?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_archived')) : ?>
                                    <th scope="col" class="w-10 text-center d-none d-md-table-cell">
                                        <span class="icon-folder icon-fw" aria-hidden="true"></span>
                                        <span class="d-none d-lg-inline"><?php echo Text::_('COM_BANNERS_COUNT_ARCHIVED_ITEMS'); ?></span>
                                    </th>
                                <?php endif; ?>
                                <?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_trashed')) : ?>
                                    <th scope="col" class="w-10 text-center d-none d-md-table-cell">
                                        <span class="icon-trash" aria-hidden="true"></span>
                                        <span class="d-none d-lg-inline"><?php echo Text::_('COM_BANNERS_COUNT_TRASHED_ITEMS'); ?></span>
                                    </th>
                                <?php endif; ?>
                                <th scope="col" class="w-10 d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_BANNERS_HEADING_PURCHASETYPE', 'a.purchase_type', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="w-3 d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($this->items as $i => $item) :
                                $canCreate  = $user->authorise('core.create', 'com_banners');
                                $canEdit    = $user->authorise('core.edit', 'com_banners');
                                $canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $user->id || is_null($item->checked_out);
                                $canChange  = $user->authorise('core.edit.state', 'com_banners') && $canCheckin;
                                ?>
                                <tr class="row<?php echo $i % 2; ?>">
                                    <td class="text-center">
                                        <?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->name); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'clients.', $canChange); ?>
                                    </td>
                                    <th scope="row" class="has-context">
                                        <div>
                                            <?php if ($item->checked_out) : ?>
                                                <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'clients.', $canCheckin); ?>
                                            <?php endif; ?>
                                            <?php if ($canEdit) : ?>
                                                <a href="<?php echo Route::_('index.php?option=com_banners&task=client.edit&id=' . (int) $item->id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape($item->name); ?>">
                                                    <?php echo $this->escape($item->name); ?></a>
                                            <?php else : ?>
                                                <?php echo $this->escape($item->name); ?>
                                            <?php endif; ?>
                                        </div>
                                    </th>
                                    <td class="small d-none d-md-table-cell">
                                        <?php echo $item->contact; ?>
                                    </td>
                                    <td class="text-center btns d-none d-md-table-cell itemnumber">
                                        <a class="btn <?php echo ($item->count_published > 0) ? 'btn-success' : 'btn-secondary'; ?>" href="<?php echo Route::_('index.php?option=com_banners&view=banners&filter[client_id]=' . (int) $item->id . '&filter[published]=1'); ?>"
                                        aria-describedby="tip-publish<?php echo $i; ?>">
                                            <?php echo $item->count_published; ?>
                                        </a>
                                        <div role="tooltip" id="tip-publish<?php echo $i; ?>">
                                            <?php echo Text::_('COM_BANNERS_COUNT_PUBLISHED_ITEMS'); ?>
                                        </div>
                                    </td>
                                    <td class="text-center btns d-none d-md-table-cell itemnumber">
                                        <a class="btn <?php echo ($item->count_unpublished > 0) ? 'btn-danger' : 'btn-secondary'; ?>" href="<?php echo Route::_('index.php?option=com_banners&view=banners&filter[client_id]=' . (int) $item->id . '&filter[published]=0'); ?>"
                                        aria-describedby="tip-unpublish<?php echo $i; ?>">
                                            <?php echo $item->count_unpublished; ?>
                                        </a>
                                        <div role="tooltip" id="tip-unpublish<?php echo $i; ?>">
                                            <?php echo Text::_('COM_BANNERS_COUNT_UNPUBLISHED_ITEMS'); ?>
                                        </div>
                                    </td>
                                    <td class="text-center btns d-none d-md-table-cell itemnumber">
                                        <a class="btn <?php echo ($item->count_archived > 0) ? 'btn-primary' : 'btn-secondary'; ?>" href="<?php echo Route::_('index.php?option=com_banners&view=banners&filter[client_id]=' . (int) $item->id . '&filter[published]=2'); ?>"
                                        aria-describedby="tip-archived<?php echo $i; ?>">
                                            <?php echo $item->count_archived; ?>
                                        </a>
                                        <div role="tooltip" id="tip-archived<?php echo $i; ?>">
                                            <?php echo Text::_('COM_BANNERS_COUNT_ARCHIVED_ITEMS'); ?>
                                        </div>
                                    </td>
                                    <td class="text-center btns d-none d-md-table-cell itemnumber">
                                        <a class="btn <?php echo ($item->count_trashed > 0) ? 'btn-dark' : 'btn-secondary'; ?>" href="<?php echo Route::_('index.php?option=com_banners&view=banners&filter[client_id]=' . (int) $item->id . '&filter[published]=-2'); ?>"
                                        aria-describedby="tip-trashed<?php echo $i; ?>">
                                            <?php echo $item->count_trashed; ?>
                                        </a>
                                        <div role="tooltip" id="tip-trashed<?php echo $i; ?>">
                                            <?php echo Text::_('COM_BANNERS_COUNT_TRASHED_ITEMS'); ?>
                                        </div>
                                    </td>
                                    <td class="small d-none d-md-table-cell">
                                        <?php if ($item->purchase_type < 0) : ?>
                                            <?php echo Text::sprintf('COM_BANNERS_DEFAULT', Text::_('COM_BANNERS_FIELD_VALUE_' . $purchaseTypes[$params->get('purchase_type')])); ?>
                                        <?php else : ?>
                                            <?php echo Text::_('COM_BANNERS_FIELD_VALUE_' . $purchaseTypes[$item->purchase_type]); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        <?php echo $item->id; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php // Load the pagination. ?>
                    <?php echo $this->pagination->getListFooter(); ?>

                <?php endif; ?>

                <input type="hidden" name="task" value="">
                <input type="hidden" name="boxchecked" value="0">
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>
