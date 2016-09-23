#### Maintainer's Area

Install Node:  https://nodejs.org/en/
Run: `npm install` in this folder
Got to assets `cd assetes` and type again `npm install` then go back `cd ..`

Run: `grunt` will do the automatic update for all the assets

possible commands:
- grunt clean:old           clears the media/vendor folder
- gitclone:cloneCodemirror  fetches latest codemirror to assets/tmp folder
- gitclone:cloneCombobox    fetches latest combobox to assets/tmp folder
- gitclone:cloneCropjs      fetches latest combobox to assets/tmp folder
- concat:addons             concatenates some codemirror files
- concat:addons             concatenates some codemirror files
- copy:transfer             copy everything to media/vendor/*
- uglify:build              minifies various javascripts
- cssmin:codemirror         minifies various stylesheets



Make sure that you have updated the assets/package.json and ran 'npm install' in the sub directory assets,
in order to update the libraries!!!
