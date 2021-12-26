<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

?>

<?php if (!empty($displayData['item']->associations)) : ?>
<?php $associations = $displayData['item']->associations; ?>

<dd class="association">
	<span class="info-icon icon-globe icon-fw" aria-hidden="true"></span>
	<span class="info-label">
	<?php echo Text::_('JASSOCIATIONS'); ?>
	</span>
	<span class="info-value">
	<?php foreach ($associations as $association) : ?>
		<?php if ($displayData['item']->params->get('flags', 1) && $association['language']->image) : ?>
			<?php $flag = HTMLHelper::_('image', 'mod_languages/' . $association['language']->image . '.gif', $association['language']->title_native, array('title' => $association['language']->title_native), true); ?>
			<a class="<?php echo strtolower($association['language']->lang_code); ?>" href="<?php echo Route::_($association['item']); ?>"><?php echo $flag; ?></a>
		<?php else : ?>
			<?php $class = 'btn btn-' . strtolower($association['language']->lang_code).' | btn-secondary btn-sm'; ?>
			<a class="<?php echo $class; ?>" title="<?php echo $association['language']->title_native; ?>" href="<?php echo Route::_($association['item']); ?>">
				<span class="lang-code"><?php echo $association['language']->lang_code; ?></span>
				<span class="lang-title"><?php echo $association['language']->title_native; ?></span>
			</a>
		<?php endif; ?>
	<?php endforeach; ?>
	</span>
</dd>
<?php endif; ?>
