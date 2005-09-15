// <?php !! This fools phpdocumentor into parsing this file
/**
* @version $Id: observable.js 4 2005-09-06 19:22:37Z akede $
* @package Mambo
* @subpackage javascript
* @copyright (C) 2000 - 2005 Miro International Pty Ltd
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* Mambo is Free Software
*/

function Observable(/* Boolean */ aIsAsync) {
  this.mObservers = new List();
  this.mIsAsync = aIsAsync || false;
}

Observable.prototype = {
  notify: function(aValue) {
    var length = this.mObservers.getLength();
    for (var i = 0; i < length; ++i) {
     this.mObservers.getAt(i).observe(aValue);
    }
  },

  addObserver: function (/* Object */ aObserver) {
    if (!aObserver.observe) {
      throw 'Observer.addObserver: not an observer';
    }
    this.mObservers.addUnique(aObserver);
  },

  removeObserver: function (/* Object */ aObserver) {
    this.mObservers.removeUnique(aObserver);
  }
};

