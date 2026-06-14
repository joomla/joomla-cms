/**
 * Assets Builder
 */
import path from 'node:path';
import fsp from 'node:fs/promises';
import { rollup } from 'rollup';
import VuePlugin from 'rollup-plugin-vue';
import { nodeResolve } from '@rollup/plugin-node-resolve';
import commonjs from '@rollup/plugin-commonjs';
import replace from '@rollup/plugin-replace';
import { babel } from '@rollup/plugin-babel';
import DefaultModuleBuilder from '../../build/build-modules-js/builder/default-module-builder.mjs';
import { minifyJSContent } from '../../build/build-modules-js/javascript/js-handle.mjs';

export const buildMediaManager = async (basePath, targetPath) => {
  const srcFile = path.join(basePath, 'src', 'mediamanager.es6.js');
  const targetFile = path.join(targetPath, 'js', 'media-manager.js');
  const targetFileMin = path.join(targetPath, 'js', 'media-manager.min.js');
  const isProduction = process.env.NODE_ENV !== 'DEVELOPMENT';

  return rollup({
    input: srcFile,
    plugins: [
      VuePlugin({
        target: 'browser',
        css: false,
        compileTemplate: true,
        template: {
          isProduction,
        },
      }),
      nodeResolve(),
      commonjs(),
      replace({
        'process.env.NODE_ENV': JSON.stringify((process.env.NODE_ENV && process.env.NODE_ENV.toLocaleLowerCase()) || 'production'),
        __VUE_OPTIONS_API__: true,
        __VUE_PROD_DEVTOOLS__: !isProduction,
        preventAssignment: true,
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
                browsers: [
                  '> 1%',
                  'not op_mini all',
                  /** https://caniuse.com/es6-module */
                  'chrome >= 61',
                  'safari >= 11',
                  'edge >= 16',
                  'Firefox >= 60',
                ],
              },
              loose: true,
              bugfixes: false,
              ignoreBrowserslistConfig: true,
            },
          ],
        ],
      }),
    ],
  }).then((build) => {
    return build.write({
        format: 'es',
        sourcemap: !isProduction ? 'inline' : false,
        file: targetFile,
      })
      .then((value) => (isProduction ? minifyJSContent(value.output[0].code) : value.output[0]))
      .then((content) => {
        if (isProduction) {
          return fsp.writeFile(targetFileMin, content, { encoding: 'utf8', mode: 0o644 });
        }

        // Copy media-manager.js => media-manager.min.js
        return fsp.copyFile(targetFile, targetFileMin);
      }).then(() => {
        return build.close();
      })
  });
};

export default class MediaModuleBuilder extends DefaultModuleBuilder
{
  /**
   * Process JavaScript files and Modules
   * @returns { Promise }
   */
  async js() {
    await super.js();

    return buildMediaManager(this.basePath, this.targetPath);
  }
};
