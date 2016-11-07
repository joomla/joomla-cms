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

define(['jquery', 'testsRoot/switcher/spec-setup', 'jasmineJquery'], function ($) {
	
	describe('Switcher ', function () {
		describe('After running initializer code', function () {
			it('Should bind click event to each toggler', function () {
				expect($('#toggler-1')).toHandle('click');
				expect($('#toggler-2')).toHandle('click');
				expect($('#toggler-3')).toHandle('click');
			});

			it('Should display content of toggler-1', function () {
				expect($('#content-toggler-1')).toBeVisible();
			});

			it('Should add class active to toggler-1', function () {
				expect($('#toggler-1')).toHaveClass('active');
			});

			it('Should not let rest of the togglers to have class active', function () {
				expect($('#toggler-2')).not.toHaveClass('active');
				expect($('#toggler-3')).not.toHaveClass('active');
			});

			it('Should set document.location.hash to #toggler-1', function () {
				expect(document.location.hash).toEqual('#toggler-1');
			});

			it('Should call onHide callback function', function () {
				expect(spy_on_hide).toHaveBeenCalledWith(jasmine.objectContaining({length: 1}));
			});
		});

		describe('On clicking toggler-2', function () {
			beforeAll(function () {
				$('#toggler-2').click();
			});

			it('Should hide content of toggler-1', function () {
				expect($('#content-toggler-1')).not.toBeVisible();
			});

			it('Should call onShow callback function', function () {
				expect(spy_on_show).toHaveBeenCalledWith(jasmine.objectContaining({length: 1}));
			});

			it('Should remove class active from toggler-1', function () {
				expect($('#toggler-1')).not.toHaveClass('active');
			});

			it('Should show content of toggler-2', function () {
				expect($('#content-toggler-2')).toBeVisible();
			});

			it('Should call onHide callback function', function () {
				expect(spy_on_hide).toHaveBeenCalledWith(jasmine.objectContaining({length: 1}));
			});

			it('Should add class active to toggler-2', function () {
				expect($('#toggler-2')).toHaveClass('active');
			});

			it('Should set document.location.hash to #toggler-2', function () {
				expect(document.location.hash).toEqual('#toggler-2');
			});
		});

		describe('On function return', function () {
			it('Should return display function', function () {
				expect(switcher.display).toEqual(jasmine.any(Function));
			});

			it('Should return hide function', function () {
				expect(switcher.hide).toEqual(jasmine.any(Function));
			});

			it('Should return hideAll function', function () {
				expect(switcher.hideAll).toEqual(jasmine.any(Function));
			});

			it('Should return show function', function () {
				expect(switcher.show).toEqual(jasmine.any(Function));
			});
		});
	});
});
