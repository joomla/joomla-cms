<?php

/**
 * @package       JED
 *
 * @subpackage    VEL
 *
 * @copyright     (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', 'select');

$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');

// Import CSS
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->useStyle('com_jed.list');
?>

<form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post"
      name="adminForm" id="adminForm">
    <?php echo '<fieldset class="liveitems"><legend>' . Text::_('COM_JED_VEL_LIVEITEMS_LIST_HEADER') . '</legend>' . Text::_('COM_JED_VEL_LIVEITEMS_LIST_BODY') . '</fieldset>'; ?>
    <?php if (!empty($this->filterForm)) {
        echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
    } ?>
    <div class="table-responsive">
        <table class="category table table-striped table-bordered table-hover" id="VelLiveItemsList"
               data-filter="#filter" data-page-size="10">
            <thead>
            <tr>
                <th scope="col" data-toggle="true">
                    <?php echo HTMLHelper::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
                </th>
                <th scope="col">
                    <?php echo HTMLHelper::_('grid.sort', 'COM_JED_GENERAL_PUBLISHED_DATE_LABEL', 'publication_date_sort', $listDirn, $listOrder); ?>
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
            <tbody>
            <?php foreach ($this->items as $i => $item) : ?>
                <tr class="cat-list-row<?php echo $i % 2; ?>">


                    <td headers="categorylist_header_title" class="list-title">

                        <a href="<?php echo Route::_('index.php?option=com_jed&view=velitem&id=' . (int) $item->id); ?>">
                            <?php echo $this->escape($item->title); ?></a>
                    </td>
                    <td headers="categorylist_header_date" class="list-date small">

                        <?php echo $item->publication_date; ?>
                    </td>


                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
    <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>

    <input type="hidden" name="task" value=""/>

    <?php echo HTMLHelper::_('form.token'); ?>

</form>
