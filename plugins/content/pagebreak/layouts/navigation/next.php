<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.pagenavigation.layout
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$displayData = new JRegistry($displayData);

$pnSpace = '';

if (JText::_('JGLOBAL_LT') || JText::_('JGLOBAL_GT'))
{
	$pnSpace = ' ';
}
?>

<?php if ($link = $displayData->get('link', null)) : ?>
<a href="<?php echo $link; ?>"><?php echo JText::_('JNEXT') . $pnSpace . JText::_('JGLOBAL_GT') . JText::_('JGLOBAL_GT'); ?></a>
<?php else : ?>
<?php echo JText::_('JNEXT'); ?>
<?php endif; ?>
