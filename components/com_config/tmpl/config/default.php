<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate')
	->useScript('com_config.config');

?>

<form action="<?php echo Route::_('index.php?option=com_config'); ?>" id="application-form" method="post" name="adminForm" class="form-validate">

	<button type="button" class="btn btn-primary" data-submit-task="config.apply">
		<span class="icon-check" aria-hidden="true"></span>
		<?php echo Text::_('JSAVE') ?>
	</button>
	<button type="button" class="btn btn-danger" data-submit-task="config.cancel">
		<span class="icon-times" aria-hidden="true"></span>
		<?php echo Text::_('JCANCEL') ?>
	</button>

	<hr>

	<?php echo $this->loadTemplate('site'); ?>
	<?php echo $this->loadTemplate('seo'); ?>
	<?php echo $this->loadTemplate('metadata'); ?>

	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>

</form>
