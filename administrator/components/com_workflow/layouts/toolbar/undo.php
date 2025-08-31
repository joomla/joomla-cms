<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

Factory::getApplication()->getDocument()->getWebAssetManager()
    ->useScript('webcomponent.toolbar-button');

?>
<joomla-toolbar-button>
    <button id="undo-workflow" class="btn btn-info action-button" tabindex="0">
        <span class="icon-undo-2 icon-fw" aria-hidden="true"></span>
        <?php echo Text::_('COM_WORKFLOW_UNDO'); ?>
    </button>
</joomla-toolbar-button>


<script>
    document.getElementById('undo-workflow')?.addEventListener('click', () => {
        WorkflowGraph.Event.fire('onClickUndoWorkflow');
    });
</script>

