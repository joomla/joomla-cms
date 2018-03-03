/**
 * @package     Joomla.Tests
 * @subpackage  JavaScript Tests
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since       3.6.3
 * @version     1.0.0
 */

define(['jquery', 'text!testsRoot/joomla-field-subform/fixtures/fixture.html', 'media/system/webcomponents/js/joomla-field-subform', 'libs/core'], function ($, fixture) {
	$('body').append(fixture);
});
