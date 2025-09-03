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

$onclickAttr = 'onclick="WorkflowGraph.Event.fire(\'onClickRedoWorkflow\')"';
?>
<joomla-toolbar-button>
    <button id="redo-workflow" class="btn btn-info action-button" <?php echo $onclickAttr ?>>
        <span class="icon-redo icon-fw" aria-hidden="true"></span>
        <?php echo Text::_('COM_WORKFLOW_REDO'); ?>
    </button>
</joomla-toolbar-button>
