<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/** @var \Joomla\Component\Fields\Administrator\View\Fields\HtmlView $this */

if (Factory::getApplication()->isClient('site')) {
    Session::checkToken('get') or die(Text::_('JINVALID_TOKEN'));
}

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('com_fields.admin-fields-modal')->useScript('modal-content-select');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$editor    = Factory::getApplication()->getInput()->get('editor', '', 'cmd');
?>
<div class="container-popup">

    <form action="<?php echo Route::_('index.php?option=com_fields&view=fields&layout=modal&tmpl=component&editor=' . $editor . '&' . Session::getFormToken() . '=1'); ?>" method="post" name="adminForm" id="adminForm">

        <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
        <?php if (empty($this->items)) : ?>
            <div class="alert alert-info">
                <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
            </div>
        <?php else : ?>
            <table class="table" id="fieldList">
                <caption class="visually-hidden">
                    <?php echo Text::_('COM_FIELDS_FIELDS_TABLE_CAPTION'); ?>,
                            <span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
                            <span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
                </caption>
                <thead>
                    <tr>
                        <th scope="col" class="w-1 text-center">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
                        </th>
                        <th scope="col" class="title">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
                        </th>
                        <th scope="col" class="w-15 d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_FIELDS_FIELD_GROUP_LABEL', 'g.title', $listDirn, $listOrder); ?>
                        </th>
                        <th scope="col" class="w-10 d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_FIELDS_FIELD_TYPE_LABEL', 'a.type', $listDirn, $listOrder); ?>
                        </th>
                        <th scope="col" class="w-10 d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
                        </th>
                        <th scope="col" class="w-10 d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'a.language', $listDirn, $listOrder); ?>
                        </th>
                        <th scope="col" class="w-1 d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $iconStates = [
                        -2 => 'icon-trash',
                        0  => 'icon-unpublish',
                        1  => 'icon-publish',
                        2  => 'icon-archive',
                    ];
                    foreach ($this->items as $i => $item) :
                        $attrs = 'data-content-select data-content-type="com_fields.field"'
                            . ' data-id="' . ((int) $item->id) . '"'
                            . ' data-title="' . $this->escape($item->title) . '"'
                            . ' data-name="' . $this->escape($item->name) . '"'
                            . ' data-group="' . ((int) $item->group_id) . '"';

                        $attrs1 = $attrs;
                        $attrs1 .= ' data-html="{field ' . ((int) $item->id) . '}"';
                        $attrs2 = $attrs;
                        $attrs2 .= ' data-html="{fieldgroup ' . ((int) $item->group_id) . '}"';

                        // @TODO: Remove onclick="" after full transition to postMessage()
                        ?>
                    <tr class="row<?php echo $i % 2; ?>">
                        <td class="text-center">
                            <span class="tbody-icon">
                                <span class="<?php echo $iconStates[$this->escape($item->state)]; ?>" aria-hidden="true"></span>
                            </span>
                        </td>
                        <th scope="row" class="has-context">
                            <button type="button" class="btn btn-sm btn-success w-100" <?php echo $attrs1; ?>
                                    onclick="Joomla.fieldIns('<?php echo $this->escape($item->id); ?>', '<?php echo $this->escape($editor); ?>');">
                                <?php echo $this->escape($item->title); ?>
                            </button>
                        </th>
                        <td class="small d-none d-md-table-cell">
                            <button type="button" class="btn btn-sm btn-warning w-100" <?php echo $item->group_id ? $attrs2 : ''; ?>
                                    onclick="Joomla.fieldgroupIns('<?php echo $this->escape($item->group_id); ?>', '<?php echo $this->escape($editor); ?>');">
                                <?php echo $item->group_id ? $this->escape($item->group_title) : Text::_('JNONE'); ?>
                            </button>
                        </td>
                        <td class="small d-none d-md-table-cell">
                            <?php echo $item->type; ?>
                        </td>
                        <td class="small d-none d-md-table-cell">
                            <?php echo $this->escape($item->access_level); ?>
                        </td>
                        <td class="small d-none d-md-table-cell">
                            <?php echo LayoutHelper::render('joomla.content.language', $item); ?>
                        </td>
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
        <?php echo HTMLHelper::_('form.token'); ?>

    </form>
</div>
