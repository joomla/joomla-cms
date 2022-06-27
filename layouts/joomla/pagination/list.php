<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$list = $displayData['list'];

?>
<nav class="pagination__wrapper" aria-label="<?php echo Text::_('JLIB_HTML_PAGINATION'); ?>">
	<ul class="pagination ms-0 mb-4">
		<?php echo $list['start']['data']; ?>
		<?php echo $list['previous']['data']; ?>

		<?php foreach ($list['pages'] as $page) : ?>
			<?php echo $page['data']; ?>
		<?php endforeach; ?>

		<?php echo $list['next']['data']; ?>
		<?php echo $list['end']['data']; ?>
	</ul>
</nav>
