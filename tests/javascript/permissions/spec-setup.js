/**
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @package     Joomla
 * @subpackage  JavaScript Tests
 * @since       3.6
 * @version     1.0.0
 */

define(['jquery', 'text!testsRoot/permissions/fixtures/fixture.html', 'libs/permissions', 'libs/core', 'jasmineJquery'], function ($, fixture) {
	$('body').append(fixture);

	window.id = '0';
	window.value = '1';

	event = {target: '#sendBtn'};

	responses = {
		success: {
			status: 200,
			statusText: 'HTTP/1.1 200 OK',
			contentType: 'text/plain',
			responseText: '{"data": "true", "message": "0"}'
		},
		fail: {
			status: 404,
			statusText: 'HTTP/1.1 404 Not Found',
			contentType: 'text/plain',
			responseText: ''
		}
	};
});
