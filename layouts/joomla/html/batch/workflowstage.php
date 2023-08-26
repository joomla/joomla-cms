<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>
<label id="batch-workflowstage-lbl" for="batch-workflowstage-id">
    <?php echo Text::_('JLIB_HTML_BATCH_WORKFLOW_STAGE_LABEL'); ?>
</label>

<?php

$attr = [
    'id'        => 'batch-workflowstage-id',
    'group.label' => 'text',
    'group.items' => null,
    'list.attr' => [
        'class' => 'form-select'
    ]
];

$groups = HTMLHelper::_('workflowstage.existing', ['title' => Text::_('JLIB_HTML_BATCH_WORKFLOW_STAGE_NOCHANGE')]);

echo HTMLHelper::_('select.groupedlist', $groups, 'batch[workflowstage_id]', $attr);
