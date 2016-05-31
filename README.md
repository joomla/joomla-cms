Joomla! CMS™ [![Analytics](https://ga-beacon.appspot.com/UA-544070-3/joomla-cms/readme)](https://github.com/igrigorik/ga-beacon)
====================

Build Status
---------------------
Travis-CI: [![Build Status](https://travis-ci.org/joomla-projects/custom-fields.svg?branch=custom-fields)](https://travis-ci.org/joomla-projects/custom-fields)

What is this?
---------------------
This is the official custom fields repository for the Joomla 3.7 version. Please open here issues and pull requests for feedback.

This repository adds custom fields functionality to the Joomla CMS, based on the code of [DPFields](https://github.com/Digital-Peak/DPFields). It will be integrated as a horizontal component. This means, loading of fields into JForm and displaying them on the front is controlled trough the _Fields_ system plugin. Field management is done trough the com_fields component and will be integrated similar to com_categories as a new _Fields_ menu item in the articles manager.

#### Testing Instructions
Please keep in mind that this repository adds two new tables and new entries to the extension table. The following test instruction acts more as a getting started guide. How to get more information can be found at the end of this file.

##### Create a custom field
1. Log in on the back end.
2. Navigate to Content -> Articles.
3. Click on the left sidebar on Fields.
4. Click on the New button on the top.
5. Define a title and label.
6. Click the Save & Close button on the top.

##### Create an article
1. Log in on the back end.
2. Navigate to Content -> Articles.
3. Click on the New button on the top.
4. Define a title.
5. Open the Fields tab.
6. Add some text on the custom field.
7. Click the Save & Close button on the top.

##### View it on the front
1. Log in on the back end.
2. Create an article menu item in the menu manager and select the new article.
3. Go to the front site of your Joomla installation.
4. Open the article.
5. Check if the custom field is displayed.

A more detailed test instruction article with screenshots can be found on our [blog](https://joomla.digital-peak.com/blog/198-custom-fields-in-joomla-3-7). We scratched here only on the surface of com_fields. If you want to check out what is all possible with this PR should have a look on the [full documentation of DPFields](https://joomla.digital-peak.com/documentation/162-dpfields). There are many options like layout overrides, inline field rendering in the article description, categories per fields, permissions. What works for DPFields will also work for com_fields.

Copyright
---------------------
* Copyright (C) 2005 - 2016 Open Source Matters. All rights reserved.
* [Special Thanks](https://docs.joomla.org/Joomla!_Credits_and_Thanks)
* Distributed under the GNU General Public License version 2 or later
* See [License details](https://docs.joomla.org/Joomla_Licenses)
