/**
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

jQuery(function(a){a(".popup").click(function(d){d.preventDefault();var c=700;var b=500;var e=(a(window).height()/2)-(b/2);var f=(a(window).width()/2)-(c/2);window.open(a(this).attr("href"),"popupWindow","width="+c+",height="+b+",scrollbars=yes,left="+f+"top="+e)})});
