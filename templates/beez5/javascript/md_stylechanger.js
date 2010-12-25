/*global window, localStorage, fontSizeTitle, bigger, reset, smaller, biggerTitle, resetTitle, smallerTitle, Cookie */
var prefsLoaded = false;
var defaultFontSize = 100;
var currentFontSize = defaultFontSize;

function supportsLocalStorage() {
	return ('localStorage' in window) && window.localStorage !== null;
}

function setFontSize(fontSize) {
	document.body.style.fontSize = fontSize + '%';
}

function changeFontSize(sizeDifference) {
	currentFontSize = parseInt(currentFontSize, 10) + parseInt(sizeDifference * 5, 10);
	if (currentFontSize > 180) {
		currentFontSize = 180;
	} else if (currentFontSize < 60) {
		currentFontSize = 60;
	}
	setFontSize(currentFontSize);
}

function revertStyles() {
	currentFontSize = defaultFontSize;
	changeFontSize(0);
}

function writeFontSize(value) {
	if (supportsLocalStorage()) {
		localStorage.fontSize = value;
	} else {
		Cookie.write("fontSize", value, {duration: 180});
	}
}

function readFontSize() {
	if (supportsLocalStorage()) {
		return localStorage.fontSize;
	} else {
		return Cookie.read("fontSize");
	}
}

function setUserOptions() {
	if (!prefsLoaded) {
		var size = readFontSize();
		currentFontSize = size ? size : defaultFontSize;
		setFontSize(currentFontSize);
		prefsLoaded = true;
	}
}

function addControls() {
	var xhtml = "http://www.w3.org/1999/xhtml";
	var container = document.getElementById('fontsize');

	var link = [];
	var linkClass = ["larger", "reset", "smaller"];
	var linkTitle = [biggerTitle, resetTitle, smallerTitle];
	var linkAction = ["changeFontSize(2); return false;", "revertStyles(); return false;", "changeFontSize(-2); return false;"];
	var linkContent = [document.createTextNode(bigger), document.createTextNode(reset), document.createTextNode(smaller)];

	var headingText = document.createTextNode(fontSizeTitle);

	var heading;
	var p;
	var span;
	if (document.createElementNS) {
		heading = document.createElementNS(xhtml, "h3");
		p = document.createElementNS(xhtml, "p");
		link[3] = document.createElementNS(xhtml, "a");
		span = document.createElementNS(xhtml, "span");
	} else {
		heading = document.createElement("h3");
		p = document.createElement("p");
		link[3] = document.createElement("a");
		span = document.createElement("span");
	}

	p.setAttribute("class", "fontsize");
	span.setAttribute("class", "unseen");
	link[3].setAttribute("href", "index.php");

	for (var x = 0; x < 3; x++) {
		link[x] = link[3].cloneNode(true);
		link[x].setAttribute("class", linkClass[x]);
		link[x].setAttribute("title", linkTitle[x]);
		link[x].setAttribute("onclick", linkAction[x]);
		link[x].appendChild(linkContent[x]);
	}

	span.appendChild(document.createTextNode('\u00a0')); //This is a nbsp
	heading.appendChild(headingText);

	p.appendChild(link[0]);
	p.appendChild(span.cloneNode(true));
	p.appendChild(link[1]);
	p.appendChild(link[2]);
	p.appendChild(span);

	container.appendChild(heading);
	container.appendChild(p);
}

function saveSettings() {
	writeFontSize(currentFontSize);
}

window.addEvent('domready', setUserOptions);
window.addEvent('domready', addControls);
window.addEvent('unload', saveSettings);