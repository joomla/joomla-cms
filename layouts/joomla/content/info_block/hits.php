<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

?>
<dd class="hits">
	<span class="info-icon icon-eye icon-fw" aria-hidden="true"></span>
	<meta itemprop="interactionCount" content="UserPageVisits:<?php echo $displayData['item']->hits; ?>">
	<span class="info-label">
		<?php echo Text::sprintf('COM_CONTENT_ARTICLE_HITS', ''); ?>
	</span>
	<span class="info-value">
		<?php echo $displayData['item']->hits; ?>
	</span>
</dd>
