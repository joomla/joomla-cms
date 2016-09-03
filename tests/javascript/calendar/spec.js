/**
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @package     Joomla
 * @subpackage  JavaScript Tests
 * @since       __DEPLOY_VERSION__
 * @version     1.0.0
 */

define(['jquery', 'testsRoot/calendar/spec-setup', 'jasmineJquery'], function ($) {

	JoomlaCalendar.init(".field-calendar");

	describe('Calendar set for the input element', function () {
		it('Should have calendar element under the input element', function () {
			expect($('body')).toContainElement('.j-calendar');
		});
	});

	describe('Calendar should appear on button click', function () {
		it('Should appear on button click', function () {
			$('.field-calendar').find('button').click();
			expect($('.j-calendar').css('display')).toEqual('block');
		});
	});

	describe('Calendar should disappear on other element click', function () {
		it('Should hide on on other element click', function () {
			$('#calclosebtn').focus();
			expect($('.j-calendar').css('display')).toEqual('none');
		});
	});
});
