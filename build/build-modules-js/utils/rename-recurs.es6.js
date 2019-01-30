const Fs = require('fs');
const Path = require('path');

const renameFilesRecursive = (dir, from, to) => {
  Fs.readdirSync(dir).forEach((it) => {
    const itsPath = Path.resolve(dir, it);
    const itsStat = Fs.statSync(itsPath);

    if (itsStat.isDirectory()) {
      renameFilesRecursive(itsPath, from, to);
    } else if (itsPath.search(from) > -1) {
      Fs.renameSync(itsPath, itsPath.replace(from, to));
    }
  });
};


module.exports.run = renameFilesRecursive;
