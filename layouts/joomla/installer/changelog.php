<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

array_walk(
	$displayData,
	function ($items, $changeType) {
		// If there are no items, continue
		if (empty($items))
		{
			return;
		}

		switch ($changeType)
		{
			case 'security':
				$class = 'bg-danger';
				break;
			case 'fix':
				$class = 'bg-dark';
				break;
			case 'language':
				$class = 'bg-primary';
				break;
			case 'addition':
				$class = 'bg-success';
				break;
			case 'change':
				$class = 'bg-warning text-dark';
				break;
			case 'remove':
				$class = 'bg-secondary';
				break;
			default:
			case 'note':
				$class = 'bg-info';
				break;
		}

		?>
		<div class="changelog">
			<div class="changelog__item">
				<div class="changelog__tag">
					<span class="badge <?php echo $class; ?>"><?php echo Text::_('COM_INSTALLER_CHANGELOG_' . $changeType); ?></span>
				</div>
				<div class="changelog__list">
					<ul>
						<li><?php echo implode('</li><li>', $items); ?></li>
					</ul>
				</div>
			</div>
		</div>
		<?php
	}
);
