<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_postinstall
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$lang     = Factory::getLanguage();
$renderer = $this->document->loadRenderer('module');
$options  = array('style' => 'raw');
$mod      = ModuleHelper::getModule('mod_feed');
$param    = array(
	'rssurl'      => 'https://www.joomla.org/announcements/release-news.feed?type=rss',
	'rsstitle'    => 0,
	'rssdesc'     => 0,
	'rssimage'    => 1,
	'rssitems'    => 5,
	'rssitemdesc' => 1,
	'rssitemdate' => 1,
	'rssrtl'      => $lang->isRtl() ? 1 : 0,
	'word_count'  => 200,
	'cache'       => 0,
	);
$params = array('params' => json_encode($param));

?>

<form action="index.php" method="post" name="adminForm" class="form-inline mb-3" id="adminForm">
	<input type="hidden" name="option" value="com_postinstall">
	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
	<label for="eid" class="mr-sm-2"><?php echo Text::_('COM_POSTINSTALL_MESSAGES_FOR'); ?></label>
	<?php echo HTMLHelper::_('select.genericlist', $this->extension_options, 'eid', array('onchange' => 'this.form.submit()', 'class' => 'custom-select'), 'value', 'text', $this->eid, 'eid'); ?>
</form>

<?php if ($this->eid == $this->joomlaFilesExtensionId) : ?>
<div class="row">
	<div class="col-md-8">
<?php endif; ?>
<?php if (empty($this->items)) : ?>
	<div class="jumbotron">
		<h2><?php echo Text::_('COM_POSTINSTALL_LBL_NOMESSAGES_TITLE'); ?></h2>
		<p><?php echo Text::_('COM_POSTINSTALL_LBL_NOMESSAGES_DESC'); ?></p>
		<a href="<?php echo Route::_('index.php?option=com_postinstall&view=messages&task=message.reset&eid=' . $this->eid . '&' . $this->token . '=1'); ?>" class="btn btn-warning btn-lg">
			<span class="fas fa-eye" aria-hidden="true"></span>
			<?php echo Text::_('COM_POSTINSTALL_BTN_RESET'); ?>
		</a>
	</div>
<?php else : ?>
	<?php foreach ($this->items as $item) : ?>
	<div class="card card-outline-secondary mb-3">
		<div class="card-body">
			<h3><?php echo Text::_($item->title_key); ?></h3>
			<p class="small">
				<?php echo Text::sprintf('COM_POSTINSTALL_LBL_SINCEVERSION', $item->version_introduced); ?>
			</p>
			<div>
				<?php echo Text::_($item->description_key); ?>
				<?php if ($item->type !== 'message') : ?>
				<a href="<?php echo Route::_('index.php?option=com_postinstall&view=messages&task=message.action&id=' . $item->postinstall_message_id . '&' . $this->token . '=1'); ?>" class="btn btn-primary">
					<?php echo Text::_($item->action_key); ?>
				</a>
				<?php endif; ?>
				<?php if (Factory::getUser()->authorise('core.edit.state', 'com_postinstall')) : ?>
				<a href="<?php echo Route::_('index.php?option=com_postinstall&view=messages&task=message.unpublish&id=' . $item->postinstall_message_id . '&' . $this->token . '=1'); ?>" class="btn btn-danger btn-sm">
					<?php echo Text::_('COM_POSTINSTALL_BTN_HIDE'); ?>
				</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php endforeach; ?>
<?php endif; ?>
<?php if ($this->eid == $this->joomlaFilesExtensionId) : ?>
	</div>
	<div class="col-md-4">
		<h2><?php echo Text::_('COM_POSTINSTALL_LBL_RELEASENEWS'); ?></h2>
		<?php echo $renderer->render($mod, $params, $options); ?>
	</div>
</div>
<?php endif; ?>
