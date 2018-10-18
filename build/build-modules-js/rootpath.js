// Root Path
const rootPath = () => __dirname.replace('/build/build-modules-js', '').replace('\\build\\build-modules-js', '');

module.exports._ = rootPath;
