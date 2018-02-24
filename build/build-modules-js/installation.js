const fs = require('fs');
const ini = require('ini');
const xml2js = require('xml2js')
const Recurs = require("recursive-readdir");

const rootPath = __dirname.replace('/build/build-modules-js', '').replace('\\build\\build-modules-js', '');
const dir = rootPath + '/installation/language';
const destError = rootPath + '/templates/system/js';
const destCalendar = rootPath + '/media/system/js/fields/tmp';

// Set the initial template
const header = `/**
 * @package     Joomla.Installation
 * @subpackage  JavaScript
 * @copyright   Copyright (C) 2005 - ${(new Date()).getFullYear()} Open Source Matters. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * This file is auto generated. Please do not modify it directly, use \`node build --installer\`
 */`;
let templateError = `${header}
window.errorLocale = {`;

installation = () => {
	Recurs(dir).then(
		(files) => {

			files.forEach((file) => {
				if (!/.ini/.test(file)) {
					return;
				}

				const languageStrings = ini.parse(fs.readFileSync(file, 'UTF-8'));
				const parser = new xml2js.Parser();
				const xmlFileContent = fs.readFileSync(file.replace('.ini', '.xml'), 'UTF-8');
				let xmlContent;
				let weekEnd;
				let calendar;

				parser.parseString(xmlFileContent, function (err, result) {
					xmlContent = result;
					xmlContent = xmlContent.metafile.metadata[0];
					weekEnd = xmlContent.weekEnd[0].split(',');
					weekEnd = weekEnd.map(Number);
					calendar = xmlContent.calendar[0];
				});

				// Installation Error page script
				if (languageStrings && languageStrings["MIN_PHP_ERROR_LANGUAGE"] &&
					languageStrings["MIN_PHP_ERROR_HEADER"] &&
					languageStrings["MIN_PHP_ERROR_TEXT"] &&
					languageStrings["MIN_PHP_ERROR_URL_TEXT"]) {
					const name = file.replace('.ini', '').replace(/.+\//, '');
					templateError += `
  "${name}": {
    "language": "${languageStrings["MIN_PHP_ERROR_LANGUAGE"]}",
    "header": "${languageStrings["MIN_PHP_ERROR_HEADER"]}",
    "text1": "${languageStrings["MIN_PHP_ERROR_TEXT"]}",
    "help-url-text": "${languageStrings["MIN_PHP_ERROR_URL_TEXT"]}"
  },`;
				} else {
					throw new Error(`${file} is missing some translation strings for the PHP error page`)
				}


				// Calendar locale script
				if (languageStrings["CALENDAR_TODAY"] &&
					languageStrings["CALENDAR_WEEK"] &&
					languageStrings["CALENDAR_TIME"] &&
					languageStrings["CALENDAR_DAY_FULL_SUNDAY"] &&
					languageStrings["CALENDAR_DAY_FULL_MONDAY"] &&
					languageStrings["CALENDAR_DAY_FULL_TUESDAY"] &&
					languageStrings["CALENDAR_DAY_FULL_WEDNESDAY"] &&
					languageStrings["CALENDAR_DAY_FULL_THURSDAY"] &&
					languageStrings["CALENDAR_DAY_FULL_FRIDAY"] &&
					languageStrings["CALENDAR_DAY_FULL_SATURDAY"] &&
					languageStrings["CALENDAR_DAY_SHORT_SUNDAY"] &&
					languageStrings["CALENDAR_DAY_SHORT_MONDAY"] &&
					languageStrings["CALENDAR_DAY_SHORT_TUESDAY"] &&
					languageStrings["CALENDAR_DAY_SHORT_WEDNESDAY"] &&
					languageStrings["CALENDAR_DAY_SHORT_THURSDAY"] &&
					languageStrings["CALENDAR_DAY_SHORT_FRIDAY"] &&
					languageStrings["CALENDAR_DAY_SHORT_SATURDAY"] &&
					languageStrings["CALENDAR_MONTH_FULL_JANUARY"] &&
					languageStrings["CALENDAR_MONTH_FULL_FEBRUARY"] &&
					languageStrings["CALENDAR_MONTH_FULL_MARCH"] &&
					languageStrings["CALENDAR_MONTH_FULL_APRIL"] &&
					languageStrings["CALENDAR_MONTH_FULL_MAY"] &&
					languageStrings["CALENDAR_MONTH_FULL_JUNE"] &&
					languageStrings["CALENDAR_MONTH_FULL_JULY"] &&
					languageStrings["CALENDAR_MONTH_FULL_AUGUST"] &&
					languageStrings["CALENDAR_MONTH_FULL_SEPTEMBER"] &&
					languageStrings["CALENDAR_MONTH_FULL_OCTOBER"] &&
					languageStrings["CALENDAR_MONTH_FULL_NOVEMBER"] &&
					languageStrings["CALENDAR_MONTH_FULL_DECEMBER"] &&
					languageStrings["CALENDAR_MONTH_SHORT_JANUARY"] &&
					languageStrings["CALENDAR_MONTH_SHORT_FEBRUARY"] &&
					languageStrings["CALENDAR_MONTH_SHORT_MARCH"] &&
					languageStrings["CALENDAR_MONTH_SHORT_APRIL"] &&
					languageStrings["CALENDAR_MONTH_SHORT_MAY"] &&
					languageStrings["CALENDAR_MONTH_SHORT_JUNE"] &&
					languageStrings["CALENDAR_MONTH_SHORT_JULY"] &&
					languageStrings["CALENDAR_MONTH_SHORT_AUGUST"] &&
					languageStrings["CALENDAR_MONTH_SHORT_SEPTEMBER"] &&
					languageStrings["CALENDAR_MONTH_SHORT_OCTOBER"] &&
					languageStrings["CALENDAR_MONTH_SHORT_NOVEMBER"] &&
					languageStrings["CALENDAR_MONTH_SHORT_DECEMBER"] &&
					languageStrings["CALENDAR_AM_UPPER"] &&
					languageStrings["CALENDAR_PM_UPPER"] &&
					languageStrings["CALENDAR_AM_LOWER"] &&
					languageStrings["CALENDAR_PM_LOWER"] &&
					languageStrings["CALENDAR_MIN_YEAR"] &&
					languageStrings["CALENDAR_MAX_YEAR"] &&
					languageStrings["CALENDAR_CLOSE"] &&
					languageStrings["CALENDAR_SAVE"]) {

					const templateCalendar = `${header}
window.JoomlaCalLocale = {
  today: "${languageStrings["CALENDAR_TODAY"]}",
  weekend: [${weekEnd}],
  wk: "${languageStrings["CALENDAR_WEEK"]}",
  time: "${languageStrings["CALENDAR_TIME"]}:",
  days: ["${languageStrings["CALENDAR_DAY_FULL_SUNDAY"]}", "${languageStrings["CALENDAR_DAY_FULL_MONDAY"]}", "${languageStrings["CALENDAR_DAY_FULL_TUESDAY"]}", "${languageStrings["CALENDAR_DAY_FULL_WEDNESDAY"]}", "${languageStrings["CALENDAR_DAY_FULL_THURSDAY"]}", "${languageStrings["CALENDAR_DAY_FULL_FRIDAY"]}", "${languageStrings["CALENDAR_DAY_FULL_SATURDAY"]}"],
  shortDays: ["${languageStrings["CALENDAR_DAY_SHORT_SUNDAY"]}", "${languageStrings["CALENDAR_DAY_SHORT_MONDAY"]}", "${languageStrings["CALENDAR_DAY_SHORT_TUESDAY"]}", "${languageStrings["CALENDAR_DAY_SHORT_WEDNESDAY"]}", "${languageStrings["CALENDAR_DAY_SHORT_THURSDAY"]}", "${languageStrings["CALENDAR_DAY_SHORT_FRIDAY"]}", "${languageStrings["CALENDAR_DAY_SHORT_SATURDAY"]}"],
  months: ["${languageStrings["CALENDAR_MONTH_FULL_JANUARY"]}", "${languageStrings["CALENDAR_MONTH_FULL_FEBRUARY"]}", "${languageStrings["CALENDAR_MONTH_FULL_MARCH"]}", "${languageStrings["CALENDAR_MONTH_FULL_APRIL"]}", "${languageStrings["CALENDAR_MONTH_FULL_MAY"]}", "${languageStrings["CALENDAR_MONTH_FULL_JUNE"]}", "${languageStrings["CALENDAR_MONTH_FULL_JULY"]}", "${languageStrings["CALENDAR_MONTH_FULL_AUGUST"]}", "${languageStrings["CALENDAR_MONTH_FULL_SEPTEMBER"]}", "${languageStrings["CALENDAR_MONTH_FULL_OCTOBER"]}", "${languageStrings["CALENDAR_MONTH_FULL_NOVEMBER"]}", "${languageStrings["CALENDAR_MONTH_FULL_DECEMBER"]}"],
  shortMonths: ["${languageStrings["CALENDAR_MONTH_SHORT_JANUARY"]}", "${languageStrings["CALENDAR_MONTH_SHORT_FEBRUARY"]}", "${languageStrings["CALENDAR_MONTH_SHORT_MARCH"]}", "${languageStrings["CALENDAR_MONTH_SHORT_APRIL"]}", "${languageStrings["CALENDAR_MONTH_SHORT_MAY"]}", "${languageStrings["CALENDAR_MONTH_SHORT_JUNE"]}", "${languageStrings["CALENDAR_MONTH_SHORT_JULY"]}", "${languageStrings["CALENDAR_MONTH_SHORT_AUGUST"]}", "${languageStrings["CALENDAR_MONTH_SHORT_SEPTEMBER"]}", "${languageStrings["CALENDAR_MONTH_SHORT_OCTOBER"]}", "${languageStrings["CALENDAR_MONTH_SHORT_NOVEMBER"]}", "${languageStrings["CALENDAR_MONTH_SHORT_DECEMBER"]}"],
  AM: "${languageStrings["CALENDAR_AM_UPPER"]}",
  PM:  "${languageStrings["CALENDAR_PM_UPPER"]}",
  am: "${languageStrings["CALENDAR_AM_LOWER"]}",
  pm: "${languageStrings["CALENDAR_PM_LOWER"]}",
  dateType: "${calendar}",
  minYear: ${parseInt(languageStrings["CALENDAR_MIN_YEAR"])},
  maxYear: ${parseInt(languageStrings["CALENDAR_MAX_YEAR"])},
  exit: "${languageStrings["CALENDAR_CLOSE"]}",
  save: "${languageStrings["CALENDAR_SAVE"]}"
};`;

					if (!fs.existsSync(destCalendar)) {
						fs.mkdirSync(destCalendar);
					}

					// Write the file
					fs.writeFile(`${destCalendar}/${xmlContent.tag[0]}.js`, templateCalendar, (err) => {
						if (err) {
							return console.log(err);
						}

						console.log(`The ${xmlContent.tag[0]} calendar javascript file was saved!`);
					});

				} else {
					throw new Error(`${file} is missing some translation strings for the calendar`)
				}
			});

			templateError += `
}`;

			if (!fs.existsSync(destError)) {
				fs.mkdirSync(destError);
			}

			// Write the file
			fs.writeFile(`${destError}/error-locales.js`, templateError, (err) => {
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
