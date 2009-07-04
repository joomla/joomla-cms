<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_admin
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Add specific helper files for html generation
JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers'.DS.'html');
// Load switcher behavior
JHtml::_('behavior.switcher');
?>

<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm">

	<div id="config-document">
		<div id="page-site">
			<table class="noshow">
				<tr>
					<td>
						<?php echo $this->loadTemplate('system'); ?>
					</td>
				</tr>
			</table>
		</div>

		<div id="page-phpsettings">
			<table class="noshow">
				<tr>
					<td>
						<?php echo $this->loadTemplate('phpsettings'); ?>
					</td>
				</tr>
			</table>
		</div>

		<div id="page-config">
			<table class="noshow">
				<tr>
					<td>
						<?php echo $this->loadTemplate('config'); ?>
					</td>
				</tr>
			</table>
		</div>

		<div id="page-directory">
			<table class="noshow">
				<tr>
					<td>
						<?php echo $this->loadTemplate('directory'); ?>
					</td>
				</tr>
			</table>
		</div>

		<div id="page-phpinfo">
			<table class="noshow">
				<tr>
					<td>
						<?php echo $this->loadTemplate('phpinfo'); ?>
					</td>
				</tr>
			</table>
		</div>
	</div>

	<div class="clr"></div>
</form>
