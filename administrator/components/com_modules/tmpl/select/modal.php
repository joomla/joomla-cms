<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$this->modalLink = '&tmpl=component&view=module&layout=modal';
?>
<div class="container-popup">
	<?php $this->setLayout('default'); ?>
	<?php echo $this->loadTemplate(); ?>
</div>
