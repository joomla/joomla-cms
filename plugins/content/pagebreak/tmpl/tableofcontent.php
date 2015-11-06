<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.pagebreak
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$app        = JFactory::getApplication();
$limitstart = $app->input->getUInt('limitstart', 0);
$showall    = $app->input->getInt('showall', 0);
$heading    = isset($row->title) ? $row->title : JText::_('PLG_CONTENT_PAGEBREAK_NO_TITLE');

// Define class for navigation
$class = ($limitstart === 0 && $showall === 0) ? 'toclink active' : 'toclink';
?>

<div class="pull-right article-index">
	<?php if ($this->params->get('article_index') == 1 && $this->params->get('article_index_text') == '') : ?>
		<h3><?php echo JText::_('PLG_CONTENT_PAGEBREAK_ARTICLE_INDEX'); ?></h3>
	<?php endif; ?>

	<?php if ($this->params->get('article_index_text') && $this->params->get('article_index')) : ?>
		<h3><?php echo htmlspecialchars($this->params->get('article_index_text'), ENT_QUOTES, 'UTF-8'); ?></h3>
	<?php endif; ?>

	<ul class="nav nav-tabs nav-stacked">
		<li class="<?php echo $class; ?>">
			<a
				href="<?php echo JRoute::_(ContentHelperRoute::getArticleRoute($row->slug, $row->catid, $row->language) . '&showall=&limitstart='); ?>"
				class="<?php echo $class; ?>"
			>
				<?php echo $heading; ?>
			</a>
		</li>

		<?php
		$i      = 2;
		$output = '';

		foreach ($matches as $bot)
		{
			$link = JRoute::_(ContentHelperRoute::getArticleRoute($row->slug, $row->catid, $row->language) . '&showall=&limitstart=' . ($i - 1));

			if (@$bot[0])
			{
				$attrs2 = JUtility::parseAttributes($bot[0]);

				if (@$attrs2['alt'])
				{
					$title = stripslashes($attrs2['alt']);
				}
				elseif (@$attrs2['title'])
				{
					$title = stripslashes($attrs2['title']);
				}
				else
				{
					$title = JText::sprintf('PLG_CONTENT_PAGEBREAK_PAGE_NUM', $i);
				}
			}
			else
			{
				$title = JText::sprintf('PLG_CONTENT_PAGEBREAK_PAGE_NUM', $i);
			}

			$liClass = ($limitstart == $i - 1) ? ' class="active"' : '';
			$class   = ($limitstart == $i - 1) ? 'toclink active' : 'toclink';
			$output .= '<li' . $liClass . '><a href="' . $link . '" class="' . $class . '">' . $title . '</a></li>';
			$i++;
		}

		if ($this->params->get('showall'))
		{
			$link    = JRoute::_(ContentHelperRoute::getArticleRoute($row->slug, $row->catid, $row->language) . '&showall=1&limitstart=');
			$liClass = ($limitstart == $i - 1) ? ' class="active"' : '';
			$class   = ($limitstart == $i - 1) ? 'toclink active' : 'toclink';
			$output .= '<li' . $liClass . '><a href="' . $link . '" class="' . $class . '">'
				. JText::_('PLG_CONTENT_PAGEBREAK_ALL_PAGES') . '</a></li>';
		}

		echo $output;
		?>
	</ul>
</div>
