const fs = require('fs');
const ini = require('ini');
const Recurs = require("recursive-readdir");

const rootPath = __dirname.replace('/build/build-modules-js', '').replace('\\build\\build-modules-js', '');
const dir = rootPath + '/installation/language';
const dest = rootPath + '/templates/system/js';

// Set the initial template
let template = `/**
 * @package     Joomla.Installation
 * @subpackage  JavaScript
 * @copyright   Copyright (C) 2005 - ${(new Date()).getFullYear()} Open Source Matters. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * This file is auto generated. Please do not modify it directly, use \`node build --installer\`
 */
window.errorLocale = {`;


installation = () => {
	Recurs(dir).then(
		(files) => {
			files.forEach((file) => {
				const languageStrings = ini.parse(fs.readFileSync(file, 'UTF-8'));
				if (languageStrings["MIN_PHP_ERROR_LANGUAGE"]) {
					const name = file.replace('.ini', '').replace(/.+\//, '');
					template += `
  "${name}": {
    "language": "` + languageStrings["MIN_PHP_ERROR_LANGUAGE"] + `",
    "header": "` + languageStrings["MIN_PHP_ERROR_HEADER"] + `",
    "text1": "` + languageStrings["MIN_PHP_ERROR_TEXT"] + `",
    "help-url-text": "` + languageStrings["MIN_PHP_ERROR_URL_TEXT"] + `"
  },`;
				}
			});

			template = template + `
}`;

			if (!fs.existsSync(dest)) {
				fs.mkdirSync(dest);
			}

			// Write the file
			fs.writeFile(`${dest}/error-locales.js`, template, (err) => {
				if (err) {
					return console.log(err);
				}

				console.log("The installation javascript error file was saved!");
			});
		},
		(error) => {
			console.error("something exploded", error);
		}
	);

};

module.exports.installation = installation;
