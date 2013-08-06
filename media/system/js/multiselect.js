/**
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * JavaScript behavior to allow shift select in administrator grids
 */
(function($) {
    
    Joomla = Joomla || {};
    var $boxes;
    Joomla.JMultiSelect = function(table) {
        var $last;

        var initialize = function(table) {
            $boxes = $('#' + table).find('input[type=checkbox]');
            $boxes.on('click', function(e) {
                doselect(e)
            });
        }
        
        var doselect = function(e) {
            var $current = $(e.target);
            if (e.shiftKey && $last.length) {
                var isChecked = $current.is(':checked');
                var lastIndex = $boxes.index($last);
                var currentIndex = $boxes.index($current);
                if (currentIndex < lastIndex) {
                    // handle selection from bottom up
                    var swap = lastIndex;
                    lastIndex = currentIndex;
                    currentIndex = swap;
                }
                $boxes.slice(lastIndex, currentIndex + 1).attr('checked', isChecked);
            }

            $last = $current;
        }
        initialize(table);
    }

})(jQuery);
