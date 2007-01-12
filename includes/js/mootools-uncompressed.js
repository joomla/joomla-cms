/*
Script: Moo.js
	My Object Oriented javascript.
	
Dependancies:
	 Has no dependancies.
	
Author:
	Valerio Proietti, <http://mad4milk.net>

License:
	MIT-style license.

Credits:
	- Class is slightly based on Base.js  <http://dean.edwards.name/weblog/2006/03/base/> (c) 2006 Dean Edwards, License <http://creativecommons.org/licenses/LGPL/2.1/>
	- Some functions are based on those found in prototype.js <http://prototype.conio.net/> (c) 2005 Sam Stephenson sam [at] conio [dot] net, MIT-style license
	- Documentation by Aaron Newton (aaron.newton [at] cnet [dot] com) and Valerio Proietti.
*/

/*
Class: Class
	The base class object of the <http://mootools.net> framework.
	
Arguments:
	properties - the collection of properties that apply to the class. Creates a new class, its initialize method will fire upon class instantiation.
	
Example:
	>var Cat = new Class({
	>	initialize: function(name){
	>		this.name = name;
	>	}
	>});
	>var myCat = new Cat('Micia');
	>alert myCat.name; //alerts 'Micia'
*/

var Class = function(properties){
	var klass = function(){
		for (var p in this){
			if (this[p]) this[p]._proto_ = this;
		}
		if (arguments[0] != 'noinit' && this.initialize) return this.initialize.apply(this, arguments);
	};
	klass.extend = this.extend;
	klass.implement = this.implement;
	klass.prototype = properties;
	return klass;
};

/*
Property: empty
	Returns an empty function
*/

Class.empty = function(){};

/*
Property: create
	same as new Class. see <Class>
*/

Class.create = function(properties){
	return new Class(properties);
};

Class.prototype = {

	/*
	Property: extend
		Returns the copy of the Class extended with the passed in properties.
		
	Arguments:
		properties - the properties to add to the base class in this new Class.
		
	Example:
		>var Animal = new Class({
		>	initialize: function(age){
		>		this.age = age;
		>	}
		>});
		>var Cat = Animal.extend({
		>	initialize: function(name, age){
		>		this.parent(age); //will call the previous initialize;
		>		this.name = name;
		>	}
		>});
		>var myCat = new Cat('Micia', 20);
		>alert myCat.name; //alerts 'Micia'
		>alert myCat.age; //alerts 20
	*/

	extend: function(properties){
		var pr0t0typ3 = new this('noinit');
		for (var property in properties){
			var previous = pr0t0typ3[property];
			var current = properties[property];
			if (previous && previous != current) current = previous.parentize(current) || current;
			pr0t0typ3[property] = current;
		}
		return new Class(pr0t0typ3);
	},
	
	/*	
	Property: implement
		Implements the passed in properties to the base Class prototypes, altering the base class, unlike <Class.extend>.

	Arguments:
		properties - the properties to add to the base class.
		
	Example:
		>var Animal = new Class({
		>	initialize: function(age){
		>		this.age = age;
		>	}
		>});
		>Animal.implement({
		>	setName: function(name){
		>		this.name = name
		>	}
		>});
		>var myAnimal = new Animal(20);
		>myAnimal.setName('Micia');
		>alert(myAnimal.name); //alerts 'Micia'
	*/
	
	implement: function(properties){
		for (var property in properties) this.prototype[property] = properties[property];
	}

};

/* Section: Object related Functions  */

/*
Function: Object.extend
	Copies all the properties from the second passed object to the first passed Object.
	If you do myWhatever.extend = Object.extend the first parameter will become myWhatever, and your extend function will only need one parameter.
		
Example:
	>var firstOb = {
	>	'name': 'John',
	>	'lastName': 'Doe'
	>};
	>var secondOb = {
	>	'age': '20',
	>	'sex': 'male',
	>	'lastName': 'Dorian'
	>};
	>Object.extend(firstOb, secondOb);
	>//firstOb will become: 
	>{
	>	'name': 'John',
	>	'lastName': 'Dorian',
	>	'age': '20',
	>	'sex': 'male'
	>};
	
Returns:
	The first object, extended.
*/

Object.extend = function(){
	var args = arguments;
	if (args[1]) args = [args[0], args[1]];
	else args = [this, args[0]];
	for (var property in args[1]) args[0][property] = args[1][property];
	return args[0];
};

/*
Function: Object.Native
	Will add a .extend method to the objects passed as a parameter, equivalent to <Class.implement>

Arguments:
	a number of classes/native javascript objects

*/

Object.Native = function(){
	for (var i = 0; i < arguments.length; i++) arguments[i].extend = Class.prototype.implement;
};

new Object.Native(Function, Array, String, Number);

Function.extend({

	parentize: function(current){
		var previous = this;
		return function(){
			this.parent = previous;
			return current.apply(this, arguments);
		};
	}

});
/* 
Script: Function.js
	Contains Function prototypes, utility functions and Chain.

Dependencies: 
	<Moo.js>

Author:
	Valerio Proietti, <http://mad4milk.net>

License:
	MIT-style license.

Credits:
	- Some functions are inspired by those found in prototype.js <http://prototype.conio.net/> (c) 2005 Sam Stephenson sam [at] conio [dot] net, MIT-style license
*/

/*
Class: Function
	A collection of The Function Object prototype methods.
*/

Function.extend({
	
	/*
	Property: pass
		Shortcut to create closures with arguments and bind.
	
	Returns:
		a function.
	
	Arguments:
		args - the arguments to pass to that function (array or single variable)
		bind - optional, the object that the "this" of the function will refer to.
	
	Example:
		>myFunction.pass([arg1, arg2], myElement);
	*/
	
	pass: function(args, bind){
		var fn = this;
		if ($type(args) != 'array') args = [args];
		return function(){
			return fn.apply(bind || fn._proto_ || fn, args);
		};
	},
	
	/*
	Property: bind
		method to easily create closures with "this" altered.
	
	Arguments:
		bind - optional, the object that the "this" of the function will refer to.
	
	Returns:
		a function.
	
	Example:
		>function myFunction(){
		>	this.setStyle('color', 'red');
		>	// note that 'this' here refers to myFunction, not an element
		>	// we'll need to bind this function to the element we want to alter
		>};
		>var myBoundFunction = myFunction.bind(myElement);
		>myBoundFunction(); // this will make the element myElement red.
	*/
	
	bind: function(bind){
		var fn = this;
		return function(){
			return fn.apply(bind, arguments);
		};
	},
	
	/*
	Property: bindAsEventListener
		cross browser method to pass event firer
	
	Arguments:
		bind - optional, the object that the "this" of the function will refer to.
	
	Returns:
		a function with the parameter bind as its "this" and as a pre-passed argument event or window.event, depending on the browser.
	
	Example:
		>function myFunction(event){
		>	alert(event.clientx) //returns the coordinates of the mouse..
		>};
		>myElement.onclick = myFunction.bindAsEventListener(myElement);
	*/
		
	bindAsEventListener: function(bind){
		var fn = this;
		return function(event){
			fn.call(bind, event || window.event);
			return false;
		};
	},
	
	/*
	Property: delay
		Delays the execution of a function by a specified duration.
	
	Arguments:
		ms - the duration to wait in milliseconds
		bind - optional, the object that the "this" of the function will refer to.
	
	Example:
		>myFunction.delay(50, myElement) //wait 50 milliseconds, then call myFunction and bind myElement to it
		>(function(){alert('one second later...')}).delay(1000); //wait a second and alert
	*/
	
	delay: function(ms, bind){
		return setTimeout(this.bind(bind || this._proto_ || this), ms);
	},
	
	/*
	Property: periodical
		Executes a function in the specified intervals of time
		
	Arguments:
		ms - the duration of the intervals between executions.
		bind - optional, the object that the "this" of the function will refer to.
	*/
	
	periodical: function(ms, bind){
		return setInterval(this.bind(bind || this._proto_ || this), ms);
	}

});

/* Section: Utility Functions  */

/*
Function: $clear
	clears a timeout or an Interval.

Returns:
	null

Arguments:
	timer - the setInterval or setTimeout to clear.

Example:
	>var myTimer = myFunction.delay(5000); //wait 5 seconds and execute my function.
	>myTimer = $clear(myTimer); //nevermind

See also:
	<Function.delay>, <Function.periodical>
*/

function $clear(timer){
	clearTimeout(timer);
	clearInterval(timer);
	return null;
};

/*
Function: $type
	Returns the type of object that matches the element passed in.

Arguments:
	obj - the object to inspect.

Example:
	>var myString = 'hello';
	>$type(myString); //returns "string"

Returns:
	'function' - if obj is a function
	'textnode' - if obj is a node but not an element
	'element' - if obj is a DOM element
	'array' - if obj is an array
	'object' - if obj is an object
	'string' - if obj is a string
	'number' - if obj is a number
	false - (boolean) if the object is not defined or none of the above, or if it's an empty string.
*/
	
function $type(obj){
	if (!obj) return false;
	var type = false;
	if (obj instanceof Function) type = 'function';
	else if (obj.nodeName){
		if (obj.nodeType == 3 && !/\S/.test(obj.nodeValue)) type = 'textnode';
		else if (obj.nodeType == 1) type = 'element';
	}
	else if (obj instanceof Array) type = 'array';
	else if (typeof obj == 'object') type = 'object';
	else if (typeof obj == 'string') type = 'string';
	else if (typeof obj == 'number' && isFinite(obj)) type = 'number';
	return type;
};

/*Class: Chain*/

var Chain = new Class({
	
	/*
	Property: chain
		adds a function to the Chain instance stack.
		
	Arguments:
		fn - the function to append.
	
	Returns:
		the instance of the <Chain> class.
	
	Example:
		>var myChain = new Chain();
		>myChain.chain(myFunction).chain(myFunction2);
	*/
	
	chain: function(fn){
		this.chains = this.chains || [];
		this.chains.push(fn);
		return this;
	},
	
	/*
	Property: callChain
		Executes the first function of the Chain instance stack, then removes it. The first function will then become the second.

	Example:
		>myChain.callChain(); //executes myFunction
		>myChain.callChain(); //executes myFunction2
	*/
	
	callChain: function(){
		if (this.chains && this.chains.length) this.chains.splice(0, 1)[0].delay(10, this);
	},

	/*
	Property: clearChain
		Clears the stack of a Chain instance.
	*/
	
	clearChain: function(){
		this.chains = [];
	}

});
/*
Script: Array.js
	Contains Array prototypes and the function <$A>;

Dependencies:
	<Moo.js>
	
Author:
	Valerio Proietti, <http://mad4milk.net>

License:
	MIT-style license.
*/

/*
Class: Array
	A collection of The Array Object prototype methods.
*/

if (!Array.prototype.forEach){
	
	/*	
	Mehod: forEach
		Iterates through an array; note: <Array.each> is the preferred syntax for this funciton.
		
	Arguments:
		fn - the function to execute with each item in the array
		bind - optional, the object that the "this" of the function will refer to.
		
	Example:
		>var Animals = ['Cat', 'Dog', 'Coala'];
		>Animals.forEach(function(animal){
		>	document.write(animal)
		>});

	See also:
		<Function.bind>
		<Array.each>
	*/
	
	Array.prototype.forEach = function(fn, bind){
		for(var i = 0; i < this.length ; i++) fn.call(bind, this[i], i);
	};
}

Array.extend({
	
	/*
	Property: each
		Same as <Array.each>.
	*/
	
	each: Array.prototype.forEach,
	
	/*	
	Property: copy
		Copy the array and returns it.
	
	Returns:
		an Array
			
	Example:
		>var letters = ["a","b","c"];
		>var copy = ["a","b","c"].copy();
	*/
	
	copy: function(){
		var newArray = [];
		for (var i = 0; i < this.length; i++) newArray.push(this[i]);
		return newArray;
	},
	
	/*	
	Property: remove
		Removes an item from the array.
		
	Arguments:
		item - the item to remove
		
	Returns:
		the Array without the item removed.
		
	Example:
		>["1","2","3"].remove("2") // ["1","3"];
	*/
	
	remove: function(item){
		for (var i = 0; i < this.length; i++){
			if (this[i] == item) this.splice(i, 1);
		}
		return this;
	},
	
	/*	
	Property: test
		Tests an array for the presence of an item.
		
	Arguments:
		item - the item to search for in the array.
		
	Returns:
		true - the item was found
		false - it wasn't
		
	Example:
		>["a","b","c"].test("a"); // true
		>["a","b","c"].test("d"); // false
	*/
	
	test: function(item){
		for (var i = 0; i < this.length; i++){
			if (this[i] == item) return true;
		};
		return false;
	},
	
	/*	
	Property: extend
		Extends an array with another
		
	Arguments:
		newArray - the array to extend ours with
		
	Example:
		>var Animals = ['Cat', 'Dog', 'Coala'];
		>Animals.extend(['Lizard']);
		>//Animals is now: ['Cat', 'Dog', 'Coala', 'Lizard'];
	*/
	
	extend: function(newArray){
		for (var i = 0; i < newArray.length; i++) this.push(newArray[i]);
		return this;
	},
	
	/*	
	Property: associate
		Creates an associative array based on the array of keywords passed in.
		
	Arguments:
		keys - the array of keywords.
		
	Example:
		(sart code)
		var Animals = ['Cat', 'Dog', 'Coala', 'Lizard'];
		var Speech = ['Miao', 'Bau', 'Fruuu', 'Mute'];
		var Speeches = Animals.associate(speech);
		//Speeches['Miao'] is now Cat.
		//Speeches['Bau'] is now Dog.
		//...
		(end)
	*/
	
	associate: function(keys){
		var newArray = [];
		for (var i =0; i < this.length; i++) newArray[keys[i]] = this[i];
		return newArray;
	}

});

/* Section: Utility Functions  */

/*
Function: $A()
	Same as <Array.copy>, but as function. 
	Useful to apply Array prototypes to iterable objects, as a collection of DOM elements or the arguments object.
	
Example:
	>function myFunction(){
	>	$A(arguments).each(argument, function(){
	>		alert(argument);
	>	});
	>};
	>//the above will alert all the arguments passed to the function myFunction.
*/

function $A(array){
	return Array.prototype.copy.call(array);
};
/*
Script: String.js
	Contains String prototypes and Number prototypes.

Dependencies:
	<Moo.js>
	
Author:
	Valerio Proietti, <http://mad4milk.net>

License:
	MIT-style license.
*/

/*
Class: String
	A collection of The String Object prototype methods.
*/

String.extend({
	
	/*	
	Property: test
		Tests a string with a regular expression.
		
	Arguments:
		regex - the regular expression you want to match the string with
		params - optional, any parameters you want to pass to the regex
		
	Returns:
		an array with the instances of the value searched for or empty array.
		
	Example:
		>"I like cookies".test("cookie"); // returns ["I like cookies", "cookie"]
		>"I like cookies".test("COOKIE", "i") //ignore case
		>"I like cookies because cookies are good".test("COOKIE", "ig"); //ignore case, find all instances.
		>"I like cookies".test("cake"); //returns empty array
	*/
	
	test: function(regex, params){
		return this.match(new RegExp(regex, params));
	},
	
	/*	
	Property: toInt
		parses a string to an integer.
		
		Returns:
			either an int or "NaN" if the string is not a number.
		
		Example:
			>var value = "10px".toInt(); // value is 10
	*/
	toInt: function(){
		return parseInt(this);
	},
	
	/*	
	Property: camelCase
		Converts a hiphenated string to a camelcase string.
		
	Example:
		>"I-like-cookies".camelCase(); //"ILikeCookies"
		
	Returns:
		the camel cased string
	*/
	
	camelCase: function(){
		return this.replace(/-\D/gi, function(match){
			return match.charAt(match.length - 1).toUpperCase();
		});
	},
	
	/*	
	Property: capitalize
		Converts the first letter in each word of a string to Uppercase.
		
	Example:
		>"i like cookies".capitalize(); //"I Like Cookies"
		
	Returns:
		the capitalized string
	*/
	capitalize: function(){
		return this.toLowerCase().replace(/\b[a-z]/g, function(match){
			return match.toUpperCase();
		});
	},
	
	/*	
	Property: trim
		Trims the leading and trailing spaces off a string.
		
	Example:
		>"    i like cookies     ".trim() //"i like cookies"
		
	Returns:
		the trimmed string
	*/
	
	trim: function(){
		return this.replace(/^\s*|\s*$/g, '');
	},
	
	/*	
	Property: clean
		trims (<String.trim>) a string AND removes all the double spaces in a string.
		
	Returns:
		the cleaned string
		
	Example:
		>" i      like     cookies      \n\n".clean() //"i like cookies"
	*/

	clean: function(){
		return this.replace(/\s\s/g, ' ').trim();
	},

	/*	
	Property: rgbToHex
		Converts an RGB value to hexidecimal. The string must be in the format of "rgb(255, 255, 255)" or "rgba(255, 255, 255, 1)";
		
	Arguments:
		array - boolean value, defaults to false. Use true if you want the array ['FF', '33', '00'] as output instead of #FF3300
		
	Returns:
		hex string or array. returns transparent if the fourth value of rgba in input string is 0,
		
	Example:
		>"rgb(17,34,51)".rgbToHex(); //"#112233"
		>"rgba(17,34,51,0)".rgbToHex(); //"transparent"
		>"rgb(17,34,51)".rgbToHex(true); //[11,22,33]
	*/
	
	rgbToHex: function(array){
		var rgb = this.test('([\\d]{1,3})', 'g');
		if (rgb[3] == 0) return 'transparent';
		var hex = [];
		for (var i = 0; i < 3; i++){
			var bit = (rgb[i]-0).toString(16);
			hex.push(bit.length == 1 ? '0'+bit : bit);
		}
		var hexText = '#'+hex.join('');
		if (array) return hex;
		else return hexText;
	},
	
	/*	
	Property: hexToRgb
		Converts a hexidecimal color value to RGB. Input string must be the hex color value (with or without the hash). Also accepts triplets ('333');
		
	Arguments:
		array - boolean value, defaults to false. Use true if you want the array ['255', '255', '255'] as output instead of "rgb(255,255,255)";
		
	Returns:
		rgb string or array.
		
	Example:
		>"#112233".hexToRgb(); //"rgb(17,34,51)"
		>"#112233".hexToRgb(true); //[17,34,51]
	*/
	
	hexToRgb: function(array){
		var hex = this.test('^[#]{0,1}([\\w]{1,2})([\\w]{1,2})([\\w]{1,2})$');
		var rgb = [];
		for (var i = 1; i < hex.length; i++){
			if (hex[i].length == 1) hex[i] += hex[i];
			rgb.push(parseInt(hex[i], 16));
		}
		var rgbText = 'rgb('+rgb.join(',')+')';
		if (array) return rgb;
		else return rgbText;
	}

});

/*
Class: Number
	contains the internal method toInt.
*/

Number.extend({

	/*
	Property: toInt
		Returns this number; useful because toInt must work on both Strings and Numbers.
	*/

	toInt: function(){
		return this;
	}

});
/*
Script: Element.js
	Contains useful Element prototypes, to be used with the dollar function <$>.
	
Dependencies:
	<Moo.js>, <Function.js>, <Array.js>, <String.js>

Author:
	Valerio Proietti, <http://mad4milk.net>
	
License:
	MIT-style license.
	
Credits:
	- Some functions are inspired by those found in prototype.js <http://prototype.conio.net/> (c) 2005 Sam Stephenson sam [at] conio [dot] net, MIT-style license
*/

/*
Class: Element
	Custom class to allow all of its methods to be used with any DOM element via the dollar function <$>.
*/

var Element = new Class({

	/*
	Property: initialize
		Creates a new element of the type passed in.
			
	Arguments:
		el - the tag name for the element you wish to create.
			
	Example:
		>var div = new Element('div');
	*/

	initialize: function(el){
		if ($type(el) == 'string') el = document.createElement(el);
		return $(el);
	},

	inject: function(el, where){
		el = $(el) || new Element(el);
		switch(where){
			case "before": $(el.parentNode).insertBefore(this, el); break;
			case "after": {
					if (!el.getNext()) $(el.parentNode).appendChild(this);
					else $(el.parentNode).insertBefore(this, el.getNext());
			} break;
			case "inside": el.appendChild(this); break;
		}
		return this;
	},
	
	/*
	Property: injectBefore
		Inserts the Element before the passed element.
			
	Parameteres:
		el - a string representing the element to be injected in (myElementId, or div), or an element reference.
		If you pass div or another tag, the element will be created.
			
	Example:
		>html: 
		><div id="myElement"></div>
		><div id="mySecondElement"></div>
		>js:
		>$('mySecondElement').injectBefore('myElement');
		>resulting html
		><div id="myElement"></div>
		><div id="mySecondElement"></div>

	*/
	
	injectBefore: function(el){
		return this.inject(el, 'before');
	},
	
	/*  
	Property: injectAfter
		Same as <Element.injectBefore>, but inserts the element after.
	*/
	
	injectAfter: function(el){
		return this.inject(el, 'after');
	},

	/*  
	Property: injectInside
		Same as <Element.injectBefore>, but inserts the element inside.
	*/
	
	injectInside: function(el){
		return this.inject(el, 'inside');
	},

	/*  
	Property: adopt
		Inserts the passed element inside the Element. Works as <Element.injectInside> but in reverse.
			
	Parameteres:
		el - a string representing the element to be injected in (myElementId, or div), or an element reference.
		If you pass div or another tag, the element will be created.
	*/
	
	adopt: function(el){
		this.appendChild($(el) || new Element(el));
		return this;
	},
	
	/*  
	Property: remove
		Removes the Element from the DOM.
			
	Example:
		>$('myElement').remove() //bye bye
	*/
	
	remove: function(){
		this.parentNode.removeChild(this);
	},
	
	/*  
	Property: clone
		Clones the Element and returns the cloned one.
		
	Returns: 
		the cloned element
		
	Example:
		>var clone = $('myElement').clone().injectAfter('myElement');
		>//clones the Element and append the clone after the Element.
	*/
	
	clone: function(contents){
		return $(this.cloneNode(contents || true));
	},

	/*  
	Property: replaceWith
		Replaces the Element with an element passed.
			
	Parameteres:
		el - a string representing the element to be injected in (myElementId, or div), or an element reference.
		If you pass div or another tag, the element will be created.
		
	Returns:
		the passed in element
			
	Example:
		>$('myOldElement').replaceWith($('myNewElement')); //$('myOldElement') is gone, and $('myNewElement') is in its place.
	*/
	
	replaceWith: function(el){
		var el = $(el) || new Element(el);
		this.parentNode.replaceChild(el, this);
		return el;
	},
	
	/*  
	Property: appendText
		Appends text node to a DOM element.

	Arguments:
		text - the text to append.
		
	Example:
		><div id="myElement">hey</div>
		>$('myElement').appendText(' howdy'); //myElement innerHTML is now "hey howdy"
  	*/

	appendText: function(text){
		if (this.getTag() == 'style' && window.ActiveXObject) this.styleSheet.cssText = text;
		else this.appendChild(document.createTextNode(text));
		return this;
	},
	
	/*
	Property: hasClass
		Tests the Element to see if it has the passed in className.
		
	Returns:
	 	true - the Element has the class
	 	false - it doesn't
	 
	Arguments:
		className - the class name to test.
	 
	Example:
		><div id="myElement" class="testClass"></div>
		>$('myElement').hasClass('testClass'); //returns true
	*/

	hasClass: function(className){
		return !!this.className.test("\\b"+className+"\\b");
	},

	/*	
	Property: addClass
		Adds the passed in class to the Element, if the element doesnt already have it.
		
	Arguments:
		className - the class name to add
		
	Example: 
		><div id="myElement" class="testClass"></div>
		>$('myElement').addClass('newClass'); //<div id="myElement" class="testClass newClass"></div>
	*/
	
	addClass: function(className){
		if (!this.hasClass(className)) this.className = (this.className+' '+className.trim()).clean();
		return this;
	},
	
	/*	
	Property: removeClass
		works like <Element.addClass>, but removes the class from the element.
	*/

	removeClass: function(className){
		if (this.hasClass(className)) this.className = this.className.replace(className.trim(), '').clean();
		return this;
	},

	/*	
	Property: toggleClass
		Adds or removes the passed in class name to the element, depending on if it's present or not.
		
	Arguments:
		className - the class to add or remove
		
	Example:
		><div id="myElement" class="myClass"></div>
		>$('myElement').toggleClass('myClass');
		><div id="myElement" class=""></div>
		>$('myElement').toggleClass('myClass');
		><div id="myElement" class="myClass"></div>
	*/
	
	toggleClass: function(className){
		if (this.hasClass(className)) return this.removeClass(className);
		else return this.addClass(className);
	},
	
	/*
	Property: setStyle	
		Sets a css property to the Element.
		
		Arguments:
			property - the property to set
			value - the value to which to set it
		
		Example:
			>$('myElement').setStyle('width', '300px'); //the width is now 300px
	*/
	
	setStyle: function(property, value){
		if (property == 'opacity') this.setOpacity(parseFloat(value));
		else this.style[property.camelCase()] = value;
		return this;
	},

	/*
	Property: setStyles
		Applies a collection of styles to the Element.
		
	Arguments:
		source - an object or string containing all the styles to apply
		
	Examples:
		>$('myElement').setStyles({
		>	border: '1px solid #000',
		>	width: '300px',
		>	height: '400px'
		>});

		OR
		
		>$('myElement').setStyle('border: 1px solid #000; width: 300px; height: 400px;');
	*/
	
	setStyles: function(source){
		if ($type(source) == 'object') {
			for (var property in source) this.setStyle(property, source[property]);
		} else if ($type(source) == 'string') {
			if (window.ActiveXObject) this.cssText = source;
			else this.setAttribute('style', source);
		}
		return this;
	},
	
	/*	
	Property: setOpacity
		Sets the opacity of the Element, and sets also visibility == "hidden" if opacity == 0, and visibility = "visible" if opacity == 1.
		
	Arguments:
		opacity - Accepts numbers from 0 to 1.
		
	Example:
		>$('myElement').setOpacity(0.5) //make it 50% transparent
	*/
	
	setOpacity: function(opacity){
		if (opacity == 0){
			if(this.style.visibility != "hidden") this.style.visibility = "hidden";
		} else {
			if(this.style.visibility != "visible") this.style.visibility = "visible";
		}
		if (window.ActiveXObject) this.style.filter = "alpha(opacity=" + opacity*100 + ")";
		this.style.opacity = opacity;
		return this;
	},
	
	/*	
	Property: getStyle
		Returns the style of the Element given the property passed in.
		
	Arguments:
		property - the css style property you want to retrieve
		
	Example:
		>$('myElement').getStyle('width'); //returns "400px"
		>//but you can also use
		>$('myElement').getStyle('width').toInt(); //returns "400"
		
	Returns:
		the style as a string
	*/
	
	getStyle: function(property){
		var proPerty = property.camelCase();
		var style = this.style[proPerty] || false;
		if (!style) {
			if (document.defaultView) style = document.defaultView.getComputedStyle(this,null).getPropertyValue(property);
			else if (this.currentStyle) style = this.currentStyle[proPerty];
		}
		if (style && ['color', 'backgroundColor', 'borderColor'].test(proPerty) && style.test('rgb')) style = style.rgbToHex();
		return style;
	},

	/*	
	Property: addEvent
		Attaches an event listener to a DOM element.
		
	Arguments:
		action - the event to monitor ('click', 'load', etc)
		fn - the function to execute
		
	Example:
		>$('myElement').addEvent('click', function(){alert('clicked!')});
	*/

	addEvent: function(action, fn){
		this[action+fn] = fn.bind(this);
		if (this.addEventListener) this.addEventListener(action, fn, false);
		else this.attachEvent('on'+action, this[action+fn]);
		var el = this;
		if (this != window) Unload.functions.push(function(){
			el.removeEvent(action, fn);
			el[action+fn] = null;
		});
		return this;
	},
	
	/*	
	Property: removeEvent
		Works as Element.addEvent, but instead removes the previously added event listener.
	*/
	
	removeEvent: function(action, fn){
		if (this.removeEventListener) this.removeEventListener(action, fn, false);
		else this.detachEvent('on'+action, this[action+fn]);
		return this;
	},

	getBrother: function(what){
		var el = this[what+'Sibling'];
		while ($type(el) == 'textnode') el = el[what+'Sibling'];
		return $(el);
	},
	
	/*
	Property: getPrevious
		Returns the previousSibling of the Element, excluding text nodes.
		
	Example:
		>$('myElement').getPrevious(); //get the previous DOM element from myElement
		
	Returns:
		the sibling element or undefined if none found.
	*/
	
	getPrevious: function(){
		return this.getBrother('previous');
	},
	
	/*
	Property: getNext
		Works as Element.getPrevious, but tries to find the nextSibling.
	*/
	
	getNext: function(){
		return this.getBrother('next');
	},
	
	/*
	Property: getNext
		Works as <Element.getPrevious>, but tries to find the firstChild.
	*/

	getFirst: function(){
		var el = this.firstChild;
		while ($type(el) == 'textnode') el = el.nextSibling;
		return $(el);
	},

	/*
	Property: getLast
		Works as <Element.getPrevious>, but tries to find the lastChild.
	*/

	getLast: function(){
		var el = this.lastChild;
		while ($type(el) == 'textnode')
		el = el.previousSibling;
		return $(el);
	},

	/*	
	Property: setProperty
		Sets an attribute for the Element.
		
	Arguments:
		property - the property to assign the value passed in
		value - the value to assign to the property passed in
		
	Example:
		>$('myImage').setProperty('src', 'whatever.gif'); //myImage now points to whatever.gif for its source
	*/

	setProperty: function(property, value){
		var el = false;
		switch(property){
			case 'class': this.className = value; break;
			case 'style': this.setStyles(value); break;
			case 'name': if (window.ActiveXObject && this.getTag() == 'input'){
				el = $(document.createElement('<input name="'+value+'" />'));
				$A(this.attributes).each(function(attribute){
					if (attribute.name != 'name') el.setProperty(attribute.name, attribute.value);
					
				});
				if (this.parentNode) this.replaceWith(el);
			};
			default: this.setAttribute(property, value);
		}
		return el || this;
	},
	
	/*	
	Property: setProperties
		Sets numerous attributes for the Element.
		
	Arguments:
		source - an object with key/value pairs.
		
	Example:
		>$('myElement').setProperties({
		>	src: 'whatever.gif',
		>	alt: 'whatever dude'
		>});
		><img src="whatever.gif" alt="whatever dude">
	*/
	
	setProperties: function(source){
		for (var property in source) this.setProperty(property, source[property]);
		return this;
	},
	
	/*
	Property: setHTML
		Sets the innerHTML of the Element.
		
	Arguments:
		html - the new innerHTML for the element.
		
	Example:
		>$('myElement').setHTML(newHTML) //the innerHTML of myElement is now = newHTML
	*/
	
	setHTML: function(html){
		this.innerHTML = html;
		return this;
	},
	
	/*	
	Property: getProperty
		Gets the an attribute of the Element.
		
	Arguments:
		property - the attribute to retrieve
		
	Example:
		>$('myImage').getProperty('src') // returns whatever.gif
		
	Returns:
		the value, or an empty string
	*/
	
	getProperty: function(property){
		return this.getAttribute(property);
	},
	
	/*
	Property: getTag
		Returns the tagName of the element in lower case.
		
	Example:
		>$('myImage').getTag() // returns 'img'
		
	Returns:
		The tag name in lower case
	*/
	
	getTag: function(){
		return this.tagName.toLowerCase();
	},
	
	getOffset: function(what){
		what = what.capitalize();
		var el = this;
		var offset = 0;
		do {
			offset += el['offset'+what] || 0;
			el = el.offsetParent;
		} while (el);
		return offset;
	},

	/*	
	Property: getTop
		Returns the distance from the top of the window to the Element.
	*/
	
	getTop: function(){
		return this.getOffset('top');
	},
	
	/*	
	Property: getLeft
		Returns the distance from the left of the window to the Element.
	*/
	
	getLeft: function(){
		return this.getOffset('left');
	},
	
	/*	
	Property: getValue
		Returns the value of the Element, if its tag is textarea, select or input.
	*/
	
	getValue: function(){
		var value = false;
		switch(this.getTag()){
			case 'select': value = this.getElementsByTagName('option')[this.selectedIndex].value; break;
			case 'input': if ( (this.checked && ['checkbox', 'radio'].test(this.type)) || (['hidden', 'text', 'password'].test(this.type)) ) 
				value = this.value; break;
			case 'textarea': value = this.value;
		}
		return value;
	}

});

new Object.Native(Element);

Element.extend({
	hasClassName: Element.prototype.hasClass,
	addClassName: Element.prototype.addClass,
	removeClassName: Element.prototype.removeClass,
	toggleClassName: Element.prototype.toggleClass
});

/* Section: Utility Functions  */

/*
Function: $Element
	Applies a method with the passed in args to the passed in element. Useful if you dont want to extend the element
		
	Arguments:
		el - the element
		method - a string representing the Element Class method to execute on that element
		args - an array representing the arguments to pass to that method
		
	Example:
		>$Element(el, 'hasClass', className) //true or false
*/

function $Element(el, method, args){
	if ($type(args) != 'array') args = [args];
	return Element.prototype[method].apply(el, args);
};

/*
Function: $()
	returns the element passed in with all the Element prototypes applied.
	
Arguments:
	el - a reference to an actual element or a string representing the id of an element
		
Example:
	>$('myElement') // gets a DOM element by id with all the Element prototypes applied.
	>var div = document.getElementById('myElement');
	>$(div) //returns an Element also with all the mootools extentions applied.
		
	You'll use this when you aren't sure if a variable is an actual element or an id, as
	well as just shorthand for document.getElementById().
		
Returns:
	a DOM element or false (if no id was found)
		
Note:
	you need to call $ on an element only once to get all the prototypes.
	But its no harm to call it multiple times, as it will detect if it has been already extended.
*/

function $(el){
	if ($type(el) == 'string') el = document.getElementById(el);
	if ($type(el) == 'element'){
		if (!el.extend){
			Unload.elements.push(el);
			el.extend = Object.extend;
			el.extend(Element.prototype);
		}
		return el;
	} else return false;
};

window.addEvent = document.addEvent = Element.prototype.addEvent;
window.removeEvent = document.removeEvent = Element.prototype.removeEvent;

var Unload = {

	elements: [], functions: [], vars: [],

	unload: function(){
		Unload.functions.each(function(fn){
			fn();
		});
		
		window.removeEvent('unload', window.removeFunction);
		
		Unload.elements.each(function(el){
			for(var p in Element.prototype){
				window[p] = null;
				document[p] = null;
				el[p] = null;
			}
			el.extend = null;
		});
	}
	
};

window.removeFunction = Unload.unload;
window.addEvent('unload', window.removeFunction);
/*
Script: Fx.js
	Applies visual transitions to any element. Contains Fx.Base, Fx.Style and Fx.Styles

Dependencies:
	<Moo.js>, <Function.js>, <Array.js>, <String.js>, <Element.js>

Author:
	Valerio Proietti, <http://mad4milk.net>

License:
	MIT-style license.
*/

var Fx = fx = {};

/*
Class: Fx.Base
	Base class for the Mootools fx library.
	
Options:
	onStart - the function to execute as the effect begins; nothing (<Class.empty>) by default.
	onComplete - the function to execute after the effect has processed; nothing (<Class.empty>) by default.
	transition - the equation to use for the effect see <Fx.Transitions>; default is <Fx.Transitions.sineInOut>
	duration - the duration of the effect in ms; 500 is the default.
	unit - the unit is 'px' by default (other values include things like 'em' for fonts or '%').
	wait - boolean: to wait or not to wait for a current transition to end before running another of the same instance. defaults to true.
	fps - the frames per second for the transition; default is 30
*/

Fx.Base = new Class({

	setOptions: function(options){
		this.options = Object.extend({
			onStart: Class.empty,
			onComplete: Class.empty,
			transition: Fx.Transitions.sineInOut,
			duration: 500,
			unit: 'px',
			wait: true,
			fps: 50
		}, options || {});
	},

	step: function(){
		var time = new Date().getTime();
		if (time < this.time + this.options.duration){
			this.cTime = time - this.time;
			this.setNow();
		} else {
			this.options.onComplete.pass(this.element, this).delay(10);
			this.clearTimer();
			this.callChain();
			this.now = this.to;
		}
		this.increase();
	},
	
	/*	
	Property: set
		Immediately sets the value with no transition.
	
	Arguments:
		to - the point to jump to
	
	Example:
		>var myFx = new Fx.Style('myElement', 'opacity').set(0); //will make it immediately transparent
	*/
	
	set: function(to){
		this.now = to;
		this.increase();
		return this;
	},
	
	setNow: function(){
		this.now = this.compute(this.from, this.to);
	},

	compute: function(from, to){
		return this.options.transition(this.cTime, from, (to - from), this.options.duration);
	},
	
	/*
	Property: custom
		Executes an effect from one position to the other.
	
	Arguments:
		from - integer:  staring value
		to - integer: the ending value
	
	Examples:
		>var myFx = new Fx.Style('myElement', 'opacity').custom(0,1); //display a transition from transparent to opaque.
	*/
	
	custom: function(from, to){
		if (!this.options.wait) this.clearTimer();
		if (this.timer) return;
		this.options.onStart.pass(this.element, this).delay(10);
		this.from = from;
		this.to = to;
		this.time = new Date().getTime();
		this.timer = this.step.periodical(Math.round(1000/this.options.fps), this);
		return this;
	},
	
	/*
	Property: clearTimer
		Stops processing the transition.
	*/
	clearTimer: function(){
		this.timer = $clear(this.timer);
		return this;
	},
	
	setStyle: function(element, property, value){
		element.setStyle(property, value + this.options.unit);
	}

});

Fx.Base.implement(new Chain);

/*	
Class: Fx.Style
	The Style effect; Extends <Fx.Base>, inherits all its properties. Used to transition any css property from one value to another.

Arguments:
	el - the $(element) to apply the style transition to
	property - the property to transition
	options - the Fx.Base options (see: <Fx.Base>)
	
Example:
	>var marginChange = new fx.Style('myElement', 'margin-top', {duration:500});
	>marginChange.custom(10, 100);
*/

Fx.Style = Fx.Base.extend({

	initialize: function(el, property, options){
		this.element = $(el);
		this.setOptions(options);
		this.property = property.camelCase();
	},
	
	/*	
	Property: hide
		Same as <Fx.Base.set>(0)
	*/
	
	hide: function(){
		return this.set(0);
	},

	/*	
	Property: goTo
		will apply <Fx.Base.custom>, setting the starting point to the current position.
		
	Arguments:
		val - the ending value
	*/

	goTo: function(val){
		return this.custom(this.now || 0, val);
	},

	increase: function(){
		this.setStyle(this.element, this.property, this.now);
	}

});

/*
Class: Fx.Styles
	Allows you to animate multiple css properties at once; Extends <Fx.Base>, inherits all its properties.
	
Arguments:
	el - the $(element) to apply the styles transition to
	options - the fx options (see: <Fx.Base>)

Example:
	>var myEffects = new fx.Styles('myElement', {duration: 1000, transition: fx.linear});
	>myEffects.custom({
	>	'height': [10, 100],
	>	'width': [900, 300]
	>});
*/

Fx.Styles = Fx.Base.extend({

	initialize: function(el, options){
		this.element = $(el);
		this.setOptions(options);
		this.now = {};
	},

	setNow: function(){
		for (var p in this.from) this.now[p] = this.compute(this.from[p], this.to[p]);
	},
	
	/*
	Property:	custom
		The function you'll actually use to execute a transition.
	
	Arguments:
		an object
		
	Example:
		see <Fx.Styles>
	*/

	custom: function(objFromTo){
		if (this.timer && this.options.wait) return;
		var from = {};
		var to = {};
		for (var p in objFromTo){
			from[p] = objFromTo[p][0];
			to[p] = objFromTo[p][1];
		}
		return this.parent(from, to);
	},

	increase: function(){
		for (var p in this.now) this.setStyle(this.element, p, this.now[p]);
	}

});

/*
Class: Element
	Custom class to allow all of its methods to be used with any DOM element via the dollar function <$>.
*/

Element.extend({

	/*
	Property: effect
		Applies an <Fx.Style> to the Element; This a shortcut for <Fx.Style>.

	Example:
		>var myEffect = $('myElement').effect('height', {duration: 1000, transition: Fx.Transitions.linear});
		>myEffect.custom(10, 100);
	*/
	
	effect: function(property, options){
		return new Fx.Style(this, property, options);
	},
	
	/*	
	Property: effects
		Applies an <Fx.Styles> to the Element; This a shortcut for <Fx.Styles>.
		
	Example:
		>var myEffects = $(myElement).effects({duration: 1000, transition: Fx.Transitions.sineInOut});
 		>myEffects.custom({'height': [10, 100], 'width': [900, 300]});
	*/

	effects: function(options){
		return new Fx.Styles(this, options);
	}

});

/*
Class: Fx.Transitions
	A collection of transition equations for use with the <Fx> Class.
		
See Also:
	<Fxtransitions.js> for a whole bunch of transitions.
		
Credits:
	Easing Equations, (c) 2003 Robert Penner (http://www.robertpenner.com/easing/), Open Source BSD License.
*/

Fx.Transitions = {
	
	/* Property: linear */
	linear: function(t, b, c, d){
		return c*t/d + b;
	},
	
	/* Property: sineInOut */
	sineInOut: function(t, b, c, d){
		return -c/2 * (Math.cos(Math.PI*t/d) - 1) + b;
	}

};
/*
Script: Dom.js
	Css Query related function and <Element> extensions
		
Dependencies:
	<Moo.js>, <Function.js>, <Array.js>, <String.js>, <Element.js>

Author:
	Valerio Proietti, <http://mad4milk.net>

License:
	MIT-style license.
*/

/* Section: Utility Functions  */

/*
Function: $S()
	Selects DOM elements based on css selector(s). Extends the elements upon matching.
			
Arguments:
	any number of css selectors
			
Example:
	>$S('a') //an array of all anchor tags on the page
	>$S('a', 'b') //an array of all anchor and bold tags on the page
	>$S('#myElement') //array containing only the element with id = myElement
	>$S('#myElement a.myClass') //an array of all anchor tags with the class "myClass" within the DOM element with id "myElement"

Returns:
	array - array of all the dom elements matched
*/

function $S(){
	var els = [];
	$A(arguments).each(function(sel){
		if ($type(sel) == 'string') els.extend(document.getElementsBySelector(sel));
		else if ($type(sel) == 'element') els.push($(sel));
	});
	return $Elements(els);
};


/*
Function: $$
	Same as <$S>
*/

var $$ = $S;

/* 
Function: $E 
	Selects a single (i.e. the first found) Element based on the selector passed in and an optional filter element.
			
Arguments:
	selector - the css selector to match
	filter - optional; a DOM element to limit the scope of the selector match; defaults to document.
			
Example:
>$E('a', 'myElement') //find the first anchor tag inside the DOM element with id 'myElement'
			
Returns:
	a DOM element - the first element that matches the selector
*/

function $E(selector, filter){
	return ($(filter) || document).getElement(selector);
};

/*
Function: $ES
	Returns a collection of Elements that match the selector passed in limited to the scope of the optional filter.
	See Also: <Element.getElements> for an alternate syntax.
	
Retunrs:
	array - an array of dom elements that match the selector within the filter
				
Arguments:
	selector - css selector to match
	filter - optional; a DOM element to limit the scope of the selector match; defaults to document.
		
Examples:
	>$ES("a") //gets all the anchor tags; synonymous with $S("a")
	>$ES('a','myElement') //get all the anchor tags within $('myElement')	
*/

function $ES(selector, filter){
	return ($(filter) || document).getElementsBySelector(selector);
};

function $Elements(elements){
	return Object.extend(elements, new Elements);
};

/*
Class: Element
	Custom class to allow all of its methods to be used with any DOM element via the dollar function <$>.
*/

Element.extend({
	
	/*
	Property: getElements 
		Gets all the elements within an element that match the given (single) selector.
			
	Arguments:
		selector - the css selector to match
			
	Example:
		>$('myElement').getElements('a'); // get all anchors within myElement
		
	Credits:
		Say thanks to Christophe Beyls <http://digitalia.be> for the new regular expression that rules getElements, a big step forward in terms of speed.
	*/
	
	getElements: function(selector){
		var filters = [];
		selector.clean().split(' ').each(function(sel, i){
			var bits = sel.test('^(\\w*|\\*)(?:#(\\w+)|\\.(\\w+))?(?:\\[["\']?(\\w+)["\']?([\\*\\^\\$]?=)["\']?(\\w*)["\']?\\])?$');
			if (!bits) return;
			if (!bits[1]) bits[1] = '*';
			var param = bits.remove(bits[0]).associate(['tag', 'id', 'class', 'attribute', 'operator', 'value']);
			if (i == 0){
				if (param['id']){
					var el = this.getElementById(param['id']);
					if (!el || (param['tag'] != '*' && $(el).getTag() != param['tag'])) return false;
					filters = [el];
				} else {
					filters = $A(this.getElementsByTagName(param['tag']));
				}
			} else {
				if (param['id']) filters = $Elements(filters).filterById(param['id']);
				filters = $Elements(filters).filterByTagName(param['tag']);
			}
			if (param['class']) filters = $Elements(filters).filterByClassName(param['class']);
			if (param['attribute']) filters = $Elements(filters).filterByAttribute(param['attribute'], param['value'], param['operator']);

		}, this);
		filters.each(function(el){
			$(el);
		});
		return $Elements(filters);
	},
	
	/*
	Property: getElement
		Same as <Element.getElements>, but returns only the first. Alternate syntax for <$E>, where filter is the Element.

	*/
	
	getElement: function(selector){
		return this.getElementsBySelector(selector)[0];
	},
	
	/*
	Property: getElement
		Same as <Element.getElements>, but allows for comma separated selectors, as in css. Alternate syntax for <$S>, where filter is the Element.

	*/

	getElementsBySelector: function(selector){
		var els = [];
		selector.split(',').each(function(sel){
			els.extend(this.getElements(sel));
		}, this);
		return $Elements(els);
	}

});

document.extend = Object.extend;

/* Section: document related functions */

document.extend({
	/*
	Function: document.getElementsByClassName 
		Returns all the elements that match a specific class name. 
		Here for compatibility purposes. can also be written: document.getElements('.className'), or $S('.className')
	*/

	getElementsByClassName: function(className){
		return document.getElements('.'+className);
	},
	getElement: Element.prototype.getElement,
	getElements: Element.prototype.getElements,
	getElementsBySelector: Element.prototype.getElementsBySelector

});

/*
Class: Elements
	Methods for dom queries arrays, as <$S>.
*/

var Elements = new Class({
	
	/*
	Property: action
		Applies the supplied actions collection to each Element in the collection.
			
	Arguments:
		actions - an Object with key/value pairs for the actions to apply. 
		The initialize key is executed immediatly.
		Keys beginning with on will add a simple event (onclick for example).
		Keys ending with event will add an event with <Element.addEvent>.
		Other keys are useless.
		
	Example:
		>$S('a').action({
		>	initialize: function() {
		>		this.addClassName("anchor");
		>	},
		>	onclick: function(){
		>		alert('clicked!');
		>	},
		>	mouseoverevent: function(){
		>		alert('mouseovered!');
		>	}
		>});
	*/
	
	action: function(actions){
		this.each(function(el){
			el = $(el);
			if (actions.initialize) actions.initialize.apply(el);
			for(var action in actions){
				var evt = false;
				if (action.test('^on[\\w]{1,}')) el[action] = actions[action];
				else if (evt = action.test('([\\w-]{1,})event$')) el.addEvent(evt[1], actions[action]);
			}
		});
	},

	//internal methods

	filterById: function(id){
		var found = [];
		this.each(function(el){
			if (el.id == id) found.push(el);
		});
		return found;
	},

	filterByClassName: function(className){
		var found = [];
		this.each(function(el){
			if ($Element(el, 'hasClass', className)) found.push(el);
		});
		return found;
	},

	filterByTagName: function(tagName){
		var found = [];
		this.each(function(el){
			found.extend($A(el.getElementsByTagName(tagName)));
		});
		return found;
	},

	filterByAttribute: function(name, value, operator){
		var found = [];
		this.each(function(el){
			var att = el.getAttribute(name);
			if(!att) return;
			if (!operator) return found.push(el);
			
			switch(operator){
				case '*=': if (att.test(value)) found.push(el); break;
				case '=': if (att == value) found.push(el); break;
				case '^=': if (att.test('^'+value)) found.push(el); break;
				case '$=': if (att.test(value+'$')) found.push(el);
			}

		});
		return found;
	}

});

new Object.Native(Elements);
/*
Script: Ajax.js
	Contains the ajax class. Also contains methods to generate querystings from forms and Objects.
		
Dependencies:
	<Moo.js>, <Function.js>, <Array.js>, <String.js>, <Element.js>

Author:
	Valerio Proietti, <http://mad4milk.net>

License:
	MIT-style license.
*/

/*
Class: Ajax
	For all your asynchronous needs. Note: this class implements <Chain>

Arguments:
	url - the url pointing to the server-side script.
	options - optional, an object containing options.

Options:
	method - 'post' or 'get' - the prototcol for the request; optional, defaults to 'post'.
	postBody - if method is post, you can write parameters here. Can be a querystring, an object or a Form element.
	async - boolean: asynchronous option; true uses asynchronous requests. Defaults to true.
	onComplete - function to execute when the ajax request completes.
	onStateChange - function to execute when the state of the XMLHttpRequest changes.
	update - $(element) to insert the response text of the XHR into, upon completion of the request.
	evalScripts - boolean; default is false. Execute scripts in the response text onComplete.

Example:
	>var myAjax = new Ajax(url, {method: 'get'}).request();								
*/
	
var Ajax = ajax = new Class({

	setOptions: function(options){
		this.options = {
			method: 'post',
			postBody: null,
			async: true,
			onComplete: Class.empty,
			onStateChange: Class.empty,
			update: null,
			evalScripts: false
		};
		Object.extend(this.options, options || {});
	},
	
	initialize: function(url, options){
		this.setOptions(options);
		this.url = url;
		this.transport = this.getTransport();
	},
	
	/*
	Property: request
		Executes the ajax request.
			
	Example:
		>var myAjax = new Ajax(url, {method: 'get'});
		>myAjax.request();
		
		OR
		
		>new Ajax(url, {method: 'get'}).request();
	*/
	
	request: function(){
		this.transport.open(this.options.method, this.url, this.options.async);
		this.transport.onreadystatechange = this.onStateChange.bind(this);
		if (this.options.method == 'post'){
			this.transport.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			if (this.transport.overrideMimeType) this.transport.setRequestHeader('Connection', 'close');
		}
		switch($type(this.options.postBody)){
			case 'element': this.options.postBody = $(this.options.postBody).toQueryString(); break;
			case 'object': this.options.postBody = Object.toQueryString(this.options.postBody);
		}
		if($type(this.options.postBody) == 'string') this.transport.send(this.options.postBody);
		else this.transport.send(null);
		return this;
	},

	onStateChange: function(){
		this.options.onStateChange.delay(10, this);
		if (this.transport.readyState == 4 && this.transport.status == 200){
			if (this.options.update) $(this.options.update).setHTML(this.transport.responseText);
			this.options.onComplete.pass([this.transport.responseText, this.transport.responseXML], this).delay(20);
			if (this.options.evalScripts) this.evalScripts.delay(30, this);
			this.transport.onreadystatechange = Class.empty;
			this.callChain();
		}
	},
	
	/*  
	Property: evalScripts
		Executes scripts in the response text
	*/
	
	evalScripts: function(){
		if(scripts = this.transport.responseText.match(/<script[^>]*?>[\S\s]*?<\/script>/g)){
			scripts.each(function(script){
				eval(script.replace(/^<script[^>]*?>/, '').replace(/<\/script>$/, ''));
			});
		}
	},

	getTransport: function(){
		if (window.XMLHttpRequest) return new XMLHttpRequest();
		else if (window.ActiveXObject) return new ActiveXObject('Microsoft.XMLHTTP');
	}

});

Ajax.implement(new Chain);

/* Section: Object related Functions  */

/*  
Function: Object.toQuerySTring
	Generates a querysting from a key/pair values in an object
			
Arguments:
	source - the object to generate the querystring from.

Returns:
	the query string.
			
Example:
	>Object.toQueryString({apple: "red", lemon: "yellow"}); //returns "apple=red&lemon=yellow"
*/

Object.toQueryString = function(source){
	var queryString = [];
	for (var property in source) queryString.push(encodeURIComponent(property)+'='+encodeURIComponent(source[property]));
	return queryString.join('&');
};

/*
Class: Element
	Custom class to allow all of its methods to be used with any DOM element via the dollar function <$>.
*/

Element.extend({
	
	/*
	Property: send
		Sends a form with an ajax post request
			
	Arguments:
		options - option collection for ajax request. See <Ajax.initialize> for option list.
	
	Returns:
		The Ajax Class Instance
			
	Example:
		(start code)
		<form id="myForm" action="submit.php">
		<input name="email" value="bob@bob.com">
		<input name="zipCode" value="90210">
		</form>
		<script>
		$('myForm').send()
		</script>
		(end)
	*/
	
	send: function(options){
		options = Object.extend(options, {postBody: this.toQueryString(), method: 'post'});
		return new Ajax(this.getProperty('action'), options).request();
	},
	
	/*
	Property: toQueryString
		Reads the children inputs of the Element and generates a  query string, based on their values. Used internally in <Ajax>
			
	Example:
		(start code)
		<form id="myForm" action="submit.php">
		<input name="email" value="bob@bob.com">
		<input name="zipCode" value="90210">
		</form>

		<script>
		 $('myForm').toQueryString()
		</script>
		(end)

		Returns:
			email=bob@bob.com&zipCode=90210
	*/
	
	toQueryString: function(){
		var queryString = [];
		$A(this.getElementsByTagName('*')).each(function(el){
			var name = $(el).name;
			var value = el.getValue();
			if (value && name) queryString.push(encodeURIComponent(name)+'='+encodeURIComponent(value));
		});
		return queryString.join('&');
	}

});
/*
Script: Drag.js
	Contains Classes Drag.Base, Drag.Move and a couple of element methods to drag and resize your elements.
		
Dependencies:
	<Moo.js>, <Function.js>, <Array.js>, <String.js>, <Element.js>

Author:
	Valerio Proietti, <http://mad4milk.net>

License:
	MIT-style license.
*/

var Drag = {};

/*
Class: Drag.Base
	Modify two css properties of an element based on the position of the mouse.

Arguments:
	el - the $(element) to apply the transformations to.
	xModifier - required. The css property to modify, based to the position on the X axis of the mouse. defaults to none.
	yModifier - required. The css property to modify, based to the position on the Y axis of the mouse pointer. defaults to none.
	options - optional. The options object.

Options:
	handle - the $(element) to act as the handle for the draggable element. defaults to the $(element) itself.
	onStart - function to execute when the user starts to drag (on mousedown); optional.
	onComplete - optional, function to execute when the user completes the drag.
	onDrag - optional, function to execute at every step of the dragged $(element).
	xMax - optional, the maximum value for the x property of the dragged $(element).
	xMin - optional, the minium value for the x property of the dragged $(element).
	yMax - optional, the maximum value for the y property of the dragged $(element).
	yMin - optional, the minium value for the y property of the dragged $(element).
*/

Drag.Base = new Class({

	setOptions: function(options){
		this.options = Object.extend({
			handle: false,
			unit: 'px', 
			onStart: Class.empty, 
			onComplete: Class.empty, 
			onDrag: Class.empty,
			xMax: false,
			xMin: false,
			yMax: false,
			yMin: false
		}, options || {});
	},

	initialize: function(el, xModifier, yModifier, options){
		this.setOptions(options);
		this.element = $(el);
		this.handle = $(this.options.handle) || this.element;
		if (xModifier) this.xp = xModifier.camelCase();
		if (yModifier) this.yp = yModifier.camelCase();
		this.handle.onmousedown = this.start.bind(this);
	},

	start: function(evt){
		evt = evt || window.event;
		this.startX = evt.clientX;
		this.startY = evt.clientY;
		
		this.handleX = this.startX - this.handle.getLeft();
		this.handleY = this.startY - this.handle.getTop();
		
		this.set(evt);
		this.options.onStart.pass(this.element, this).delay(10);
		document.onmousemove = this.drag.bind(this);
		document.onmouseup = this.end.bind(this);
		return false;
	},

	addStyles: function(x, y){
		if (this.xp){
			var stylex = this.element.getStyle(this.xp).toInt();
		
			var movex = function(val){
				this.element.setStyle(this.xp, val+this.options.unit);
			}.bind(this);
		
			if (this.options.xMax && stylex >= this.options.xMax){
				if (this.clientX <= this.handleX+this.handle.getLeft()) movex(stylex+x);
				if (stylex > this.options.xMax) movex(this.options.xMax);
			} else if(this.options.xMin && stylex <= this.options.xMin){
				if (this.clientX >= this.handleX+this.handle.getLeft()) movex(stylex+x);
				if (stylex < this.options.xMin) movex(this.options.xMin);
			} else movex(stylex+x);
		}
		if (this.yp){
			var styley = this.element.getStyle(this.yp).toInt();

			var movey = function(val){
				this.element.setStyle(this.yp, val+this.options.unit);
			}.bind(this);

			if (this.options.yMax && styley >= this.options.yMax){
				if (this.clientY <= this.handleY+this.handle.getTop()) movey(styley+y);
				if (styley > this.options.yMax) movey(this.options.yMax);
			} else if(this.options.yMin && styley <= this.options.yMin){
				if (this.clientY >= this.handleY+this.handle.getTop()) movey(styley+y);
				if (styley < this.options.yMin) movey(this.options.yMin);
			} else movey(styley+y);
		}
	},

	drag: function(evt){
		evt = evt || window.event;
		this.clientX = evt.clientX;
		this.clientY = evt.clientY;
		this.options.onDrag.pass(this.element, this).delay(5);
		this.addStyles((this.clientX-this.lastMouseX), (this.clientY-this.lastMouseY));
		this.set(evt);
		return false;
	},

	set: function(evt){
		this.lastMouseX = evt.clientX;
		this.lastMouseY = evt.clientY;
		return false;
	},

	end: function(){
		document.onmousemove = null;
		document.onmouseup = null;
		this.options.onComplete.pass(this.element, this).delay(10);
	}

});

/*
Class: Drag.Move
	Extends <Drag.Base>, has additional functionality for dragging an element (modifying its left and top values).

Arguments:
	el - the $(element) to apply the transformations to.
	options - optional. The options object.
	
Options:
	all the drag.Base options, plus:
	snap - if true the element will start dragging after a certain distance has been reached from the mousedown. defaults to true.
	snapDistance - integer representing the snapping distance. Default is 8.
	onSnap - function to execute when the element has been dragged the snapDistance.
	xModifier - the modifier to handle left and right dragging; defaults to left.
	yModifier - the modifier to handle up and down dragging; defaults to top.
	container - if set to an element will fill automatically xMin/xMax/yMin/yMax based on the $(element) size and position. defaults to false.
*/

Drag.Move = Drag.Base.extend({

	extendOptions: function(options){
		this.options = Object.extend(this.options || {}, Object.extend({
			onSnap: Class.empty,
			droppables: [],
			snapDistance: 8,
			snap: true,
			xModifier: 'left',
			yModifier: 'top',
			container: false
		}, options || {}));
	},

	initialize: function(el, options){
		this.extendOptions(options);
		this.container = $(this.options.container);
		this.parent(el, this.options.xModifier, this.options.yModifier, this.options);
	},

	start: function(evt){
		if (this.options.container) {
			var cont = $(this.options.container).getPosition();
			Object.extend(this.options, {
				xMax: cont.right-this.element.offsetWidth,
				xMin: cont.left,
				yMax: cont.bottom-this.element.offsetHeight,
				yMin: cont.top
			});
		}
		this.parent(evt);
		if (this.options.snap) document.onmousemove = this.checkAndDrag.bind(this);
		return false;
	},

	drag: function(evt){
		this.parent(evt);
		this.options.droppables.each(function(drop){
			if (this.checkAgainst(drop)){
				if (drop.onOver && !drop.dropping) drop.onOver.pass([this.element, this], drop).delay(10);
				drop.dropping = true;
			} else {
				if (drop.onLeave && drop.dropping) drop.onLeave.pass([this.element, this], drop).delay(10);
				drop.dropping = false;
			}
		}, this);
		return false;
	},

	checkAndDrag: function(evt){
		evt = evt || window.event;
		var distance = Math.round(Math.sqrt(Math.pow(evt.clientX - this.startX, 2)+Math.pow(evt.clientY - this.startY, 2)));
		if (distance > this.options.snapDistance){
			this.set(evt);
			this.options.onSnap.pass(this.element, this).delay(10);
			document.onmousemove = this.drag.bind(this);
			this.addStyles(-(this.startX-evt.clientX), -(this.startY-evt.clientY));
		}
		return false;
	},

	checkAgainst: function(el){
		x = this.clientX+Window.getScrollLeft();
		y = this.clientY+Window.getScrollTop();
		var el = $(el).getPosition();
		return (x > el.left && x < el.right && y < el.bottom && y > el.top);
	},

	end: function(){
		this.parent();
		this.options.droppables.each(function(drop){
			if (drop.onDrop && this.checkAgainst(drop)) drop.onDrop.pass([this.element, this], drop).delay(10);
		}, this);
	}

});

/*
Class: Element
	Custom class to allow all of its methods to be used with any DOM element via the dollar function <$>.
*/

Element.extend({
	
	/*
	Property: makeDraggable
		Makes an element draggable with the supplied options.
		
	Arguments:
		options - see <Drag.Move> and <Drag.Base> for acceptable options.
	*/
	
	makeDraggable: function(options){
		return new Drag.Move(this, options);
	},
	
	/*
	Property: makeResizable
		Makes an element resizable (by dragging) with the supplied options.

	Arguments:
		options - see <Drag.Base> for acceptable options.
	*/
	
	makeResizable: function(options){
		return new Drag.Base(this, 'width', 'height', options);
	},
	
	/*	
	Property: getPosition
		Returns an object with width, height, left, right, top, and bottom, representing the values of the Element
		
	Example:
		>var myValues = $('myElement').getPosition();
		>//myValues will be..
	 	>{
		>	width: 200,
		>	height: 300,
		>	left: 100,
		>	top: 50,
		>	right: 300,
		>	bottom: 350
		>}
	*/

	getPosition: function(){
		var obj = {};
		obj.width = this.offsetWidth;
		obj.height = this.offsetHeight;
		obj.left = this.getLeft();
		obj.top = this.getTop();
		obj.right = obj.left + obj.width;
		obj.bottom = obj.top + obj.height;
		return obj;
	}

});
/*
Script: Window.js
	Window methods, as those to get the size or a better onload.
		
Dependencies:
	<Moo.js>, <Function.js>, <String.js>

Author:
	Valerio Proietti, <http://mad4milk.net>

License:
	MIT-style license.
*/

/*
Class: Window
	Cross browser methods to get the window size, onDomReady method.
*/

var Window = {
	
	/*	
	Property: disableImageCache
		Disables background image chache for internex explorer, to prevent flickering. 
		To be called if you have effects with background images, and they flicker.
		
	Example:
		Window.disableImageCache();
	*/
	
	disableImageCache: function(){
		if (window.ActiveXObject) document.execCommand("BackgroundImageCache", false, true);
	},

	extend: Object.extend,
	
	/*	
	Property: getWidth
		Returns an integer representing the width of the browser.
	*/

	getWidth: function(){
		return window.innerWidth || document.documentElement.clientWidth || 0;
	},
	
	/*
	Property: getHeight
		Returns an integer representing the height of the browser.
	*/

	getHeight: function(){
		return window.innerHeight || document.documentElement.clientHeight || 0;
	},

	/*
	Property: getScrollHeight
		Returns an integer representing the scrollHeight of the window.

	See Also:
		<http://developer.mozilla.org/en/docs/DOM:element.scrollHeight>
	*/

	getScrollHeight: function(){
		return document.documentElement.scrollHeight;
	},

	/*
	Property: getScrollWidth
		Returns an integer representing the scrollWidth of the window.
		
	See Also:
		<http://developer.mozilla.org/en/docs/DOM:element.scrollWidth>
	*/

	getScrollWidth: function(){
		return document.documentElement.scrollWidth;
	},

	/*	
	Property: getScrollTop
		Returns an integer representing the scrollTop of the window (the number of pixels the window has scrolled from the top).
		
	See Also:
		<http://developer.mozilla.org/en/docs/DOM:element.scrollTop>
	*/

	getScrollTop: function(){
		return document.documentElement.scrollTop || window.pageYOffset || 0;
	},

	/*
	Property: getScrollLeft
		Returns an integer representing the scrollLeft of the window (the number of pixels the window has scrolled from the left).
		
	See Also:
		<http://developer.mozilla.org/en/docs/DOM:element.scrollLeft>
	*/
	
	getScrollLeft: function(){
		return document.documentElement.scrollLeft || window.pageXOffset || 0;
	},
	
	/*
	Property: onDomReady
		Executes the passed in function when the DOM is ready (when the document tree has loaded, not waiting for images).
		
	Credits:
		(c) Dean Edwards/Matthias Miller/John Resig, remastered for mootools. Later touched up by Christophe Beyls <http://digitalia.be>.
		
	Arguments:
		init - the function to execute when the DOM is ready
		
	Example:
		> Window.onDomReady(function(){alert('the dom is ready');});
	*/
	
	onDomReady: function(init){
		var state = document.readyState;
		if (state && document.childNodes && !document.all && !navigator.taintEnabled){ //khtml
			if (state.test(/loaded|complete/)) return init();
			else return Window.onDomReady.pass(init).delay(100);
		} else if (state && window.ActiveXObject){ //ie
			var script = $('_ie_ready_');
			if (!script) document.write("<script id='_ie_ready_' defer='true' src='://'></script>");
			$('_ie_ready_').addEvent('readystatechange', function(){
				if (this.readyState == 'complete') init();
			});
			return;
		} else { //others
			var myInit = function() {
				if (arguments.callee.done) return;
				arguments.callee.done = true;
				init();
			};
			window.addEvent("load", myInit);
			document.addEvent("DOMContentLoaded", myInit);
		}
	}

};
/*	
Script: Cookie.js
	A cookie reader/creator

Credits: 
	based on the functions by Peter-Paul Koch (http://quirksmode.org)
	
Dependencies:
	<Moo.js>, <String.js>
*/

/*
Class: Cookie
	Class for creating, getting, and removing cookies.
*/

var Cookie = {
	
	/*	
	Property: set
		Sets a cookie in the browser.
		
	Arguments:
		key - the key (name) for the cookie
		value - the value to set
		duration - optional, how long the cookie should remain (in days); defaults to 1 year.
		
	Example:
		>Cookie.set("username", "Aaron", 1) //save this for a day
		
	*/

	set: function(key, value, duration){
		var date = new Date();
		date.setTime(date.getTime()+((duration || 365)*86400000));
		document.cookie = key+"="+value+"; expires="+date.toGMTString()+"; path=/";
	},
	
	/*	
	Property: get
		Gets the value of a cookie.
		
	Arguments:
		key - the name of the cookie you wish to retrieve.
		
	Example:
		>Cookie.get("username") //returns Aaron
	*/
	
	get: function(key){
		var myValue, myVal;
		document.cookie.split(';').each(function(cookie){
			if(myVal = cookie.trim().test(key+'=(.*)')) myValue = myVal[1];
		});
		return myValue;
	},

	/*
	Property: remove
		Removes a cookie from the browser.
		
	Arguments:
		key - the name of the cookie to remove
		
	Examples:
		>Cookie.remove("username") //bye-bye Aaron
	*/

	remove: function(key){
		this.set(key, '', -1);
	}

};
/*
Script: Json.js
	Simple Json parser and Stringyfier, See: <http://www.json.org/>
		
Dependencies:
	<Moo.js>, <Function.js>, <Array.js>, <String.js>, <Element.js>

Author:
	Valerio Proietti, <http://mad4milk.net>

License:
	MIT-style license.
*/

/*
Class: Json
	Simple Json parser and Stringyfier, See: <http://www.json.org/>
*/

var Json = {

	/*
	Property: toString
		Converts an object to a string, to be passed in server-side scripts as a parameter. Although its not normal usage for this class, this method can also be used to convert functions and arrays to strings.
		
	Arguments:
		obj - the object to convert to string
		
	Returns:
		A json string
		
	Example:
		(start code)
		Json.toString({apple: 'red', lemon: 'yellow'}); "{"apple":"red","lemon":"yellow"}" //don't get hung up on the quotes; it's just a string.
		(end)
	*/

	toString: function(el){
		var string = [];
		
		var isArray = function(array){
			var string = [];
			array.each(function(ar){
				string.push(Json.toString(ar));
			});
			return string.join(',');
		};
		
		var isObject = function(object){
			var string = [];
			for (var property in object) string.push('"'+property+'":'+Json.toString(object[property]));
			return string.join(',');
		};
		
		switch($type(obj)){
			case 'number': string.push(obj); break;
			case 'string': string.push('"'+obj+'"'); break;
			case 'function': string.push(obj); break;
			case 'object': string.push('{'+isObject(obj)+'}'); break;
			case 'array': string.push('['+isArray(obj)+']');
		}
		
		return string.join(',');
	},
	
	/*	
	Function: evaluate
		converts a json string to an object.
		
	Arguments:
		str - the string to evaluate.
		
	Example:
		>var myObject = Json.evaluate('{"apple":"red","lemon":"yellow"}');
		>//myObject will become {apple: 'red', lemon: 'yellow'}
	*/

	evaluate: function(str){
		return eval('(' + str + ')');
	}

};
/*
Script: Sortables.js
	Contains <Sortables> Class.
	
Dependencies:
	<Moo.js>, <Function.js>, <Array.js>, <String.js>, <Element.js>, <Fx.js>, <Drag.js>
	
Author:
	Valerio Proietti, <http://mad4milk.net>

License:
	MIT-style license.
*/

/*
Class: Sortables
	Creates an interface for <Drag> and drop, resorting of a list.

Arguments:
	elements - requires,  the collection of elements that will become sortables.
	options - an Object, see options below.

Options:
	handles - a collection of elements to be used for drag handles. defaults to the elements.
	fxDuration - the duration in ms of the effects applied to the dragged element and its clone. defaults to 250 ms.
	fxTransition - the transition for the effects (see also: <Fx.Transitions>).
	maxOpacity - the opacity for the dragged clone
	onStart - function executed when the item starts dragging
	onComplete - function executed when the item ends dragging
	contain - boolean, true keeps the dragged item constrained to it's parent element, defaults to false.
*/
	
var Sortables = new Class({

	setOptions: function(options) {
		this.options = {
			handles: false,
			fxDuration: 250,
			fxTransition: Fx.Transitions.sineInOut,
			maxOpacity: 0.5,
			onComplete: Class.empty,
			onStart: Class.empty,
			contain: false
		};
		Object.extend(this.options, options || {});
	},

	initialize: function(elements, options){
		this.setOptions(options);
		this.options.handles = this.options.handles || elements;
		var trash = new Element('div').injectInside($(document.body));
		$A(elements).each(function(el, i){
			var copy = $(el).clone().setStyles({
				'position': 'absolute',
				'opacity': '0',
				'display': 'none'
			}).injectInside(trash);
			var elEffect = el.effect('opacity', {
				duration: this.options.fxDuration,
				wait: false,
				transition: this.options.fxTransition
			}).set(1);
			var copyEffects = copy.effects({
				duration: this.options.fxDuration,
				wait: false,
				transition: this.options.fxTransition,
				onComplete: function(){
					copy.setStyle('display', 'none');
				}
			});
			var yMax = false;
			var yMin = false;
			if (this.options.contain){
				yMax = $(el.parentNode).getTop()+el.parentNode.offsetHeight-el.offsetHeight;
				yMin = el.parentNode.getTop();
			}
			var dragger = new Drag.Move(copy, {
				handle: this.options.handles[i],
				yMax: yMax,
				yMin: yMin,
				xModifier: false,
				onStart: function(){
					this.options.onStart.bind(this).delay(10);
					copy.setHTML(el.innerHTML).setStyles({
						'display': 'block',
						'opacity': this.options.maxOpacity,
						'top': el.getTop()+'px',
						'left': el.getLeft()+'px'
					});
					elEffect.custom(elEffect.now, this.options.maxOpacity);
				}.bind(this),
				onComplete: function(){
					this.options.onComplete.bind(this).delay(10);
					copyEffects.custom({
						'opacity': [this.options.maxOpacity, 0],
						'top': [copy.getTop(), el.getTop()]
					});
					elEffect.custom(elEffect.now, 1);
				}.bind(this),
				onDrag: function(){
					if (el.getPrevious() && copy.getTop() < (el.getPrevious().getTop())) el.injectBefore(el.getPrevious());
					else if (el.getNext() && copy.getTop() > (el.getNext().getTop())) el.injectAfter(el.getNext());
				}
			});
		}, this);
	}

});
/*
Script: Fxpack.js
	More Specific Effects.

Author:
	Valerio Proietti, <http://mad4milk.net>

License:
	MIT-style license.
		
Dependencies:
	<Moo.js>, <Function.js>, <Array.js>, <String.js>, <Element.js>, <Fx.js>

*/

/*		
Class: Fx.Scroll
	The scroller effect; scrolls an element or the window to a location. Extends <Fx.Base>, inherits all its properties.

Arguments:
	el - the $(element) to apply the style transition to
	options - the Fx.Base options (see: <Fx.Base>)
*/

Fx.Scroll = Fx.Base.extend({

	initialize: function(el, options) {
		this.element = $(el);
		this.setOptions(options);
	},
	
	/*	
	Property: down
		Scrolls an element down to the bottom of its scroll height.
	*/

	down: function(){
		return this.custom(this.element.scrollTop, this.element.scrollHeight-this.element.offsetHeight);
	},
	
	/*
	Property: up
		Scrolls an element up to the top of its scroll height.
	*/

	up: function(){
		return this.custom(this.element.scrollTop, 0);
	},

	increase: function(){
		this.element.scrollTop = this.now;
	}
});

/*
Class: Fx.Slide
	The slide effect; slides an element in horizontally or vertically, the contents will fold inside. Extends <Fx.Base>, inherits all its properties.
	
Note:
	This effect works on any block element, but the element *cannot be positioned*; no margins or absolute positions. To position the element, put it inside another element (a wrapper div, for instance) and position that instead.
	
Options:
	mode - set it to vertical or horizontal. Defaults to vertical.
	and all the <Fx.Base> options

Example:
	(start code)
	var mySlider = new Fx.Slide('myElement', {duration: 500});
	mySlider.toggle() //toggle the slider up and down.
	(end)
*/

Fx.Slide = Fx.Base.extend({

	initialize: function(el, options){
		this.element = $(el);
		this.wrapper = new Element('div').injectAfter(this.element).setStyle('overflow', 'hidden').adopt(this.element);
		this.setOptions(options);
		if (!this.options.mode) this.options.mode = 'vertical';
		this.now = [];
	},

	setNow: function(){
		[0,1].each(function(i){
			this.now[i] = this.compute(this.from[i], this.to[i]);
		}, this);
	},

	vertical: function(){
		this.margin = 'top';
		this.layout = 'height';
		this.startPosition = [this.element.scrollHeight, '0'];
		this.endPosition = ['0', -this.element.scrollHeight];
		return this;
	},

	horizontal: function(){
		this.margin = 'left';
		this.layout = 'width';
		this.startPosition = [this.element.scrollWidth, '0'];
		this.endPosition = ['0', -this.element.scrollWidth];
		return this;
	},
	
	/*
	Property: hide
		Hides the element without a transition.
	*/

	hide: function(){
		this[this.options.mode]();
		this.wrapper.setStyle(this.layout, '0');
		this.element.setStyle('margin-'+this.margin, -this.element['scroll'+this.layout.capitalize()]+this.options.unit);
		return this;
	},
	
	/*
	Property: show
		Shows the element without a transition.
	*/

	show: function(){
		this[this.options.mode]();
		this.wrapper.setStyle(this.layout, this.element['scroll'+this.layout.capitalize()]+this.options.unit);
		this.element.setStyle('margin-'+this.margin, '0');
		return this;
	},
	
	/*
	Property: toggle
		Hides or shows a slide element, depending on its state;
	*/

	toggle: function(mode){
		this[this.options.mode]();
		if (this.wrapper['offset'+this.layout.capitalize()] > 0) return this.custom(this.startPosition, this.endPosition);
		else return this.custom(this.endPosition, this.startPosition);
	},

	increase: function(){		
		this.wrapper.setStyle(this.layout, this.now[0]+this.options.unit);
		this.element.setStyle('margin-'+this.margin, this.now[1]+this.options.unit);
	}
	
});

/*
Class: Fx.Color
	Smoothly transitions the color of an element; Extends <Fx.Base>, inherits all its properties.
	
Credits:
	fx.Color, originally by Tom Jensen (http://neuemusic.com) MIT-style LICENSE.
	
Arguments:
	same arguments as <Fx.Style>, only accepts color based properties.
	
Example:
	(start code)
	var myColorFx = new Fx.Color('myElement', 'color', {duration: 500});
	myColorFx.custom('000000', 'FF0000') //fade from black to red
	(end)
*/

Fx.Color = Fx.Base.extend({

	initialize: function(el, property, options){
		this.element = $(el);
		this.setOptions(options);
		this.property = property;
		this.now = [];
	},

	/*	
	Property: custom
		Transitions one color of the element specified in class creation smoothly from one color to the next.
		
	Arguments:
		from - the starting color
		to - the ending color
		
	Note:
		Both values can be any of the following formats:
		'#333' - css shorthand with the hash
		'333' - or without the hash
		'#333333' - css longhand with the hash
		'333333' - without the hash
	*/

	custom: function(from, to){
		return this.parent(from.hexToRgb(true), to.hexToRgb(true));
	},

	setNow: function(){
		[0,1,2].each(function(i){
			this.now[i] = Math.round(this.compute(this.from[i], this.to[i]));
		}, this);
	},

	increase: function(){
		this.element.setStyle(this.property, "rgb("+this.now[0]+","+this.now[1]+","+this.now[2]+")");
	},
	
	/*	
	Property: fromColor
		Transitions from the color passed in to the current color of the element.
		
	Arguments:
		color - the color to transition *from* to the current color of the element.
		
	Example:
		>myColorFx.fromColor('F00') //transition from red to whatever color the element is currently
	*/

	fromColor: function(color){
		return this.custom(color, this.element.getStyle(this.property));
	},
	
	/*	
	Property: toColor
		Transitions to the color passed in from the current color of the element.
		
	Arguments:
		color - the color to transition *to* from the current color of the element.
		
	Example:
		>myColorFx.toColor('F00') //transition from whatever color the element is currently to red
	*/
	
	toColor: function(color){
		return this.custom(this.element.getStyle(this.property), color);
	}

});
/*	
Script: Fxutils.js
		Contains Fx.Height, Fx.Width, Fx.Opacity. Only useful if you really, really need to toggle those values, and toggling only works in STRICT DOCTYPE.
		See <Fx.Style> for a better alternative.

Dependencies:
	<Moo.js>, <Function.js>, <Array.js>, <String.js>, <Element.js>, <Fx.js>
	
Author:
	Valerio Proietti, <http://mad4milk.net>

License:
	MIT-style license.
*/

/*
Class: Fx.Height
	Alters the height of an element. Extends <Fx.Style> (and consequentially <Fx.Base>), and inherits all its methods.

Arguments:
	el - the $(element) to apply the style transition to
	options - the Fx.Base options (see: <Fx.Base>)
	
Example:
	>var myEffect = new Fx.Height('myElementId', {duration: 500});
	>myEffect.toggle(); //will close the element if open, and vice-versa.
*/

Fx.Height = Fx.Style.extend({

	initialize: function(el, options){
		this.parent(el, 'height', options);
		this.element.setStyle('overflow', 'hidden');
	},
	
	/*
	Property: toggle
		Toggles the height of an element from zero to it's scrollHeight, and vice-versa.
	*/

	toggle: function(){
		if (this.element.offsetHeight > 0) return this.custom(this.element.offsetHeight, 0);
		else return this.custom(0, this.element.scrollHeight);
	},

	/*
	Property: show
		Size the element to its full scrollHeight immediatly, without applying a transition.
	*/

	show: function(){
		return this.set(this.element.scrollHeight);
	}

});

/*
Class: Fx.Width
	Same as Fx.Height, but uses Width. It always toggles from its initial width to zero, and vice versa.
*/

Fx.Width = Fx.Style.extend({

	initialize: function(el, options){
		this.parent(el, 'width', options);
		this.element.setStyle('overflow', 'hidden');
		this.iniWidth = this.element.offsetWidth;
	},

	toggle: function(){
		if (this.element.offsetWidth > 0) return this.custom(this.element.offsetWidth, 0);
		else return this.custom(0, this.iniWidth);
	},

	show: function(){
		return this.set(this.iniWidth);
	}

});

/*
Class: Fx.Opacity
	Same as Fx.Height, but uses Opacity. It always toggles from opaque to transparent, and vice versa.
*/

Fx.Opacity = Fx.Style.extend({

	initialize: function(el, options){
		this.parent(el, 'opacity', options);
		this.now = 1;
	},

	toggle: function(){
		if (this.now > 0) return this.custom(1, 0);
		else return this.custom(0, 1);
	},

	show: function(){
		return this.set(1);
	}

});
/*
Script: Fxtransitions.js
	Cool transitions,  to be used with <Fx.Js>
	
Dependencies:
	<Fx.js>

Author:
	Robert Penner, <http://www.robertpenner.com/easing/>, modified to be used with mootools.
		
License:
	Easing Equations v1.5, (c) 2003 Robert Penner, all rights reserved. Open Source BSD License.
*/

/*
Class: Fx.Transitions
	A collection of tweaning transitions for use with the <Fx.Base> classes.
*/

Fx.Transitions = {

	/* Property: linear */
	linear: function(t, b, c, d){
		return c*t/d + b;
	},
	
	/* Property: quadIn */
	quadIn: function(t, b, c, d){
		return c*(t/=d)*t + b;
	},

	/* Property: quatOut */
	quadOut: function(t, b, c, d){
		return -c *(t/=d)*(t-2) + b;
	},

	/* Property: quadInOut */
	quadInOut: function(t, b, c, d){
		if ((t/=d/2) < 1) return c/2*t*t + b;
		return -c/2 * ((--t)*(t-2) - 1) + b;
	},

	/* Property: cubicIn */
	cubicIn: function(t, b, c, d){
		return c*(t/=d)*t*t + b;
	},

	/* Property: cubicOut */
	cubicOut: function(t, b, c, d){
		return c*((t=t/d-1)*t*t + 1) + b;
	},

	/* Property: cubicInOut */
	cubicInOut: function(t, b, c, d){
		if ((t/=d/2) < 1) return c/2*t*t*t + b;
		return c/2*((t-=2)*t*t + 2) + b;
	},

	/* Property: quartIn */
	quartIn: function(t, b, c, d){
		return c*(t/=d)*t*t*t + b;
	},

	/* Property: quartOut */
	quartOut: function(t, b, c, d){
		return -c * ((t=t/d-1)*t*t*t - 1) + b;
	},

	/* Property: quartInOut */
	quartInOut: function(t, b, c, d){
		if ((t/=d/2) < 1) return c/2*t*t*t*t + b;
		return -c/2 * ((t-=2)*t*t*t - 2) + b;
	},

	/* Property: quintIn */
	quintIn: function(t, b, c, d){
		return c*(t/=d)*t*t*t*t + b;
	},

	/* Property: quintOut */
	quintOut: function(t, b, c, d){
		return c*((t=t/d-1)*t*t*t*t + 1) + b;
	},

	/* Property: quintInOut */
	quintInOut: function(t, b, c, d){
		if ((t/=d/2) < 1) return c/2*t*t*t*t*t + b;
		return c/2*((t-=2)*t*t*t*t + 2) + b;
	},

	/* Property: sineIn */
	sineIn: function(t, b, c, d){
		return -c * Math.cos(t/d * (Math.PI/2)) + c + b;
	},

	/* Property: sineOut */
	sineOut: function(t, b, c, d){
		return c * Math.sin(t/d * (Math.PI/2)) + b;
	},

	/* Property: sineInOut */
	sineInOut: function(t, b, c, d){
		return -c/2 * (Math.cos(Math.PI*t/d) - 1) + b;
	},

	/* Property: expoIn */
	expoIn: function(t, b, c, d){
		return (t==0) ? b : c * Math.pow(2, 10 * (t/d - 1)) + b;
	},

	/* Property: expoOut */
	expoOut: function(t, b, c, d){
		return (t==d) ? b+c : c * (-Math.pow(2, -10 * t/d) + 1) + b;
	},

	/* Property: expoInOut */
	expoInOut: function(t, b, c, d){
		if (t==0) return b;
		if (t==d) return b+c;
		if ((t/=d/2) < 1) return c/2 * Math.pow(2, 10 * (t - 1)) + b;
		return c/2 * (-Math.pow(2, -10 * --t) + 2) + b;
	},

	/* Property: circIn */
	circIn: function(t, b, c, d){
		return -c * (Math.sqrt(1 - (t/=d)*t) - 1) + b;
	},

	/* Property: circOut */
	circOut: function(t, b, c, d){
		return c * Math.sqrt(1 - (t=t/d-1)*t) + b;
	},

	/* Property: circInOut */
	circInOut: function(t, b, c, d){
		if ((t/=d/2) < 1) return -c/2 * (Math.sqrt(1 - t*t) - 1) + b;
		return c/2 * (Math.sqrt(1 - (t-=2)*t) + 1) + b;
	},

	/* Property: elasticIn */
	elasticIn: function(t, b, c, d, a, p){
		if (t==0) return b;  if ((t/=d)==1) return b+c;  if (!p) p=d*.3; if (!a) a = 1;
		if (a < Math.abs(c)){ a=c; var s=p/4; }
		else var s = p/(2*Math.PI) * Math.asin(c/a);
		return -(a*Math.pow(2,10*(t-=1)) * Math.sin( (t*d-s)*(2*Math.PI)/p )) + b;
	},

	/* Property: elasticOut */
	elasticOut: function(t, b, c, d, a, p){
		if (t==0) return b;  if ((t/=d)==1) return b+c;  if (!p) p=d*.3; if (!a) a = 1;
		if (a < Math.abs(c)){ a=c; var s=p/4; }
		else var s = p/(2*Math.PI) * Math.asin(c/a);
		return a*Math.pow(2,-10*t) * Math.sin( (t*d-s)*(2*Math.PI)/p ) + c + b;
	},

	/* Property: elasticInOut */
	elasticInOut: function(t, b, c, d, a, p){
		if (t==0) return b;  if ((t/=d/2)==2) return b+c;  if (!p) p=d*(.3*1.5); if (!a) a = 1;
		if (a < Math.abs(c)){ a=c; var s=p/4; }
		else var s = p/(2*Math.PI) * Math.asin(c/a);
		if (t < 1) return -.5*(a*Math.pow(2,10*(t-=1)) * Math.sin( (t*d-s)*(2*Math.PI)/p )) + b;
		return a*Math.pow(2,-10*(t-=1)) * Math.sin( (t*d-s)*(2*Math.PI)/p )*.5 + c + b;
	},

	/* Property: backIn */
	backIn: function(t, b, c, d, s){
		if (!s) s = 1.70158;
		return c*(t/=d)*t*((s+1)*t - s) + b;
	},

	/* Property: backOut */
	backOut: function(t, b, c, d, s){
		if (!s) s = 1.70158;
		return c*((t=t/d-1)*t*((s+1)*t + s) + 1) + b;
	},

	/* Property: backInOut */
	backInOut: function(t, b, c, d, s){
		if (!s) s = 1.70158;
		if ((t/=d/2) < 1) return c/2*(t*t*(((s*=(1.525))+1)*t - s)) + b;
		return c/2*((t-=2)*t*(((s*=(1.525))+1)*t + s) + 2) + b;
	},

	/* Property: bounceIn */
	bounceIn: function(t, b, c, d){
		return c - Fx.Transitions.bounceOut (d-t, 0, c, d) + b;
	},

	/* Property: bounceOut */
	bounceOut: function(t, b, c, d){
		if ((t/=d) < (1/2.75)){
			return c*(7.5625*t*t) + b;
		} else if (t < (2/2.75)){
			return c*(7.5625*(t-=(1.5/2.75))*t + .75) + b;
		} else if (t < (2.5/2.75)){
			return c*(7.5625*(t-=(2.25/2.75))*t + .9375) + b;
		} else {
			return c*(7.5625*(t-=(2.625/2.75))*t + .984375) + b;
		}
	},

	/* Property: bounceInOut */
	bounceInOut: function(t, b, c, d){
		if (t < d/2) return Fx.Transitions.bounceIn(t*2, 0, c, d) * .5 + b;
		return Fx.Transitions.bounceOut(t*2-d, 0, c, d) * .5 + c*.5 + b;
	}	

};
/*	
Script: Tips.js
	Tooltips, BubbleTips, whatever they are, they will appear on mouseover
	
Dependencies:
	<Moo.js>, <Function.js>, <Array.js>, <String.js>, <Element.js>, <Fx.js>
	
Author:
	Valerio Proietti, <http://mad4milk.net>

License:
	MIT-style license.
	
Credits:
	Tips.js is based on Bubble Tooltips (<http://web-graphics.com/mtarchive/001717.php>) by Alessandro Fulcitiniti <http://web-graphics.com>
*/

/*
Class: Tips
	Display a tip on any element with a title and/or href.

Arguments: 
	elements - a collection of elements to apply the tooltips to on mouseover.
	options - an object. See options Below.

Options: 
	transitionStart - the transition effect used to show the tip (see <Fx.Transitions>).
	transitionEnd - the transition effect used to hide the tip (see <Fx.Transitions>).
	maxTitleChars - the maximum number of characters to display in the title of the tip. defaults to 30.
	fxDuration - the duration (in ms) for the transition effect when the tip to appears and disappears. defaults to 150.
	maxOpacity - how opaque to make the tooltip (0 = 0% opaque, 1= 100% opaque). defaults to 1.
	timeOut - the delay to wait to show the tip (how long the user must hover to have the tooltip appear). defaults to 100.
	className - the class name to apply to the tooltip

Example:
	(start code)
	<img src="/images/i.png" title="The body of the tooltip is stored in the title" tooltitle="The Title of the Tooltip" class="toolTipImg"/>
	<script>
		var myTips = new Tips($S('.toolTipImg'), {
			maxTitleChars: 50, //I like my captions a little long
			maxOpacity: .9, //let's leave a little transparancy in there
		});
	</script>
	(end)
*/

var Tips = new Class({

	setOptions: function(options){
		this.options = {
			transitionStart: Fx.Transitions.sineInOut,
			transitionEnd: Fx.Transitions.sineInOut,
			maxTitleChars: 30,
			fxDuration: 150,
			maxOpacity: 1,
			timeOut: 100,
			className: 'tooltip'
		}
		Object.extend(this.options, options || {});
	},

	initialize: function(elements, options){
		this.elements = elements;
		this.setOptions(options);
		this.toolTip = new Element('div').addClassName(this.options.className).setStyle('position', 'absolute').injectInside(document.body);
		this.toolTitle = new Element('H4').injectInside(this.toolTip);
		this.toolText = new Element('p').injectInside(this.toolTip);
		this.fx = new fx.Style(this.toolTip, 'opacity', {duration: this.options.fxDuration, wait: false}).hide();
		$A(elements).each(function(el){
			$(el).myText = el.title || false;
			if (el.myText) el.removeAttribute('title');
			if (el.href){
				if (el.href.test('http://')) el.myTitle = el.href.replace('http://', '');
				if (el.href.length > this.options.maxTitleChars) el.myTitle = el.href.substr(0,this.options.maxTitleChars-3)+"...";
			}
			if (el.myText && el.myText.test('::')){
				var dual = el.myText.split('::');
				el.myTitle = dual[0].trim();
				el.myText = dual[1].trim();
			} 
			el.onmouseover = function(){
				this.show(el);
				return false;
			}.bind(this);
			el.onmousemove = this.locate.bindAsEventListener(this);
			el.onmouseout = function(){
				this.timer = $clear(this.timer);
				this.disappear();
			}.bind(this);
		}, this);
	},

	show: function(el){
		this.toolTitle.innerHTML = el.myTitle;
		this.toolText.innerHTML = el.myText;
		this.timer = $clear(this.timer);
		this.fx.options.transition = this.options.transitionStart;
		this.timer = this.appear.delay(this.options.timeOut, this);
	},

	appear: function(){
		this.fx.custom(this.fx.now, this.options.maxOpacity);
	},

	locate: function(evt){
		var doc = document.documentElement;
		this.toolTip.setStyles({'top': evt.clientY + doc.scrollTop + 15 + 'px', 'left': evt.clientX + doc.scrollLeft - 30 + 'px'});
	},

	disappear: function(){
		this.fx.options.transition = this.options.transitionEnd;
		this.fx.custom(this.fx.now, 0);
	}

});
/*	
Script: Accordion.js
	Fx.Elements and Accordion.
		
Dependencies:
	<Moo.js>, <Function.js>, <Array.js>, <String.js>, <Element.js>, <Fx.js>

Author:
	Valerio Proietti, <http://mad4milk.net>

License:
	MIT-style license.
*/

/*
Class: Fx.Elements
	Fx.Elements allows you to apply any number of styles trantisions to a selection of elements. Wonky syntax but very powerful. Used internally by the accordion.

Arguments:
	elements - a collection of elements the effects will be applied to.
	options - same as <Fx.Base> options.
*/

Fx.Elements = Fx.Base.extend({

	initialize: function(elements, options){
		this.elements = [];
		elements.each(function(el){
			this.elements.push($(el));
		}, this);
		this.setOptions(options);
		this.now = {};
	},

	setNow: function(){
		for (var i in this.from){
			var iFrom = this.from[i];
			var iTo = this.to[i];
			var iNow = this.now[i] = {};
			for (var p in iFrom) iNow[p] = this.compute(iFrom[p], iTo[p]);
		}
	},

	/*
	Property: custom
		Applies the passed in style transitions to each object named (see example). Each item in the collection is refered to as a numerical string ("1" for instance). The first item is "0", the second "1", etc.
		
	Example:
		(start code)
		var myElementsEffects = new Fx.Elements($$('a'));
		myElementsEffects.custom({
			'0': { //let's change the first element's opacity and width
				'opacity': [0,1],
				'width': [100,200]
			},
			'1': { //and the second one's opacity
				'opacity': [0.2, 0.5]
			}
		});
		(end)
	*/

	custom: function(objObjs){
		if (this.timer && this.options.wait) return;
		var from = {};
		var to = {};
		for (var i in objObjs){
			var iProps = objObjs[i];
			var iFrom = from[i] = {};
			var iTo = to[i] = {};
			for (var prop in iProps){
				iFrom[prop] = iProps[prop][0];
				iTo[prop] = iProps[prop][1];
			}
		}
		return this.parent(from, to);
	},

	increase: function(){
		for (var i in this.now){
			var iNow = this.now[i];
			for (var p in iNow) this.setStyle(this.elements[i.toInt()], p, iNow[p]);
		}
	}

});

/*
Class: Fx.Accordion
	The Fx.Accordion function creates a group of elements that are toggled when their handles are clicked. When one elements toggles in, the others toggles back.

Arguments:
	elements - required, a collection of elements the transitions will be applied to.
	togglers - required, a collection of elements, the elements handlers.
	options - optional, see options below, and <Fx.Base> options.

Options:
	start - either 'open-first' or 'first-open'. 'open-first' will slide that element open, while 'first-open' will just show that element as open immediately with no transition.
	fixedHeight - integer, if you want your accordion to have a fixed height. defaults to false.
	fixedWidth - integer, if you want your accordion to have a fixed width. defaults to false.
	alwaysHide - boolean, if you want the ability to close your only-open item. defaults to false.
	wait - boolean. means that open and close transitions can cancel current ones (so if you click
	 on items before the previous finishes transitioning, the clicked transition will fire canceling the previous). true means that if one element is sliding open or closed, clicking on another will have no effect. for Accordion defaults to false.
	onActive - function to execute when an element starts to show
	onBackground - function to execute when an element starts to hide
	height - boolean, will add a height transition to the accordion if true. defaults to true.
	opacity - boolean, will add an opacity transition to the accordion if true. defaults to true.
	width - boolean, will add a width transition to the accordion if true. defaults to false, css mastery is required to make this work!
*/

Fx.Accordion = Fx.Elements.extend({

	extendOptions: function(options){
		Object.extend(this.options, Object.extend({
			start: 'open-first',
			fixedHeight: false,
			fixedWidth: false,
			alwaysHide: false,
			wait: false,
			onActive: Class.empty,
			onBackground: Class.empty,
			height: true,
			opacity: true,
			width: false
		}, options || {}));
	},

	initialize: function(togglers, elements, options){
		this.parent(elements, options);
		this.extendOptions(options);
		this.previousClick = 'nan';
		togglers.each(function(tog, i){
			$(tog).addEvent('click', function(){this.showThisHideOpen(i)}.bind(this));
		}, this);
		this.togglers = togglers;
		this.h = {}; this.w = {}; this.o = {};
		this.elements.each(function(el, i){
			this.now[i] = {};
			$(el).setStyles({'height': 0, 'overflow': 'hidden'});
		}, this);
		switch(this.options.start){
			case 'first-open': this.elements[0].setStyle('height', this.elements[0].scrollHeight+this.options.unit); break;
			case 'open-first': this.showThisHideOpen(0); break;
		}
	},

	hideThis: function(i){
		if (this.options.height) this.h = {'height': [this.elements[i].offsetHeight, 0]};
		if (this.options.width) this.w = {'width': [this.elements[i].offsetWidth, 0]};
		if (this.options.opacity) this.o = {'opacity': [this.now[i]['opacity'] || 1, 0]};
	},

	showThis: function(i){
		if (this.options.height) this.h = {'height': [this.elements[i].offsetHeight, this.options.fixedHeight || this.elements[i].scrollHeight]};
		if (this.options.width) this.w = {'width': [this.elements[i].offsetWidth, this.options.fixedWidth || this.elements[i].scrollWidth]};
		if (this.options.opacity) this.o = {'opacity': [this.now[i]['opacity'] || 0, 1]};
	},

	/*
	Property: showThisHideOpen
		Shows a specific item and hides all others. Useful when triggering an accordion from outside.
		
	Arguments:
		iToShow - the index of the item to show.
	*/

	showThisHideOpen: function(iToShow){
		if (iToShow != this.previousClick || this.options.alwaysHide){
			this.previousClick = iToShow;
			var objObjs = {};
			var err = false;
			var madeInactive = false;
			this.elements.each(function(el, i){
				this.now[i] = this.now[i] || {};
				if (i != iToShow){
					this.hideThis(i);
				} else if (this.options.alwaysHide){
					if (el.offsetHeight == el.scrollHeight){
						this.hideThis(i);
						madeInactive = true;
					} else if (el.offsetHeight == 0){
						this.showThis(i);
					} else {
						err = true;
					}
				} else if (this.options.wait && this.timer){
					this.previousClick = 'nan';
					err = true;
				} else {
					this.showThis(i);
				}
				objObjs[i] = Object.extend(this.h, Object.extend(this.o, this.w));
			}, this);
			if (err) return;
			if (!madeInactive) this.options.onActive.call(this, this.togglers[iToShow], iToShow);
			this.togglers.each(function(tog, i){
				if (i != iToShow || madeInactive) this.options.onBackground.call(this, tog, i);
			}, this);
			return this.custom(objObjs);
		}
	}

});