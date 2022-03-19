<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

extract($displayData, EXTR_OVERWRITE);

/**
 * Layout variables
 * -----------------
 * @var   string  $id
 * @var   string  $onclick
 * @var   string  $class
 * @var   string  $text
 * @var   string  $btnClass
 * @var   string  $tagName
 * @var   string  $htmlAttributes
 * @var   string  $hasButtons
 * @var   string  $button
 * @var   string  $dropdownItems
 * @var   string  $caretClass
 * @var   string  $toggleSplit
 */

$direction = Factory::getLanguage()->isRtl() ? 'dropdown-menu-end' : '';

?>

<?php if ($hasButtons && trim($button) !== ''): ?>
	<?php // If there is a toggle split then render the items. Else render the parent button which has the items in the custom element.  ?>
	<?php if ($toggleSplit ?? true): ?>
		<?php HTMLHelper::_('bootstrap.dropdown', '.dropdown'); ?>
		<?php // @todo use a class instead of the inline style.
			 //  Reverse order solves a console err for dropdown ?>
		<div id="<?php echo $id; ?>" class="btn-group dropdown-<?php echo $name ?? ''; ?>" role="group">
			<button type="button" class="<?php echo $caretClass ?? ''; ?> dropdown-toggle-split"
				data-bs-toggle="dropdown" data-bs-target=".dropdown-menu" data-bs-display="static" aria-haspopup="true" aria-expanded="false">
				<span class="visually-hidden"><?php echo Text::_('JGLOBAL_TOGGLE_DROPDOWN'); ?></span>
				<span class="icon-chevron-down" aria-hidden="true"></span>
			</button>

			<?php echo $button; ?>

			<?php if (trim($dropdownItems) !== ''): ?>
				<div class="dropdown-menu <?php echo $direction; ?>">
					<?php echo $dropdownItems; ?>
				</div>
			<?php endif; ?>
		</div>
	<?php else: ?>
		<?php echo $button; ?>
	<?php endif; ?>
<?php endif; ?>
