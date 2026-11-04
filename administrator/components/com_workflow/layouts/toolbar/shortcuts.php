<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

Factory::getApplication()->getDocument()->getWebAssetManager()
    ->useScript('webcomponent.toolbar-button');

$shortcutsPopupOptions = json_encode([
    'src'             => '#shortcuts-popup-content',
    'width'           => '800px',
    'height'          => 'fit-content',
    'textHeader'      => Text::_('COM_WORKFLOW_GRAPH_SHORTCUTS_TITLE'),
    'preferredParent' => 'body',
]);
?>
<joomla-toolbar-button>
    <button
        class="btn btn-info action-button"
        data-joomla-dialog="<?php echo htmlspecialchars($shortcutsPopupOptions, ENT_QUOTES, 'UTF-8'); ?>"
    >
        <span class="fa fa-keyboard" aria-hidden="true"></span>
        <?php echo Text::_('COM_WORKFLOW_GRAPH_SHORTCUTS'); ?>
    </button>
</joomla-toolbar-button>
