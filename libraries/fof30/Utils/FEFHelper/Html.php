<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Utils\FEFHelper;

defined('_JEXEC') || die;

use FOF30\View\DataView\DataViewInterface;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Pagination\Pagination;

/**
 * Interim FEF helper which was used in FOF 3.2. This is deprecated. Please use the FEFHelper.browse JHtml helper
 * instead. The implementation of this class should be a good hint on how you can do that.
 *
 * @deprecated 4.0
 */
abstract class Html
{
	/**
	 * Helper function to create Javascript code required for table ordering
	 *
	 * @param   string  $order  Current order
	 *
	 * @return string    Javascript to add to the page
	 */
	public static function jsOrderingBackend($order)
	{
		return HTMLHelper::_('FEFHelper.browse.orderjs', $order, true);
	}

	/**
	 * Creates the required HTML code for backend pagination and sorting
	 *
	 * @param   Pagination  $pagination  Pagination object
	 * @param   array       $sortFields  Fields allowed to be sorted
	 * @param   string      $order       Ordering field
	 * @param   string      $order_Dir   Ordering direction (ASC, DESC)
	 *
	 * @return string
	 */
	public static function selectOrderingBackend($pagination, $sortFields, $order, $order_Dir)
	{
		if (is_null($sortFields))
		{
			$sortFields = [];
		}

		if (is_string($sortFields))
		{
			$sortFields = [$sortFields];
		}

		if (!is_array($sortFields))
		{
			$sortFields = [];
		}

		return
			'<div class="akeeba-filter-bar akeeba-filter-bar--right">' .
			HTMLHelper::_('FEFHelper.browse.orderheader', null, $sortFields, $pagination, $order, $order_Dir) .
			'</div>';
	}

	/**
	 * Returns the drag'n'drop reordering field for Browse views
	 *
	 * @param   DataViewInterface  $view           The DataView you're rendering against
	 * @param   string             $orderingField  The name of the field you're ordering by
	 * @param   string             $order          The order value of the current row
	 * @param   string             $class          CSS class for the ordering value INPUT field
	 * @param   string             $icon           CSS class for the d'n'd handle icon
	 * @param   string             $inactiveIcon   CSS class for the d'n'd disabled icon
	 *
	 * @return string
	 */
	public static function dragDropReordering(DataViewInterface $view, $orderingField, $order, $class = 'input-sm', $icon = 'akion-drag', $inactiveIcon = 'akion-android-more-vertical')
	{
		return HTMLHelper::_('FEFHelper.browse.order', $orderingField, $order, $class, $icon, $inactiveIcon, $view);
	}
}
