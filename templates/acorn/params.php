<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 * @version     14-Nov-19
 * @copyright   Copyright(c) 2016-2019 Troy T. Hall
 * @license     GPL2
 */
defined('_JEXEC') or die;

use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Factory;

/** @var string $googlefontCSS */
/** @var string $googleFont */
/** @var string $debug */

// $data contains template params use $data->get('') to retrieve the params.
// $template contains template folder.
// __DIR__ is template filepath
// $css is the default variable required to be returned.

// use the following to automatically output varname in the line above the css
// if($debug){ $style .= "\n" . '/* $myvargoeshere */' . "\n";}

if (defined('JDEBUG') && JDEBUG)
{
	$debug = true;
}
else
{
	$debug = false;
}
// Includes.
include_once Path::clean(__DIR__ . '/framework/functions.php');

$iconCaret = $data->get('icondownCaret', 'icon-arrow-down-3');

/*
 * ==================================================
 * Typography elements & Google fonts
 * ------------- MUST BE FIRST!!!  ------------------
 * ==================================================
 */
// Make sure we have both Font and font family
$googleFont = $data->get('googleFont');
$fontfamily = trim($data->get('googlefontCSS'));

if ($fontfamily && $googleFont)
{

// Strip everything preceding @
	$stripstart = stristr($googleFont, "@");

// Strip everything after &
	$import = trim(stristr($stripstart, '&', true) . "');");

// Save fontfamily as comment so it can be inserted right after @import.
	$fontfamily = "/*\n" . trim($fontfamily) . "\n*/";

// Since we're using @ import it MUST be first!!
	$css = $import . "\n" . $fontfamily . "\n";
}
if ($debug)
{
	$css .= '/* End of Google Fonts */' . "\n";
}

/*
 * ================================
 *  ------------ LOGO - -----------
 * ================================
 */

// Logo Params
$json  = false;
$style = '';
if ($data->get('extendedlogoParams'))
{
	$logoWidth                    = checkPX($data->get('logoWidth'));
	$logoHeight                   = checkPX($data->get('logoHeight'));
	$logoMargin                   = checkPX($data->get('logoMargin'));
	$logoPadding                  = checkPX($data->get('logoPadding'));
	$logohorizontal_alignmentment = $data->get('logohorizontal_alignment');
	$logovertical_alignmentment   = $data->get('logovertical_alignment');

	// SET LOGO image WIDTH AND HEIGHT
	if ($debug)
	{
		$style .= "\n" . '/* logo */' . "\n";
	}
	$style .= ".navbar-header .logo img{ \n";
	if ($logoWidth)
	{
		$style .= "	width: " . $logoWidth . ";\n";
		$style .= "	min-width: " . $logoWidth . ";\n";
		$style .= "	max-width: " . $logoWidth . ";\n";
	}
	if ($logoHeight)
	{
		$style .= "	height: " . $logoHeight . ";\n";
		$style .= "	min-height: " . $logoHeight . ";\n";
		$style .= "	max-height: " . $logoHeight . ";\n";
	}
	if ($logoMargin)
	{
		$style .= "	margin: " . $logoMargin . ";\n";
	}
	if ($logoPadding)
	{
		$style .= "	padding: " . $logoPadding . ";\n";
	}
	$style .= "}\n";


	// Allow for positioning of the logo
	$style .= ".logo {\n"
		. "	justify-self: " . $logohorizontal_alignmentment . ";\n"
		. "	align-self: " . $logovertical_alignmentment . ";\n"
		. "}\n";
}
if ($debug)
{
	$style .= '/* END OF LOGO PARAMS */' . "\n";
}
// Add it to $css
$css .= $style;

/*
 * =============================
 *  --------- Layout -----------
 * =============================
 */

$bodytopMargin = checkPX($data->get('bodytopMargin', ''));
if ($debug && $bodytopMargin)
{
	$css .= "\n" . '/* $bodytopMargin */' . "\n";
}
$css .= $bodytopMargin ? "body{\n    margin-top: " . $bodytopMargin . ";\n}\n" : '';

if ($debug && $bodytopMargin)
{
	$style .= "\n" . '/* END OF LAYOUT PARAMS */' . "\n";
}


/*
 * =================================
 *  --------- TYPOGRAPHY -----------
 * =================================
 */
// their is going to be more then one element so parse 1 v 1

$subformData = $data->get('typography');
if ($debug && $subformData)
{
	$style .= "\n" . '/* TYPOGRAPHY PARAMS */' . "\n";
}
// Parse
$style = '';
foreach ($subformData as $value)
{
	$typographyElement  = $value->typographyElement;
	$typographyClass    = $value->typographyClass;
	$typographyFont     = $value->typographyFont;
	$typographyColor    = $value->typographyColor;
	$typographyfontSize = checkPX($value->typographyfontSize);
	$typographyMargin   = checkPX($value->typographyMargin);
	$typographyPadding  = checkPX($value->typographyPadding);

	if ($typographyPadding || $typographyMargin || $typographyfontSize || $typographyFont || $typographyColor):
		$style .= $typographyElement . $typographyClass . "{\n";
		$style .= $typographyColor ? "  color: " . $typographyColor . ";\n" : '';
		$style .= $typographyFont ? "   font-family: " . $typographyFont . ";\n" : '';
		$style .= $typographyfontSize ? "   font-size: " . $typographyfontSize . ";\n" : '';
		$style .= $typographyMargin ? " margin: " . $typographyMargin . ";\n" : '';
		$style .= $typographyPadding ? "    padding: " . $typographyPadding . ";\n" : '';
		$style .= "}\n";
	endif;
}
$css   .= $style;
$style = '';
// END OF TYPOGRAPHY

/*
 * ================================
 *  --------- Main Menu -----------
 * ================================
 */
// Variables
$nav_bg_color             = '';
$nav_Top                  = '';
$nav_Bottom               = '';
$nav_Left                 = '';
$nav_Right                = '';
$nav_Border               = '';
$style                    = '';
$nav_Location             = $data->get('nav_Location');
$nav_lineheight           = $data->get('nav_lineheight');
$nav_itemHeight           = checkPX($data->get('nav_itemHeight'));
$nav_horizontal_alignment = $data->get('nav_horizontal_alignment');
$nav_style                = $data->get('nav_style');
$nav_vertical_alignment   = $data->get('nav_vertical_alignment');

if ($nav_Location === 'navbar-standard')
{
	if ($debug)
	{
		$style .= "\n" . '/* $nav_dropdownlinkColor */' . "\n";
	}
	$style .= "nav.navbar." . $nav_Location . " #nav-toggle{\n"
		. " padding-left: 0;\n"
		. " padding-right: 0;\n"
		. "}\n";
}
if ($debug)
{
	$style .= "\n" . '/* $nav_Location */' . "\n";
}
// Align main menu
$style .= "nav.navbar." . $nav_Location . "{\n"
	. "	justify-content: " . $nav_horizontal_alignment . ";\n"
	. "	align-items: " . $nav_vertical_alignment . ";\n}\n";

// Set lineheight
if ($nav_lineheight || $nav_itemHeight)
{
	if ($debug)
	{
		$style .= "\n" . '/* $nav_itemHeight & $nav_lineheight */' . "\n";
	}
	$style .= "nav li.nav-item *{\n";
	$style .= $nav_lineheight ? " line-height: " . $nav_lineheight . ";\n" : '';
	$style .= $nav_itemHeight ? " font-size: " . $nav_itemHeight . ";\n" : '';
	$style .= "}\n";
}
$css   .= $style;
$style = '';

if ($debug)
{
	$style .= '/* END OF MAIN MENU PARAMS */' . "\n";
}
// Check for extended parameters to be active
if ($data->get('extendedmainmenuParams'))
{
	/**
	 * ==================================================
	 * Call Main Menu color chooser
	 * ==================================================
	 */
	// Section Nav Background
	$nav_backgroundColor              = $data->get('nav_extendedParams.nav_backgroundColor');
	$nav_menubackgroundColor          = $data->get('nav_extendedParams.nav_menubackgroundColor');
	$nav_activebackgroundColor        = $data->get('nav_extendedParams.nav_activebackgroundColor');
	$nav_hoverbackgroundColor         = $data->get('nav_extendedParams.nav_hoverbackgroundColor');
	$nav_linkbackgroundColor          = $data->get('nav_extendedParams.nav_linkbackgroundColor');
	$nav_linkhoverbackgroundColor     = $data->get('nav_extendedParams.nav_linkhoverbackgroundColor');
	$nav_dropdownbackgroundColor      = $data->get('nav_extendedParams.nav_dropdownbackgroundColor');
	$nav_dropdownhoverbackgroundColor = $data->get('nav_extendedParams.nav_dropdownhoverbackgroundColor');

	// Section Nav Text Colors
	$nav_textColor          = $data->get('nav_extendedParams.nav_textColor');
	$nav_texthoverColor     = $data->get('nav_extendedParams.nav_texthoverColor');
	$nav_linkColor          = $data->get('nav_extendedParams.nav_linkColor');
	$nav_linkhoverColor     = $data->get('nav_extendedParams.nav_linkhoverColor');
	$nav_activeColor        = $data->get('nav_extendedParams.nav_activeColor');
	$nav_activehoverColor   = $data->get('nav_extendedParams.nav_activehoverColor');
	$nav_dropdownColor      = $data->get('nav_extendedParams.nav_dropdownColor');
	$nav_dropdownhoverColor = $data->get('nav_extendedParams.nav_dropdownhoverColor');
	$nav_dropdownlinkColor  = $data->get('nav_extendedParams.nav_dropdownlinkColor');

	// NAV AREA
	if ($nav_backgroundColor)
	{
		if ($debug)
		{
			$style .= "\n" . '/* $nav_backgroundColor */' . "\n";
		}
		$style .= "nav.navbar{\n"
			. " background-color: " . $nav_backgroundColor . ";\n"
			. "}\n";
	}
	if ($nav_menubackgroundColor)
	{
		if ($debug)
		{
			$style .= "\n" . '/* $nav_menubackgroundColor */' . "\n";
		}
		$style .= "nav  {\n"
			. " background-color: " . $nav_menubackgroundColor
			. ";\n}\n";
	}

	if ($nav_activebackgroundColor)
	{
		if ($debug)
		{
			$style .= "\n" . '/* $nav_activebackgroundColor */' . "\n";
		}
		$style .= ".navbar-nav > .active > a,\n"
			. ".navbar-nav > .active > a:hover,\n"
			. ".navbar-nav > .active > a:focus,\n"
			. ".navbar-default .navbar-nav > .active > a,\n"
			. ".navbar-default .navbar-nav > .active > a:hover,\n"
			. ".navbar-default .navbar-nav > .active > a:focus,\n"
			. ".navbar-inverse .navbar-nav > .active > a,\n"
			. ".navbar-inverse .navbar-nav > .active > a:hover,\n"
			. ".navbar-inverse .navbar-nav > .active > a:focus,\n"
			. ".navbar-nav > .active > span.heading,\n"
			. ".navbar-nav > .active > span.heading:hover,\n"
			. ".navbar-nav > .active > span.heading:focus,\n"
			. ".navbar-nav > .active > span.separator,\n"
			. ".navbar-nav > .active > span.separator:hover,\n"
			. ".navbar-nav > .active > span.separator:focus,\n"
			. ".navbar-default .dropdown-menu > .active > a,\n"
			. ".navbar-default .dropdown-menu > .active > a:hover,\n"
			. ".navbar-default .dropdown-menu > .active > a:focus{\n"
			. " background-color: " . $nav_activebackgroundColor . ";\n"
			. "}\n";
	}

	if ($nav_hoverbackgroundColor)
	{
		if ($debug)
		{
			$style .= "\n" . '/* $nav_hoverbackgroundColor */' . "\n";
		}
		$style .= "nav ul.navbar-nav > li:hover,\n"
			. "nav ul.navbar-nav > li:focus{\n "
			. " background-color: " . $nav_hoverbackgroundColor . ";\n"
			. "}\n";
	}
	if ($nav_textColor)
	{
		if ($debug)
		{
			$style .= "\n" . '/* $nav_textColor */' . "\n";
		}
		$style .= "nav,\n"
			. "nav ul.nav,\n"
			. "nav ul.nav > li,\n"
			. "nav ul.nav > li > span.separator,\n"
			. "nav ul.nav > li > span.heading,\n"
			. "nav .navbar-nav > li > span.heading,\n"
			. "nav .navbar-nav > li > span.separator,\n"
			. "nav span.heading,\n"
			. "nav span.separator{\n"
			. " color: " . $nav_textColor . ";\n"
			. "}\n";
	}
	if ($nav_texthoverColor)
	{
		if ($debug)
		{
			$style .= "\n" . '/* $nav_texthoverColor */' . "\n";
		}
		$style .= "nav ul.nav > li:hover,\n"
			. "nav ul.nav > li > span.separator:hover,\n"
			. "nav ul.nav > li > span.heading:hover,\n"
			. "nav .navbar-nav > li > span.heading:hover,\n"
			. "nav .navbar-nav > li > span.separator:hover,\n"
			. "nav span.heading:hover,\n"
			. "nav span.separator:hover,\n"
			. "nav ul.nav > li:focus,\n"
			. "nav ul.nav > li > span.separator:focus,\n"
			. "nav ul.nav > li > span.heading:focus,\n"
			. "nav .navbar-nav > li > span.heading:focus,\n"
			. "nav .navbar-nav > li > span.separator:focus,\n"
			. "nav span.heading:focus,\n"
			. "nav span.separator:focus{\n"
			. " color: " . $nav_texthoverColor . ";\n"
			. "}\n";
	}


	// NAV LINKS
	if ($nav_linkColor)
	{
		if ($debug)
		{
			$style .= "\n" . '/* $nav_linkColor */' . "\n";
		}
		$style .= "nav ul.navbar-nav li > a,\n"
			. ".navbar-default .navbar-nav > li > a,\n"
			. ".navbar-inverse .navbar-nav > li > a{\n"
			. "	color: " . $nav_linkColor . ";\n"
			. "}\n";
	}
	if ($nav_linkhoverColor)
	{
		if ($debug)
		{
			$style .= "\n" . '/* $nav_linkhoverColor */' . "\n";
		}
		$style .= "nav .navbar-nav > li > a:hover,\n"
			. ".navbar-nav > li > a:focus,\n"
			. ".navbar-default .navbar-nav > li > a:hover,\n"
			. ".navbar-inverse .navbar-nav > li > a:hover,\n"
			. ".navbar-default .navbar-nav > li > a:focus,\n"
			. ".navbar-inverse .navbar-nav > li > a:focus{\n"
			. " color: " . $nav_linkhoverColor . ";\n"
			. "}\n";
	}
	if ($nav_linkbackgroundColor)
	{
		if ($debug)
		{
			$style .= "\n" . '/* $nav_linkbackgroundColor */' . "\n";
		}
		$style .= ".navbar-nav > li > a,\n"
			. ".navbar-default .navbar-nav > li > a,\n"
			. ".navbar-inverse .navbar-nav > li > a{\n"
			. "	background-color: " . $nav_linkbackgroundColor . ";\n"
			. "}\n";
	}
	if ($nav_linkhoverbackgroundColor)
	{
		if ($debug)
		{
			$style .= "\n" . '/* $nav_linkhoverbackgroundColor */' . "\n";
		}
		$style .= ".navbar-nav > li > a:hover,\n"
			. ".navbar-nav > li > a:focus,\n"
			. ".navbar-default .navbar-nav > li > a:hover,\n"
			. ".navbar-inverse .navbar-nav > li > a:hover,\n"
			. ".navbar-default .navbar-nav > li > a:focus,\n"
			. ".navbar-inverse .navbar-nav > li > a:focus{\n"
			. "	background-color: " . $nav_linkhoverbackgroundColor . ";\n"
			. "}\n";
	}
	if ($nav_activeColor)
	{
		if ($debug)
		{
			$style .= "\n" . '/* $nav_activeColor */' . "\n";
		}
		$style .= ".navbar-nav active > a,\n"
			. ".navbar-default .navbar-nav > .active > a,\n"
			. ".navbar-inverse .navbar-nav > .active > a{\n"
			. "	color: " . $nav_activeColor . ";\n"
			. "}\n";
	}
	if ($nav_activehoverColor)
	{
		if ($debug)
		{
			$style .= "\n" . '/* $nav_activehoverColor */' . "\n";
		}
		$style .= ".navbar-nav > .active > a:hover,\n"
			. ".navbar-nav > .active > a:focus,\n"
			. ".navbar-default .navbar-nav > .active > a:hover,\n"
			. ".navbar-inverse .navbar-nav > .active > a:hover,\n"
			. ".navbar-default .navbar-nav > .active > a:focus,\n"
			. ".navbar-inverse .navbar-nav > .active > a:focus{\n"
			. " color: " . $nav_activehoverColor . ";\n"
			. "}\n";
	}


	// NAV DROPDOWNS
	if ($nav_dropdownbackgroundColor)
	{
		if ($debug)
		{
			$style .= "\n" . '/* $nav_dropdownbackgroundColor */' . "\n";
		}
		$style .= "nav .dropdown-menu,\n"
			. ".navbar-default .dropdown-menu,\n"
			. ".navbar-inverse .dropdown-menu{\n"
			. " background-color: " . $nav_dropdownbackgroundColor . ";\n"
			. "}\n";
	}

	if ($nav_dropdownhoverbackgroundColor)
	{
		if ($debug)
		{
			$style .= "\n" . '/* $nav_dropdownhoverbackgroundColor */' . "\n";
		}
		$style .= ".navbar-default .dropdown-menu:focus,\n"
			. ".navbar-default .dropdown-menu:hover,\n"
			. ".navbar-default .dropdown:hover,\n"
			. ".navbar-default .dropdown:focus,\n"
			. ".navbar-default .dropdown-menu:hover,\n"
			. ".navbar-inverse .dropdown-menu:hover,\n"
			. ".navbar-default .dropdown-menu:focus,\n"
			. ".navbar-inverse .dropdown-menu:focus,\n"
			. ".navbar-default .dropdown-menu span.heading:focus,\n"
			. ".navbar-default .dropdown-menu span.heading:hover,\n"
			. ".navbar-default .dropdown-menu .separator:hover,\n"
			. ".navbar-default .dropdown-menu .separator:focus{\n"
			. " background-color: " . $nav_dropdownhoverbackgroundColor . ";\n"
			. "}\n";
	}

	if ($nav_dropdownColor)
	{
		if ($debug)
		{
			$style .= "\n" . '/* $nav_dropdownColor */' . "\n";
		}
		$style .= ".navbar-default .dropdown-menu > li > a,\n"
			. ".navbar-inverse .dropdown-menu > li > a,\n"
			. ".navbar-default .dropdown-menu > li > span.separator,\n"
			. ".navbar-inverse .dropdown-menu > li > span.separator,\n"
			. ".navbar-default .dropdown-menu > li > span.heading,\n"
			. ".navbar-inverse .dropdown-menu > li > span.heading{\n"
			. " color: " . $nav_dropdownColor . ";\n"
			. "}\n";
	}


	if ($nav_dropdownhoverColor)
	{
		if ($debug)
		{
			$style .= "\n" . '/* $nav_dropdownhoverColor */' . "\n";
		}
		$style .= ".navbar-default .dropdown-menu:focus,\n"
			. ".navbar-default .dropdown-menu:hover,\n"
			. ".navbar-default .dropdown:hover,\n"
			. ".navbar-default .dropdown:focus,\n"
			. ".navbar-default .dropdown-menu > li > a:hover,\n"
			. ".navbar-inverse .dropdown-menu > li > a:hover,\n"
			. ".navbar-default .dropdown-menu > li >a:focus,\n"
			. ".navbar-inverse .dropdown-menu > li > a:focus,\n"
			. ".navbar-default .dropdown-menu > li > span.heading:focus,\n"
			. ".navbar-default .dropdown-menu > li > span.heading:hover,\n"
			. ".navbar-default .dropdown-menu > li > span.separator:hover,\n"
			. ".navbar-default .dropdown-menu > li > span.separator:focus{\n"
			. " color: " . $nav_dropdownhoverColor . ";\n"
			. "}\n";
	}

	if ($nav_dropdownlinkColor)
	{
		if ($debug)
		{
			$style .= "\n" . '/* $nav_dropdownlinkColor */' . "\n";
		}
		$style .= ".navbar-default .dropdown-menu li a,\n"
			. ".navbar-inverse .dropdown-menu li a{\n"
			. "color: " . $nav_dropdownlinkColor
			. ";\n}\n";
	}


	$css   .= $style;
	$style = '';
	/* END MAIN MENU COLOR CHOOSER SECTION(s) */
	if ($debug)
	{
		$style .= '/* END OF EXTENDED NAV STYLE PARAMS */' . "\n";
	}

	/**
	 * ==================================================
	 * Call Main Menu border & Decoration chooser
	 * ==================================================
	 */

	$nav_borderPlacement     = $data->get('nav_extendedParams.nav_borderPlacement');
	$nav_borderColor         = $data->get('nav_extendedParams.nav_borderColor');
	$nav_borderStyle         = $data->get('nav_extendedParams.nav_borderStyle');
	$nav_borderSize          = checkPX($data->get('nav_extendedParams.nav_borderSize'));
	$nav_itemborderPlacement = $data->get('nav_extendedParams.nav_itemborderPlacement', '');
	$nav_itemborderColor     = $data->get('nav_extendedParams.nav_itemborderColor');
	$nav_itemborderStyle     = $data->get('nav_extendedParams.nav_itemborderStyle');
	$nav_itemborderSize      = checkPX($data->get('nav_extendedParams.nav_itemborderSize'));
	$nav_itemHeight          = checkPX($data->get('nav_extendedParams.nav_itemHeight'));

// Navbar border
	if ($nav_borderPlacement == 'topandbottom')
	{
		if ($debug)
		{
			$style .= "\n" . '/* topandbottom*/' . "\n";
		}
		$style .= ".navbar{\n"
			. " border-top: " . $nav_borderSize . " " . $nav_borderStyle . " " . $nav_borderColor . ";\n"
			. " border-bottom: " . $nav_borderSize . " " . $nav_borderStyle . " " . $nav_borderColor . ";\n"
			. "}\n";
	}
	elseif ($nav_borderPlacement == 'leftandright')
	{
		if ($debug)
		{
			$style .= "\n" . '/* leftandright */' . "\n";
		}
		$style .= ".navbar{\n"
			. " border-left: " . $nav_borderSize . " " . $nav_borderStyle . " " . $nav_borderColor . ";\n"
			. " border-right: " . $nav_borderSize . " " . $nav_borderStyle . " " . $nav_borderColor . ";\n"
			. "}\n";
	}
	elseif ($nav_borderPlacement == 'all')
	{
		if ($debug)
		{
			$style .= "\n" . '/* all */' . "\n";
		}
		$style .= ".navbar{\n"
			. " border: " . $nav_borderSize . " " . $nav_borderStyle . " " . $nav_borderColor . ";\n"
			. "}\n";
	}
	elseif ($nav_borderPlacement == 'none')
	{
		if ($debug)
		{
			$style .= "\n" . '/* none */' . "\n";
		}
		$style .= ".navbar{\n"
			. " border: none;\n"
			. "}\n";
	}
	elseif ($nav_borderPlacement == 'left')
	{
		if ($debug)
		{
			$style .= "\n" . '/* left */' . "\n";
		}
		$style .= ".navbar{\n"
			. " border-left: " . $nav_borderSize . " " . $nav_borderStyle . " " . $nav_borderColor . ";\n"
			. "}\n";
	}
	elseif ($nav_borderPlacement == 'right')
	{
		if ($debug)
		{
			$style .= "\n" . '/* right */' . "\n";
		}
		$style .= ".navbar{\n"
			. " border-right: " . $nav_borderSize . " " . $nav_borderStyle . " " . $nav_borderColor . ";\n"
			. "}\n";
	}
	elseif ($nav_borderPlacement == 'bottom')
	{
		if ($debug)
		{
			$style .= "\n" . '/* bottom */' . "\n";
		}
		$style .= ".navbar{\n"
			. " border-bottom: " . $nav_borderSize . " " . $nav_borderStyle . " " . $nav_borderColor . ";\n"
			. "}\n";
	}
	elseif ($nav_borderPlacement == 'top')
	{
		if ($debug)
		{
			$style .= "\n" . '/* top */' . "\n";
		}
		$style .= ".navbar{\n"
			. " border-top: " . $nav_borderSize . " " . $nav_borderStyle . " " . $nav_borderColor . ";\n"
			. "}\n";
	}


}
$css   .= $style;
$style = '';

// Set menu placement values
if ($nav_itemborderPlacement == 'topandbottom')
{
	if ($debug)
	{
		$style .= "\n" . '/* item topandbottom */' . "\n";
	}
	$style .= ".nav-item{\n"
		. " border-top: " . $nav_borderSize . " " . $nav_borderStyle . " " . $nav_borderColor . ";\n"
		. " border-bottom: " . $nav_borderSize . " " . $nav_borderStyle . " " . $nav_borderColor . ";\n"
		. "}\n";
}
elseif ($nav_itemborderPlacement == 'leftandright')
{
	if ($debug)
	{
		$style .= "\n" . '/* item leftandright */' . "\n";
	}
	$style .= ".nav-item{\n"
		. " border-left: " . $nav_borderSize . " " . $nav_borderStyle . " " . $nav_borderColor . ";\n"
		. " border-right: " . $nav_borderSize . " " . $nav_borderStyle . " " . $nav_borderColor . ";\n"
		. "}\n";
}
elseif ($nav_itemborderPlacement == 'all')
{
	if ($debug)
	{
		$style .= "\n" . '/* item all */' . "\n";
	}
	$style .= ".nav-item{\n"
		. " border: " . $nav_borderSize . " " . $nav_borderStyle . " " . $nav_borderColor . ";\n"
		. "}\n";
}
elseif ($nav_itemborderPlacement == 'none')
{
	if ($debug)
	{
		$style .= "\n" . '/* item none */' . "\n";
	}
	$style .= ".nav-item{\n"
		. " border: none;\n"
		. "}\n";
}
elseif ($nav_itemborderPlacement == 'left')
{
	if ($debug)
	{
		$style .= "\n" . '/* item left */' . "\n";
	}
	$style .= ".nav-item{\n"
		. " border-left: " . $nav_borderSize . " " . $nav_borderStyle . " " . $nav_borderColor . ";\n"
		. "}\n";
}
elseif ($nav_itemborderPlacement == 'right')
{
	if ($debug)
	{
		$style .= "\n" . '/* item right */' . "\n";
	}
	$style .= ".nav-item{\n"
		. " border-right: " . $nav_borderSize . " " . $nav_borderStyle . " " . $nav_borderColor . ";\n"
		. "}\n";
}
elseif ($nav_itemborderPlacement == 'bottom')
{
	if ($debug)
	{
		$style .= "\n" . '/* item bottom */' . "\n";
	}
	$style .= ".nav-item{\n"
		. " border-bottom: " . $nav_borderSize . " " . $nav_borderStyle . " " . $nav_borderColor . ";\n"
		. "}\n";
}
elseif ($nav_itemborderPlacement == 'top')
{
	if ($debug)
	{
		$style .= "\n" . '/* item top */' . "\n";
	}
	$style .= ".nav-item{\n"
		. " border-top: " . $nav_borderSize . " " . $nav_borderStyle . " " . $nav_borderColor . ";\n"
		. "}\n";
}

if ($debug)
{
	$style .= '/* END OF EXTENDED NAV BORDER PARAMS */' . "\n";
}
$css   .= $style;
$style = '';
// end extendedmainmenuParams

// END OF MAIN MENU

/*
 * ==================================
 *  --------- Mobile Menu -----------
 * ==================================
 */
$hamburgerSize       = checkPX($data->get('mmenuhamburgerSize'));
$hamburgerBackground = $data->get('mmenuhamburgerBackground');
if ($hamburgerSize)
	if ($debug)
	{
		$style .= '/* END OF $hamburgerSize */' . "\n";
	}
{
	$style = "ul.mm-list > .dropdown i." . $iconCaret . ",\n"
		. "ul.mm-list > .dropdown span." . $iconCaret . ",\n"
		. "ul.mm-list > .dropdown span.caret,\n"
		. "ul.mm-list > .dropdown i.caret{\n"
		. "	transform: rotate(-90deg);\n}\n";
}
if ($hamburgerBackground)
{
	if ($debug)
	{
		$style .= '/* END OF $hamburgerBackground */' . "\n";
	}
	$style .= ".navbar-toggle .icon-bar {\n"
		. "background-color: " . $hamburgerBackground . ";\n"
		. "}\n";
}
$css   .= $style;
$style = '';
// END OF MOBILE MENU

/*
 * =============================
 *  --------- HEADER -----------
 * =============================
 */


// END OF HEADER


/*
 * =============================
 *  --------- FOOTER -----------
 * =============================
 */
$style = '';

$footerbackgroundColor = $data->get('footer_extendedParams.footerbackgroundColor');
$footertextColor       = $data->get('footer_extendedParams.footertextColor ');
$footertexthoverColor  = $data->get('footer_extendedParams.footertexthoverColor ');
$footerlinkColor       = $data->get('footer_extendedParams.footerlinkColor ');
$footerfontSize        = checkPX($data->get('footer_extendedParams.footerfontSize '));
$footerMargin          = checkPX($data->get('footer_extendedParams.footerMargin'));
$footerPadding         = checkPX($data->get('footer_extendedParams.footerPadding '));
$footerborderPlacement = $data->get('footer_extendedParams.footerborderPlacement');
$footerborderColor     = $data->get('footer_extendedParams.footerborderColor ');
$footerborderStyle     = $data->get('footer_extendedParams.footerborderStyle');
$footerborderSize      = checkPX($data->get('footer_extendedParams.footerborderSize'));
/*  ----- END FOOTER CHOOSER ----- */

$footerTop    = '';
$footerBottom = '';
$footerLeft   = '';
$footerRight  = '';
$footerBorder = '';

if (!$footerborderPlacement == '')
{

	if ($footerborderPlacement == 'none')
	{
		$footerBorder = 'none';
	}
	elseif ($footerborderPlacement == 'topandbottom')
	{
		$footerTop    = 'top';
		$footerBottom = 'bottom';
	}
	elseif ($footerborderPlacement == 'leftandright')
	{
		$footerLeft  = 'left';
		$footerRight = 'right';
	}
	elseif ($footerborderPlacement == 'all')
	{
		$footerBorder = 'all';
	}
	elseif ($footerborderPlacement == 'left')
	{
		$footerLeft = 'left';
	}
	elseif ($footerborderPlacement == 'right')
	{
		$footerRight = 'right';
	}
	elseif ($footerborderPlacement == 'bottom')
	{
		$footerBottom = 'bottom';
	}
	elseif ($footerborderPlacement == 'top')
	{
		$footerTop = 'top';
	}
}
if ($footerBorder == 'none')
{
	$style .= "footer.footer #footer .container {\n border:none;\n}";
}

if ($footertextColor || $footerfontSize || $footerMargin || $footerPadding || $footerborderPlacement || $footerborderColor || $footerborderStyle || $footerborderSize)
{
	if ($debug)
	{
		$style .= "\n" . '/* footer */' . "\n";
	}
	$style .= "footer.footer #footer .container {\n";

// top & bottom
	if ($footerBottom && $footerTop)
	{
		if ($footerborderSize)
		{
			$style .= "	border-" . $footerBottom . "-width: " . $footerborderSize . ";\n";
			$style .= "	border-" . $footerTop . "-width: " . $footerborderSize . ";\n";
		}
		if ($footerborderStyle)
		{
			$style .= "	border-" . $footerBottom . "-style: " . $footerborderStyle . ";\n";
			$style .= "	border-" . $footerTop . "-style: " . $footerborderStyle . ";\n";
		}
		if ($footerborderColor)
		{
			$style .= "	border-" . $footerBottom . "-color: " . $footerborderColor . ";\n";
			$style .= "	border-" . $footerTop . "-color: " . $footerborderColor . ";\n";
		}
	}
	elseif ($footerLeft && $footerRight)
	{

		if ($footerborderSize)
		{
			$style .= "	border-" . $footerRight . "-width: " . $footerborderSize . ";\n";
			$style .= "	border-" . $footerLeft . "-width: " . $footerborderSize . ";\n";
		}
		if ($footerborderStyle)
		{
			$style .= "	border-" . $footerRight . "-style: " . $footerborderStyle . ";\n";
			$style .= "	border-" . $footerLeft . "-style: " . $footerborderStyle . ";\n";
		}
		if ($footerborderColor)
		{
			$style .= "	border-" . $footerRight . "-color: " . $footerborderColor . ";\n";
			$style .= "	border-" . $footerLeft . "-color: " . $footerborderColor . ";\n";
		}
	}
	elseif ($footerLeft && !$footerRight)
	{
		if ($footerborderSize)
		{
			$style .= "	border-" . $footerLeft . "-width: " . $footerborderSize . ";\n";
		}
		if ($footerborderStyle)
		{
			$style .= "	border-" . $footerLeft . "-style: " . $footerborderStyle . ";\n";
		}
		if ($footerborderColor)
		{
			$style .= "	border-" . $footerLeft . "-color: " . $footerborderColor . ";\n";
		}
	}
	elseif ($footerRight && !$footerLeft)
	{
		if ($footerborderSize)
		{
			$style .= "	border-" . $footerRight . "-width: " . $footerborderSize . ";\n";
		}
		if ($footerborderStyle)
		{
			$style .= "	border-" . $footerRight . "-style: " . $footerborderStyle . ";\n";
		}
		if ($footerborderColor)
		{
			$style .= "	border-" . $footerRight . "-color: " . $footerborderColor . ";\n";
		}
	}
	elseif ($footerTop && !$footerBottom)
	{
		if ($footerborderSize)
		{
			$style .= "	border-" . $footerTop . "-width: " . $footerborderSize . ";\n";
		}
		if ($footerborderStyle)
		{
			$style .= "	border-" . $footerTop . "-style: " . $footerborderStyle . ";\n";
		}
		if ($footerborderColor)
		{
			$style .= "	border-" . $footerTop . "-color: " . $footerborderColor . ";\n";
		}
	}
	elseif ($footerBottom && !$footerTop)
	{
		if ($footerborderSize)
		{
			$style .= "	border-" . $footerBottom . "-width: " . $footerborderSize . ";\n";
		}
		if ($footerborderStyle)
		{
			$style .= "	border-" . $footerBottom . "-style: " . $footerborderStyle . ";\n";
		}
		if ($footerborderColor)
		{
			$style .= "	border-" . $footerBottom . "-color: " . $footerborderColor . ";\n";
		}
	}
	elseif ($footerBorder = 'all')
	{
		if ($footerborderSize)
		{
			$style .= "	border-width: " . $footerborderSize . ";\n";
		}
		if ($footerborderStyle)
		{
			$style .= "	border-style: " . $footerborderStyle . ";\n";
		}
		if ($footerborderColor)
		{
			$style .= "	border-color: " . $footerborderColor . ";\n";
		}
	}

	/* ---- BEGIN FOOTER COLORING----  */
	$style .= $footertextColor ? "	color: " . $footertextColor . ";\n" : '';

	$style .= $footerfontSize ? " font-size: " . $footerfontSize . ";\n" : '';

	// Close it
	$style .= "}\n";
}

$style .= $footerbackgroundColor ? "footer.footer #footer {\n   background-color: " . $footerbackgroundColor . ";\n}\n" : '';

$style .= $footerlinkColor ? "footer.footer #footer a{\n color: " . $footerlinkColor . ";\n}\n" : '';

$style .= $footertexthoverColor ? "footer.footer #footer a:hover{\n color: " . $footertexthoverColor . ";\n}\n" : '';

// Footer MARGIN AND PADDING
if ($footerMargin || $footerPadding)
{
	$style .= "footer.footer #footer .module{\n";

	$style .= $footerMargin ? "	margin: " . $footerMargin . ";\n" : '';
	$style .= $footerPadding ? "	padding: " . $footerPadding . ";\n" : '';

	$style .= "\n}\n";
}
// Output completed styling
$css   .= $style;
$style = '';


/* BEGIN FOOTER WIDE */
$footerwidebackgroundColor = $data->get('footer_extendedParams.footerwidebackgroundColor');
$footerwideColor           = $data->get('footer_extendedParams.footerwideColor ');
$footerwidehoverColor      = $data->get('footer_extendedParams.footerwidehoverColor ');
$footerwidelinkColor       = $data->get('footer_extendedParams.footerwidelinkColor ');
$footerwidefontSize        = checkPX($data->get('footer_extendedParams.footerwidefontSize '));
$footerwideMargin          = checkPX($data->get('footer_extendedParams.footerwideMargin'));
$footerwidePadding         = checkPX($data->get('footer_extendedParams.footerwidePadding '));
$footerwideborderPlacement = $data->get('footer_extendedParams.footerwideborderPlacement');
$footerwideborderColor     = $data->get('footer_extendedParams.footerwideborderColor ');
$footerwideborderStyle     = $data->get('footer_extendedParams.footerwideborderStyle');
$footerwideborderSize      = checkPX($data->get('footer_extendedParams.footerwideborderSize'));
/*  ----- END FOOTER CHOOSER ----- */

$footerwideTop    = '';
$footerwideBottom = '';
$footerwideLeft   = '';
$footerwideRight  = '';
$footerwideBorder = '';

if (!$footerwideborderPlacement == '')
{

	if ($footerwideborderPlacement == 'none')
	{
		$footerwideBorder = 'none';
	}
	elseif ($footerwideborderPlacement == 'topandbottom')
	{
		$footerwideTop    = 'top';
		$footerwideBottom = 'bottom';
	}
	elseif ($footerwideborderPlacement == 'leftandright')
	{
		$footerwideLeft  = 'left';
		$footerwideRight = 'right';
	}
	elseif ($footerwideborderPlacement == 'all')
	{
		$footerwideBorder = 'all';
	}
	elseif ($footerwideborderPlacement == 'left')
	{
		$footerwideLeft = 'left';
	}
	elseif ($footerwideborderPlacement == 'right')
	{
		$footerwideRight = 'right';
	}
	elseif ($footerwideborderPlacement == 'bottom')
	{
		$footerwideBottom = 'bottom';
	}
	elseif ($footerwideborderPlacement == 'top')
	{
		$footerwideTop = 'top';
	}
}


if ($footerwideBorder == 'none')
{
	$doc->addStyleDeclaration("footer.footer #footer-wide .footer-wide {\n	border:none;\n}");
}

if ($footerwideColor || $footerwideMargin || $footerwidePadding || $footerwideborderPlacement || $footerwideborderColor || $footerwideborderStyle || $footerwideborderSize)
{
	if ($debug)
	{
		$style .= "\n" . '/* footer-wide */' . "\n";
	}
	$style = "footer.footer #footer-wide .footer-wide{\n";

// top & bottom
	if ($footerwideBottom && $footerwideTop)
	{
		if ($footerwideborderSize)
		{
			$style .= "	border-" . $footerwideBottom . "-width: " . $footerwideborderSize . ";\n";
			$style .= "	border-" . $footerwideTop . "-width: " . $footerwideborderSize . ";\n";
		}
		if ($footerwideborderStyle)
		{
			$style .= "	border-" . $footerwideBottom . "-style: " . $footerwideborderStyle . ";\n";
			$style .= "	border-" . $footerwideTop . "-style: " . $footerwideborderStyle . ";\n";
		}
		if ($footerwideborderColor)
		{
			$style .= "	border-" . $footerwideBottom . "-color: " . $footerwideborderColor . ";\n";
			$style .= "	border-" . $footerwideTop . "-color: " . $footerwideborderColor . ";\n";
		}
	}
	elseif ($footerwideLeft && $footerwideRight)
	{

		if ($footerwideborderSize)
		{
			$style .= "	border-" . $footerwideRight . "-width: " . $footerwideborderSize . ";\n";
			$style .= "	border-" . $footerwideLeft . "-width: " . $footerwideborderSize . ";\n";
		}
		if ($footerwideborderStyle)
		{
			$style .= "	border-" . $footerwideRight . "-style: " . $footerwideborderStyle . ";\n";
			$style .= "	border-" . $footerwideLeft . "-style: " . $footerwideborderStyle . ";\n";
		}
		if ($footerwideborderColor)
		{
			$style .= "	border-" . $footerwideRight . "-color: " . $footerwideborderColor . ";\n";
			$style .= "	border-" . $footerwideLeft . "-color: " . $footerwideborderColor . ";\n";
		}
	}
	elseif ($footerwideLeft && !$footerwideRight)
	{
		if ($footerwideborderSize)
		{
			$style .= "	border-" . $footerwideLeft . "-width: " . $footerwideborderSize . ";\n";
		}
		if ($footerwideborderStyle)
		{
			$style .= "	border-" . $footerwideLeft . "-style: " . $footerwideborderStyle . ";\n";
		}
		if ($footerwideborderColor)
		{
			$style .= "	border-" . $footerwideLeft . "-color: " . $footerwideborderColor . ";\n";
		}
	}
	elseif ($footerwideRight && !$footerwideLeft)
	{
		if ($footerwideborderSize)
		{
			$style .= "	border-" . $footerwideRight . "-width: " . $footerwideborderSize . ";\n";
		}
		if ($footerwideborderStyle)
		{
			$style .= "	border-" . $footerwideRight . "-style: " . $footerwideborderStyle . ";\n";
		}
		if ($footerwideborderColor)
		{
			$style .= "	border-" . $footerwideRight . "-color: " . $footerwideborderColor . ";\n";
		}
	}
	elseif ($footerwideTop && !$footerwideBottom)
	{
		if ($footerwideborderSize)
		{
			$style .= "	border-" . $footerwideTop . "-width: " . $footerwideborderSize . ";\n";
		}
		if ($footerwideborderStyle)
		{
			$style .= "	border-" . $footerwideTop . "-style: " . $footerwideborderStyle . ";\n";
		}
		if ($footerwideborderColor)
		{
			$style .= "	border-" . $footerwideTop . "-color: " . $footerwideborderColor . ";\n";
		}
	}
	elseif ($footerwideBottom && !$footerwideTop)
	{
		if ($footerwideborderSize)
		{
			$style .= "	border-" . $footerwideBottom . "-width: " . $footerwideborderSize . ";\n";
		}
		if ($footerwideborderStyle)
		{
			$style .= "	border-" . $footerwideBottom . "-style: " . $footerwideborderStyle . ";\n";
		}
		if ($footerwideborderColor)
		{
			$style .= "	border-" . $footerwideBottom . "-color: " . $footerwideborderColor . ";\n";
		}
	}
	elseif ($footerwideBorder = 'all')
	{
		if ($footerwideborderSize)
		{
			$style .= "	border-width: " . $footerwideborderSize . ";\n";
		}
		if ($footerwideborderStyle)
		{
			$style .= "	border-style: " . $footerwideborderStyle . ";\n";
		}
		if ($footerwideborderColor)
		{
			$style .= "	border-color: " . $footerwideborderColor . ";\n";
		}
	}

	if ($footerwideColor)
	{
		$style .= "	color: " . $footerwideColor . ";\n";
	}
	$style .= "}\n";

	// Output completed styling
	$doc->addStyleDeclaration($style);
}
if ($footerwidebackgroundColor)
{
	$doc->addStyleDeclaration("footer.footer > #footer-wide {	background-color: " . $footerwidebackgroundColor . ";\n}");
}
if ($footerwidelinkColor)
{
	$doc->addStyleDeclaration("footer.footer > #footer-wide  a { color: " . $footerwidelinkColor . ";\n}");
}
if ($footerwidehoverColor)
{
	$doc->addStyleDeclaration("footer.footer > #footer-wide  a:hover { color: " . $footerwidehoverColor . ";\n}");
}
// Footer MARGIN AND PADDING
if ($footerMargin || $footerPadding)
{
	$style = "footer.footer #footer-wide .footer-wide .module{\n";
	if ($footerMargin)
	{
		$style .= "	margin: " . $footerwideMargin . ";\n";
	}
	if ($footerPadding)
	{
		$style .= "	padding: " . $footerwidePadding . ";\n";
	}
	$style .= "\n}\n";
}
// Output completed styling
$css .= $style;
// END OF FOOTER


/*
 * ================================
 *  --------- COPYRIGHT -----------
 * ================================
 */

// END OF COPYRIGHT


/*
 * ====================================
 *  --------- MISCELLANEOUS -----------
 * ====================================
 */
$gotop = $data->get('gotop');
if ($gotop)
{
	$gotopfontColor       = $data->get('gotopParams.gotopColor');
	$gotopfontSize        = checkPX($data->get('gotopParams.gotopfontSize'));
	$gotopbackgroundColor = $data->get('gotopParams.gotopbackgroundColor');
	$gotopbuttonColor     = $data->get('gotopParams.gotopbuttonColor');

	if ($gotopbackgroundColor || $gotopfontSize || $gotopfontColor)
	{
		$style .= ".go-top{\n";
		$style .= $gotopfontColor ? " color: " . $gotopfontColor . ";\n" : '';
		$style .= $gotopfontSize ? " font-size: " . $gotopfontSize . ";\n" : '';
		$style .= $gotopbackgroundColor ? " background-color: " . $gotopbackgroundColor . ";\n" : '';
		$style .= "}\n";
	}
	if ($gotopbuttonColor)
	{
		$style .= ".go-top.btn{\n";
		$style .= " color: " . $gotopbuttonColor . ";\n";
		$style .= "}\n";
	}
}
$css   .= $style;
$style = '';
// END OF MISCELLANEOUS


/*
 * ==================================
 *  --------- CUSTOM CODE -----------
 * ==================================
 */


// END OF CUSTOM CODE


/*
 * ===================================
 *  --------- SOCIAL ICONS -----------
 * ===================================
 */
if ($data->get('socialIcons'))
{

	/* Social Icons */
	$socialiconsRow           = $data->get('socialiconsRow');
	$socialiconsColumn        = $data->get('socialiconsColumn');
	$socialiconSize           = checkPX($data->get('socialiconSize'));
	$socialiconscolumnReverse = $data->get('socialiconscolumnReverse');

// Get Social Icon class's
	/* get social array */
	$chooser        = $data->get('socialArray');
	$json           = json_decode($chooser, true);
	$filtered_array = group_by_key($json);

	foreach ($filtered_array as $index => $value)
	{
		$class = str_ireplace(' ', '.', $value[1]);
		$url   = str_ireplace(' ', '.', $value[2]);

		/* if socialiconsSize has a value then figure out if we have a icon or not */
		if ($socialiconSize && $class && $url)
		{
			$style .= "span." . $class . "{\n"
				. " font-size: " . checkPX($socialiconSize) . ";\n"
				. " line-height: " . $socialiconSize . ";\n"
				. " padding: 2px;\n"
				. "}\n";
		}
	}
// Set initial matrix position
	$style .= ".header-col3{\n"
		. " flex-direction: " . $socialiconscolumnReverse . ";\n"
		. "}\n";


	$style .= ".socialicons{\n"
		. " justify-content: " . $socialiconsColumn . ";\n"
		. " align-items: " . $socialiconsRow . ";\n"
		. "}\n";

	if ($data->get('socialiconsLocation') == 'footer' || $data->get('socialiconsLocation') == 'pagetop')
	{
		$style .= "ul.pagetop.social-icons,ul.footer.social-icons{\n"
			. " justify-content: " . $socialiconsColumn . ";\n"
			. " align-items: " . $socialiconsRow . ";\n"
			. " flex-direction: " . $socialiconscolumnReverse . ";\n"
			. " display: flex;\n"
			. "}\n";
	}
	if ($socialiconscolumnReverse == 'column-reverse' || $socialiconscolumnReverse == 'row-reverse')
	{
		$style .= "ul.pagetop.social-icons,ul.footer.social-icons{\n"
			. " align-items: " . $socialiconsColumn . ";\n"
			. " justify-content: " . $socialiconsRow . ";\n"
			. " flex-direction: " . $socialiconscolumnReverse . ";\n"
			. "}\n";

	}

// END OF SOCIAL ICONS
	$css .= $style;
}
$style = '';
