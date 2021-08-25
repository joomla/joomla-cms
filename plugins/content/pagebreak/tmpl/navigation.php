<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.pagebreak
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$lang = JFactory::getLanguage();
?>
<ul>
	<li>
		<?php if ($links['previous']) :
		$direction = $lang->isRtl() ? 'right' : 'left';
		$title = htmlspecialchars($this->list[$page]->title, ENT_QUOTES, 'UTF-8');
		$ariaLabel = JText::_('JPREVIOUS') . ': ' . $title . ' (' . JText::sprintf('JLIB_HTML_PAGE_CURRENT_OF_TOTAL', $page, $n) . ')';
		?>
		<a href="<?php echo $links['previous']; ?>" title="<?php echo $title; ?>" aria-label="<?php echo $ariaLabel; ?>" rel="prev">
			<?php echo '<span class="icon-chevron-' . $direction . '" aria-hidden="true"></span> ' . JText::_('JPREV'); ?>
		</a>
		<?php endif; ?>
	</li>
	<li>
		<?php if ($links['next']) :
		$direction = $lang->isRtl() ? 'left' : 'right';
		$title = htmlspecialchars($this->list[$page + 2]->title, ENT_QUOTES, 'UTF-8');
		$ariaLabel = JText::_('JNEXT') . ': ' . $title . ' (' . JText::sprintf('JLIB_HTML_PAGE_CURRENT_OF_TOTAL', ($page + 2), $n) . ')';
		?>
		<a href="<?php echo $links['next']; ?>" title="<?php echo $title; ?>" aria-label="<?php echo $ariaLabel; ?>" rel="next">
			<?php echo JText::_('JNEXT') . ' <span class="icon-chevron-' . $direction . '" aria-hidden="true"></span>'; ?>
		</a>
		<?php endif; ?>
	</li>
</ul>
