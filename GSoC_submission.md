# GSoC Webservices 2019 Submission

This page to record the progress made during Google Summer of Code program in the last three months.

## About the project

This project is a continuation of the implementation of webservices in Joomla. Webservices are API layer that helps third-party developers work with a Joomla website. This can significantly improve the popularity of Joomla CMS, because it will help to create very functional and complex integrations with sites made on Joomla.

## Work done
You can get vew the **specification** of the project [here](https://github.com/joomla-projects/gsoc19_webservices/blob/master/manual/en-US/gsoc-2019/specification.md).

During the work on the project, implemented **API layers for core Joomla components** and **documentation**, implementation of **API for Weblinks extension** and **guideline** how to implement it.

##

Implemented **Core Joomla APIs**:

* com_banners
* com_categories
* com_config
* com_contact
* com_content
* com_fields
* com_installer
* com_languages
* com_menus
* com_messages
* com_modules
* com_newsfeeds
* com_plugins
* com_privacy
* com_redirect
* com_tags
* com_templates
* com_users

Code: [Pull request](https://github.com/joomla/joomla-cms/pull/26002)

To work with the Joomla API **documentation** you can get it here [here](https://docs.joomla.org/J4.x:Joomla_Core_APIs).

##

It was necessary to implement an **API for Joomla weblinks extension**, it is important to create a guideline for the community.

Code: [Pull request](https://github.com/joomla-extensions/weblinks/pull/407)

**Instructions for implementing the component** have been created and you can find it [here](https://docs.joomla.org/J4.x:Adding_an_API_to_a_Joomla_Component).

## More Work
We covered almost all the components we wanted! But three webservices remained in the work: com_finder, com_contenthistory, com_media.

To the additional task that was associated with the entity level, we did not have enough time to design a solution for it in full.

## Contribution

These are Oleksandr Samoilov’s contributions:

* [to the Joomla CMS](https://github.com/joomla-projects/gsoc19_webservices/commits/api_components?author=a-samoylov)
* [to the Weblinks extension](https://github.com/a-samoylov/weblinks/commits/webservices?author=a-samoylov)
