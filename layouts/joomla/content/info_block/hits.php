<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
			<dd class="hits">
					<span class="icon-eye-open" aria-hidden="true"></span>
					<meta itemprop="interactionCount" content="UserPageVisits:<?php echo $displayData['item']->hits; ?>" />
					<?php echo JText::sprintf('COM_CONTENT_ARTICLE_HITS', $displayData['item']->hits); ?>
			</dd>