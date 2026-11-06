<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var \Joomla\Component\Workflow\Administrator\View\Workflow\HtmlView $this */

echo LayoutHelper::render(
    'upcoming.table',
    ['items' => $this->upcomingTransitions, 'showWorkflow' => false],
    JPATH_ADMINISTRATOR . '/components/com_workflow/layouts'
);
?>
<p>
    <a href="<?php echo Route::_('index.php?option=com_workflow&view=upcoming&extension=' . $this->escape($this->extension) . ($this->section ? '.' . $this->section : '') . '&list[workflow_id]=' . (int) $this->item->id); ?>">
        <?php echo Text::_('COM_WORKFLOW_UPCOMING_VIEW_ALL'); ?>
    </a>
</p>
