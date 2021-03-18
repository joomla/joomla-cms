<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

$canEdit   = $displayData['params']->get('access-edit');
$articleId = $displayData['item']->id;
?>

<?php if ($canEdit) : ?>
	<div class="icons">
		<?php Factory::getApplication()->getDocument()->getWebAssetManager()->useScript('bootstrap.js.bundle'); ?>
		<div class="btn-group float-right">
			<button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton-<?php echo $articleId; ?>" aria-label="<?php echo Text::_('JUSER_TOOLS'); ?>"
				data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				<span class="fas fa-cog" aria-hidden="true"></span>
			</button>
			<div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton-<?php echo $articleId; ?>">
				<?php echo HTMLHelper::_('icon.edit', $displayData['item'], $displayData['params']); ?>
			</div>
		</div>
	</div>
<?php endif; ?>
