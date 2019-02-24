Joomla! CMS™ [![Analytics](https://ga-beacon.appspot.com/UA-544070-3/joomla-cms/readme)](https://github.com/igrigorik/ga-beacon)
====================

Build Status
---------------------
| Travis-CI  | Drone-CI | AppVeyor |
| ------------- | ------------- | ------------- |
| [![Build Status](https://travis-ci.org/joomla/joomla-cms.svg?branch=staging)](https://travis-ci.org/joomla/joomla-cms)  | [![Build Status](http://213.160.72.75/api/badges/joomla/joomla-cms/status.svg)](http://213.160.72.75/joomla/joomla-cms)  | [![Build status](https://ci.appveyor.com/api/projects/status/bpcxulw6nnxlv8kb/branch/staging?svg=true)](https://ci.appveyor.com/project/joomla/joomla-cms)  |

What is this?
---------------------
* This is the source of Joomla! 4.x.
* Joomla's [Official website](https://www.joomla.org).
* Joomla! 4.0 [version history](https://docs.joomla.org/Special:MyLanguage/Joomla_4.0_version_history).
* Detailed changes are in the [changelog](https://github.com/joomla/joomla-cms/commits/4.0-dev).

What is Joomla?
---------------------
* [Joomla!](https://www.joomla.org/about-joomla.html) is a **Content Management System** (CMS) which enables you to build websites and powerful online applications.
* It is a simple and powerful web server application which requires a server with PHP and either MySQL or PostgreSQL to run. You can find [full technical requirements here](https://downloads.joomla.org/technical-requirements).
* Joomla! is **free and Open Source software** distributed under the GNU General Public License version 2 or later.

Looking for an installable package?
---------------------
Joomla is not installable out of the box from this repository, please use:
- For the latest stable package: https://downloads.joomla.org
- For a nightly package: https://developer.joomla.org/nightly-builds.html

How to get a working installation from the source
---------------------
For detailed instructions please visit https://docs.joomla.org/J4.x:Setting_Up_Your_Local_Environment

You will need:
- PHP - basically the same as you need for running a Joomla Site, but you need the cli (command line interface) Version (see https://docs.joomla.org/Configuring_a_LAMPP_server_for_PHP_development)
- Composer - for managing Joomla's PHP Dependencies. For help installing composer please read the documentation at https://getcomposer.org/doc/00-intro.md
- Node.js - for compiling Joomla's Javascript and SASS files. For help installing Node.js please follow the instructions available on https://nodejs.org/en/
- Git - for version management. Download from here https://git-scm.com/downloads (MacOS users can also use Brew and Linux users can use the built-in package manager, eg apt, yum, etc). 

**Steps to setup the local environment:**
- Clone the repository:
```bash
git clone git@github.com:joomla/joomla-cms.git
```
- Go to the joomla-cms folder:
```bash
cd joomla-cms
```
- Install all the needed composer packages:
```bash
composer install
```
- Install all the needed npm packages:
```bash
npm install
```

Do you want to improve Joomla?
--------------------
* Where to [request a feature](https://issues.joomla.org)?
* How do you [report a bug](https://docs.joomla.org/Special:MyLanguage/Filing_bugs_and_issues) on the [Issue Tracker](https://issues.joomla.org)?
* Get Involved: Joomla! is community developed software. [Join the community](https://volunteers.joomla.org).
* Documentation for [Developers](https://docs.joomla.org/Special:MyLanguage/Portal:Developers).
* Documentation for [Web designers](https://docs.joomla.org/Special:MyLanguage/Web_designers).

Copyright
---------------------
* Copyright (C) 2005 - 2018 Open Source Matters. All rights reserved.
* [Special Thanks](https://docs.joomla.org/Special:MyLanguage/Joomla!_Credits_and_Thanks)
* Distributed under the GNU General Public License version 2 or later
* See [License details](https://docs.joomla.org/Special:MyLanguage/Joomla_Licenses)
