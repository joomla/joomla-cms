const { readdir } = require('fs').promises;
const { resolve } = require('path');

async function* recursiveSearch(dir) {
  const dirents = await readdir(dir, { withFileTypes: true });
  for (const dirent of dirents) {
    const res = resolve(dir, dirent.name);
    if (dirent.isDirectory()) {
      yield* recursiveSearch(res);
    } else {
      yield res;
    }
  }
}

async function reduceAsync(asyncIter, f, init) {
  let res = init;
  for await (const x of asyncIter) {
    res = f(res, x);
  }
  return res;
}

module.exports = { recursiveSearch, reduceAsync };
