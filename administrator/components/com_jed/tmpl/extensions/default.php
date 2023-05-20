<?php

/**
 * @package    JED
 *
 * @copyright  (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/* OLD CC GENERATED */
/*
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/src/Helper/');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');

// Import CSS
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
//$wa->useStyle('com_jed.admin')
  //  ->useScript('com_jed.admin');

$user      = JedHelper::getUser();
$userId    = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$canOrder  = $user->authorise('core.edit.state', 'com_jed');
$saveOrder = $listOrder == 'a.ordering';

if ($saveOrder)
{
    $saveOrderingUrl = 'index.php?option=com_jed&task=extensions.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
    HTMLHelper::_('draggablelist.draggable');
}

?>

<form action="<?php echo Route::_('index.php?option=com_jed&view=extensions'); ?>" method="post"
      name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
            <?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

                <div class="clearfix"></div>
                <table class="table table-striped" id="extensionList">
                    <thead>
                    <tr>
                        <th width="1%" >
                            <input type="checkbox" name="checkall-toggle" value=""
                                   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
                        </th>
                        <?php if (isset($this->items[0]->ordering)): ?>
                        <th width="1%" class="nowrap center hidden-phone">
                            <?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
                        </th>
                        <?php endif; ?>

                        <th class='left'>
                            <?php echo HTMLHelper::_('searchtools.sort',  'JGLOBAL_FIELD_ID_LABEL', 'a.id', $listDirn, $listOrder); ?>
                        </th>
                        <th class='left'>
                            <?php echo HTMLHelper::_('searchtools.sort',  'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
                        </th>
                        <th class='left'>
                            <?php echo HTMLHelper::_('searchtools.sort',  'JFIELD_ALIAS_LABEL', 'a.alias', $listDirn, $listOrder); ?>
                        </th>
                        <th class='left'>
                            <?php echo HTMLHelper::_('searchtools.sort',  'COM_JED_EXTENSIONS_JOOMLA_VERSIONS', 'a.joomla_versions', $listDirn, $listOrder); ?>
                        </th>
                        <th class='left'>
                            <?php echo HTMLHelper::_('searchtools.sort',  'COM_JED_EXTENSIONS_POPULAR', 'a.popular', $listDirn, $listOrder); ?>
                        </th>
                        <th class='left'>
                            <?php echo HTMLHelper::_('searchtools.sort',  'COM_JED_EXTENSIONS_REQUIRES_REGISTRATION', 'a.requires_registration', $listDirn, $listOrder); ?>
                        </th>
                        <th class='left'>
                            <?php echo HTMLHelper::_('searchtools.sort',  'COM_JED_EXTENSIONS_GPL_LICENSE_TYPE', 'a.gpl_license_type', $listDirn, $listOrder); ?>
                        </th>
                        <th class='left'>
                            <?php echo HTMLHelper::_('searchtools.sort',  'COM_JED_EXTENSIONS_CAN_UPDATE', 'a.can_update', $listDirn, $listOrder); ?>
                        </th>
                        <th class='left'>
                            <?php echo HTMLHelper::_('searchtools.sort',  'COM_JED_EXTENSIONS_INCLUDES', 'a.includes', $listDirn, $listOrder); ?>
                        </th>
                        <th class='left'>
                            <?php echo HTMLHelper::_('searchtools.sort',  'COM_JED_EXTENSIONS_PRIMARY_CATEGORY_ID', 'a.primary_category_id', $listDirn, $listOrder); ?>
                        </th>
                        <th class='left'>
                            <?php echo HTMLHelper::_('searchtools.sort',  'COM_JED_EXTENSIONS_APPROVED', 'a.approved', $listDirn, $listOrder); ?>
                        </th>
                        <th class='left'>
                            <?php echo HTMLHelper::_('searchtools.sort',  'COM_JED_EXTENSIONS_APPROVED_TIME', 'a.approved_time', $listDirn, $listOrder); ?>
                        </th>
                        <th class='left'>
                            <?php echo HTMLHelper::_('searchtools.sort',  'JPUBLISHED', 'a.published', $listDirn, $listOrder); ?>
                        </th>
                        <th width="5%" class="nowrap center">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
                        </th>
                    </tr>
                    </thead>
                    <tfoot>
                    <tr>
                        <td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
                            <?php echo $this->pagination->getListFooter(); ?>
                        </td>
                    </tr>
                    </tfoot>
                    <tbody <?php if ($saveOrder) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" <?php endif; ?>>
                    <?php foreach ($this->items as $i => $item) :
                        $ordering   = ($listOrder == 'a.ordering');
                        $canCreate  = $user->authorise('core.create', 'com_jed');
                        $canEdit    = $user->authorise('core.edit', 'com_jed');
                        $canCheckin = $user->authorise('core.manage', 'com_jed');
                        $canChange  = $user->authorise('core.edit.state', 'com_jed');
                        ?>
                        <tr class="row<?php echo $i % 2; ?>" data-draggable-group='1' data-transition>
                            <td >
                                <?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                            </td>

                            <?php if (isset($this->items[0]->ordering)) : ?>
                            <td class="order nowrap center hidden-phone">
                            <?php
                                $iconClass = '';
                                if (!$canChange)
                                {
                                    $iconClass = ' inactive';
                                }
                                elseif (!$saveOrder)
                                {
                                    $iconClass = ' inactive" title="' . Text::_('JORDERINGDISABLED');
                                }
                            ?>
                            <span class="sortable-handler<?php echo $iconClass ?>">
                                <span class="icon-ellipsis-v" aria-hidden="true"></span>
                            </span>
                            <?php if ($canChange && $saveOrder) : ?>
                                <input type="text" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order hidden">
                            <?php endif; ?>
                            </td>
                            <?php endif; ?>

                            <td>
                                <?php echo $item->id; ?>
                            </td>
                            <td>
                                <?php if (isset($item->checked_out) && $item->checked_out && ($canEdit || $canChange)) : ?>
                                    <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'extensions.', $canCheckin); ?>
                                <?php endif; ?>
                                <?php if ($canEdit) : ?>
                                    <a href="<?php echo Route::_('index.php?option=com_jed&task=extension.edit&id='.(int) $item->id); ?>">
                                        <?php echo $this->escape($item->title); ?>
                                    </a>
                                <?php else : ?>
                                    <?php echo $this->escape($item->title); ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo $item->alias; ?>
                            </td>
                            <td>
                                <?php echo $item->joomla_versions; ?>
                            </td>
                            <td>
                                <?php echo $item->popular; ?>
                            </td>
                            <td>
                                <?php echo $item->requires_registration; ?>
                            </td>
                            <td>
                                <?php echo $item->gpl_license_type; ?>
                            </td>
                            <td>
                                <?php echo $item->can_update; ?>
                            </td>
                            <td>
                                <?php echo $item->includes; ?>
                            </td>
                            <td>
                                <?php echo $item->primary_category_id; ?>
                            </td>
                            <td>
                                <?php echo $item->approved; ?>
                            </td>
                            <td>
                                <?php echo $item->approved_time; ?>
                            </td>
                            <td>
                                <?php echo $item->published; ?>
                            </td>
                            <td>
                                <?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'extensions.', $canChange, 'cb'); ?>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <input type="hidden" name="task" value=""/>
                <input type="hidden" name="boxchecked" value="0"/>
                <input type="hidden" name="list[fullorder]" value="<?php echo $listOrder; ?> <?php echo $listDirn; ?>"/>
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>
*/
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Jed\Component\Jed\Administrator\View\Extensions\HtmlView;
use Jed\Component\Jed\Administrator\Helper\JedHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/** @var HtmlView $this */

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
try {
    $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
} catch (Exception $e) {
}
$wa->getRegistry()
    ->addExtensionRegistryFile('com_jed');
$wa->usePreset('com_jed.autoComplete')
    ->addInlineScript(<<<JS
    window.addEventListener('DOMContentLoaded', () => {
        jed.filterDeveloperAutocomplete();
    });
JS
    );

$user      = JedHelper::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<form id="adminForm" action="<?php echo Route::_('index.php?option=com_jed&view=extensions'); ?>" method="post" name="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
                <?php if (empty($this->items)) : ?>
                    <div class="alert alert-info">
                        <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                        <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                    </div>
                <?php else : ?>
                <table class="table itemList" id="extensionList">
                    <caption class="visually-hidden">
                        <?php echo Text::_('COM_JED_EXTENSIONS_TABLE_CAPTION'); ?>,
                        <span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
                        <span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
                    </caption>
                    <thead>
                    <tr>
                        <td class="w-1 ">
                            <?php echo HTMLHelper::_('grid.checkall'); ?>
                        </td>
                        <td scope="col" class="w-1  d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JPUBLISHED', 'extensions.published', $listDirn, $listOrder); ?>
                        </td>
                        <td scope="col" class="w-1  d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_JED_EXTENSIONS_APPROVED', 'extensions.approved', $listDirn, $listOrder); ?>
                        </td>
                        <td scope="col" class="w-20 d-none d-md-table-cell">
                        <?php echo HTMLHelper::_('searchtools.sort', 'COM_JED_EXTENSIONS_TITLE', 'extensions.title', $listDirn, $listOrder); ?>
                        </td>
                        <td scope="col" class="w-10 d-none d-md-table-cell ">
                        <?php echo HTMLHelper::_('searchtools.sort', 'COM_JED_EXTENSIONS_CATEGORY', 'categories.title', $listDirn, $listOrder); ?>
                        </td>
                        <td scope="col" class="w-10 d-none d-md-table-cell ">
                        <?php echo HTMLHelper::_('searchtools.sort', 'COM_JED_EXTENSIONS_LAST_UPDATED', 'extensions.modified_on', $listDirn, $listOrder); ?>
                        </td>
                        <td scope="col" class="w-10 d-none d-md-table-cell ">
                        <?php echo HTMLHelper::_('searchtools.sort', 'COM_JED_EXTENSIONS_DATE_ADDED', 'extensions.created_on', $listDirn, $listOrder); ?>
                        </td>
                        <td scope="col" class="w-10 d-none d-md-table-cell ">
                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_JED_EXTENSIONS_DEVELOPER', 'users.name', $listDirn, $listOrder); ?>
                        </td>
                        <td scope="col" class="w-10 d-none d-md-table-cell ">
                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_JED_EXTENSIONS_TYPE', 'extensions.type', $listDirn, $listOrder); ?>
                        </td>
                        <td scope="col" class="w-10 d-none d-md-table-cell ">
                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_JED_EXTENSIONS_REVIEWCOUNT', 'extensions.reviewcount', $listDirn, $listOrder); ?>
                        </td>
                        <td scope="col" class="w-3 d-none d-lg-table-cell">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'extensions.id', $listDirn, $listOrder); ?>
                        </td>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($this->items as $i => $item) :
                        $ordering = ($listOrder === 'extension.id');
                        $canCreate = $user->authorise('core.create', 'com_jed.extension.' . $item->id);
                        $canEdit = $user->authorise('core.edit', 'com_jed.extension.' . $item->id);
                        $canCheckin = $user->authorise(
                            'core.manage',
                            'com_checkin'
                        ) || $item->checked_out === $userId || $item->checked_out === 0;
                        $canEditOwn = $user->authorise(
                            'core.edit.own',
                            'com_jed.extension.' . $item->id
                        ) && $item->created_by === $userId;
                        $canChange = $user->authorise('core.edit.state', 'com_jed.extension.' . $item->id) && $canCheckin;
                        ?>
                        <tr>
                            <td>
                                <?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                            </td>
                            <td class="center" width="50">
                                <?php
                                switch ($item->published) {
                                    // Rejected
                                    case '-1':
                                        $icon = 'unpublish';
                                        break;
                                    // Approved
                                    case '1':
                                        $icon = 'publish';
                                        break;
                                    // Awaiting response
                                    case '2':
                                        $icon = 'expired';
                                        break;
                                    // Pending
                                    case '0':
                                    default:
                                        $icon = 'pending';
                                        break;
                                }
                                echo '<span class="icon-' . $icon . '" aria-hidden="true"></span>';
                                ?>
                            </td>
                            <td>
                                <?php
                                switch ($item->approved) {
                                    // Rejected
                                    case '-1':
                                        $icon = 'unpublish';
                                        break;
                                    // Approved
                                    case '1':
                                        $icon = 'publish';
                                        break;
                                    // Awaiting response
                                    case '2':
                                        $icon = 'expired';
                                        break;
                                    // Pending
                                    case '0':
                                    default:
                                        $icon = 'pending';
                                        break;
                                }
                                echo '<span class="icon-' . $icon . '" aria-hidden="true"></span>';
                                ?>
                            </td>
                            <td>
                                <div class="pull-left break-word">
                                    <?php if ($item->checked_out) : ?>
                                        <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'extensions.', $canCheckin); ?>
                                    <?php endif; ?>
                                    <?php if ($canEdit) : ?>
                                        <?php echo HTMLHelper::_('link', 'index.php?option=com_jed&task=extension.edit&id=' . $item->id, $item->title); ?>
                                    <?php else : ?>
                                        <?php echo $this->escape($item->title); ?>
                                    <?php endif; ?>
                                    <span class="small break-word">
                                        <?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <?php echo $item->category; ?>
                            </td>
                            <td>
                                <?php
                                if (!is_null($item->modified_on)) {
                                    echo HTMLHelper::_(
                                        'date',
                                        $item->modified_on,
                                        Text::_('COM_JED_DATETIME_FORMAT')
                                    );
                                }
                                ?>
                            </td>
                            <td>
                                
                                <?php echo HTMLHelper::_('date', $item->created_on, Text::_('COM_JED_DATETIME_FORMAT')); ?>
                            </td>
                            <td>
                                <?php echo $item->developer; ?>
                            </td>
                            <td>
                                <?php echo  $item->type; ?>
                            </td>
                            <td>
                                <?php echo $item->reviewCount; ?>
                            </td>
                            <td>
                                <?php echo $item->id; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                    <?php echo $this->pagination->getListFooter(); ?>
                <?php endif; ?>
                <input type="hidden" name="task" value=""/>
                <input type="hidden" name="boxchecked" value="0"/>
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>
