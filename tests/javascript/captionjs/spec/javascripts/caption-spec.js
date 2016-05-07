/**
 * Created by Ruchiranga on 4/29/2016.
 */

define(['jquery', 'jcaption', 'jasmineJquery'], function($) {

    describe('JCaption', function() {
        jasmine.getFixtures().fixturesPath = 'base/tests/javascript/captionjs/spec/javascripts/fixtures';
        var fixture = readFixtures('caption-fixture.html');
        $('body').append(fixture);
        
        new JCaption('img.test');

        it('Should have image 1', function() {
            expect($('#img1')).toExist();
        });

        it('Should have image 2', function() {
            expect($('#img2')).toExist();
        });

        it('Should have caption as "Joomla logo" under image 1', function() {
            expect($('p')[0]).toHaveText('Joomla logo');
        });

        it('Should have caption as "Joomla" under image 2', function() {
            expect($('p')[1]).toHaveText('Joomla');
        });
    });


});
