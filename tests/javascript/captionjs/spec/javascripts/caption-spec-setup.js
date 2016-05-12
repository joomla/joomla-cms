/**
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @package     Joomla
 * @subpackage  JavaScript Tests
 * @since       3.6
 * @version     1.0.0
 */

define(['jquery', 'libs/caption', 'jasmineJquery'], function ($) {
    jasmine.getFixtures().fixturesPath = 'base/tests/javascript/captionjs/spec/javascripts/fixtures';
    var fixture = readFixtures('caption-fixture.html');
    $('body').append(fixture);

    new JCaption('img.test');
});
