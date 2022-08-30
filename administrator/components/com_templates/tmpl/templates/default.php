<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns')
    ->useScript('multiselect');

$user      = Factory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>

<form action="<?php echo Route::_('index.php?option=com_templates&view=templates'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this, 'options' => array('selectorFieldName' => 'client_id'))); ?>
                <?php if ($this->total > 0) : ?>
                    <table class="table" id="templateList">
                        <caption class="visually-hidden">
                            <?php echo Text::_('COM_TEMPLATES_TEMPLATES_TABLE_CAPTION'); ?>,
                            <span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
                            <span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
                        </caption>
                        <thead>
                            <tr>
                                <th scope="col" class="w-20 col1template d-none d-md-table-cell">
                                    <?php echo Text::_('COM_TEMPLATES_HEADING_IMAGE'); ?>
                                </th>
                                <th scope="col" class="w-30">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_TEMPLATES_HEADING_TEMPLATE', 'a.element', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="w-10 d-none d-md-table-cell text-center">
                                    <?php echo Text::_('JVERSION'); ?>
                                </th>
                                <th scope="col" class="w-10 d-none d-md-table-cell text-center">
                                    <?php echo Text::_('JDATE'); ?>
                                </th>
                                <th scope="col" class="w-25 d-none d-md-table-cell text-center">
                                    <?php echo Text::_('JAUTHOR'); ?>
                                </th>
                                <?php if ($this->pluginState) : ?>
                                    <th scope="col" class="w-10 d-none d-md-table-cell text-center">
                                        <?php echo Text::_('COM_TEMPLATES_OVERRIDES'); ?>
                                    </th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($this->items as $i => $item) : ?>
                            <tr class="row<?php echo $i % 2; ?>">
                                <td class="text-center d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('templates.thumb', $item); ?>
                                    <?php echo HTMLHelper::_('templates.thumbModal', $item); ?>
                                </td>
                                <th scope="row" class="template-name">
                                    <a href="<?php echo Route::_('index.php?option=com_templates&view=template&id=' . (int) $item->extension_id . '&file=' . $this->file); ?>">
                                        <?php echo Text::sprintf('COM_TEMPLATES_TEMPLATE_DETAILS', ucfirst($item->name)); ?></a>
                                    <div>
                                        <?php if ($this->preview) : ?>
                                            <?php $client = (int) $item->client_id === 1 ? 'administrator' : 'site'; ?>
                                            <a href="<?php echo Route::link($client, 'index.php?tp=1&template=' . $item->element); ?>" target="_blank" aria-labelledby="preview-<?php echo $item->extension_id; ?>">
                                                <?php echo Text::_('COM_TEMPLATES_TEMPLATE_PREVIEW'); ?>
                                            </a>
                                            <div role="tooltip" id="preview-<?php echo $item->extension_id; ?>"><?php echo Text::sprintf('COM_TEMPLATES_TEMPLATE_NEW_PREVIEW', $item->name); ?></div>
                                        <?php else : ?>
                                            <?php echo Text::_('COM_TEMPLATES_TEMPLATE_NO_PREVIEW'); ?>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (isset($item->xmldata->inheritable) && $item->xmldata->inheritable) : ?>
                                        <div class="badge bg-primary">
                                            <span class="fas fa-link text-light" aria-hidden="true"></span>
                                            <?php echo Text::_('COM_TEMPLATES_TEMPLATE_IS_PARENT'); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (isset($item->xmldata->parent) && (string) $item->xmldata->parent !== '') : ?>
                                        <div class="badge bg-info text-light">
                                            <span class="fas fa-clone text-light" aria-hidden="true"></span>
                                            <?php echo Text::sprintf('COM_TEMPLATES_TEMPLATE_IS_CHILD_OF', (string) $item->xmldata->parent); ?>
                                        </div>
                                    <?php endif; ?>
                                </th>
                                <td class="small d-none d-md-table-cell text-center">
                                    <?php echo $this->escape($item->xmldata->get('version')); ?>
                                </td>
                                <td class="small d-none d-md-table-cell text-center">
                                    <?php echo $this->escape($item->xmldata->get('creationDate')); ?>
                                </td>
                                <td class="d-none d-md-table-cell text-center">
                                    <?php if ($author = $item->xmldata->get('author')) : ?>
                                        <div><?php echo $this->escape($author); ?></div>
                                    <?php else : ?>
                                        &mdash;
                                    <?php endif; ?>
                                    <?php if ($email = $item->xmldata->get('authorEmail')) : ?>
                                        <div><?php echo $this->escape($email); ?></div>
                                    <?php endif; ?>
                                    <?php if ($url = $item->xmldata->get('authorUrl')) : ?>
                                        <div><a href="<?php echo $this->escape($url); ?>"><?php echo $this->escape($url); ?></a></div>
                                    <?php endif; ?>
                                </td>
                                <?php if ($this->pluginState) : ?>
                                    <td class="d-none d-md-table-cell text-center">
                                        <?php if (!empty($item->updated)) : ?>
                                            <span class="badge bg-warning text-dark"><?php echo Text::plural('COM_TEMPLATES_N_CONFLICT', $item->updated); ?></span>
                                        <?php else : ?>
                                            <span class="badge bg-success"><?php echo Text::_('COM_TEMPLATES_UPTODATE'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
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
            </div>
        </div>
    </div>
</form>
