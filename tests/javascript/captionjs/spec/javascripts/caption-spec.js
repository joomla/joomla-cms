/**
 * Created by Ruchiranga on 4/29/2016.
 */

define(['jquery', 'jcaptionSetup', 'jasmineJquery'], function ($) {

    describe('JCaption initialized with valid selector', function () {
        it('Should have caption as "Joomla logo" under image 1', function () {
            expect($('p')[0]).toHaveText('Joomla logo');
        });

        it('Should have caption as "Joomla" under image 2', function() {
            expect($('p')[1]).toHaveText('Joomla');
        });
    });

    describe('JCaption with align and width options', function () {
        it('Should have and element with class right', function () {
            expect($('.right')).toExist();
        });

        it('Should have specified CSS', function () {
            expect($('.right')).toHaveCss({
                float: 'right',
                width: '100px'
            });
        });
    });
});
