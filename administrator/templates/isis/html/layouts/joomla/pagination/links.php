<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\Registry\Registry;

$list = $displayData['list'];
$pages = $list['pages'];
$pagesTotal = $list['pagesTotal'];

$options = new Registry($displayData['options']);

$showLimitBox   = $options->get('showLimitBox', 0);
$showPagesLinks = $options->get('showPagesLinks', true);
$showLimitStart = $options->get('showLimitStart', true);
?>

<div class="pagination pagination-toolbar clearfix">

	<?php if ($showLimitBox) : ?>
		<div class="limit pull-right">
			<?php echo $list['limitfield']; ?>
		</div>
	<?php endif; ?>

	<?php if ($showPagesLinks && (!empty($pages))) : ?>
		<nav role="navigation" aria-label="<?php echo JText::_('JLIB_HTML_PAGINATION'); ?>">
			<ul class="pagination-list">
				<?php
					$pages['start']['pagOptions'] = array('addText' => ' (' . JText::sprintf('JLIB_HTML_PAGE_CURRENT_OF_TOTAL', 1, $pagesTotal) . ')');
					echo JLayoutHelper::render('joomla.pagination.link', $pages['start']);
					echo JLayoutHelper::render('joomla.pagination.link', $pages['previous']); ?>
				<?php foreach ($pages['pages'] as $page) :
					$page['pagOptions'] = array('liClass' => 'hidden-phone');
				?>
					<?php echo JLayoutHelper::render('joomla.pagination.link', $page); ?>
				<?php endforeach; ?>
				<?php
					echo JLayoutHelper::render('joomla.pagination.link', $pages['next']);
					$pages['end']['pagOptions'] = array('addText' => ' (' . JText::sprintf('JLIB_HTML_PAGE_CURRENT_OF_TOTAL', $pagesTotal, $pagesTotal) . ')');
					echo JLayoutHelper::render('joomla.pagination.link', $pages['end']); ?>
			</ul>
		</nav>
	<?php endif; ?>

	<?php if ($showLimitStart) : ?>
		<input type="hidden" name="<?php echo $list['prefix']; ?>limitstart" value="<?php echo $list['limitstart']; ?>" />
	<?php endif; ?>

</div>
