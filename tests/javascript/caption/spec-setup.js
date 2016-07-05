/**
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @package     Joomla
 * @subpackage  JavaScript Tests
 * @since       3.6
 * @version     1.0.0
 */

define(['jquery', 'text!testsRoot/caption/fixtures/fixture.html', 'libs/caption'], function ($, fixture) {
	$('body').append(fixture);

	new JCaption('#single img.test');
	new JCaption('#multiple img.test');
	new JCaption('#empty img.test');
	new JCaption('#options img.test');
});
