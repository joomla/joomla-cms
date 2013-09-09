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
?>
<tr>
    <td class="imgTotal">
        <a href="index.php?option=com_media&amp;view=medialist&amp;tmpl=component&amp;search=<?php echo $input->get('filter.search')?>&amp;folder=<?php echo $this->state->get('parent'); ?>"
           target="folderframe">
            <i class="icon-arrow-up"></i></a>
    </td>
    <td class="description">
        <a href="index.php?option=com_media&amp;view=medialist&amp;tmpl=component&amp;search=<?php echo $input->get('filter.search')?>&amp;folder=<?php echo $this->state->get('parent'); ?>"
           target="folderframe">..</a>
    </td>
    <td>&#160;</td>
    <td>&#160;</td>
	<td>&#160;</td>
    <?php if ($user->authorise('core.delete', 'com_media')): ?>
        <td>&#160;</td>
    <?php endif; ?>
    <?php if ($user->authorise('core.create', 'com_media')): ?>
        <td>&#160;</td>
    <?php endif; ?>
</tr>
