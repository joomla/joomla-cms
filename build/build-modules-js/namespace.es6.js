const Fs = require('fs');
const Path = require('path');
const RootPath = require('./utils/rootpath.es6.js')._();


const jregex = /defined\('(JPATH_BASE|_JEXEC|JPATH_PLATFORM)'\) or die;/gm;
const oldClassName = 'JFeedEntry';
const classUse  = 'use Joomla\\CMS\\Feed\\FeedEntry;';
const newClassName = classUse.substr(classUse.lastIndexOf('\\') + 1).slice(0, -1);
const root = `${RootPath}/`;
const filesToIgnore = [
	'classmap.php',
	'finder_indexer.php',
	'behavior.php',
	'restore.php',
	'com_finder',
	'Provider',
]

module.exports.run = () => {
	//const classList = Fs.readFileSync(`${RootPath}/libraries/classmap.php`, 'utf-8');

	searchDir(root, '.php');
};

const searchDir = (startPath, filter) => {
    if (!Fs.existsSync(startPath)) {
        console.log("Directory doesn't exist", startPath);
        return;
    }

    let files = Fs.readdirSync(startPath);

	files = files.filter(el => {
		return !filesToIgnore.includes(el);
	});

    for (let i = 0; i < files.length; i++) {
        const filename = Path.join(startPath, files[i]);
        const stat = Fs.lstatSync(filename);

        if (stat.isDirectory()) {
            searchDir(filename, filter); // Recurse
        }
        else if (filename.indexOf(filter) >= 0) {
			const file = Fs.readFileSync(filename, 'utf-8');
			getInstances(file, filename);
        };
    };
};

const readFile = (path) => {
	Fs.readFile(path, 'utf-8', (err, content) => {
		if (err) {
			throw err;
			return;
		}
		getInstances(content, path);
	});
};

const getInstances = (content, path) => {
	const regex = /(?=\S)(?!\/\*\*)(?!\/\/)(?!\*)(\\?JFeedEntry)/gm;
	const matches = content.match(regex);

	if (matches !== null) {
		const replace = content.replace(regex, newClassName);
		appendUse(replace, path);
	}
};

const appendUse = (content, path) => {
	const matches = content.match(jregex);

	if (matches !== null) {
		const replace = content.replace(jregex, matches[0] + '\n\n' + classUse);
		sortUse(replace, path);
	}
};

const sortUse = (content, path) => {
	const regex = /use (.*?);/gm;
	const matches = content.match(regex);

	if (matches !== null) {
		// Order the array alphabetically and remove duplicates
		let newUse = removeDuplicates(matches.sort());

		// Convert to a string, and replace commas with a new line
		newUse = newUse.toString().replace(/,/g, '\n');

		// Replace all 'use' calls with an empty line
		let replace = content.replace(regex, '');

		// Replace jrejex with the new string
		const matches2 = content.match(jregex);
		replace = replace.replace(jregex, matches2[0] + '\n\n' + newUse);

		// Replace multiple blank lines with just 1
		replace = replace.replace(/([ \t]*\n){3,}/, '\r\n\r\n');

		Fs.writeFileSync(path, replace, 'utf-8');
		console.log(`COMPLETE - ${path}`);
	}
};

const removeDuplicates = (array) => {
    return array.sort().filter((item, pos, ary) => {
        return !pos || item != ary[pos - 1];
    })
}
