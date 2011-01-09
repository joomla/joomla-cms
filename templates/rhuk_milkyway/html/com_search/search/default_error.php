<?php
/**
 * @version		
 * @package		Joomla.Site
 * @subpackage	com_search
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die; ?>

<table class="searchintro<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
	<tr>
		<td colspan="3" >
			<?php echo $this->escape($this->error); ?>
		</td>
	</tr>
</table>
