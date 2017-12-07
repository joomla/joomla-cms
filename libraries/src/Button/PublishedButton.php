<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Button;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * The PublishedButton class.
 *
 * @since  __DEPLOY_VERSION__
 */
class PublishedButton extends ActionButton
{
	/**
	 * Configure this object.
	 *
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function preprocess()
	{
		$this->addState(1, 'unpublish', 'publish', 'JLIB_HTML_UNPUBLISH_ITEM', ['tip_title' => 'JPUBLISHED']);
		$this->addState(0, 'publish', 'unpublish', 'JLIB_HTML_PUBLISH_ITEM', ['tip_title' => 'JUNPUBLISHED']);
		$this->addState(2, 'unpublish', 'archive', 'JLIB_HTML_UNPUBLISH_ITEM', ['tip_title' => 'JARCHIVED']);
		$this->addState(-2, 'publish', 'trash', 'JLIB_HTML_PUBLISH_ITEM', ['tip_title' => 'JTRASHED']);
	}

	/**
	 * Render action button by item value.
	 *
	 * @param   mixed        $value        Current value of this item.
	 * @param   string       $row          The row number of this item.
	 * @param   array        $options      The options to override group options.
	 * @param   string|Date  $publishUp    The date which item publish up.
	 * @param   string|Date  $publishDown  The date which item publish down.
	 *
	 * @return  string  Rendered HTML.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function render(string $value = null, string $row = null, array $options = [], $publishUp = null, $publishDown = null): string
	{
		if ($publishUp || $publishDown)
		{
			$bakState = $this->getState($value);
			$default  = $this->getState($value) ? : $this->getState('_default');

			$nullDate = Factory::getDbo()->getNullDate();
			$nowDate = Factory::getDate()->toUnix();

			$tz = Factory::getUser()->getTimezone();

			$publishUp   = ($publishUp !== $nullDate) ? Factory::getDate($publishUp, 'UTC')->setTimeZone($tz) : null;
			$publishDown = ($publishDown !== $nullDate) ? Factory::getDate($publishDown, 'UTC')->setTimeZone($tz) : null;

			// Add tips and special titles
			// Create special titles for published items
			if ($value === '1')
			{
				// Create tip text, only we have publish up or down settings
				$tips = array();

				if ($publishUp)
				{
					$tips[] = Text::sprintf('JLIB_HTML_PUBLISHED_START', HTMLHelper::_('date', $publishUp, Text::_('DATE_FORMAT_LC5'), 'UTC'));
				}

				if ($publishDown)
				{
					$tips[] = Text::sprintf('JLIB_HTML_PUBLISHED_FINISHED', HTMLHelper::_('date', $publishDown, Text::_('DATE_FORMAT_LC5'), 'UTC'));
				}

				$tip = empty($tips) ? false : implode('<br>', $tips);

				$default['title'] = $tip;

				$options['tip_title'] = 'JLIB_HTML_PUBLISHED_ITEM';

				if ($publishUp > $nullDate && $nowDate < $publishUp->toUnix())
				{
					$options['tip_title'] = 'JLIB_HTML_PUBLISHED_PENDING_ITEM';
					$default['icon'] = 'pending';
				}

				if ($publishDown > $nullDate && $nowDate > $publishDown->toUnix())
				{
					$options['tip_title'] = 'JLIB_HTML_PUBLISHED_EXPIRED_ITEM';
					$default['icon'] = 'expired';
				}
			}

			$this->states[$value] = $default;

			$html = parent::render($value, $row, $options);

			$this->states[$value] = $bakState;

			return $html;
		}

		return parent::render($value, $row, $options);
	}
}
