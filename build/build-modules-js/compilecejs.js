const Autoprefixer = require('autoprefixer');
const Babel = require('./babel.js');
const CssNano = require('cssnano');
const Fs = require('fs');
const FsExtra = require('fs-extra');
const Postcss = require('postcss');
const Promise = require('bluebird');
const Sass = require('node-sass');
const RootPath = require('./rootpath.js')._();

const createJsFiles = (element, es6File) => {
    // Define some settings
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
        `${RootPath}/media/system/webcomponents/js/joomla-${element}.js`,
        `${RootPath}/media/system/webcomponents/js/joomla-${element}.min.js`,
        `${RootPath}/media/system/webcomponents/js/joomla-${element}-es5.js`,
        `${RootPath}/media/system/webcomponents/js/joomla-${element}-es5.min.js`,
    ];

    settings.forEach((setting, index) => {
        Babel.run(es6File, setting, outputFiles[index])
    });
};

const compile = (options) => {
    // Make sure that the dist paths exist
    if (!Fs.existsSync(`${RootPath}/media/system/webcomponents`)) {
        FsExtra.mkdirSync(`${RootPath}/media/system/webcomponents`);
    }
    if (!Fs.existsSync(`${RootPath}/media/system/webcomponents/js`)) {
        FsExtra.mkdirSync(`${RootPath}/media/system/webcomponents/js`);
    }

    if (!Fs.existsSync(`${RootPath}/media/system/webcomponents/css`)) {
        Fs.mkdirSync(`${RootPath}/media/system/webcomponents/css`);
    }

    options.settings.elements.forEach((element) => {
        // Get the contents of the ES-XXXX file
        let es6File = Fs.readFileSync(`${RootPath}/build/media/webcomponents/js/${element}/${element}.js`, 'utf8');
        // Check if there is a css file
        if (Fs.existsSync(`${RootPath}/build/media/webcomponents/scss/${element}/${element}.scss`)) {
            if (!Fs.existsSync(`${RootPath}/build/media/webcomponents/scss/${element}/${element}.scss`)) {
                return;
            }

            Sass.render({
                file: `${RootPath}/build/media/webcomponents/scss/${element}/${element}.scss`,
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
                    console.log(`Creating /media/system/webcomponents/css/joomla-${element}`);
                    console.log(`Prefixing for: ${options.settings.browsers}`);

                    const cleaner = Postcss(
                        [
                            Autoprefixer({
                                env: {
                                    targets: {
                                        browsers: [options.settings.browsers]
                                    },
                                }
                            }),
                        ],
                    );

                    if (typeof result === 'object' && result.css) {
                        cleaner.process(result.css.toString(), {from: undefined})
                            .then((res) => {
                                if (/{{CSS_CONTENTS_PLACEHOLDER}}/.test(es6File)) {
                                    if (typeof res === 'object' && res.css) {
                                        Postcss([CssNano]).process(res.css.toString(), {from: undefined}).then(cssMin => {
                                            es6File = es6File.replace('{{CSS_CONTENTS_PLACEHOLDER}}', cssMin.css.toString());
                                            createJsFiles(element, es6File);
                                        });
                                    }
                                } else {
                                    if (typeof res === 'object' && res.css) {
                                        Fs.writeFileSync(
                                            `${RootPath}/media/system/webcomponents/css/joomla-${element}.css`,
                                            res.css.toString(),
                                            { encoding: 'UTF-8' },
                                        );
                                        Postcss([CssNano]).process(res.css.toString(), {from: undefined}).then(cssMin => {
                                            Fs.writeFileSync(
                                                `${RootPath}/media/system/webcomponents/css/joomla-${element}.min.css`,
                                                cssMin.css.toString(),
                                                { encoding: 'UTF-8' },
                                            );
                                        });
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
