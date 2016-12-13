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

define(['jquery', 'text!testsRoot/repeatable/fixtures/fixture.html', 'libs/repeatable'], function ($, fixture) {
	$('body').append(fixture);

	spy_weready = jasmine.createSpy('weready');
	spy_prepare_template = jasmine.createSpy('prepare-template');
	spy_prepare_modal = jasmine.createSpy('prepare-modal');
	spy_row_add = jasmine.createSpy('row-add');
	spy_row_remove = jasmine.createSpy('row-remove');
	spy_value_update = jasmine.createSpy('value-update');

	var $element = $('input.form-field-repeatable');

	$element.on('weready', spy_weready)
		.on('prepare-template', spy_prepare_template)
		.on('prepare-modal', spy_prepare_modal)
		.on('row-add', spy_row_add)
		.on('row-remove', spy_row_remove)
		.on('value-update', spy_value_update);

	$element.JRepeatable();
});
