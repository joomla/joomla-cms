/**
 * @package     Joomla.Tests
 * @subpackage  JavaScript Tests
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since       3.6.3
 * @version     1.0.0
 */

define(['jquery', 'text!testsRoot/subform-repeatable/fixtures/fixture.html', 'libs/subform-repeatable'], function ($, fixture) {
	$('body').append(fixture);

	spy_subform_ready = jasmine.createSpy('subform-ready');
	spy_subform_row_add = jasmine.createSpy('subform-row-add');
	spy_subform_row_remove = jasmine.createSpy('subform-row-remove');

	var $element = $('#repeatable-container');

	$element.on('subform-ready', spy_subform_ready)
		.on('subform-row-add', spy_subform_row_add)
		.on('subform-row-remove', spy_subform_row_remove);

	$('div.subform-repeatable').subformRepeatable();
});
