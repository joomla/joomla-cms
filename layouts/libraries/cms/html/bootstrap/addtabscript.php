<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$selector = empty($displayData['selector']) ? '' : $displayData['selector'];
$id = empty($displayData['id']) ? '' : $displayData['id'];
$active = empty($displayData['active']) ? '' : $displayData['active'];
$title = empty($displayData['title']) ? '' : $displayData['title'];


echo "(function($){
				$(document).ready(function() {
					// Handler for .ready() called.
					var tab = $('<li class=\"" . $active . "\"><a href=\"#" . $id . "\" data-toggle=\"tab\">" . $title . "</a></li>');
					$('#" . $selector . "Tabs').append(tab);
				});
			})(jQuery);";
