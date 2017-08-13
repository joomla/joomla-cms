/*global window, localStorage, fontSizeTitle, bigger, reset, smaller, biggerTitle, resetTitle, smallerTitle, Cookie */
var prefsLoaded = false;
var defaultFontSize = 100;
var currentFontSize = defaultFontSize;



Object.append(Browser.Features, {
	localstorage: (function() {
		return ('localStorage' in window) && window.localStorage !== null;
	})()
});

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
	if (Browser.Features.localstorage) {
		localStorage.fontSize = value;
	} else {
		Cookie.write("fontSize", value, {duration: 180});
	}
}

function readFontSize() {
	if (Browser.Features.localstorage) {
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
	var container = document.id('fontsize');
	var content = '<h3>'+ fontSizeTitle +'</h3><p><a title="'+ biggerTitle +'"  href="#" onclick="changeFontSize(2); return false">'+ bigger +'</a><span class="unseen">.</span><a href="#" title="'+resetTitle+'" onclick="revertStyles(); return false">'+ reset +'</a><span class="unseen">.</span><a href="#"  title="'+ smallerTitle +'" onclick="changeFontSize(-2); return false">'+ smaller +'</a></p>';
	container.set('html', content);
}

function saveSettings() {
	writeFontSize(currentFontSize);
}


window.addEvent('domready', function () {

    smaller = Joomla.JText._('TPL_BEEZ3_SMALLER');
    fontSizeTitle = Joomla.JText._('TPL_BEEZ3_FONTSIZE');
    bigger = Joomla.JText._('TPL_BEEZ3_BIGGER');
    reset = Joomla.JText._('TPL_BEEZ3_RESET');
    biggerTitle = Joomla.JText._('TPL_BEEZ3_INCREASE_SIZE');
    smallerTitle = Joomla.JText._('TPL_BEEZ3_DECREASE_SIZE');
    resetTitle = Joomla.JText._('TPL_BEEZ3_REVERT_STYLES_TO_DEFAULT');

});
window.addEvent('domready', setUserOptions);
window.addEvent('domready', addControls);
window.addEvent('unload', saveSettings);
