<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_media
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 ***************************************************************************************
 * Warning: Some modifications and improved were made by the Community Juuntos for
 * the latinamerican Project Jokte! CMS
 ***************************************************************************************
 */

// No direct access.
defined('_JEXEC') or die;

?>
<?php if (count($this->images) > 0 || count($this->folders) > 0 || count($this->documents) > 0) { ?>
<div class="manager">

		<?php for ($i=0, $n=count($this->folders); $i<$n; $i++) :
			$this->setFolder($i);
			echo $this->loadTemplate('folder');
		endfor; ?>
		<?php if ($this->clave != "normal"):
			for ($i=0, $n=count($this->documents); $i<$n; $i++) :
			  $this->setDocument($i);
			  echo $this->loadTemplate('document');
			endfor; 
		  endif;
		  ?>

        <?php for ($i=0, $n=count($this->images); $i<$n; $i++) :
                $this->setImage($i);
                echo $this->loadTemplate('image');
        endfor; ?>
     
</div>
<?php } else { ?>
	<div id="media-noimages">
		<p><?php echo JText::_('COM_MEDIA_NO_IMAGES_FOUND'); ?></p>
	</div>
<?php } ?>
