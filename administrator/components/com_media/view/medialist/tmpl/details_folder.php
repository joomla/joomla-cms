<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

$user = JFactory::getUser();
$input = JFactory::getApplication()->input;
JHtml::_('bootstrap.tooltip');
?>
<tr>
    <td class="imgTotal">
        <a href="index.php?option=com_media&amp;view=medialist&amp;search=<?php echo $input->get('filter.search')?>&amp;tmpl=component&amp;folder=<?php echo $this->_tmp_folder->path_relative; ?>"
           target="folderframe">
            <i class="icon-folder-2"></i></a>
    </td>
    <td class="description">
        <a href="index.php?option=com_media&amp;view=medialist&amp;search=<?php echo $input->get('filter.search')?>&amp;tmpl=component&amp;folder=<?php echo $this->_tmp_folder->path_relative; ?>"
           target="folderframe"><?php echo $this->_tmp_folder->name; ?></a>
    </td>
    <td>&#160;

    </td>
    <td>&#160;

    </td>
	<td>&#160;

    </td>
    <?php if ($user->authorise('core.delete', 'com_media')): ?>
        <td>
            <a class="delete-item" target="_top"
               href="index.php?option=com_media&amp;task=handler.process&amp;tmpl=index&amp;operation=delete&amp;folder=<?php echo $this->state->get('folder'); ?>&amp;<?php echo JSession::getFormToken(); ?>=1&amp;rm[]=<?php echo $this->_tmp_folder->name; ?>"
               rel="<?php echo $this->_tmp_folder->name; ?>' :: <?php echo $this->_tmp_folder->files + $this->_tmp_folder->folders; ?>"><i
                    class="icon-remove hasTooltip" title="<?php echo JText::_('JACTION_DELETE'); ?>"></i></a>
            <input type="checkbox" name="rm[]" value="<?php echo $this->_tmp_folder->name; ?>"/>
        </td>
    <?php endif; ?>
    <?php if ($user->authorise('core.create', 'com_media')): ?>
        <td>&#160;</td>
    <?php endif; ?>
</tr>
