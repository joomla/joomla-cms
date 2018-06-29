const fs = require('fs');
const ini = require('ini');
const Recurs = require("recursive-readdir");

const rootPath = __dirname.replace('/build/build-modules-js', '').replace('\\build\\build-modules-js', '');
const dir = `${rootPath}/installation/language`;
const dest = `${rootPath}/templates/system/js`;
const installationFile = `${rootPath}/templates/system/incompatible.html`;

// Set the initial template
let template = `
var errorLocale = {`;

installation = () => {
	let installationContent = fs.readFileSync(`${__dirname}/incompatible.html`, 'utf-8');

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

			installationContent = installationContent.replace('{{jsonContents}}', template);

			fs.writeFile(installationFile, installationContent, (err) => {
				if (err) {
					return console.log(err);
				}

				console.log("The installation error page was saved!");
			});
		},
		(error) => {
			console.error("something exploded", error);
		}
	);
};

module.exports.installation = installation;
