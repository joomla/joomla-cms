<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

if (Factory::getApplication()->isClient('site')) {
    Session::checkToken('get') or die(Text::_('JINVALID_TOKEN'));
}

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('com_modules.admin-modules-modal');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$editor    = Factory::getApplication()->input->get('editor', '', 'cmd');
$link      = 'index.php?option=com_modules&view=modules&layout=modal&tmpl=component&' . Session::getFormToken() . '=1';

if (!empty($editor)) {
    $link .= '&editor=' . $editor;
}
?>
<div class="container-popup">

    <form action="<?php echo Route::_($link); ?>" method="post" name="adminForm" id="adminForm">

        <?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

        <?php if ($this->total > 0) : ?>
        <table class="table" id="moduleList">
            <caption class="visually-hidden">
                <?php echo Text::_('COM_MODULES_TABLE_CAPTION'); ?>,
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
                    <th scope="col" class="w-15 d-none d-md-table-cell">
                        <?php echo HTMLHelper::_('searchtools.sort', 'COM_MODULES_HEADING_POSITION', 'a.position', $listDirn, $listOrder); ?>
                    </th>
                    <th scope="col" class="w-10 d-none d-md-table-cell">
                        <?php echo HTMLHelper::_('searchtools.sort', 'COM_MODULES_HEADING_MODULE', 'name', $listDirn, $listOrder); ?>
                    </th>
                    <th scope="col" class="w-10 d-none d-md-table-cell">
                        <?php echo HTMLHelper::_('searchtools.sort', 'COM_MODULES_HEADING_PAGES', 'pages', $listDirn, $listOrder); ?>
                    </th>
                    <th scope="col" class="w-10 d-none d-md-table-cell">
                        <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'ag.title', $listDirn, $listOrder); ?>
                    </th>
                    <th scope="col" class="w-10 d-none d-md-table-cell">
                        <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'l.title', $listDirn, $listOrder); ?>
                    </th>
                    <th scope="col" class="w-1 d-none d-md-table-cell">
                        <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php
                $iconStates = array(
                    -2 => 'icon-trash',
                    0  => 'icon-times',
                    1  => 'icon-check',
                    2  => 'icon-folder',
                );
                foreach ($this->items as $i => $item) :
                    ?>
                <tr class="row<?php echo $i % 2; ?>">
                    <td class="text-center">
                        <span class="tbody-icon">
                            <span class="<?php echo $iconStates[$this->escape($item->published)]; ?>" aria-hidden="true"></span>
                        </span>
                    </td>
                    <th scope="row" class="has-context">
                        <a class="js-module-insert btn btn-sm btn-success w-100" href="#" data-module="<?php echo $item->id; ?>" data-editor="<?php echo $this->escape($editor); ?>">
                            <?php echo $this->escape($item->title); ?>
                        </a>
                    </th>
                    <td class="small d-none d-md-table-cell">
                        <?php if ($item->position) : ?>
                        <a class="js-position-insert btn btn-sm btn-warning w-100" href="#" data-position="<?php echo $this->escape($item->position); ?>" data-editor="<?php echo $this->escape($editor); ?>"><?php echo $this->escape($item->position); ?></a>
                        <?php else : ?>
                        <span class="btn btn-sm btn-secondary w-100"><?php echo Text::_('JNONE'); ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="small d-none d-md-table-cell">
                        <?php echo $item->name; ?>
                    </td>
                    <td class="small d-none d-md-table-cell">
                        <?php echo $item->pages; ?>
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
        <input type="hidden" name="editor" value="<?php echo $editor; ?>" />
        <?php echo HTMLHelper::_('form.token'); ?>

    </form>
</div>
