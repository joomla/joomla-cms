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

Make sure that you have updated the vendors_versions.yaml file in order to update the libraries!!!

Will update the following external sourced static assets that Joomla is using.

- Jquery:........... version .... 3.1.0
- Jquery-migrate:... version .... 3.0.0
- Bootstrap:........ version .... 4.0.0-alpha.4'
- Tether:........... version .... 1.3.7
- Font awesome:..... version .... 4.6.3
- Chosen:........... version .... 1.4.3
- Jquery-minicolors: version .... 2.1.10
- Jquery-sortable:.. version .... 0.9.13
- Jquery-ui:........ version .... 1.12.1
- MediaElement:..... version .... 2.22.0
- Punycode.......... version .... 1.4.1
- TinyMCE:.......... version .... 4.4.3
- Codemirror........ version .... 5.19.0

The following are always fetching the gihub repo (master branch)

- Combobox
- Jcrop
- Autocomplete
