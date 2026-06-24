/**
 * Assets Builder
 */
import fs from 'node:fs';
import fsp from 'node:fs/promises';
import path from 'node:path';
import { rollup } from 'rollup';
import { nodeResolve } from '@rollup/plugin-node-resolve';
import replace from '@rollup/plugin-replace';
import { babel } from '@rollup/plugin-babel';
import DefaultModuleBuilder from '../../../build/build-modules-js/builder/default-module-builder.mjs';
import { resolvePackageFile } from '../../../build/build-modules-js/utils/resolve-package.mjs';
import { minifyJSContent } from '../../../build/build-modules-js/javascript/js-handle.mjs';

const minifyBootstrapModule = async (targetFile, code) => {
  const code2 = code
    .replace('./popper.js', './popper.min.js')
    .replace('./dom.js', './dom.min.js');

  return minifyJSContent(code2).then((jsMin) => {
    return fsp.writeFile(
      targetFile.replace('.js', '.min.js'),
      jsMin,
      { encoding: 'utf8', mode: 0o644 }
    );
  });
};

const compileBootstrapJS = async (basePath, targetPath) => {
  const modulePathJson = resolvePackageFile(path.join('bootstrap', 'package.json'));

  if (!modulePathJson) {
    throw new Error(`Package "bootstrap" not found`);
  }

  const moduleOptions = JSON.parse(fs.readFileSync(modulePathJson, { encoding: 'utf8' }));
  const modulePathBase = path.dirname(modulePathJson);
  const domImports = fs.readdirSync(path.join(modulePathBase, 'js/src/dom'));
  const utilImports = fs.readdirSync(path.join(modulePathBase, 'js/src/util'));
  const bsVersion = moduleOptions.version;

  return rollup({
    input: path.join(basePath, 'js', 'index.es6.js'),
    plugins: [
      nodeResolve(),
      replace({
        preventAssignment: true,
        'process.env.NODE_ENV': "'production'",
      }),
      babel({
        exclude: 'node_modules/core-js/**',
        babelHelpers: 'bundled',
        babelrc: false,
        presets: [
          [
            '@babel/preset-env',
            {
              targets: {
                browsers: ['> 1%', 'not op_mini all'],
              },
            },
          ],
        ],
      }),
      {
        name: 'bootstrap-version-plugin',
        renderChunk(code) {
          return code
            .replace('./popper.js', `./popper.js?${bsVersion}`)
            .replace('./dom.js', `./dom.js?${bsVersion}`);
        }
      }
    ],
  }).then((build) => {
    return build.write({
      format: 'es',
      sourcemap: false,
      dir: path.join(targetPath, 'js'),
      chunkFileNames: '[name].js',
      manualChunks: {
        alert: ['media_source/vendor/bootstrap/js/alert.es6.js'],
        button: ['media_source/vendor/bootstrap/js/button.es6.js'],
        carousel: ['media_source/vendor/bootstrap/js/carousel.es6.js'],
        collapse: ['media_source/vendor/bootstrap/js/collapse.es6.js'],
        dropdown: ['media_source/vendor/bootstrap/js/dropdown.es6.js'],
        modal: ['media_source/vendor/bootstrap/js/modal.es6.js'],
        offcanvas: ['media_source/vendor/bootstrap/js/offcanvas.es6.js'],
        popover: ['media_source/vendor/bootstrap/js/popover.es6.js'],
        scrollspy: ['media_source/vendor/bootstrap/js/scrollspy.es6.js'],
        tab: ['media_source/vendor/bootstrap/js/tab.es6.js'],
        toast: ['media_source/vendor/bootstrap/js/toast.es6.js'],
        popper: ['@popperjs/core'],
        dom: [
          'node_modules/bootstrap/js/src/base-component.js',
          ...domImports.map((file) => `node_modules/bootstrap/js/src/dom/${file}`),
          ...utilImports.map((file) => `node_modules/bootstrap/js/src/util/${file}`),
        ],
      },
    }).then((result) => {
      fs.unlinkSync(path.join(targetPath, 'js', 'index.es6.js'));

      const promises = [];
      result.output.forEach((chunk) => {
        if (chunk.fileName === 'index.es6.js') return;

        promises.push(minifyBootstrapModule(
          path.join(targetPath, 'js', chunk.fileName),
          chunk.code
        ));
      });

      return Promise.all(promises).then(() => build.close());
    });
  });
}

export default class BootstrapModuleBuilder extends DefaultModuleBuilder
{
  /**
   * Remove files on target location
   * @returns { Promise }
   */
  async clear() {
    // Do not clear whole target because it is mix of customized JS and source files from the bootstrap package.
    // Remove only related JS
    const jsPath = path.join(this.targetPath, 'js');

    if (!fs.existsSync(jsPath)) {
      return;
    }

    return fsp.rm(jsPath, { recursive: true });
  }

  /**
   * Process CSS files
   * @returns { Promise }
   */
  async css() {
    // Nothing to do here
  }

  /**
   * Process JavaScript files and Modules
   * @returns { Promise }
   */
  async js() {
    return compileBootstrapJS(this.basePath, this.targetPath);
  }
};
