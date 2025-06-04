<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
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
use Joomla\Component\Content\Site\Helper\RouteHelper;

/** @var \Joomla\Component\Categories\Administrator\View\Categories\HtmlView $this */

$app = Factory::getApplication();

if ($app->isClient('site')) {
    Session::checkToken('get') or die(Text::_('JINVALID_TOKEN'));
}

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('core')
    ->useScript('modal-content-select');

$extension = $this->escape($this->state->get('filter.extension'));
// @todo: Use of Function is deprecated and should be removed in 6.0. It stays only for backward compatibility.
$function  = $app->getInput()->getCmd('function', 'jSelectCategory');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<div class="container-popup">

    <form action="<?php echo Route::_('index.php?option=com_categories&view=categories&layout=modal&tmpl=component&function=' . $function . '&' . Session::getFormToken() . '=1'); ?>" method="post" name="adminForm" id="adminForm">

        <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>

        <?php if (empty($this->items)) : ?>
            <div class="alert alert-info">
                <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
            </div>
        <?php else : ?>
            <table class="table" id="categoryList">
                <caption class="visually-hidden">
                    <?php echo Text::_('COM_CATEGORIES_TABLE_CAPTION'); ?>,
                            <span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
                            <span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
                </caption>
                <thead>
                    <tr>
                        <th scope="col" class="w-1 text-center">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
                        </th>
                        <th scope="col">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
                        </th>
                        <th scope="col" class="w-10 d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'access_level', $listDirn, $listOrder); ?>
                        </th>
                        <th scope="col" class="w-15 d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language_title', $listDirn, $listOrder); ?>
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
                    ?>
                    <?php foreach ($this->items as $i => $item) : ?>
                        <?php
                        $lang = '';
                        if ($item->language && Multilanguage::isEnabled()) {
                            $tag = \strlen($item->language);
                            if ($tag == 5) {
                                $lang = substr($item->language, 0, 2);
                            } elseif ($tag == 6) {
                                $lang = substr($item->language, 0, 3);
                            }
                        }

                        $link     = RouteHelper::getCategoryRoute($item->id, $item->language);
                        $itemHtml = '<a href="' . $this->escape($link) . '"' . ($lang ? ' hreflang="' . $lang . '"' : '') . '>' . $item->title . '</a>';
                        ?>
                        <tr class="row<?php echo $i % 2; ?>">
                            <td class="text-center">
                                <span class="tbody-icon">
                                    <span class="<?php echo $iconStates[$this->escape($item->published)]; ?>" aria-hidden="true"></span>
                                </span>
                            </td>
                            <th scope="row">
                                <?php $attribs = 'data-content-select data-content-type="' . $extension . '.category"'
                                    . ' data-id="' . $item->id . '"'
                                    . ' data-title="' . $this->escape($item->title) . '"'
                                    . ' data-uri="' . $this->escape($link) . '"'
                                    . ' data-language="' . $this->escape($lang) . '"'
                                    . ' data-html="' . $this->escape($itemHtml) . '"';
                                ?>
                                <?php echo LayoutHelper::render('joomla.html.treeprefix', ['level' => $item->level]); ?>
                                <a href="javascript:void(0)" <?php echo $attribs; ?> onclick="if (window.parent && !window.parent.JoomlaExpectingPostMessage) window.parent.<?php echo $this->escape($function); ?>('<?php echo $item->id; ?>', '<?php echo $this->escape(addslashes($item->title)); ?>', null, '<?php echo $this->escape(RouteHelper::getCategoryRoute($item->id, $item->language)); ?>', '<?php echo $this->escape($lang); ?>', null);">
                                    <?php echo $this->escape($item->title); ?></a>
                                <div class="small" title="<?php echo $this->escape($item->path); ?>">
                                    <?php if (empty($item->note)) : ?>
                                        <?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
                                    <?php else : ?>
                                        <?php echo Text::sprintf('JGLOBAL_LIST_ALIAS_NOTE', $this->escape($item->alias), $this->escape($item->note)); ?>
                                    <?php endif; ?>
                                </div>
                            </th>
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

        <?php echo $this->filterForm->renderControlFields(); ?>

    </form>
</div>
