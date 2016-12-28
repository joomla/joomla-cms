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

define(['jquery', 'testsRoot/repeatable/spec-setup', 'jasmineJquery'], function ($) {

	var $modal = $('#jform_test_modal');

	describe('Repeatable before triggering btn click', function () {
		it('should take jform_test_container out of the form', function () {
			expect($('#jform-test')).not.toContainElement('#jform_test_container');
		});

		it('should register on shown event on the modal element', function () {
			expect($('#jform_test_modal')).toHandle('shown');
		});

		it('should fix name attributes on the repeatable element contents', function () {
			expect($modal.find('input').first()).toHaveAttr('name', '1-field1');
			expect($modal.find('input').last()).toHaveAttr('name', '1-field2');
		});

		it('should fix id attributes on the repeatable element contents', function () {
			expect($modal.find('input').first()).toHaveAttr('id', '1-field1');
			expect($modal.find('input').last()).toHaveAttr('id', '1-field2');
		});

		it('should fire the weready event', function () {
			expect(spy_weready).toHaveBeenCalled();
		});

		it('should fire the prepare-template event', function () {
			expect(spy_prepare_template).toHaveBeenCalled();
		});

		it('should fire the prepare-modal event', function () {
			expect(spy_prepare_modal).toHaveBeenCalled();
		});
	});

	describe('Repeatable after triggering \'open modal\' button click', function () {
		beforeAll(function () {
			$('#jform_test_button').click();
		});

		it('should change the CSS of the modal window', function () {
			var $modal = $('#jform_test_modal');
			var $rowsContainer = $('#jform_test').find('tbody').first();
			var modalHalfWidth = $modal.width() / 2;
			var rowsHalfWidth = $rowsContainer.width() / 2;

			expect($modal).toHaveCss({
				'overflow': rowsHalfWidth > modalHalfWidth ? 'auto' : 'visible'
			});
		});
	});

	describe('Repeatable after triggering \'Add new after\' element click', function () {
		beforeAll(function () {
			$modal.find('.add').first().click();
		});

		it('should make the total number of text inputs in the modal 4', function () {
			expect($modal.find('input').length).toEqual(4);
		});

		it('should fix name attributes on the new repeatable element contents', function () {
			expect($modal.find('input').last()).toHaveAttr('name', '2-field2');
		});

		it('should fix id attributes on the new repeatable element contents', function () {
			expect($modal.find('input').last()).toHaveAttr('id', '2-field2');
		});

		it('should fire the row-add event', function () {
			expect(spy_row_add).toHaveBeenCalled();
		});
	});

	describe('Repeatable after triggering \'Remove\' element click', function () {
		beforeAll(function () {
			$modal.find('.remove').first().click();
		});

		it('should make the total number of text inputs in the modal back to 2', function () {
			expect($modal.find('input').length).toEqual(2);
		});

		it('should fire the row-remove event', function () {
			expect(spy_row_remove).toHaveBeenCalled();
		});
	});

	describe('Repeatable after triggering \'Close\' element click', function () {
		beforeAll(function () {
			$modal.find('.close-modal').first().click();
		});

		it('should make the modal not visible', function () {
			expect($modal).not.toBeVisible();
		});
	});

	describe('Repeatable after triggering \'Save and Close\' element click', function () {
		beforeAll(function () {
			$('#jform_test_button').click();
			$modal.find('input').first().val('test_input');
			$modal.find('.save-modal-data').first().click();
		});

		it('should make the modal not visible', function () {
			expect($modal).not.toBeVisible();
		});

		it('should set the filled values in JSON format to the value attribute of hidden input', function () {
			expect($('#jform_test').val()).toEqual('{"field1":["test_input"],"field2":[""]}');
		});

		it('should fire the value-update event', function () {
			expect(spy_value_update).toHaveBeenCalled();
		});
	});

	describe('Repeatable after triggering \'open modal\' followed by  \'Save and Close\'', function () {
		beforeAll(function () {
			$('#jform_test_button').click();
		});

		it('should make the modal visible again', function () {
			expect($modal).toBeVisible();
		});

		it('should have the previously filled value preserved in the input element', function () {
			expect($modal.find('input').first().val()).toEqual('test_input');
		});
	});
});
