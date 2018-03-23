<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

$params     = new Registry;
$dispatcher = JEventDispatcher::getInstance();
$dispatcher->trigger('onContentBeforeDisplay', array('com_media.file', &$this->_tmp_video, &$params));
?>

<li class="imgOutline thumbnail height-80 width-80 center">
	<a class="img-preview" href="javascript:ImageManager.populateFields('<?php echo $this->_tmp_video->path_relative; ?>')" title="<?php echo $this->_tmp_video->name; ?>" >
		<div class="imgThumb">
			<div class="imgThumbInside">
			<?php echo JHtml::_('image', $this->_tmp_video->icon_32, $this->_tmp_video->title, null, true); ?>
			</div>
		</div>
		<div class="imgDetails small">
			<?php echo JHtml::_('string.truncate', $this->_tmp_video->name, 10, false); ?>
		</div>
	</a>
</li>
<?php
$dispatcher->trigger('onContentAfterDisplay', array('com_media.file', &$this->_tmp_video, &$params));
