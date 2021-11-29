<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
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
 * @since  4.0.0
 */
class PublishedButton extends ActionButton
{
	/**
	 * Configure this object.
	 *
	 * @return  void
	 *
	 * @since  4.0.0
	 */
	protected function preprocess()
	{
		$this->addState(1, 'unpublish', 'publish', Text::_('JLIB_HTML_UNPUBLISH_ITEM'), ['tip_title' => Text::_('JPUBLISHED')]);
		$this->addState(0, 'publish', 'unpublish', Text::_('JLIB_HTML_PUBLISH_ITEM'), ['tip_title' => Text::_('JUNPUBLISHED')]);
		$this->addState(2, 'unpublish', 'archive', Text::_('JLIB_HTML_UNPUBLISH_ITEM'), ['tip_title' => Text::_('JARCHIVED')]);
		$this->addState(-2, 'publish', 'trash', Text::_('JLIB_HTML_PUBLISH_ITEM'), ['tip_title' => Text::_('JTRASHED')]);
	}

	/**
	 * Render action button by item value.
	 *
	 * @param   integer|null  $value        Current value of this item.
	 * @param   integer|null  $row          The row number of this item.
	 * @param   array         $options      The options to override group options.
	 * @param   string|Date   $publishUp    The date which item publish up.
	 * @param   string|Date   $publishDown  The date which item publish down.
	 *
	 * @return  string  Rendered HTML.
	 *
	 * @since  4.0.0
	 */
	public function render(?int $value = null, ?int $row = null, array $options = [], $publishUp = null, $publishDown = null): string
	{
		if ($publishUp || $publishDown)
		{
			$bakState = $this->getState($value);
			$default  = $this->getState($value) ?? $this->unknownState;

			$nullDate = Factory::getDbo()->getNullDate();
			$nowDate = Factory::getDate()->toUnix();

			$tz = Factory::getUser()->getTimezone();

			$publishUp   = ($publishUp !== null && $publishUp !== $nullDate) ? Factory::getDate($publishUp, 'UTC')->setTimezone($tz) : false;
			$publishDown = ($publishDown !== null && $publishDown !== $nullDate) ? Factory::getDate($publishDown, 'UTC')->setTimezone($tz) : false;

			// Add tips and special titles
			// Create special titles for published items
			if ($value === 1)
			{
				// Create tip text, only we have publish up or down settings
				$tips = array();

				if ($publishUp)
				{
					$tips[] = Text::sprintf('JLIB_HTML_PUBLISHED_START', HTMLHelper::_('date', $publishUp, Text::_('DATE_FORMAT_LC5'), 'UTC'));
					$tips[] = Text::_('JLIB_HTML_PUBLISHED_UNPUBLISH');
				}

				if ($publishDown)
				{
					$tips[] = Text::sprintf('JLIB_HTML_PUBLISHED_FINISHED', HTMLHelper::_('date', $publishDown, Text::_('DATE_FORMAT_LC5'), 'UTC'));
				}

				$tip = empty($tips) ? false : implode('<br>', $tips);

				$default['title'] = $tip;

				$options['tip_title'] = Text::_('JLIB_HTML_PUBLISHED_ITEM');

				if ($publishUp && $nowDate < $publishUp->toUnix())
				{
					$options['tip_title'] = Text::_('JLIB_HTML_PUBLISHED_PENDING_ITEM');
					$default['icon'] = 'pending';
				}

				if ($publishDown && $nowDate > $publishDown->toUnix())
				{
					$options['tip_title'] = Text::_('JLIB_HTML_PUBLISHED_EXPIRED_ITEM');
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
