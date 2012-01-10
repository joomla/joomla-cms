<?php
/**
 * @version		$Id: default.php 21518 2011-06-10 21:38:12Z chdemko $
 * @package		Joomla.Administrator
 * @subpackage	com_admin
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Add specific helper files for html generation
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
// Load switcher behavior
JHtml::_('behavior.switcher');
?>

<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="config-document">
		<div id="page-site" class="tab">
			<div class="noshow">
				<div class="width-100">
					<?php echo $this->loadTemplate('system'); ?>
				</div>
			</div>
		</div>

		<div id="page-phpsettings" class="tab">
			<div class="noshow">
				<div class="width-60">
					<?php echo $this->loadTemplate('phpsettings'); ?>
				</div>
			</div>
		</div>

		<div id="page-config" class="tab">
			<div class="noshow">
				<div class="width-60">
					<?php echo $this->loadTemplate('config'); ?>
				</div>
			</div>
		</div>

		<div id="page-directory" class="tab">
			<div class="noshow">
				<div class="width-60">
					<?php echo $this->loadTemplate('directory'); ?>
				</div>
			</div>
		</div>

		<div id="page-phpinfo" class="tab">
			<div class="noshow">
				<div class="width-100">
					<?php echo $this->loadTemplate('phpinfo'); ?>
				</div>
			</div>
		</div>
	</div>

	<div class="clr"></div>
</form>
