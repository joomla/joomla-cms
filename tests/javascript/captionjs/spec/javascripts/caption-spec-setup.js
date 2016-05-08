/**
 * Created by Ruchiranga on 5/8/2016.
 */
define(['jquery', 'jcaption', 'jasmineJquery'], function ($) {
    jasmine.getFixtures().fixturesPath = 'base/tests/javascript/captionjs/spec/javascripts/fixtures';
    var fixture = readFixtures('caption-fixture.html');
    $('body').append(fixture);

    new JCaption('img.test');
});