/**
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @package     Joomla
 * @subpackage  JavaScript Tests
 * @since       __DEPLOY_VERSION__
 * @version     1.0.0
 */

define(['jquery', 'testsRoot/calendar/spec-setup', 'jasmineJquery'], function ($) {

	beforeAll(function () {
		JoomlaCalendar.init(".field-calendar");
	});

	describe('Calendar is applied for the input element', function () {
		it('Should have calendar element under input', function () {
			expect($('body')).toContainElement('.j-calendar');
		});
	});
});
