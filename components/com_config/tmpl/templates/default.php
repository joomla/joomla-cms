<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$user = Factory::getUser();

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate')
	->useScript('com_config.templates');

?>
<?php if ($this->params->get('show_page_heading')) : ?>
	<div class="page-header">
		<h1>
			<?php if ($this->escape($this->params->get('page_heading'))) : ?>
				<?php echo $this->escape($this->params->get('page_heading')); ?>
			<?php else : ?>
				<?php echo $this->escape($this->params->get('page_title')); ?>
			<?php endif; ?>
		</h1>
	</div>
<?php endif; ?>
<form action="<?php echo Route::_('index.php?option=com_config'); ?>" method="post" name="adminForm" id="templates-form" class="form-validate">

	<div id="page-site" class="tab-pane active">
		<div class="row">
			<div class="col-md-12">
				<?php echo $this->loadTemplate('options'); ?>
			</div>
		</div>
	</div>

	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>

	<div class="mb-2">
	<button type="button" class="btn btn-primary " data-submit-task="templates.apply">
		<span class="icon-check text-white" aria-hidden="true"></span>
		<?php echo Text::_('JSAVE') ?>
	</button>
	<button type="button" class="btn btn-danger" data-submit-task="templates.cancel">
		<span class="icon-times text-white" aria-hidden="true"></span>
		<?php echo Text::_('JCANCEL') ?>
	</button>
</div>

</form>
