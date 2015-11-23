# [SCEditor](http://www.sceditor.com/) v1.4.5

[![Build Status](https://travis-ci.org/samclarke/SCEditor.png?branch=master)](https://travis-ci.org/samclarke/SCEditor) [![Dependency Status](https://gemnasium.com/samclarke/SCEditor.png)](https://gemnasium.com/samclarke/SCEditor)

A lightweight WYSIWYG BBCode and XHTML editor.

For more information visit [sceditor.com](http://www.sceditor.com/)


## Usage

Include the JQuery and SCEditor JavaScript

	<link rel="stylesheet" href="minified/jquery.sceditor.min.css" type="text/css" media="all" />
	<script type="text/javascript" src="minified/jquery.sceditor.bbcode.min.js"></script>

Then to change all textareas to WYSIWYG editors, simply do:

	$(function() {
		$("textarea").sceditor({
			plugins: 'xhtml',
			style: 'minified/jquery.sceditor.default.min.css'
		});
	});

or for a BBCode WYSIWYG editor do:

	$(function() {
		$("textarea").sceditor({
			plugins: 'bbcode',
			style: 'minified/jquery.sceditor.default.min.css'
		});
	});



## Options

For a full list of options, see the [options documentation](http://www.sceditor.com/documentation/options/).



## Building and testing

You will need [Grunt](http://gruntjs.com/) installed to run the build/tests. To install Grunt run:

    npm install -g grunt-cli

Next, to install the SCEditor dev dependencies run:

    npm install

That's it! You can now build and test SCEditor with the following commands:

    grunt build # Minifies the JS and converts the LESS to CSS
    grunt test # Runs the linter and unit tests
    grunt dist # Creates the distributable ZIP file



## Contribute

Any contributions and/or pull requests would be welcome.

Themes, translations, bug reports, bug fixes and donations are greatly appreciated.



## Donate

If you would like to make a donation you can via
[PayPal](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=AVJSF5NEETYYG)
or via [Flattr](http://flattr.com/thing/400345/SCEditor)



## License

SCEditor is licensed under the [MIT](http://www.opensource.org/licenses/mit-license.php) license.

	Copyright (C) 2011 by Sam Clarke and contributors – sceditor.com

	Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

	The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.



## Credits

**Nomicons: The Full Monty Emoticons by:**
Oscar Gruno, aka Nominell v. 2.0 -> oscargruno@mac.com
Andy Fedosjeenko, aka Nightwolf -> bobo@animevanguard.com

**Icons by:**
Mark James (http://www.famfamfam.com/lab/icons/silk/)
Licensed under the [Creative Commons CC-BY license](http://creativecommons.org/licenses/by/3.0/).