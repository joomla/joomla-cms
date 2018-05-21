<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('behavior.core');
HTMLHelper::_('webcomponent', ['joomla-toolbar-button' => 'system/webcomponents/joomla-toolbar-button.min.js'], ['relative' => true, 'version' => 'auto', 'detectDebug' => true]);

/**
 * @var  int     $id
 * @var  string  $onclick
 * @var  string  $class
 * @var  string  $text
 * @var  string  $btnClass
 * @var  string  $tagName
 * @var  string  $htmlAttributes
 */
extract($displayData, EXTR_OVERWRITE);

$tagName = $tagName ?? 'button';
?>

<?php if (!empty($group)) : ?>
<a<?php echo $id ?? ''; ?> href="#" onclick="<?php echo $onclick ?? ''; ?>" class="dropdown-item">
	<span class="<?php echo trim($class ?? ''); ?>"></span>
	<?php echo $text ?? ''; ?>
</a>
<?php else : ?>
<<?php echo $tagName; ?>
	id="<?php echo $id ?? ''; ?>"
	onclick="<?php echo $onclick ?? ''; ?>"
	class="<?php echo $btnClass ?? ''; ?>"
	<?php echo $htmlAttributes ?? ''; ?>
	>
	<span class="<?php echo trim($class ?? ''); ?>" aria-hidden="true"></span>
	<?php echo $text ?? ''; ?>
</<?php echo $tagName; ?>>
<?php endif; ?>
