<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('form.validate');

?>
<h2><?php echo $this->item->name; ?></h2>
<form action="<?php echo Route::_('index.php?option=com_installer&view=updatesite&layout=edit&update_site_id=' . (int) $this->item->update_site_id); ?>" method="post" name="adminForm" id="adminForm" aria-label="<?php echo Text::_('COM_INSTALLER_UPDATE_FORM_EDIT'); ?>" class="main-card p-4 form-validate">
	<?php echo $this->form->renderFieldset('updateSite'); ?>
	<input type="hidden" name="task" value=""/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
