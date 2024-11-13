Joomla! CMS™
====================

Build Status

| Drone-CI                                                                                                                                 | AppVeyor                                                                                                                                                           | PHP                                                                           | Node                                                                                 | npm                                                                             |
|------------------------------------------------------------------------------------------------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------|-------------------------------------------------------------------------------|--------------------------------------------------------------------------------------|---------------------------------------------------------------------------------|
| [![Build Status](https://ci.joomla.org/api/badges/joomla/joomla-cms/status.svg?branch=5.3-dev)](https://ci.joomla.org/joomla/joomla-cms) | [![Build status](https://ci.appveyor.com/api/projects/status/ru6sxal8jmfckvjc/branch/5.3-dev?svg=true)](https://ci.appveyor.com/project/release-joomla/joomla-cms) | [![PHP](https://img.shields.io/badge/PHP-V8.1.0-green)](https://www.php.net/) | [![node-lts](https://img.shields.io/badge/Node-V20.0-green)](https://nodejs.org/en/) | [![npm](https://img.shields.io/badge/npm-v10.1.0-green)](https://nodejs.org/en/) |

Overview
---------------------
* This is the source of Joomla! 5.x.
* Joomla's [Official website](https://www.joomla.org).
* Joomla! 5.3 [version history](https://docs.joomla.org/Special:MyLanguage/Joomla_5.3_version_history).
* Detailed changes are in the [changelog](https://github.com/joomla/joomla-cms/commits/5.3-dev).

What is Joomla?
---------------------
* [Joomla!](https://www.joomla.org/about-joomla.html) is a **Content Management System** (CMS) which enables you to build websites and powerful online applications.
* It is a simple and powerful web server application which requires a server with PHP and either MySQL, MariaDB or PostgreSQL to run. You can find [full technical requirements here](https://downloads.joomla.org/technical-requirements).
* Joomla! is **free and Open Source software** distributed under the GNU General Public License version 2 or later.

Looking for an installable package?
---------------------
Joomla is not installable out of the box from this repository, please use:
- For the latest stable package: https://downloads.joomla.org
- For a nightly package: https://developer.joomla.org/nightly-builds.html

How to get a working installation from the source
---------------------
For detailed instructions please visit https://docs.joomla.org/Special:MyLanguage/J5.x:Setting_Up_Your_Local_Environment

You will need:
- PHP - basically the same as you need for running a Joomla Site, but you need the cli (command line interface) Version (see https://docs.joomla.org/Special:MyLanguage/Configuring_a_LAMPP_server_for_PHP_development)
- Composer - for managing Joomla's PHP Dependencies. For help installing composer please read the documentation at https://getcomposer.org/doc/00-intro.md
- Node.js - for compiling Joomla's Javascript and SASS files. For help installing Node.js please follow the instructions available on https://nodejs.org/en/
- Git - for version management. Download from here https://git-scm.com/downloads (MacOS users can also use Brew and Linux users can use the built-in package manager, eg apt, yum, etc).

**Steps to setup the local environment:**
- Clone the repository:
```bash
git clone https://github.com/joomla/joomla-cms.git
```
- Go to the joomla-cms folder:
```bash
cd joomla-cms
```
- Go to the 5.3-dev branch:
```bash
git checkout 5.3-dev
```
- Install all the needed composer packages:
```bash
composer install
```
- Install all the needed npm packages:
```bash
npm ci
```

**Things to be aware of when pulling:**
Joomla creates a cache of the namespaces of its extensions in `JOOMLA_ROOT/administrator/cache/autoload_psr4.php`. If
extensions are created, deleted or removed in git then this file needs to be recreated. You can simply delete the file
and it will be regenerated on the next call to Joomla.

Do you want to improve Joomla?
--------------------
* Where to [request a feature](https://issues.joomla.org)?
* How do you [report a bug](https://docs.joomla.org/Special:MyLanguage/Filing_bugs_and_issues) on the [Issue Tracker](https://issues.joomla.org)?
* Get Involved: Joomla! is community developed software. [Join the community](https://volunteers.joomla.org).
* Documentation for [Developers](https://docs.joomla.org/Special:MyLanguage/Portal:Developers).
* Documentation for [Web designers](https://docs.joomla.org/Special:MyLanguage/Web_designers).
* Provide a translation for Joomla: [Joomla Crowdin Project](https://joomla.crowdin.com/cms)

Copyright
---------------------
* (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
* Distributed under the GNU General Public License version 2 or later
* See [License details](https://docs.joomla.org/Special:MyLanguage/Joomla_Licenses)
