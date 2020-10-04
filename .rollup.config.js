import { nodeResolve } from '@rollup/plugin-node-resolve';
import commonjs from '@rollup/plugin-commonjs';

export default {
  input: 'build/media_source/system/js/fields/joomla-field-googlefont.esm.js',
  output: {
    file: './media/system/js/fields/joomla-field-googlefont.esm.js',
    format: 'esm'
  },
  plugins: [
    commonjs(),
    nodeResolve({
    // use "module" field for ES6 module if possible
    module: true, // Default: true

  // use "main" field or index.js, even if it's not an ES6 module
  // (needs to be converted from CommonJS to ES6
  // – see https://github.com/rollup/rollup-plugin-commonjs
  main: true,  // Default: true


  // whether to prefer built-in modules (e.g. `fs`, `path`) or
  // local ones with the same names
  preferBuiltins: // Default: false
true,  // Default: true

  // If true, inspect resolved files to check that they are
  // ES2015 modules
  modulesOnly: true,
})]
}
