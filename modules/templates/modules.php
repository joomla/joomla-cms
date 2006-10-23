<?php
/**
 * @version $Id$
 * @package Joomla
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/*
 * Module chrome that wraps the module in a table
 */
function modChrome_table($module, &$params, &$attribs)
{ ?>
	<table cellpadding="0" cellspacing="0" class="moduletable<?php echo $params->get('moduleclass_sfx'); ?>">
	<?php if ($module->showtitle != 0) : ?>
		<tr>
			<th valign="top">
				<?php echo $module->title; ?>
			</th>
		</tr>
	<?php endif; ?>
		<tr>
			<td>
				<?php echo $module->content; ?>
			</td>
		</tr>
		</table>
	<?php
}

/*
 * Module chrome that wraps the tabled module output in a <td> tag of another table
 */
function modChrome_horz($module, &$params, &$attribs)
{ ?>
	<table cellspacing="1" cellpadding="0" border="0" width="100%">
		<tr>
			<td valign="top">
				<?php modChrome_table($module, $params); ?>
			</td>
		</tr>
	</table>
	<?php
}

/*
 * xhtml (divs and font headder tags)
 */
function modChrome_xhtml($module, &$params, &$attribs)
{
	if (!empty ($module->content)) : ?>
		<div class="moduletable<?php echo $params->get('moduleclass_sfx'); ?>">
		<?php if ($module->showtitle != 0) : ?>
			<h3><?php echo $module->title; ?></h3>
		<?php endif; ?>
			<?php echo $module->content; ?>
		</div>
	<?php endif;
}

/*
 * Module chrome that allows for rounded corners by wrapping in nested div tags
 */
function modChrome_rounded($module, &$params, &$attribs)
{ ?>
		<div class="module<?php echo $params->get('moduleclass_sfx'); ?>">
			<div>
				<div>
					<div>
						<?php if ($module->showtitle != 0) : ?>
							<h3><?php echo $module->title; ?></h3>
						<?php endif; ?>
					<?php echo $module->content; ?>
					</div>
				</div>
			</div>
		</div>
	<?php
}
?>