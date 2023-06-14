<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

$loginActions = [];
$actions = [];

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns');

?>
<form action="<?php echo Route::_('index.php?option=com_users&view=debuguser&user_id=' . (int) $this->state->get('user_id')); ?>" method="post" name="adminForm" id="adminForm">
    <div id="j-main-container" class="j-main-container">
        <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
        <?php if (empty($this->items)) : ?>
            <div class="alert alert-info">
                <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
            </div>
        <?php else : ?>
            <?php
            // Split the actions table
            foreach ($this->actions as $action) :
                $name = $action[0];
                if (in_array($name, ['core.login.site', 'core.login.admin', 'core.login.api', 'core.login.offline'])) :
                    $loginActions[] = $action;
                else :
                    $actions[] = $action;
                endif;
            endforeach;
            ?>
            <div class="d-flex flex-wrap">
                <?php foreach ($loginActions as $action) :
                    $name  = $action[0];
                    $check = $this->items[0]->checks[$name];
                    if ($check === true) :
                        $class  = 'text-success icon-check';
                        $button = 'btn-success';
                        $text   = Text::_('COM_USERS_DEBUG_EXPLICIT_ALLOW');
                    elseif ($check === false) :
                        $class  = 'text-danger icon-times';
                        $button = 'btn-danger';
                        $text   = Text::_('COM_USERS_DEBUG_EXPLICIT_DENY');
                    elseif ($check === null) :
                        $class  = 'text-danger icon-minus-circle';
                        $button = 'btn-warning';
                        $text   = Text::_('COM_USERS_DEBUG_IMPLICIT_DENY');
                    endif;
                    ?>
                <div class="d-inline p-2">
                    <?php echo Text::_($action[1]); ?>
                    <span class="<?php echo $class; ?>" aria-hidden="true"></span>
                    <span class="visually-hidden"><?php echo Text::_($text); ?></span>
                </div>
                <?php endforeach; ?>
            </div>

            <table class="table">
                <caption class="visually-hidden">
                    <?php echo Text::_('COM_USERS_DEBUG_USER_TABLE_CAPTION'); ?>,
                            <span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
                            <span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
                </caption>
                <thead>
                    <tr>
                        <th scope="col">
                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_USERS_HEADING_ASSET_TITLE', 'a.title', $listDirn, $listOrder); ?>
                        </th>
                        <th scope="col">
                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_USERS_HEADING_ASSET_NAME', 'a.name', $listDirn, $listOrder); ?>
                        </th>
                        <?php foreach ($actions as $key => $action) : ?>
                        <th scope="col" class="w-6 text-center">
                            <?php echo Text::_($action[1]); ?>
                        </th>
                        <?php endforeach; ?>
                        <th scope="col" class="w-6">
                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_USERS_HEADING_LFT', 'a.lft', $listDirn, $listOrder); ?>
                        </th>
                        <th scope="col" class="w-3">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->items as $i => $item) :?>
                        <tr class="row0" scope="row">
                            <td>
                                <?php echo $this->escape(Text::_($item->title)); ?>
                            </td>
                            <td>
                                <?php echo LayoutHelper::render('joomla.html.treeprefix', ['level' => $item->level + 1]) . $this->escape($item->name); ?>
                            </td>
                            <?php foreach ($actions as $action) : ?>
                                <?php
                                $name  = $action[0];
                                $check = $item->checks[$name];
                                if ($check === true) :
                                    $class  = 'text-success icon-check';
                                    $button = 'btn-success';
                                    $text   = Text::_('COM_USERS_DEBUG_EXPLICIT_ALLOW');
                                elseif ($check === false) :
                                    $class  = 'text-danger icon-times';
                                    $button = 'btn-danger';
                                    $text   = Text::_('COM_USERS_DEBUG_EXPLICIT_DENY');
                                elseif ($check === null) :
                                    $class  = 'text-danger icon-minus-circle';
                                    $button = 'btn-warning';
                                    $text   = Text::_('COM_USERS_DEBUG_IMPLICIT_DENY');
                                else :
                                    $class  = '';
                                    $button = '';
                                    $text   = '';
                                endif;
                                ?>
                            <td class="text-center">
                                <span class="<?php echo $class; ?>" aria-hidden="true"></span>
                                <span class="visually-hidden"> <?php echo $text; ?></span>
                            </td>
                            <?php endforeach; ?>
                            <td>
                                <?php echo (int) $item->lft; ?>
                                - <?php echo (int) $item->rgt; ?>
                            </td>
                            <td>
                                <?php echo (int) $item->id; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="legend">
                <span class="text-danger icon-minus-circle" aria-hidden="true"></span>&nbsp;<?php echo Text::_('COM_USERS_DEBUG_IMPLICIT_DENY'); ?>&nbsp;
                <span class="text-success icon-check" aria-hidden="true"></span>&nbsp;<?php echo Text::_('COM_USERS_DEBUG_EXPLICIT_ALLOW'); ?>&nbsp;
                <span class="text-danger icon-times" aria-hidden="true">&nbsp;</span><?php echo Text::_('COM_USERS_DEBUG_EXPLICIT_DENY'); ?>
            </div>

            <?php // load the pagination. ?>
            <?php echo $this->pagination->getListFooter(); ?>

            <input type="hidden" name="task" value="">
            <input type="hidden" name="boxchecked" value="0">
            <?php echo HTMLHelper::_('form.token'); ?>
        <?php endif; ?>
    </div>
</form>
