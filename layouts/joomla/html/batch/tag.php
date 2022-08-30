<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>
<label id="batch-tag-lbl" for="batch-tag-id">
    <?php echo Text::_('JLIB_HTML_BATCH_TAG_LABEL'); ?>
</label>
<select name="batch[tag]" class="form-select" id="batch-tag-id">
    <option value=""><?php echo Text::_('JLIB_HTML_BATCH_TAG_NOCHANGE'); ?></option>
    <?php echo HTMLHelper::_('select.options', HTMLHelper::_('tag.tags', array('filter.published' => array(1))), 'value', 'text'); ?>
</select>
