<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');

?>
<h1><?php echo $this->item->name; ?></h1>
<form action="<?php echo Route::_('index.php?option=com_installer&view=updatesite&layout=edit&update_site_id=' . (int) $this->item->update_site_id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
	<?php echo $this->form->renderFieldset('updateSite'); ?>
	<input type="hidden" name="task" value=""/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
