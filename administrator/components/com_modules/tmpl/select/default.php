<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$document = Factory::getDocument();

$app = Factory::getApplication();

$function  = $app->input->getCmd('function');

if ($function) :
	HTMLHelper::_('script', 'com_modules/admin-select-modal.js', ['version' => 'auto', 'relative' => true]);
endif;
?>

<h2 class="mb-3"><?php echo Text::_('COM_MODULES_TYPE_CHOOSE'); ?></h2>
<ul id="new-modules-list" class="list-group">
<?php foreach ($this->items as &$item) : ?>
	<?php // Prepare variables for the link. ?>
	<?php $link       = 'index.php?option=com_modules&task=module.add' . $this->modalLink . '&eid=' . $item->extension_id; ?>
	<?php $name       = $this->escape($item->name); ?>
	<?php $desc       = HTMLHelper::_('string.truncate', $this->escape(strip_tags($item->desc)), 200); ?>

	<?php if ($document->direction != 'rtl') : ?>
	<li class="list-group-item">
		<a href="<?php echo Route::_($link); ?>" class="mr-2<?php echo $function ? ' select-link" data-function="' . $this->escape($function) : ''; ?>">
			<strong><?php echo $name; ?></strong></a>
		<small><?php echo $desc; ?></small>
	</li>
	<?php else : ?>
	<li class="list-group-item">
		<small><?php echo $desc; ?></small>
		<a href="<?php echo Route::_($link); ?>" class="mr-2<?php echo $function ? ' select-link" data-function="' . $this->escape($function) : ''; ?>"><strong><?php echo $name; ?></strong></a>
	</li>
	<?php endif; ?>
<?php endforeach; ?>
</ul>
