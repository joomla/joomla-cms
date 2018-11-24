const Autoprefixer = require('autoprefixer');
const Babel = require('./babel.js')
const Fs = require('fs');
const FsExtra = require('fs-extra');
const postcss = require('postcss');
const Promise = require('bluebird');
const Sass = require('node-sass');
const UglyCss = require('uglifycss');
const rootPath = require('./rootpath.js')._();

const createJsFiles = (element, es6File) => {
    // Predefine some settings
    const settings = [
        {
            presets: [
                ['@babel/preset-env', {
                    "targets": {
                        "browsers": ["last 1 Chrome version"]
                    }
                }],
            ],
            "comments": true
        },
        {
            presets: [
                ['@babel/preset-env', {
                    "targets": {
                        "browsers": ["last 1 Chrome version"]
                    }
                }],
                ['minify']
            ],
            "comments": false
        },
        {
            presets: [
                ['@babel/preset-env', {
                    'targets': {
                        'browsers': ["ie 11"]
                    }
                }],
            ],
            plugins: [
                '@babel/plugin-transform-classes'
            ],
            'comments': true

        },
        {
            presets: [

                ['@babel/preset-env', {
                    targets: {
                        browsers: ["ie 11"]
                    }
                }],
                ['minify']
            ],
            plugins:[
                ['@babel/plugin-transform-classes'],
            ],
            "comments": false

        }
    ];

    const outputFiles = [
        `${rootPath}/media/system/webcomponents/js/joomla-${element}.js`,
        `${rootPath}/media/system/webcomponents/js/joomla-${element}.min.js`,
        `${rootPath}/media/system/webcomponents/js/joomla-${element}-es5.js`,
        `${rootPath}/media/system/webcomponents/js/joomla-${element}-es5.min.js`,
    ];

    settings.forEach((setting, index) => {
        Babel.run(es6File, setting, outputFiles[index])
    });
};

const compile = (options) => {
    // Make sure that the dist paths exist
    if (!Fs.existsSync(`${rootPath}/media/system/webcomponents`)) {
        FsExtra.mkdirSync(`${rootPath}/media/system/webcomponents`);
    }
    if (!Fs.existsSync(`${rootPath}/media/system/webcomponents/js`)) {
        FsExtra.mkdirSync(`${rootPath}/media/system/webcomponents/js`);
    }

    if (!Fs.existsSync(`${rootPath}/media/system/webcomponents/css`)) {
        Fs.mkdirSync(`${rootPath}/media/system/webcomponents/css`);
    }

    options.settings.elements.forEach((element) => {
        // Get the contents of the ES-XXXX file
        let es6File = Fs.readFileSync(`${rootPath}/build/media/webcomponents/js/${element}/${element}.js`, 'utf8');
        // Check if there is a css file
        if (Fs.existsSync(`${rootPath}/build/media/webcomponents/scss/${element}/${element}.scss`)) {
            if (!Fs.existsSync(`${rootPath}/build/media/webcomponents/scss/${element}/${element}.scss`)) {
                return;
            }

            Sass.render({
                file: `${rootPath}/build/media/webcomponents/scss/${element}/${element}.scss`,
            }, (error, result) => {
                if (error) {
                    // eslint-disable-next-line no-console
                    console.error(`${error.column}`);
                    // eslint-disable-next-line no-console
                    console.error(`${error.message}`);
                    // eslint-disable-next-line no-console
                    console.error(`${error.line}`);
                } else {
                    // Auto prefixing
                    // eslint-disable-next-line no-console
                    console.log(`Prefixing for: ${options.settings.browsers}`);

                    const cleaner = postcss(
                        [
                            Autoprefixer({
                                add: false,
                                browsers: options.settings.browsers
                            }),
                        ],
                    );

                    const prefixer = postcss([Autoprefixer]);

                    if (typeof result === 'object' && result.css) {
                        cleaner.process(result.css.toString(), {from: undefined})
                            .then(cleaned => prefixer.process(cleaned.css, {from: undefined}))
                            .then((res) => {
                                if (/{{CSS_CONTENTS_PLACEHOLDER}}/.test(es6File)) {
                                    if (typeof res === 'object' && res.css) {
                                        es6File = es6File.replace('{{CSS_CONTENTS_PLACEHOLDER}}', UglyCss.processString(res.css.toString()));

                                        createJsFiles(element, es6File);
                                    }
                                } else {
                                    if (typeof res === 'object' && res.css) {
                                        Fs.writeFileSync(
                                            `${rootPath}/media/system/webcomponents/css/joomla-${element}.css`,
                                            res.css.toString(),
                                            { encoding: 'UTF-8' },
                                        );
                                        Fs.writeFileSync(
                                            `${rootPath}/media/system/webcomponents/css/joomla-${element}.min.css`,
                                            UglyCss.processString(res.css.toString(), { expandVars: false }),
                                            { encoding: 'UTF-8' },
                                        );
                                    }

                                    createJsFiles(element, es6File);
                                }
                            })

                            // Handle errors
                            .catch((err) => {
                                // eslint-disable-next-line no-console
                                console.error(`${err}`);
                                process.exit(-1);
                            });

                        return ;
                        // eslint-disable-next-line no-console
                        console.log(`joomla-${element} was updated.`);
                    }
                }
            });
        } else {
            createJsFiles(element, es6File);
        }
    });
};

const compileCEjs = (options, path) => {
    Promise.resolve()
        .then(() => compile(options, path))

        // Handle errors
        .catch((err) => {
            // eslint-disable-next-line no-console
            console.error(`${err}`);
            process.exit(-1);
        });
};

module.exports.compile = compileCEjs;
