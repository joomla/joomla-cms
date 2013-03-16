#!/bin/bash

## cat models/*.js 			> installation.max.js
## cat collections/*.js 		>> installation.max.js
## cat views/*.js 				>> installation.max.js

cat app.js		> installation.max.js
java -jar bin/yuicompressor-2.4.2.jar "installation.max.js" -o "installation.min.js" --charset utf-8
rm installation.max.js

