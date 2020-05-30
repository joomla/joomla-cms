<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
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
				$class = 'badge-danger';
				break;
			case 'fix':
				$class = 'badge-dark';
				break;
			case 'language':
				$class = 'badge-jlanguage';
				break;
			case 'addition':
				$class = 'badge-success';
				break;
			case 'change':
				$class = 'badge-warning';
				break;
			case 'remove':
				$class = 'badge-light';
				break;
			default:
			case 'note':
				$class = 'badge-info';
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
