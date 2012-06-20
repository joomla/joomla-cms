<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<ul <?php echo $this->folders_id; ?> class="nav nav-list collapse in" id="collapseFolder<?php foreach ($this->folders['children'] as $folder) : echo $i++; endforeach; ?>">
<?php foreach ($this->folders['children'] as $folder) : ?>
	<li id="<?php echo $folder['data']->relative; ?>"><i class="icon-folder-close pull-left" data-toggle="collapse" data-target="#collapseFolder<?php echo $i++; ?>"></i> <a href="index.php?option=com_media&amp;view=mediaList&amp;tmpl=component&amp;folder=<?php echo $folder['data']->relative; ?>" target="folderframe"><?php echo $folder['data']->name; ?></a><?php echo $this->getFolderLevel($folder); ?></li>
<?php endforeach; ?>
</ul>
