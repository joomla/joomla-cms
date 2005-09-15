// <?php !! This fools phpdocumentor into parsing this file
/**
* @version $Id: objects.js 4 2005-09-06 19:22:37Z akede $
* @package Mambo
* @subpackage javascript
* @copyright (C) 2000 - 2005 Miro International Pty Ltd
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* Mambo is Free Software
*/

// -- Map collection object ---------------------
function Map() { }

Map.prototype.toString = function() {
	str = ''; 
	for(var key in this) {
		if(typeof(this[key]) != 'function') {
			if(str) str += ','; 
			str += key+'='+this[key];		
		}
	}
	return str;	
}

String.prototype.toMap = function() {
	var map = new Map();
	var array = this.split(",");
	for (var number in array) {
		var str = array[number]; 
		var pos = str.indexOf('=');
		var key   = str.substring(0, pos );
		var value = str.substring(pos + 1, str.length);
		map[key] = value;
		
	}
	return map;
}

// -- List collection object ---------------------
function List(/* Array */ aArray) {
  this.mArray = aArray || [];
}

List.prototype.getLength = function() {
  return this.mArray.length;
};

List.prototype.getAt = function(/* Number */ aIndex) {
  if (aIndex < 0 || aIndex >= this.mArray.length){
    return undefined;
  }
  return this.mArray[aIndex];
};

List.prototype.removeAll = function() {
  this.mArray = [];
};

List.prototype.removeAt = function (/* Number */ aIndex) {
  var length = this.mArray.length;
  if (length  == 0) {
    return;
  }

  switch(aIndex) {
  case -1:
    break;
  case 0:
    this.mArray.shift();
    break;
  case length - 1:
    this.mArray.pop();
    break;
  default:
    var head = this.mArray.slice(0, aIndex);
    var tail = this.mArray.slice(aIndex+1);
    this.mArray = head.concat(tail);
    break;
  }
};

List.prototype.insertAt = function (/* Object */ aObject, /* Number */ aIndex) {
  switch(aIndex) {
  case -1:
    break;
  case 0:
    this.mArray.unshift();
    break;
  case length:
    this.mArray.push();
    break;
  default:
    var head = this.mArray.slice(0, aIndex - 1);
    var tail = this.mArray.slice(aIndex);
    this.mArray = head.concat([aObject]);
    this.mArray = this.mArray.concat(tail);
    break;
  }
};

List.prototype.findIndexOf =  function(/* Object */ aObject) {
  var length = this.mArray.length;
  for (var i = 0; i < length; ++i) {
    if (this.mArray[i] == aObject) {
      return i;
    }
  }
  return -1;
};

List.prototype.addUnique = function (/* Object */ aObject) {
  var i = this.findIndexOf(aObject);
  if (i == -1) {
    this.mArray[this.mArray.length] = aObject;
  }
};

List.prototype.removeUnique = function (/* Object */ aObject) {
  var length = this.mArray.length;
  if (length  == 0) {
    return;
  }
  var i = this.findIndexOf(aObject);
  this.removeAt(i);
};

// -- String object ----------------------------

String.prototype.trim = function() {
  return(this.replace(/^\s+/,'').replace(/\s+$/,''));
}

// -- Functions --------------------------------

function parseBool(/* String */ str) {
	switch(str) {
		case 'false' :	return new Boolean(false); break;
		case 'true'  : return new Boolean(true);  break;
		default : return; break;
	}
}




