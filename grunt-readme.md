#### Maintainer's Area

- Install Node:  https://nodejs.org/en/
- Run: `npm install` in this folder if this is the first itme!

- Run: `grunt` will do the automatic update for all the assets

possible commands:

- `grunt clean:assets`.................clears the media/vendor folder
- `grunt shell:update`.................will update all the npm packages to the version defined in /dev/assets/package.json
- `grunt curl:cmGet`...................fetches latest codemirror to assets/tmp folder
- `grunt unzip:cmUnzip`................extracts the downladed codemirror zip to assets/tmp/codemirror folder
- `grunt gitclone:cloneCombobox`.......fetches latest combobox to assets/tmp folder
- `grunt gitclone:cloneCropjs`.........fetches latest combobox to assets/tmp folder
- `grunt gitclone:cloneAutojs`.........fetches latest autocomplete to assets/tmp folder
- `grunt concat:someFiles`.............concatenates some codemirror files
- `grunt copy:fromSource`..............copy everything to media/vendor/*
- `grunt uglify:allJs`.................minifies various javascripts
- `grunt cssmin:allCss`................minifies various stylesheets
- `grunt text-scss`....................validate/lint the template SCSS
- `grunt compile`......................minifies all template JS, lints SCSS then compiles it

Make sure that you have updated the settings.yaml file in order to update the libraries!!!

Will update the following external sourced static assets that Joomla is using.

- Jquery
- Jquery-migrate
- Bootstrap
- Tether
- Font awesome
- Chosen
- Jquery-minicolors
- Jquery-sortable
- Jquery-ui
- MediaElement
- Punycode
- TinyMCE
- Awesomplete
- Codemirror

The following are always fetched with curl (no module available)

- Jcrop
- Autocomplete
