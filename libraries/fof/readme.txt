Framework on Framework (a.k.a. Joomla! RAD layer)
================================================================================

Tip: scroll to the bottom of this document for resources

--------------------------------------------------------------------------------
What is FOF?
--------------------------------------------------------------------------------

FOF (Framework on Framework) is a rapid application development framework for
Joomla!. Unlike other frameworks, it is not standalone. It extends the Joomla!
Platform instead of replacing it, featuring its own forked and extended version
of the MVC classes, keeping a strong resemblance to the existing Joomla! MVC
API. This means that you don't have to relearn writing Joomla! extensions.
Instead, you can start being productive from the first day you start using it.
Our goal is to always support the officially supported LTS versions of Joomla!
and not break backwards compatibility without a clear deprecation and migration
path.

FOF is included in Joomla! since version 3.2 of the CMS with the intention of
serving as a rapid application (RAD) framework for extension developers. Using a
RAD framework can cut down the code you write by up to 80%, allowing you to
focus on writing features instead of reinventing the wheel over and over.

--------------------------------------------------------------------------------
Free Software means collaboration
--------------------------------------------------------------------------------

The reason of existence of FOSS (Free and Open Source Software) is collaboration
between developers. FOF is no exception; it exists because of and for the
community of Joomla! developers. It is provided free of charge and with all of
the freedoms of the GPL for you to benefit. And in true Free Software spirit,
the community aspect is very strong. Participating is easy and fun.

If you want to discuss FoF, there is a Google Groups mailing list at
https://groups.google.com/forum/?hl=en&fromgroups#!forum/frameworkonframework
This is a peer discussion group where developers working with FoF can freely
discuss.

If you have a feature proposal or have found a bug, but you're not sure how to
code it yourself, please report it on the list.

If you have a feature or bug fix patch, feel free to fork the FOF project on
GitHub (https://github.com/akeeba/fof) and send a pull request. Please remember
to describe what you intended to achieve to help the FOF maintainers review your
code faster.

--------------------------------------------------------------------------------
Resources
--------------------------------------------------------------------------------

GitHub repository:
	https://github.com/akeeba/fof

Mailing list:
	https://groups.google.com/forum/?hl=en&fromgroups#!forum/frameworkonframework

Documentation [*]:
	https://www.akeebabackup.com/documentation/fof.html

Downloads [**]:
	https://www.akeebabackup.com/download/fof.html

Example components:
	https://github.com/akeeba/todo-fof-example
	https://github.com/akeeba/contactus

Notes:
~~~~~~~~~~

*  The documentation is provisionally hosted on a third party site.

** These packages must only be installed on Joomla! versions prior to 3.2.
   They are hosted on a third party site until the end of life of Joomla! 2.5.
   After that point in time no more packages will be released and FOF will only
   be available pre-installed in Joomla!.
