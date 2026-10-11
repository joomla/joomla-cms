<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var \Joomla\Component\Workflow\Administrator\View\Upcoming\HtmlView $this */
?>
<form action="<?php echo Route::_('index.php?option=com_workflow&view=upcoming&extension=' . $this->escape((string) $this->state->get('filter.extension'))); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
                <?php
                echo LayoutHelper::render(
                    'upcoming.table',
                    ['items' => $this->items, 'showWorkflow' => true],
                    JPATH_ADMINISTRATOR . '/components/com_workflow/layouts'
                );
                ?>
                <?php echo $this->pagination->getListFooter(); ?>
            </div>
        </div>
    </div>
    <input type="hidden" name="task" value="">
    <?php // Set by the pagination links, which do document.adminForm.limitstart.value = n.
    ?>
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
