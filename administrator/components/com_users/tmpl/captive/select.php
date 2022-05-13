<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Prevent direct access
defined('_JEXEC') or die;

use Joomla\Component\Users\Administrator\View\Captive\HtmlView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

// phpcs:ignoreFile
/** @var HtmlView $this */

$shownMethods = [];

?>
<div id="com-users-select">
	<h3 id="com-users-select-heading">
		<?php echo Text::_('COM_USERS_TFA_SELECT_PAGE_HEAD'); ?>
	</h3>
	<div id="com-users-select-information">
		<p>
			<?php echo Text::_('COM_USERS_LBL_SELECT_INSTRUCTIONS'); ?>
		</p>
	</div>

	<div class="com-users-select-methods">
		<?php foreach ($this->records as $record):
			if (!array_key_exists($record->method, $this->tfaMethods) && ($record->method != 'backupcodes')) continue;

			$allowEntryBatching = isset($this->tfaMethods[$record->method]) ? $this->tfaMethods[$record->method]['allowEntryBatching'] : false;

			if ($this->allowEntryBatching)
			{
				if ($allowEntryBatching && in_array($record->method, $shownMethods)) continue;
				$shownMethods[] = $record->method;
			}

			$methodName = $this->getModel()->translateMethodName($record->method);
		?>
		<a class="com-users-method"
		   href="<?php echo Route::_('index.php?option=com_users&view=captive&record_id=' . $record->id)?>">
			<img src="<?php echo Uri::root() . $this->getModel()->getMethodImage($record->method) ?>"
				 alt="<?php echo $this->escape(strip_tags($record->title)) ?>" class="com-users-method-image" />
			<?php if (!$this->allowEntryBatching || !$allowEntryBatching): ?>
			<span class="com-users-method-title">
				<?php if ($record->method === 'backupcodes'): ?>
					<?php echo $record->title ?>
				<?php else: ?>
					<?php echo $this->escape($record->title) ?>
				<?php endif; ?>
			</span>
			<span class="com-users-method-name">
				<?php echo $methodName ?>
			</span>
			<?php else: ?>
			<span class="com-users-method-title">
				<?php echo $methodName ?>
			</span>
			<span class="com-users-method-name">
				<?php echo $methodName ?>
			</span>
			<?php endif; ?>
		</a>
		<?php endforeach; ?>
	</div>
</div>
