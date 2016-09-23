#### Maintainer's Area

- Install Node:  https://nodejs.org/en/
- Run: `npm install` in this folder if this is the first itme!

- Run: `grunt` will do the automatic update for all the assets

possible commands:

- `grunt clean:assets`.................clears the media/vendor folder
- `grunt shell:update`.................will update all the npm packages to the version defined in /dev/assets/package.json
- `grunt gitclone:cloneCodemirror`.....fetches latest codemirror to assets/tmp folder
- `grunt gitclone:cloneCombobox`.......fetches latest combobox to assets/tmp folder
- `grunt gitclone:cloneCropjs`.........fetches latest combobox to assets/tmp folder
- `grunt concat:someFiles`.............concatenates some codemirror files
- `grunt copy:fromSource`..............copy everything to media/vendor/*
- `grunt uglify:allJs`.................minifies various javascripts
- `grunt cssmin:allCss`................minifies various stylesheets

Make sure that you have updated the assets/package.json in order to update the libraries!!!

Will update the following external sourced static assets that Joomla is using.

- Jquery:........... version .... 3.0.0
- Jquery-migrate:... version .... 3.0.0
- Bootstrap......... version .... 4.0.0-alpha.4'
- TinyMCE:.......... version .... 4.4.3
- Font awesome:..... version .... 4.6.3
- Punycode.......... version .... 2.0.0

The following are always fetching the gihub repo (master branch)
- Codemirror
- Combobox
- Jcrop
