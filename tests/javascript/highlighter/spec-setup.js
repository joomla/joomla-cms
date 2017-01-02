/**
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @package     Joomla
 * @subpackage  JavaScript Tests
 * @since       3.6.3
 * @version     1.0.0
 */

define(['jquery', 'text!testsRoot/highlighter/fixtures/fixture.html', 'libs/highlighter'], function ($, fixture) {
	$('body').append(fixture);

	var start = document.getElementById('highlighter-start');
	var end = document.getElementById('highlighter-end');

	highlighter = new Joomla.Highlighter({
		startElement: start,
		endElement: end,
		caseSensitive: false,
		className: 'highlight',
		onlyWords: false,
		tag: 'span'
	});
});
