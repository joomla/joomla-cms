/**
 * @package     Joomla.Installation
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// Init on dom content loaded event
var url = Joomla.getOptions('system.installation').url ? Joomla.getOptions('system.installation').url.replace(/&amp;/g, '&') : 'index.php';

if (document.getElementById('installAddFeatures')) {
	document.getElementById('installAddFeatures').addEventListener('click', function(e) {
		e.preventDefault();
		document.getElementById('installLanguages').classList.add('active');
		document.getElementById('installCongrat').classList.remove('active');
		document.getElementById('installRecommended').classList.remove('active');
	})
}

if (document.getElementById('skipLanguages')) {
	document.getElementById('skipLanguages').addEventListener('click', function(e) {
		e.preventDefault();
		document.getElementById('installSampleData').classList.add('active');
		document.getElementById('installLanguages').classList.remove('active');
	})
}

if (document.getElementById('installSampleData')) {
	document.getElementById('installSampleData').addEventListener('click', function(e) {
		e.preventDefault();
		document.getElementById('installSampleData').classList.add('active');
		document.getElementById('installLanguages').classList.remove('active');
	})
}

if (document.getElementById('skipSampleData')) {
	document.getElementById('skipSampleData').addEventListener('click', function(e) {
		e.preventDefault();
		document.getElementById('installSampleData').classList.toggle('active');
		document.getElementById('installSampleData').style.display = 'none';
		document.getElementById('installFinal').classList.add('active');
	})
}


if (document.getElementById('installLanguagesButton')) {
	document.getElementById('installLanguagesButton').addEventListener('click', function(e) {
		e.preventDefault();
		var form = document.getElementById('languagesForm');
		if (form) {
			// Install the extra languages
			Joomla.install(['languages'], form);

			document.getElementById('installLanguages').classList.remove('active');
			document.getElementById('installSampleData').classList.add('active');
		}
	})
}

if (document.getElementById('installSampleDataButton')) {
	document.getElementById('installSampleDataButton').addEventListener('click', function(e) {
		e.preventDefault();
		var form = document.getElementById('sampleDataForm');
		if (form) {
			// Install the extra languages
			Joomla.install(['sample'], form);

			document.getElementById('installSampleData').classList.toggle('active');
			document.getElementById('installSampleData').style.display = 'none';
			document.getElementById('installFinal').classList.add('active');
		}
	})
}