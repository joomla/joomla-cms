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

define(['jquery', 'text!testsRoot/permissions/fixtures/fixture.html', 'libs/permissions', 'libs/core'], function ($, fixture) {
	$('body').append(fixture);

	window.id = '0';
	window.value = '1';

	event = {target: '#sendBtn'};

	responses = {
		success: {
			status: 200,
			statusText: 'HTTP/1.1 200 OK',
			responseText: '{"data": {"result": true, "class": "test-class", "text": "Sample text"}, "messages": {}}'
		},
		fail: {
			status: 404,
			statusText: 'HTTP/1.1 404 Not Found',
			responseText: 'Error'
		}
	};
});
