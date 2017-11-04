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

define(['jquery', 'testsRoot/subform-repeatable/spec-setup', 'jasmineJquery'], function ($) {
	var $container = $('#repeatable-container');

	describe('subform-repeatable', function () {
		describe('Initialization', function () {
			it('Should set the subform-repeatable instance in data', function () {
				expect($container).toHaveData('subformRepeatable');
			});

			it('Should bind add event to container', function () {
				expect($container).toHandle("click");
			});

			it('Should trigger subform-ready event', function () {
				expect(spy_subform_ready).toHaveBeenCalled();
			});
		});

		describe('Add new row', function () {
			beforeAll(function () {
				var $button = $('#subform-original-add');

				$button.click();
				$button.click();
			});

			it('Should add one and only one new row to the table', function () {
				expect($container.find('tbody').children().length).toEqual(2);
			});

			it('Should fix the id of the template input checkbox element to "jform_group__group2__checkbox"', function () {
				expect($container.find('#jform_group__groupX__checkbox')).not.toExist();
				expect($container.find('#jform_group__group2__checkbox')).toExist();
			});

			it('Should fix the for attribute of the checkbox label element to match the changed input id', function () {
				expect($container.find('label[for="jform_group__group2__checkbox"]')).toExist();
			});

			it('Should fix the name of the template input checkbox element to "jform[group][group2][checkbox]"', function () {
				expect($container.find('#jform_group__group2__checkbox')).toHaveAttr('name','jform[group][group2][checkbox]');
			});

			it('Should fix the id of the template input radio element to "jform_group__group2__radio0"', function () {
				expect($container.find('#jform_group__groupX__radio0')).not.toExist();
				expect($container.find('#jform_group__group2__radio0')).toExist();
			});

			it('Should fix the name of the template input radio element to "jform[group][group2][radio]"', function () {
				expect($container.find('#jform_group__group2__radio0')).toHaveAttr('name','jform[group][group2][radio]');
			});

			it('Should have captured the template correctly', function () {
				var $newElement = $container.find('tbody').children().last();

				expect($newElement).toContainText('Data 3');
				expect($newElement).toContainText('Data 4');
				expect($newElement).toContainText('Checkbox label');
				expect($newElement).toContainText('Add');
				expect($newElement).toContainText('Remove');
				expect($newElement).toContainText('Move');
			});

			it('Should set data-new attribute to true in the new element', function () {
				expect($container.find('tbody').children().last()).toHaveAttr('data-new', 'true');
			});

			it('Should set data-group attribute to "group2" in the new element', function () {
				expect($container.find('tbody').children().last()).toHaveAttr('data-group', 'group2');
			});

			it('Should trigger subform-row-add event', function () {
				expect(spy_subform_row_add).toHaveBeenCalled();
			});
		});

		describe('Remove existing row', function () {
			beforeAll(function () {
				$container.find('a.group-remove.generated').click();
				$container.find('#subform-original-remove').click();
			});

			it('Should remove the added row from the table', function () {
				expect($container.find('tbody')).not.toContainText('Data 3');
				expect($container.find('tbody')).not.toContainText('Data 4');
			});

			it('Should not remove the first original row since minimum is set to 1', function () {
				expect($container.find('tbody')).toContainText('Data 1');
				expect($container.find('tbody')).toContainText('Data 2');
			});

			it('Should trigger subform-row-remove event', function () {
				expect(spy_subform_row_remove).toHaveBeenCalled();
			});
		});
	});
});
