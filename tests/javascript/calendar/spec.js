/**
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @package     Joomla
 * @subpackage  JavaScript Tests
 * @since       __DEPLOY_VERSION__
 * @version     1.0.0
 */

define(['jquery', 'testsRoot/calendar/spec-setup', 'jasmineJquery'], function ($) {

	var esc = $.Event("keydown", { keyCode: 27 });

	beforeAll(function () {
		JoomlaCalendar.init(".field-calendar");
	});

	describe('Calendar set for the input element', function () {
		it('Should have calendar element under the input element', function () {
			expect($('body')).toContainElement('.j-calendar');
		});
	});

	describe('Calendar should appear on button click', function () {
		it('Should appear on button click', function () {
			$('.field-calendar').find('button').click();
			var $el = $('.j-calendar').attr('style');
			expect($el.contains('display: block;'));
		});
	});

	describe('Calendar should disappear on document click', function () {
		it('Should close on document click', function () {
			$("#close-btn").trigger("click");
			var $el = $('.j-calendar').attr('style');
			expect($el.contains('display: none;'));
		});
	});
});
