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

define(['jquery', 'testsRoot/joomla-field-subform/spec-setup'], function () {
	var container = document.getElementById('repeatable-container');

	describe('subform-repeatable', function () {

		describe('Add new row', function () {
			beforeAll(function () {
				var button = document.getElementById('subform-original-add');

				button.click();
				button.click();
			});

			it('Should add a new row to the table', function () {
				expect(container.querySelectorAll('.subform-repeatable-group').length).toEqual(2);
			});

			it('Should fix the id of the template input checkbox element to "jform_group__group2__checkbox"', function () {
				expect(container.querySelector('#jform_group__groupX__checkbox')).toEqual(null);
				expect(container.querySelector('#jform_group__group2__checkbox')).not.toEqual(null);
			});

			it('Should fix the for attribute of the checkbox label element to match the changed input id', function () {
				expect(container.querySelector('label[for="jform_group__group2__checkbox"]')).not.toEqual(null);
			});

			it('Should fix the name of the template input checkbox element to "jform[group][group2][checkbox]"', function () {
				expect(container.querySelector('#jform_group__group2__checkbox').name).toEqual('jform[group][group2][checkbox]');
			});

			it('Should fix the id of the template input radio element to "jform_group__group2__radio0"', function () {
				expect(container.querySelector('#jform_group__groupX__radio0')).toEqual(null);
				expect(container.querySelector('#jform_group__group2__radio0')).not.toEqual(null);
			});

			it('Should fix the name of the template input radio element to "jform[group][group2][radio]"', function () {
				expect(container.querySelector('#jform_group__group2__radio0').name).toEqual('jform[group][group2][radio]');
			});

			it('Should set data-new attribute to true in the new element', function () {
				expect(container.querySelectorAll('.subform-repeatable-group')[1].getAttribute('data-new')).toEqual('1');
			});

			it('Should set data-group attribute to "group2" in the new element', function () {
				expect(container.querySelectorAll('.subform-repeatable-group')[1].getAttribute('data-group')).toEqual('group2');
			});
		});

		describe('Remove existing row', function () {
			beforeAll(function () {
				container.querySelector('a.group-remove.generated').click();
				container.querySelector('#subform-original-remove').click();
			});

			it('Should remove the added row from the table', function () {
				expect(container.querySelectorAll('.subform-repeatable-group').length).toEqual(1);
			});

			it('Should not remove the first original row since minimum is set to 1', function () {
				expect(container.querySelectorAll('.subform-repeatable-group').length).toEqual(1);
			});
		});
	});
});
