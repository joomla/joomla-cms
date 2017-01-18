/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @package     Joomla
 * @subpackage  JavaScript Tests
 * @since       3.7.0
 * @version     1.0.0
 */

define(['jquery', 'testsRoot/calendar/spec-setup', 'jasmineJquery'], function ($) {

	describe('Calendar set for the input element', function () {
		beforeAll(function () {
			JoomlaCalendar.init(".field-calendar");
		});

		it('Should have calendar element under the input element', function () {
			expect($('body')).toContainElement('.js-calendar');
		});

		it('Should appear on button click', function () {
			$('.field-calendar').find('button').click();

			expect($('.js-calendar').css('display')).toEqual('block');
		});

		it('Should have the correct date', function () {
			expect($('.field-calendar').find('input').val()).toEqual('2016-09-01 05:17:00');
		});

		// it('Should have the correct date clicking on previous year button', function () {
		//
		// 	console.info($('.field-calendar').find('.btn-prev-year'));
		// 	$('.field-calendar').find('.btn-prev-year').trigger('click');
		//
		// 	expect($('.field-calendar').find('input').val()).toEqual('2015-09-01 00:17:15');
		// });
		//
		// it('Should have the correct date clicking on next year button', function () {
		//
		// 	console.info($('.field-calendar').find('.btn-next-year'));
		// 	$('.field-calendar').find('.btn-next-year').trigger('click');
		//
		// 	expect($('.field-calendar').find('input').val()).toEqual('2016-09-01 00:17:15');
		// });
		//
		// it('Should have the correct date clicking on previous month button', function () {
		//
		// 	console.info($('.field-calendar').find('.btn-prev-month'));
		// 	$('.field-calendar').find('.btn-prev-moth').trigger('click');
		//
		// 	expect($('.field-calendar').find('input').val()).toEqual('2016-08-01 00:17:15');
		// });
		//
		// it('Should have the correct date clicking on next month button', function () {
		//
		// 	console.info($('.field-calendar').find('.btn-next-month'));
		// 	$('.field-calendar').find('.btn-next-moth').trigger('click');
		//
		// 	expect($('.field-calendar').find('input').val()).toEqual('2016-09-01 00:17:15');
		// });
		//
		// it('Should have the correct date clicking on today button', function () {
		//
		// 	console.info($('.field-calendar').find('.btn-today'));
		// 	$('.field-calendar').find('.btn-today').trigger('click');
		//
		// 	expect($('.field-calendar').find('input').val()).toEqual('2016-09-01 00:17:15');
		// });
		//
		// it('Should hide on other body element click/focus', function () {
		// 	$('#cal-close-btn').focus();
		//
		// 	expect($('.j-calendar').css('display')).not.toEqual('block');
		// });
	});
});
