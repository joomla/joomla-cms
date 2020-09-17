<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

defined('_JEXEC') || die;

use FOF30\Model\DataModel;
use FOF30\Utils\ArrayHelper;
use FOF30\Utils\FEFHelper\BrowseView;
use FOF30\View\DataView\DataViewInterface;
use FOF30\View\DataView\Html;
use FOF30\View\DataView\Raw as DataViewRaw;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Pagination\Pagination;

/**
 * Custom JHtml (HTMLHelper) class. Offers browse view controls compatible with Akeeba Frontend
 * Framework (FEF).
 *
 * Call these methods as JHtml::_('FEFHelper.browse.methodName', $parameter1, $parameter2, ...)
 */
abstract class FEFHelperBrowse
{
	/**
	 * Returns an action button on the browse view's table
	 *
	 * @param   integer       $i               The row index
	 * @param   string        $task            The task to fire when the button is clicked
	 * @param   string|array  $prefix          An optional task prefix or an array of options
	 * @param   string        $active_title    An optional active tooltip to display if $enable is true
	 * @param   string        $inactive_title  An optional inactive tooltip to display if $enable is true
	 * @param   boolean       $tip             An optional setting for tooltip
	 * @param   string        $active_class    An optional active HTML class
	 * @param   string        $inactive_class  An optional inactive HTML class
	 * @param   boolean       $enabled         An optional setting for access control on the action.
	 * @param   boolean       $translate       An optional setting for translation.
	 * @param   string        $checkbox        An optional prefix for checkboxes.
	 *
	 * @return  string  The HTML markup
	 *
	 * @since   3.3.0
	 */
	public static function action($i, $task, $prefix = '', $active_title = '', $inactive_title = '', $tip = false, $active_class = '',
	                              $inactive_class = '', $enabled = true, $translate = true, $checkbox = 'cb')
	{
		if (is_array($prefix))
		{
			$options        = $prefix;
			$active_title   = array_key_exists('active_title', $options) ? $options['active_title'] : $active_title;
			$inactive_title = array_key_exists('inactive_title', $options) ? $options['inactive_title'] : $inactive_title;
			$tip            = array_key_exists('tip', $options) ? $options['tip'] : $tip;
			$active_class   = array_key_exists('active_class', $options) ? $options['active_class'] : $active_class;
			$inactive_class = array_key_exists('inactive_class', $options) ? $options['inactive_class'] : $inactive_class;
			$enabled        = array_key_exists('enabled', $options) ? $options['enabled'] : $enabled;
			$translate      = array_key_exists('translate', $options) ? $options['translate'] : $translate;
			$checkbox       = array_key_exists('checkbox', $options) ? $options['checkbox'] : $checkbox;
			$prefix         = array_key_exists('prefix', $options) ? $options['prefix'] : '';
		}

		if ($tip)
		{
			$title = $enabled ? $active_title : $inactive_title;
			$title = $translate ? Text::_($title) : $title;
			$title = HTMLHelper::_('tooltipText', $title, '', 0);
		}

		$html   = [];

		if ($enabled)
		{
			$btnColor = 'grey';

			if (substr($active_class, 0, 2) == '--')
			{
				[$btnColor, $active_class] = explode(' ', $active_class, 2);
				$btnColor = ltrim($btnColor, '-');
			}

			$html[] = '<a class="akeeba-btn--' . $btnColor . '--mini ' . ($active_class === 'publish' ? ' active' : '') . ($tip ? ' hasTooltip' : '') . '"';
			$html[] = ' href="javascript:void(0);" onclick="return Joomla.listItemTask(\'' . $checkbox . $i . '\',\'' . $prefix . $task . '\')"';
			$html[] = $tip ? ' title="' . $title . '"' : '';
			$html[] = '>';
			$html[] = '<span class="akion-' . $active_class . '" aria-hidden="true"></span>&ensp;';
			$html[] = '</a>';
		}
		else
		{
			$btnColor = 'grey';

			if (substr($inactive_class, 0, 2) == '--')
			{
				[$btnColor, $inactive_class] = explode(' ', $inactive_class, 2);
				$btnColor = ltrim($btnColor, '-');
			}

			$html[] = '<a class="akeeba-btn--' . $btnColor . '--mini disabled akeebagrid' . ($tip ? ' hasTooltip' : '') . '"';
			$html[] = $tip ? ' title="' . $title . '"' : '';
			$html[] = '>';

			if ($active_class === 'protected')
			{
				$inactive_class = 'locked';
			}

			$html[] = '<span class="akion-' . $inactive_class . '"></span>&ensp;';
			$html[] = '</a>';
		}

		return implode($html);
	}

	/**
	 * Returns a state change button on the browse view's table
	 *
	 * @param   array         $states     array of value/state. Each state is an array of the form
	 *                                    (task, text, active title, inactive title, tip (boolean), HTML active class,
	 *                                    HTML inactive class) or ('task'=>task, 'text'=>text, 'active_title'=>active
	 *                                    title,
	 *                                    'inactive_title'=>inactive title, 'tip'=>boolean, 'active_class'=>html active
	 *                                    class,
	 *                                    'inactive_class'=>html inactive class)
	 * @param   integer       $value      The state value.
	 * @param   integer       $i          The row index
	 * @param   string|array  $prefix     An optional task prefix or an array of options
	 * @param   boolean       $enabled    An optional setting for access control on the action.
	 * @param   boolean       $translate  An optional setting for translation.
	 * @param   string        $checkbox   An optional prefix for checkboxes.
	 *
	 * @return  string  The HTML markup
	 *
	 * @since   3.3.0
	 */
	public static function state($states, $value, $i, $prefix = '', $enabled = true, $translate = true, $checkbox = 'cb')
	{
		if (is_array($prefix))
		{
			$options   = $prefix;
			$enabled   = array_key_exists('enabled', $options) ? $options['enabled'] : $enabled;
			$translate = array_key_exists('translate', $options) ? $options['translate'] : $translate;
			$checkbox  = array_key_exists('checkbox', $options) ? $options['checkbox'] : $checkbox;
			$prefix    = array_key_exists('prefix', $options) ? $options['prefix'] : '';
		}

		$state          = ArrayHelper::getValue($states, (int) $value, $states[0]);
		$task           = array_key_exists('task', $state) ? $state['task'] : $state[0];
		$text           = array_key_exists('text', $state) ? $state['text'] : (array_key_exists(1, $state) ? $state[1] : '');
		$active_title   = array_key_exists('active_title', $state) ? $state['active_title'] : (array_key_exists(2, $state) ? $state[2] : '');
		$inactive_title = array_key_exists('inactive_title', $state) ? $state['inactive_title'] : (array_key_exists(3, $state) ? $state[3] : '');
		$tip            = array_key_exists('tip', $state) ? $state['tip'] : (array_key_exists(4, $state) ? $state[4] : false);
		$active_class   = array_key_exists('active_class', $state) ? $state['active_class'] : (array_key_exists(5, $state) ? $state[5] : '');
		$inactive_class = array_key_exists('inactive_class', $state) ? $state['inactive_class'] : (array_key_exists(6, $state) ? $state[6] : '');

		return static::action(
			$i, $task, $prefix, $active_title, $inactive_title, $tip,
			$active_class, $inactive_class, $enabled, $translate, $checkbox
		);
	}

	/**
	 * Returns a published state on the browse view's table
	 *
	 * @param   integer       $value         The state value.
	 * @param   integer       $i             The row index
	 * @param   string|array  $prefix        An optional task prefix or an array of options
	 * @param   boolean       $enabled       An optional setting for access control on the action.
	 * @param   string        $checkbox      An optional prefix for checkboxes.
	 * @param   string        $publish_up    An optional start publishing date.
	 * @param   string        $publish_down  An optional finish publishing date.
	 *
	 * @return  string  The HTML markup
	 *
	 * @see     self::state()
	 *
	 * @since   3.3.0
	 */
	public static function published($value, $i, $prefix = '', $enabled = true, $checkbox = 'cb', $publish_up = null, $publish_down = null)
	{
		if (is_array($prefix))
		{
			$options  = $prefix;
			$enabled  = array_key_exists('enabled', $options) ? $options['enabled'] : $enabled;
			$checkbox = array_key_exists('checkbox', $options) ? $options['checkbox'] : $checkbox;
			$prefix   = array_key_exists('prefix', $options) ? $options['prefix'] : '';
		}

		/**
		 * Format:
		 *
		 * (task, text, active title, inactive title, tip (boolean), active icon class (without akion-), inactive icon class (without akion-))
		 */
		$states = [
			1  => [
				'unpublish', 'JPUBLISHED', 'JLIB_HTML_UNPUBLISH_ITEM', 'JPUBLISHED', true, '--green checkmark',
				'--green checkmark',
			],
			0  => [
				'publish', 'JUNPUBLISHED', 'JLIB_HTML_PUBLISH_ITEM', 'JUNPUBLISHED', true, '--red close', '--red close',
			],
			2  => [
				'unpublish', 'JARCHIVED', 'JLIB_HTML_UNPUBLISH_ITEM', 'JARCHIVED', true, '--orange ion-ios-box',
				'--orange ion-ios-box',
			],
			-2 => [
				'publish', 'JTRASHED', 'JLIB_HTML_PUBLISH_ITEM', 'JTRASHED', true, '--dark trash-a', '--dark trash-a',
			],
		];

		// Special state for dates
		if ($publish_up || $publish_down)
		{
			$nullDate = Factory::getDbo()->getNullDate();
			$nowDate  = Factory::getDate()->toUnix();

			$tz = Factory::getUser()->getTimezone();

			$publish_up   = ($publish_up != $nullDate) ? Factory::getDate($publish_up, 'UTC')->setTimeZone($tz) : false;
			$publish_down = ($publish_down != $nullDate) ? Factory::getDate($publish_down, 'UTC')->setTimeZone($tz) : false;

			// Create tip text, only we have publish up or down settings
			$tips = [];

			if ($publish_up)
			{
				$tips[] = Text::sprintf('JLIB_HTML_PUBLISHED_START', HTMLHelper::_('date', $publish_up, Text::_('DATE_FORMAT_LC5'), 'UTC'));
			}

			if ($publish_down)
			{
				$tips[] = Text::sprintf('JLIB_HTML_PUBLISHED_FINISHED', HTMLHelper::_('date', $publish_down, Text::_('DATE_FORMAT_LC5'), 'UTC'));
			}

			$tip = empty($tips) ? false : implode('<br />', $tips);

			// Add tips and special titles
			foreach ($states as $key => $state)
			{
				// Create special titles for published items
				if ($key == 1)
				{
					$states[$key][2] = $states[$key][3] = 'JLIB_HTML_PUBLISHED_ITEM';

					if ($publish_up > $nullDate && $nowDate < $publish_up->toUnix())
					{
						$states[$key][2] = $states[$key][3] = 'JLIB_HTML_PUBLISHED_PENDING_ITEM';
						$states[$key][5] = $states[$key][6] = 'android-time';
					}

					if ($publish_down > $nullDate && $nowDate > $publish_down->toUnix())
					{
						$states[$key][2] = $states[$key][3] = 'JLIB_HTML_PUBLISHED_EXPIRED_ITEM';
						$states[$key][5] = $states[$key][6] = 'alert';
					}
				}

				// Add tips to titles
				if ($tip)
				{
					$states[$key][1] = Text::_($states[$key][1]);
					$states[$key][2] = Text::_($states[$key][2]) . '<br />' . $tip;
					$states[$key][3] = Text::_($states[$key][3]) . '<br />' . $tip;
					$states[$key][4] = true;
				}
			}

			return static::state($states, $value, $i, [
				'prefix' => $prefix, 'translate' => !$tip,
			], $enabled, true, $checkbox);
		}

		return static::state($states, $value, $i, $prefix, $enabled, true, $checkbox);
	}

	/**
	 * Returns an isDefault state on the browse view's table
	 *
	 * @param   integer       $value     The state value.
	 * @param   integer       $i         The row index
	 * @param   string|array  $prefix    An optional task prefix or an array of options
	 * @param   boolean       $enabled   An optional setting for access control on the action.
	 * @param   string        $checkbox  An optional prefix for checkboxes.
	 *
	 * @return  string  The HTML markup
	 *
	 * @see     self::state()
	 * @since   3.3.0
	 */
	public static function isdefault($value, $i, $prefix = '', $enabled = true, $checkbox = 'cb')
	{
		if (is_array($prefix))
		{
			$options  = $prefix;
			$enabled  = array_key_exists('enabled', $options) ? $options['enabled'] : $enabled;
			$checkbox = array_key_exists('checkbox', $options) ? $options['checkbox'] : $checkbox;
			$prefix   = array_key_exists('prefix', $options) ? $options['prefix'] : '';
		}

		$states = [
			0 => ['setDefault', '', 'JLIB_HTML_SETDEFAULT_ITEM', '', 1, 'android-star-outline', 'android-star-outline'],
			1 => [
				'unsetDefault', 'JDEFAULT', 'JLIB_HTML_UNSETDEFAULT_ITEM', 'JDEFAULT', 1, 'android-star',
				'android-star',
			],
		];

		return static::state($states, $value, $i, $prefix, $enabled, true, $checkbox);
	}

	/**
	 * Returns a checked-out icon
	 *
	 * @param   integer       $i           The row index.
	 * @param   string        $editorName  The name of the editor.
	 * @param   string        $time        The time that the object was checked out.
	 * @param   string|array  $prefix      An optional task prefix or an array of options
	 * @param   boolean       $enabled     True to enable the action.
	 * @param   string        $checkbox    An optional prefix for checkboxes.
	 *
	 * @return  string  The HTML markup
	 *
	 * @since   3.3.0
	 */
	public static function checkedout($i, $editorName, $time, $prefix = '', $enabled = false, $checkbox = 'cb')
	{
		HTMLHelper::_('bootstrap.tooltip');

		if (is_array($prefix))
		{
			$options  = $prefix;
			$enabled  = array_key_exists('enabled', $options) ? $options['enabled'] : $enabled;
			$checkbox = array_key_exists('checkbox', $options) ? $options['checkbox'] : $checkbox;
			$prefix   = array_key_exists('prefix', $options) ? $options['prefix'] : '';
		}

		$text           = $editorName . '<br />' . HTMLHelper::_('date', $time, Text::_('DATE_FORMAT_LC')) . '<br />' . HTMLHelper::_('date', $time, 'H:i');
		$active_title   = HTMLHelper::_('tooltipText', Text::_('JLIB_HTML_CHECKIN'), $text, 0);
		$inactive_title = HTMLHelper::_('tooltipText', Text::_('JLIB_HTML_CHECKED_OUT'), $text, 0);

		return static::action(
			$i, 'checkin', $prefix, html_entity_decode($active_title, ENT_QUOTES, 'UTF-8'),
			html_entity_decode($inactive_title, ENT_QUOTES, 'UTF-8'), true, 'locked', 'locked', $enabled, false, $checkbox
		);
	}

	/**
	 * Returns the drag'n'drop reordering field for Browse views
	 *
	 * @param   string             $orderingField  The name of the field you're ordering by
	 * @param   string             $order          The order value of the current row
	 * @param   string             $class          CSS class for the ordering value INPUT field
	 * @param   string             $icon           CSS class for the d'n'd handle icon
	 * @param   string             $inactiveIcon   CSS class for the d'n'd disabled icon
	 * @param   DataViewInterface  $view           The view you're rendering against. Leave null for auto-detection.
	 *
	 * @return string
	 */
	public static function order($orderingField, $order, $class = 'input-sm', $icon = 'akion-android-more-vertical', $inactiveIcon = 'akion-android-more-vertical', DataViewInterface $view = null)
	{
		/** @var Html $view */
		if (is_null($view))
		{
			$view = BrowseView::getViewFromBacktrace();
		}
		$dndOrderingActive = $view->getLists()->order == $orderingField;

		// Default inactive ordering
		$html = '<span class="sortable-handler inactive" >';
		$html .= '<span class="' . $icon . '"></span>';
		$html .= '</span>';

		// The modern drag'n'drop method
		if ($view->getPerms()->editstate)
		{
			$disableClassName = '';
			$disabledLabel    = '';

			// DO NOT REMOVE! It will initialize Joomla libraries and javascript functions
			$hasAjaxOrderingSupport = $view->hasAjaxOrderingSupport();

			if (!is_array($hasAjaxOrderingSupport) || !$hasAjaxOrderingSupport['saveOrder'])
			{
				$disabledLabel    = Text::_('JORDERINGDISABLED');
				$disableClassName = 'inactive tip-top hasTooltip';
			}

			$orderClass = $dndOrderingActive ? 'order-enabled' : 'order-disabled';

			$html = '<div class="' . $orderClass . '">';
			$html .= '<span class="sortable-handler ' . $disableClassName . '" title="' . $disabledLabel . '">';
			$html .= '<span class="' . ($disableClassName ? $inactiveIcon : $icon) . '"></span>';
			$html .= '</span>';

			if ($dndOrderingActive)
			{
				$joomla35IsBroken = version_compare(JVERSION, '3.5.0', 'ge') ? 'style="display: none"' : '';

				$html .= '<input type="text" name="order[]" ' . $joomla35IsBroken . ' size="5" class="' . $class . ' text-area-order" value="' . $order . '" />';
			}

			$html .= '</div>';
		}

		return $html;
	}

	/**
	 * Returns the drag'n'drop reordering table header for Browse views
	 *
	 * @param   string  $orderingField  The name of the field you're ordering by
	 * @param   string  $icon           CSS class for the d'n'd handle icon
	 *
	 * @return string
	 */
	public static function orderfield($orderingField = 'ordering', $icon = 'akion-stats-bars')
	{
		$title         = Text::_('JGLOBAL_CLICK_TO_SORT_THIS_COLUMN');
		$orderingLabel = Text::_('JFIELD_ORDERING_LABEL');

		return <<< HTML
<a href="#"
   onclick="Joomla.tableOrdering('{$orderingField}','asc','');return false;"
   class="hasPopover"
   title="{$orderingLabel}"
   data-content="{$title}"
   data-placement="top"
>
    <span class="{$icon}"></span>
</a>

HTML;
	}


	/**
	 * Creates an order-up action icon.
	 *
	 * @param   integer       $i         The row index.
	 * @param   string        $task      An optional task to fire.
	 * @param   string|array  $prefix    An optional task prefix or an array of options
	 * @param   string        $text      An optional text to display
	 * @param   boolean       $enabled   An optional setting for access control on the action.
	 * @param   string        $checkbox  An optional prefix for checkboxes.
	 *
	 * @return  string  The HTML markup
	 *
	 * @since   3.3.0
	 */
	public static function orderUp($i, $task = 'orderup', $prefix = '', $text = 'JLIB_HTML_MOVE_UP', $enabled = true, $checkbox = 'cb')
	{
		if (is_array($prefix))
		{
			$options  = $prefix;
			$text     = array_key_exists('text', $options) ? $options['text'] : $text;
			$enabled  = array_key_exists('enabled', $options) ? $options['enabled'] : $enabled;
			$checkbox = array_key_exists('checkbox', $options) ? $options['checkbox'] : $checkbox;
			$prefix   = array_key_exists('prefix', $options) ? $options['prefix'] : '';
		}

		return static::action($i, $task, $prefix, $text, $text, false, 'arrow-up-b', 'arrow-up-b', $enabled, true, $checkbox);
	}

	/**
	 * Creates an order-down action icon.
	 *
	 * @param   integer       $i         The row index.
	 * @param   string        $task      An optional task to fire.
	 * @param   string|array  $prefix    An optional task prefix or an array of options
	 * @param   string        $text      An optional text to display
	 * @param   boolean       $enabled   An optional setting for access control on the action.
	 * @param   string        $checkbox  An optional prefix for checkboxes.
	 *
	 * @return  string  The HTML markup
	 *
	 * @since   3.3.0
	 */
	public static function orderDown($i, $task = 'orderdown', $prefix = '', $text = 'JLIB_HTML_MOVE_DOWN', $enabled = true, $checkbox = 'cb')
	{
		if (is_array($prefix))
		{
			$options  = $prefix;
			$text     = array_key_exists('text', $options) ? $options['text'] : $text;
			$enabled  = array_key_exists('enabled', $options) ? $options['enabled'] : $enabled;
			$checkbox = array_key_exists('checkbox', $options) ? $options['checkbox'] : $checkbox;
			$prefix   = array_key_exists('prefix', $options) ? $options['prefix'] : '';
		}

		return static::action($i, $task, $prefix, $text, $text, false, 'arrow-down-b', 'arrow-down-b', $enabled, true, $checkbox);
	}

	/**
	 * Table header for a field which changes the sort order when clicked
	 *
	 * @param   string  $title          The link title
	 * @param   string  $order          The order field for the column
	 * @param   string  $direction      The current direction
	 * @param   string  $selected       The selected ordering
	 * @param   string  $task           An optional task override
	 * @param   string  $new_direction  An optional direction for the new column
	 * @param   string  $tip            An optional text shown as tooltip title instead of $title
	 * @param   string  $form           An optional form selector
	 *
	 * @return  string
	 *
	 * @since   3.3.0
	 */
	public static function sort($title, $order, $direction = 'asc', $selected = '', $task = null, $new_direction = 'asc', $tip = '', $form = null)
	{
		HTMLHelper::_('behavior.core');
		HTMLHelper::_('bootstrap.popover');

		$direction = strtolower($direction);
		$icon      = ['akion-android-arrow-dropup', 'akion-android-arrow-dropdown'];
		$index     = (int) ($direction === 'desc');

		if ($order != $selected)
		{
			$direction = $new_direction;
		}
		else
		{
			$direction = $direction === 'desc' ? 'asc' : 'desc';
		}

		if ($form)
		{
			$form = ', document.getElementById(\'' . $form . '\')';
		}

		$html = '<a href="#" onclick="Joomla.tableOrdering(\'' . $order . '\',\'' . $direction . '\',\'' . $task . '\'' . $form . ');return false;"'
			. ' class="hasPopover" title="' . htmlspecialchars(Text::_($tip ?: $title)) . '"'
			. ' data-content="' . htmlspecialchars(Text::_('JGLOBAL_CLICK_TO_SORT_THIS_COLUMN')) . '" data-placement="top">';

		if (isset($title['0']) && $title['0'] === '<')
		{
			$html .= $title;
		}
		else
		{
			$html .= Text::_($title);
		}

		if ($order == $selected)
		{
			$html .= '<span class="' . $icon[$index] . '"></span>';
		}

		$html .= '</a>';

		return $html;
	}

	/**
	 * Method to check all checkboxes on the browse view's table
	 *
	 * @param   string  $name    The name of the form element
	 * @param   string  $tip     The text shown as tooltip title instead of $tip
	 * @param   string  $action  The action to perform on clicking the checkbox
	 *
	 * @return  string
	 *
	 * @since   3.3.0
	 */
	public static function checkall($name = 'checkall-toggle', $tip = 'JGLOBAL_CHECK_ALL', $action = 'Joomla.checkAll(this)')
	{
		HTMLHelper::_('behavior.core');
		HTMLHelper::_('bootstrap.tooltip');

		return '<input type="checkbox" name="' . $name . '" value="" class="hasTooltip" title="' . HTMLHelper::_('tooltipText', $tip)
			. '" onclick="' . $action . '" />';
	}

	/**
	 * Method to create a checkbox for a grid row.
	 *
	 * @param   integer  $rowNum      The row index
	 * @param   integer  $recId       The record id
	 * @param   boolean  $checkedOut  True if item is checked out
	 * @param   string   $name        The name of the form element
	 * @param   string   $stub        The name of stub identifier
	 *
	 * @return  mixed    String of html with a checkbox if item is not checked out, null if checked out.
	 *
	 * @since   3.3.0
	 */
	public static function id($rowNum, $recId, $checkedOut = false, $name = 'cid', $stub = 'cb')
	{
		return $checkedOut ? '' : '<input type="checkbox" id="' . $stub . $rowNum . '" name="' . $name . '[]" value="' . $recId
			. '" onclick="Joomla.isChecked(this.checked);" />';
	}

	/**
	 * Include the necessary JavaScript for the browse view's table order feature
	 *
	 * @param   string  $orderBy  Filed by which we are currently sorting the table.
	 * @param   bool    $return   Should I return the JS? Default: false (= add to the page's head)
	 *
	 * @return string
	 */
	public static function orderjs($orderBy, $return = false)
	{
		$js = <<< JS

Joomla.orderTable = function()
{
		var table = document.getElementById("sortTable");
		var direction = document.getElementById("directionTable");
		var order = table.options[table.selectedIndex].value;
		var dirn = 'asc';

		if (order !== '$orderBy')
		{
			dirn = 'asc';
		}
		else {
			dirn = direction.options[direction.selectedIndex].value;
		}

		Joomla.tableOrdering(order, dirn);
	};
JS;

		if ($return)
		{
			return $js;
		}

		try
		{
			Factory::getApplication()->getDocument()->addScriptDeclaration($js);
		}
		catch (Exception $e)
		{
			// If we have no application, well, not having table sorting JS is the least of your worries...
		}
	}

	/**
	 * Returns the table ordering / pagination header for a browse view: number of records to display, order direction,
	 * order by field.
	 *
	 * @param   DataViewRaw  $view                              The view you're rendering against. If not provided we
	 *                                                          will guess it using MAGIC.
	 * @param   array        $sortFields                        Array of field name => description for the ordering
	 *                                                          fields in the dropdown. If not provided we will use all
	 *                                                          the fields available in the model.
	 * @param   Pagination   $pagination                        The Joomla pagination object. If not provided we fetch
	 *                                                          it from the view.
	 * @param   string       $sortBy                            Order by field name. If not provided we fetch it from
	 *                                                          the view.
	 * @param   string       $order_Dir                         Order direction. If not provided we fetch it from the
	 *                                                          view.
	 *
	 * @return  string
	 *
	 * @since   3.3.0
	 */
	public static function orderheader(DataViewRaw $view = null, array $sortFields = [], Pagination $pagination = null, $sortBy = null, $order_Dir = null)
	{
		if (is_null($view))
		{
			$view = BrowseView::getViewFromBacktrace();
		}

		if (empty($sortFields))
		{
			/** @var DataModel $model */
			$model      = $view->getModel();
			$sortFields = $view->getLists()->sortFields ?? [];
			$sortFields = empty($sortFields) ? self::getSortFields($model) : $sortFields;
		}

		if (empty($pagination))
		{
			$pagination = $view->getPagination();
		}

		if (empty($sortBy))
		{
			$sortBy = $view->getLists()->order;
		}

		if (empty($order_Dir))
		{
			$order_Dir = $view->getLists()->order_Dir;

			if (empty($order_Dir))
			{
				$order_Dir = 'asc';
			}
		}

		// Static hidden text labels
		$limitLabel    = Text::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');
		$orderingDescr = Text::_('JFIELD_ORDERING_DESC');
		$sortByLabel   = Text::_('JGLOBAL_SORT_BY');

		// Order direction dropdown
		$directionSelect = HTMLHelper::_('FEFHelper.select.genericlist', [
			''     => $orderingDescr,
			'asc'  => Text::_('JGLOBAL_ORDER_ASCENDING'),
			'desc' => Text::_('JGLOBAL_ORDER_DESCENDING'),
		], 'directionTable', [
			'id'          => 'directionTable',
			'list.select' => $order_Dir,
			'list.attr'   => [
				'class'    => 'input-medium custom-select',
				'onchange' => 'Joomla.orderTable()',
			],
		]);

		// Sort by field dropdown

		$sortTable = HTMLHelper::_('FEFHelper.select.genericlist', array_merge([
			'' => Text::_('JGLOBAL_SORT_BY'),
		], $sortFields), 'sortTable', [
			'id'          => 'sortTable',
			'list.select' => $sortBy,
			'list.attr'   => [
				'class'    => 'input-medium custom-select',
				'onchange' => 'Joomla.orderTable()',
			],
		]);


		$html = <<<HTML
		<div class="akeeba-filter-element akeeba-form-group">
			<label for="limit" class="element-invisible">
				$limitLabel
			</label>
			{$pagination->getLimitBox()}
		</div>

		<div class="akeeba-filter-element akeeba-form-group">
			<label for="directionTable" class="element-invisible">
				$orderingDescr
			</label>
			$directionSelect
		</div>

		<div class="akeeba-filter-element akeeba-form-group">
			<label for="sortTable" class="element-invisible">
				{$sortByLabel}
			</label>
			$sortTable
		</div>

HTML;

		return $html;
	}

	/**
	 * Get the default sort fields from a model. It creates a hash array where the keys are the model's field names and
	 * the values are the translation keys for their names, following FOF's naming conventions.
	 *
	 * @param   DataModel  $model  The model for which we get the sort fields
	 *
	 * @return  array
	 *
	 * @since   3.3.0
	 */
	private static function getSortFields(DataModel $model)
	{
		$sortFields         = [];
		$idField            = $model->getIdFieldName() ?: 'id';
		$defaultFieldLabels = [
			'publish_up'   => 'JGLOBAL_FIELD_PUBLISH_UP_LABEL',
			'publish_down' => 'JGLOBAL_FIELD_PUBLISH_DOWN_LABEL',
			'created_by'   => 'JGLOBAL_FIELD_CREATED_BY_LABEL',
			'created_on'   => 'JGLOBAL_FIELD_CREATED_LABEL',
			'modified_by'  => 'JGLOBAL_FIELD_MODIFIED_BY_LABEL',
			'modified_on'  => 'JGLOBAL_FIELD_MODIFIED_LABEL',
			'ordering'     => 'JGLOBAL_FIELD_FIELD_ORDERING_LABEL',
			'id'           => 'JGLOBAL_FIELD_ID_LABEL',
			'hits'         => 'JGLOBAL_HITS',
			'title'        => 'JGLOBAL_TITLE',
			'user_id'      => 'JGLOBAL_USERNAME',
			'username'     => 'JGLOBAL_USERNAME',
		];
		$componentName      = $model->getContainer()->componentName;
		$viewNameSingular   = $model->getContainer()->inflector->singularize($model->getName());
		$viewNamePlural     = $model->getContainer()->inflector->pluralize($model->getName());

		foreach ($model->getFields() as $field => $fieldDescriptor)
		{
			$possibleKeys = [
				$componentName . '_' . $viewNamePlural . '_FIELD_' . $field,
				$componentName . '_' . $viewNamePlural . '_' . $field,
				$componentName . '_' . $viewNameSingular . '_FIELD_' . $field,
				$componentName . '_' . $viewNameSingular . '_' . $field,
			];

			if (array_key_exists($field, $defaultFieldLabels))
			{
				$possibleKeys[] = $defaultFieldLabels[$field];
			}

			if ($field === $idField)
			{
				$possibleKeys[] = $defaultFieldLabels['id'];
			}

			$fieldLabel = '';

			foreach ($possibleKeys as $langKey)
			{
				$langKey    = strtoupper($langKey);
				$fieldLabel = Text::_($langKey);

				if ($fieldLabel !== $langKey)
				{
					break;
				}

				$fieldLabel = '';
			}

			if (!empty($fieldLabel))
			{
				$sortFields[$field] = (new Joomla\Filter\InputFilter())->clean($fieldLabel);
			}

		}

		return $sortFields;
	}
}
