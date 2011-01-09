<?php
/**
 * @version		
 * @package		Joomla.Site
 * @subpackage	com_user
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die; ?>
<div class="componentheading">
	<?php echo $this->escape($this->message->title) ; ?>
</div>

<div class="message">
	<?php echo $this->escape($this->message->text) ; ?>
</div>
