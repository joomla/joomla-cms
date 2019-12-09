<?php
/**
 * @package    Joomla.Site
 * @subpackage mod_menu
 *
 * @copyright  2019 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

/** @var array $templateparams */

$iconCaret = $templateparams->get('icondownCaret');
// Flips iconCaret if navbar fixed @ bottom.
if($templateparams->get('nav_Location') === 'navbar-fixed-bottom'){
	// Flip caret
	$iconCaret = $templateparams->get('iconupCaret');
}

$attributes = array();

if ($item->anchor_title)
{
	$attributes['title'] = $item->anchor_title;
}

if ($item->anchor_css)
{
	$attributes['class'] = $item->anchor_css;
}

if ($item->anchor_rel)
{
	$attributes['rel'] = $item->anchor_rel;
}

$linktype = $item->title;

if ($item->menu_image)
{
	if ($item->menu_image_css)
	{
		$image_attributes['class'] = $item->menu_image_css;
		$linktype                  = JHtml::_('image', $item->menu_image, $item->title, $image_attributes);
	}
	else
	{
		$linktype = JHtml::_('image', $item->menu_image, $item->title);
	}

	if ($item->params->get('menu_text', 1))
	{
		$linktype .= '<span class="image-title">' . $item->title . '</span>';
	}
}
// Allow icon instead of image.
elseif ($item->menu_image_css)
{
	$linktype = '<i class="' . $item->menu_image_css . '"> </i>' . $linktype;
}

if ($item->browserNav == 1)
{
	$attributes['target'] = '_blank';
}
elseif ($item->browserNav == 2)
{
	$options = 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes';

	$attributes['onclick'] = "window.open(this.href, 'targetWindow', '" . $options . "'); return false;";
}

if ($active_id == $item->id || $active_id == $item->params['aliasoptions'])
{
	$attributes['aria-current'] = 'page';
}

// Add Bootstrap caret
if ($item->isParentAnchor)
{
	$linktype .= '<i class="' . $iconCaret . '"></i>';
}

// These actually cause the dropdown to occur.  /** @see https://getbootstrap.com/docs/3.4/components/#navbar **/
if ($item->deeper)
{
	$attributes['data-toggle']    = 'dropdown';
	$attributes['role']           = "button";
	$attributes['aria-has-popup'] = 'true';
	$attributes['aria-expanded']  = 'false';
	$attributes['class']          = 'dropdown-toggle';
}
echo HTMLHelper::_('link', JFilterOutput::ampReplace(htmlspecialchars($item->flink, ENT_COMPAT, 'UTF-8', false)), $linktype, $attributes);
