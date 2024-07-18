<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML\Helpers;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * HTML utility class for building a dropdown menu
 *
 * @since  3.0
 */
abstract class Dropdown
{
    /**
     * @var    array  Array containing information for loaded files
     * @since  3.0
     */
    protected static $loaded = [];

    /**
     * @var    string  HTML markup for the dropdown list
     * @since  3.0
     */
    protected static $dropDownList = null;

    /**
     * Method to inject needed script
     *
     * @return  void
     *
     * @since   3.0
     */
    public static function init()
    {
        // Only load once
        if (isset(static::$loaded[__METHOD__])) {
            return;
        }

        // Depends on Bootstrap
        HTMLHelper::_('bootstrap.framework');

        Factory::getDocument()->addScriptDeclaration("
			(function($){
				$(document).ready(function (){
					$('.has-context')
					.mouseenter(function (){
						$('.btn-group',$(this)).show();
					})
					.mouseleave(function (){
						$('.btn-group',$(this)).hide();
						$('.btn-group',$(this)).removeClass('open');
					});

					contextAction =function (cbId, task)
					{
						$('input[name=\"cid[]\"]').removeAttr('checked');
						$('#' + cbId).attr('checked','checked');
						Joomla.submitbutton(task);
					}
				});
			})(jQuery);
			");

        // Set static array
        static::$loaded[__METHOD__] = true;
    }

    /**
     * Method to start a new dropdown menu
     *
     * @return  void
     *
     * @since   3.0
     */
    public static function start()
    {
        // Only start once
        if (isset(static::$loaded[__METHOD__]) && static::$loaded[__METHOD__] == true) {
            return;
        }

        $dropDownList = '<div class="btn-group" style="margin-left:6px;display:none">
							<a href="#" data-bs-toggle="dropdown" class="dropdown-toggle btn btn-secondary btn-sm">
								<span class="caret"></span>
							</a>
							<ul class="dropdown-menu">';
        static::$dropDownList       = $dropDownList;
        static::$loaded[__METHOD__] = true;
    }

    /**
     * Method to render current dropdown menu
     *
     * @return  string  HTML markup for the dropdown list
     *
     * @since   3.0
     */
    public static function render()
    {
        $dropDownList  = static::$dropDownList;
        $dropDownList .= '</ul></div>';

        static::$dropDownList                  = null;
        static::$loaded[__CLASS__ . '::start'] = false;

        return $dropDownList;
    }

    /**
     * Append an edit item to the current dropdown menu
     *
     * @param   integer  $id          Record ID
     * @param   string   $prefix      Task prefix
     * @param   string   $customLink  The custom link if dont use default Joomla action format
     *
     * @return  void
     *
     * @since   3.0
     */
    public static function edit($id, $prefix = '', $customLink = '')
    {
        static::start();

        if (!$customLink) {
            $option = Factory::getApplication()->getInput()->getCmd('option');
            $link   = 'index.php?option=' . $option;
        } else {
            $link = $customLink;
        }

        $link .= '&task=' . $prefix . 'edit&id=' . $id;
        $link = Route::_($link);

        static::addCustomItem(Text::_('JACTION_EDIT'), $link);
    }

    /**
     * Append a publish item to the current dropdown menu
     *
     * @param   string  $checkboxId  ID of corresponding checkbox of the record
     * @param   string  $prefix      The task prefix
     *
     * @return  void
     *
     * @since   3.0
     */
    public static function publish($checkboxId, $prefix = '')
    {
        $task = $prefix . 'publish';
        static::addCustomItem(Text::_('JTOOLBAR_PUBLISH'), 'javascript:void(0)', 'onclick="contextAction(\'' . $checkboxId . '\', \'' . $task . '\')"');
    }

    /**
     * Append an unpublish item to the current dropdown menu
     *
     * @param   string  $checkboxId  ID of corresponding checkbox of the record
     * @param   string  $prefix      The task prefix
     *
     * @return  void
     *
     * @since   3.0
     */
    public static function unpublish($checkboxId, $prefix = '')
    {
        $task = $prefix . 'unpublish';
        static::addCustomItem(Text::_('JTOOLBAR_UNPUBLISH'), 'javascript:void(0)', 'onclick="contextAction(\'' . $checkboxId . '\', \'' . $task . '\')"');
    }

    /**
     * Append a featured item to the current dropdown menu
     *
     * @param   string  $checkboxId  ID of corresponding checkbox of the record
     * @param   string  $prefix      The task prefix
     *
     * @return  void
     *
     * @since   3.0
     */
    public static function featured($checkboxId, $prefix = '')
    {
        $task = $prefix . 'featured';
        static::addCustomItem(Text::_('JFEATURED'), 'javascript:void(0)', 'onclick="contextAction(\'' . $checkboxId . '\', \'' . $task . '\')"');
    }

    /**
     * Append an unfeatured item to the current dropdown menu
     *
     * @param   string  $checkboxId  ID of corresponding checkbox of the record
     * @param   string  $prefix      The task prefix
     *
     * @return  void
     *
     * @since   3.0
     */
    public static function unfeatured($checkboxId, $prefix = '')
    {
        $task = $prefix . 'unfeatured';
        static::addCustomItem(Text::_('JUNFEATURED'), 'javascript:void(0)', 'onclick="contextAction(\'' . $checkboxId . '\', \'' . $task . '\')"');
    }

    /**
     * Append an archive item to the current dropdown menu
     *
     * @param   string  $checkboxId  ID of corresponding checkbox of the record
     * @param   string  $prefix      The task prefix
     *
     * @return  void
     *
     * @since   3.0
     */
    public static function archive($checkboxId, $prefix = '')
    {
        $task = $prefix . 'archive';
        static::addCustomItem(Text::_('JTOOLBAR_ARCHIVE'), 'javascript:void(0)', 'onclick="contextAction(\'' . $checkboxId . '\', \'' . $task . '\')"');
    }

    /**
     * Append an unarchive item to the current dropdown menu
     *
     * @param   string  $checkboxId  ID of corresponding checkbox of the record
     * @param   string  $prefix      The task prefix
     *
     * @return  void
     *
     * @since   3.0
     */
    public static function unarchive($checkboxId, $prefix = '')
    {
        $task = $prefix . 'unpublish';
        static::addCustomItem(Text::_('JTOOLBAR_UNARCHIVE'), 'javascript:void(0)', 'onclick="contextAction(\'' . $checkboxId . '\', \'' . $task . '\')"');
    }

    /**
     * Append a trash item to the current dropdown menu
     *
     * @param   string  $checkboxId  ID of corresponding checkbox of the record
     * @param   string  $prefix      The task prefix
     *
     * @return  void
     *
     * @since   3.0
     */
    public static function trash($checkboxId, $prefix = '')
    {
        $task = $prefix . 'trash';
        static::addCustomItem(Text::_('JTOOLBAR_TRASH'), 'javascript:void(0)', 'onclick="contextAction(\'' . $checkboxId . '\', \'' . $task . '\')"');
    }

    /**
     * Append an untrash item to the current dropdown menu
     *
     * @param   string  $checkboxId  ID of corresponding checkbox of the record
     * @param   string  $prefix      The task prefix
     *
     * @return  void
     *
     * @since   3.0
     */
    public static function untrash($checkboxId, $prefix = '')
    {
        $task = $prefix . 'publish';
        static::addCustomItem(Text::_('JTOOLBAR_UNTRASH'), 'javascript:void(0)', 'onclick="contextAction(\'' . $checkboxId . '\', \'' . $task . '\')"');
    }

    /**
     * Append a checkin item to the current dropdown menu
     *
     * @param   string  $checkboxId  ID of corresponding checkbox of the record
     * @param   string  $prefix      The task prefix
     *
     * @return  void
     *
     * @since   3.0
     */
    public static function checkin($checkboxId, $prefix = '')
    {
        $task = $prefix . 'checkin';
        static::addCustomItem(Text::_('JTOOLBAR_CHECKIN'), 'javascript:void(0)', 'onclick="contextAction(\'' . $checkboxId . '\', \'' . $task . '\')"');
    }

    /**
     * Writes a divider between dropdown items
     *
     * @return  void
     *
     * @since   3.0
     */
    public static function divider()
    {
        static::$dropDownList .= '<li class="divider"></li>';
    }

    /**
     * Append a custom item to current dropdown menu
     *
     * @param   string   $label           The label of item
     * @param   string   $link            The link of item
     * @param   string   $linkAttributes  Custom link attributes
     * @param   string   $className       Class name of item
     * @param   boolean  $ajaxLoad        True if using ajax load when item clicked
     * @param   string   $jsCallBackFunc  Javascript function name, called when ajax load successfully
     *
     * @return  void
     *
     * @since   3.0
     */
    public static function addCustomItem(
        $label,
        $link = 'javascript:void(0)',
        $linkAttributes = '',
        $className = '',
        $ajaxLoad = false,
        $jsCallBackFunc = null
    ) {
        static::start();

        if ($ajaxLoad) {
            $href = ' href = "javascript:void(0)" onclick="loadAjax(\'' . $link . '\', \'' . $jsCallBackFunc . '\')"';
        } else {
            $href = ' href = "' . $link . '" ';
        }

        $dropDownList = static::$dropDownList;
        $dropDownList .= '<li class="' . $className . '"><a ' . $linkAttributes . $href . ' >';
        $dropDownList .= $label;
        $dropDownList .= '</a></li>';
        static::$dropDownList = $dropDownList;
    }
}
