# CMS Media source

The folder contains source files of CMS client-side resources (Stylesheets, JavaScripts, Images and other assets) needed for extensions to work.
Each folder is for a specific extension with a few exceptions like `vendor`, `system`.

## Building assets

Before using the assets they must first be built/compiled and placed under `media/` folder.

Following command helps to build, they will call builder associated with each asset:
- `npm run build -- -a` Build all assets. Will go through all resources and will build each asset.
- `npm run build -- -n <resource_name>,<resource_name>` Build only specific asset.

Additionally, each builder may provide their own task, that can be executed separately without running the whole rebuild.
In default configuration the builder uses following tasks:
- `clear` remove all associated files from `media/`.
- `copy` copy static files to `media/`
- `css` process Stylesheets files. Copy or compile to CSS when needed (example for SCSS etc.).
- `js` process JavaScript files. Copy or compile to JS when needed (example for `.es6.js` and `.vue` etc.).
- `gzip` prepare compressed version of css,js files.

To execute specific task use `-t` argument:
- `npm run build -- -a -t <task_name>,<task_name>` Will apply specified task to all asset builders.
- `npm run build -- -n <resource_name>,<resource_name> -t <task_name>,<task_name>` Will apply specified task to specified asset builders.

Use watchers while developing an extension allows to automatically refresh the assets while editing.
- `npm run watch -- -n <resource_name>` Will start watching files changes for specific asset.

## Adding new assets

To add new resource follow next steps:
- Create folder under `media_source/` with name of future asset. Example for `com_example` it will be `media_source/com_example/`.
- Add Stylesheets, JavaScript and other needed files for the asset.
- Add the resource name in to `build/build-modules-js/builders-registry.mjs` into the list of `builders` so CLI know it exists.
- (Optionally) When need extra processing add custom builder into the root of the newly created folder `media_source/com_example/builder.mjs`.
- Source files of complex Application (for example Vue based script) place under `src/` like `media_source/com_example/src/` and use custom builder to build the Application.

Done. And do not forget to run build.

### Note about JavaScript and Stylesheets

#### Javascript

- Modern JavaScript files must have an extension `.es6.js`.
  This allows ESLint to check the code style, Joomla is using the Airbnb preset https://github.com/airbnb/javascript.
  It also instructs Rollup to do the transforms for ES2017. This step creates both normal and minified files.
  Production code WILL NOT have the `.es6` part for ES2017+ files.

- Legacy JavaScript files must have an extension `.es5.js`.
  This instructs ESLint to skip checking this file.
  Also, it instructs the tools to create a minified version (production code WILL NOT have the `.es5` part)


#### SCSS
- SCSS files starting with `_` will not become entry points for SCSS.
  SCSS files will be transformed to CSS, both normal and minified versions.

### Custom builder

By default, each resource uses `DefaultModuleBuilder` class to build its asset.
However, each resource may have own `builder.mjs` script with custom logic.
To use custom builder add `builder.mjs` with custom logic in to the root of the resources' folder.

#### Anatomy of custom builder

Each builder receives the base path (path to `media_source/`) and target path (path to `media/`) as input, also cmd options.
The builder should provide list of tasks which it is able to run and expose public methods for each task to do so.

**ATTENTION**: While coding the custom builder avoid writing in to folders outside the current asset scope. This could lead to unexpected collisions.

Example builder:

```javascript
export default class ExampleModuleBuilder
{
	tasksBuild = ['clear', 'copy', 'css', 'js'];
	tasksExtras = ['gzip'];

	constructor(name = '', basePath = '', targetPath = '', options = {}) {
		if (!name) {
			throw new Error(`Argument "name" is required for ModuleBuilder.`);
		}

		if (!basePath || !targetPath) {
			throw new Error(`Arguments "basePath" and "targetPath"  is required for "${name}" ModuleBuilder.`);
		}

		this.name = name;
		this.basePath = path.join(basePath, name);
		this.targetPath = path.join(targetPath, name);
		this.options = options;
	}

	getAllTasks() {
		return [ ...this.tasksBuild, ...this.tasksExtras ];
	}

	getBuildTasks() {
		return this.tasksBuild;
	}

	// The methods to execute each task
	async clear() {}
	async copy() {}
	async css() {}
	async js() {}
	async gzip() {}
}
```

The list of tasks is not strict, and depending on the builder needs.
However, it is required the builder to implement `getBuildTasks()` so the main script knows which tasks need to run while running the complete build.
Method `getAllTasks()` is optional, needed only when builder have tasks that can be executed from CLI but which does not participate in the complete build.

To simplify the coding it is possible to extend `DefaultModuleBuilder` class.

```javascript
import DefaultModuleBuilder from '../../build/build-modules-js/builder/default-module-builder.mjs';

export default class ExampleModuleBuilder extends DefaultModuleBuilder {
	async js() {
		await super.js();

		// Run custom code
	}
}
```
