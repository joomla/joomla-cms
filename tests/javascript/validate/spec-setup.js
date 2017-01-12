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

define(['jquery', 'text!testsRoot/validate/fixtures/fixture.html', 'libs/validate', 'libs/core'], function ($, fixture) {
	$('body').append(fixture);

	document.formvalidator = new JFormValidator();
});
