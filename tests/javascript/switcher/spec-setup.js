/**
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @package     Joomla
 * @subpackage  JavaScript Tests
 * @since       __DEPLOY_VERSION__
 * @version     1.0.0
 */

define(['jquery', 'text!testsRoot/switcher/fixtures/fixture.html', 'libs/switcher'], function ($, fixture) {
	$('body').append(fixture);

	spy_on_show = jasmine.createSpy('on_show');
	spy_on_hide = jasmine.createSpy('on_hide');

	var toggler = document.getElementById('submenu');
	var element = document.getElementById('config-document');
	var options = {
		onShow : spy_on_show,
		onHide : spy_on_hide
	};

	switcher = new JSwitcher(toggler, element, options);
});
