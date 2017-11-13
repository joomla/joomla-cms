<?php
/**
 * Part of 40dev project.
 *
 * @copyright  Copyright (C) 2017 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Joomla\CMS\Button;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;

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
		$this->addState(1, 'unpublish', 'icon-publish', 'JLIB_HTML_UNPUBLISH_ITEM', ['tip_title' => 'JPUBLISHED']);
		$this->addState(0, 'publish', 'icon-unpublish', 'JLIB_HTML_PUBLISH_ITEM', ['tip_title' => 'JUNPUBLISHED']);
		$this->addState(2, 'unpublish', 'icon-archive', 'JLIB_HTML_UNPUBLISH_ITEM', ['tip_title' => 'JARCHIVED']);
		$this->addState(-2, 'publish', 'icon-trash', 'JLIB_HTML_PUBLISH_ITEM', ['tip_title' => 'JTRASHED']);
	}

	/**
	 * Render action button by item value.
	 *
	 * @param   mixed        $value        Current value of this item.
	 * @param   integer      $row          The row number of this item.
	 * @param   string|Date  $publishUp    The date which item publish up.
	 * @param   string|Date  $publishDown  The date which item publish down.
	 *
	 * @return  string  Rendered HTML.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function render($value = null, $row = null, $publishUp = null, $publishDown = null)
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
			if ($value == 1)
			{
				// Create tip text, only we have publish up or down settings
				$tips = array();

				if ($publishUp)
				{
					$tips[] = \JText::sprintf('JLIB_HTML_PUBLISHED_START', $publishUp->format(Date::$format, true));
				}

				if ($publishDown)
				{
					$tips[] = \JText::sprintf('JLIB_HTML_PUBLISHED_FINISHED', $publishDown->format(Date::$format, true));
				}

				$tip = empty($tips) ? false : implode('<br>', $tips);

				$default['title'] = $tip;

				$default['tip_title'] = 'JLIB_HTML_PUBLISHED_ITEM';

				if ($publishUp > $nullDate && $nowDate < $publishUp->toUnix())
				{
					$default['tip_title'] = 'JLIB_HTML_PUBLISHED_PENDING_ITEM';
					$default['icon'] = 'icon-pending';
				}

				if ($publishDown > $nullDate && $nowDate > $publishDown->toUnix())
				{
					$default['tip_title'] = 'JLIB_HTML_PUBLISHED_EXPIRED_ITEM';
					$default['icon'] = 'icon-expired';
				}
			}

			$this->states[$value] = $default;

			$html = parent::render($value, $row);

			$this->states[$value] = $bakState;

			return $html;
		}

		return parent::render($value, $row);
	}
}
