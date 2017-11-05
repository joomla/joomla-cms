<?php
/**
 * Part of 40dev project.
 *
 * @copyright  Copyright (C) 2017 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Joomla\CMS\Button;

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
	 * render
	 *
	 * @param  mixed   $value
	 * @param  integer $row
	 * @param  string  $publish_up
	 * @param  string  $publish_down
	 *
	 * @return string
	 * @since  __DEPLOY_VERSION__
	 */
	public function render($value = null, $row = null, $publish_up = null, $publish_down = null)
	{
		if ($publish_up || $publish_down)
		{
			$bakState = $this->getState($value);
			$default  = $this->getState($value) ? : $this->getState('_default');

			$nullDate = Factory::getDbo()->getNullDate();
			$nowDate = Factory::getDate()->toUnix();

			$tz = Factory::getUser()->getTimezone();

			$publish_up = ($publish_up != $nullDate) ? Factory::getDate($publish_up, 'UTC')->setTimeZone($tz) : false;
			$publish_down = ($publish_down != $nullDate) ? Factory::getDate($publish_down, 'UTC')->setTimeZone($tz) : false;

			// Add tips and special titles
			// Create special titles for published items
			if ($value == 1)
			{
				// Create tip text, only we have publish up or down settings
				$tips = array();

				if ($publish_up)
				{
					$tips[] = \JText::sprintf('JLIB_HTML_PUBLISHED_START', $publish_up->format(\JDate::$format, true));
				}

				if ($publish_down)
				{
					$tips[] = \JText::sprintf('JLIB_HTML_PUBLISHED_FINISHED', $publish_down->format(\JDate::$format, true));
				}

				$tip = empty($tips) ? false : implode('<br>', $tips);

				$default['title'] = $tip;

				$default['tip_title'] = 'JLIB_HTML_PUBLISHED_ITEM';

				if ($publish_up > $nullDate && $nowDate < $publish_up->toUnix())
				{
					$default['tip_title'] = 'JLIB_HTML_PUBLISHED_PENDING_ITEM';
					$default['icon'] = 'pending';
				}

				if ($publish_down > $nullDate && $nowDate > $publish_down->toUnix())
				{
					$default['tip_title'] = 'JLIB_HTML_PUBLISHED_EXPIRED_ITEM';
					$default['icon'] = 'expired';
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
