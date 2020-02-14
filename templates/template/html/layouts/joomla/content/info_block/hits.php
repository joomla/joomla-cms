<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('JPATH_BASE') or die();

use Joomla\CMS\Language\Text;

?>
<span class="hits">
	<span class="fa fa-eye-o" aria-hidden="true"></span>
	<meta itemprop="interactionCount" content="UserPageVisits:<?php echo $displayData['item']->hits; ?>">
	<?php echo Text::sprintf('COM_CONTENT_ARTICLE_HITS', $displayData['item']->hits); ?>
</span>
