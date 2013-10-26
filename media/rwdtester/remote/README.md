# Remote Preview

### [Remote Preview](http://viljamis.com/blog/2012/remote-preview/) allows you to preview any URL on large number of mobile devices simultaneously. Just enter a URL, hit Enter, and new URL gets automatically loaded on each device. Works on platforms like Android, Blackberry, iOS, Maemo, Meego, Symbian, Windows Phone and WebOS. Built by [@viljamis](http://twitter.com/viljamis) for the [Helsinki Device Lab](http://devicelab.fi) for fast site previewing. [Watch a video on Youtube](http://www.youtube.com/watch?v=7NvzRfyhd5Q) to see the tool in action.

Remote Preview works by making an ajax call every 1100ms to check if the url in the 'url.txt' file is changed. If it is, the script will then change the src attribute of the iframe and load a new page into it. If there's no changes, the script will just keep polling the url.txt until something changes. Remote Preview allows very fast previewing of different URL's to check for possible layout problems, which can then be debugged using various other tools depending on the platform where they occur.

## Basic usage

1. Move all files to a public Dropbox folder/Server/localhost, edit ‘url.txt’, hit Cmd+S (save…) and wait for devices to refresh. That's all!
2. Bookmark Remote Preview to your devices’s home screen for fast & easy application like access later on.
3. If you moved all files to a server or localhost, you can control the devices via web browser by pointing your browser to the directory called /control/.
4. To make the Controller page work, you need to make sure that PHP is enabled on the server and that the url file has necessary write permissions. You should also limit the access to this page somehow as otherwise anyone can go to that url and control the devices.
5. Use ID's to scroll down to a certain point on a page. URL#ID, for example: http://opendevicelab.com/#location

## Running on localhost

Check out this [tutorial](http://coolestguyplanettech.com/downtown/install-and-configure-apache-mysql-php-and-phpmyadmin-osx-108-mountain-lion) on how to config Apache for OS X 10.8.


## Chrome extension

There's now an extension for Chrome, which, when turned on, auto sends every URL change to the controller (even the tab changes!). The extension is available via [Chrome Web Store](https://chrome.google.com/webstore/detail/remote-preview/meilakmceeilinkpponceohlnfbhijok).

## Browser support

Current version is tested to be working on at least following platforms:

* Android OS 2.1 - 4.1.2 (Default browser + Chrome)
* Blackberry OS 7.0 (Default browser)
* iOS 4.2.1 - 6 (Default browser)
* Mac OS X (Safari, Chrome, Firefox, Opera)
* Maemo 5.0 (Default browser)
* Meego 1.2 (Default browser)
* Symbian 3 (Default browser)
* Symbian Belle (Default browser)
* WebOS 3.0.5 (Default browser)
* Windows Phone 7.5 (Default browser)
* Windows 7 (IE9)

## Known issues

* Pages open inside iframe
* You have to write the url with `http://` prefix
* On Windows Phone 7.5 the iframe's src attribute can't be empty
* On Android 4.0.4, when using Chrome browser, a fixed positioned element inside iframe seems to prevent the whole page's scrolling
* On Android 2.1, when using default browser, the page stops auto updating after user scrolls down

## License

Licensed under the MIT license.

Copyright (c) 2012-2013 Viljami Salminen, Ben Lane

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.


## Changelog

`v0.5` (2013-03-14) - Chrome extension added. Thanks to [Ben Lane](http://twitter.com/the_ben_lane) and [e3 media](http://www.e3.co.uk).

`v0.35` (2012-11-27) - Fixes few issues on the Controller page and adds better error messages. Also some refactoring to html/css/js/php. Thank you [@Krinkle](https://github.com/Krinkle)!

`v0.31` (2012-11-19) - Adds file extension for the 'url' file which allows Remote Preview to work on ISS too. Thanks [@stowball](https://twitter.com/stowball)!

`v0.3` (2012-11-14) - Adds controller which allows Remote Preview to be controlled via a web browser. Thank you [@sherred](https://github.com/sherred) and [Device Lab Edinburgh](http://www.devicelab.org)!

`v0.21` (2012-11-13) - Adds icon for touch devices.

`v0.2` (2012-11-12) - Fixes some issues which where present in the initial release and makes Remote Preview a tad faster.

`v0.1` (2012-11-8) - Release


## Want to do a pull request?

Great! New ideas are more than welcome, but please check the [Pull Request Guidelines](https://github.com/viljamis/Remote-Preview/wiki/Pull-Request-Guidelines) first before doing so.
