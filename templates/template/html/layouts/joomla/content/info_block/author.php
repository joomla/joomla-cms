<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('JPATH_BASE') or die();

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$articleView = $displayData['articleView'];
$author = ($displayData['item']->created_by_alias ?: $displayData['item']->author);
?>
<span class="createdby"<?php echo ($articleView != 'intro') ? ' itemprop="author" itemscope itemtype="https://schema.org/Person"' : ''; ?> title="<?php echo Text::sprintf('COM_CONTENT_WRITTEN_BY', $author); ?>">
	<?php $author = '<span itemprop="name">' . $author . '</span>'; ?>
	<?php if (!empty($displayData['item']->contact_link ) && $displayData['params']->get('link_author') == true) : ?>
		<a href="<?php echo JRoute::_($displayData['item']->contact_link); ?>"<?php echo ($articleView != 'intro') ? ' itemprop="url"' : ''; ?>>
			<?php echo $author; ?>
		</a>
	<?php else : ?>
		<?php echo $author; ?>
	<?php endif; ?>
</span>
