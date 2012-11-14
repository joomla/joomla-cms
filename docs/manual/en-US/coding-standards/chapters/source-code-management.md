## Source Code Management

Before we start talking about what code should look like, it is appropriate to look at how and where the source code is stored. All serious software projects, whether driven by an Open Source community or developed within a company for proprietary purposes will manage the source code is some sort of source or version management system.

## The Joomla Platform

In April 2011 the Joomla project decided to formally split off the core engine that drives the Joomla CMS into a separate project with a separate development path called the Joomla Platform. The Joomla Platform is a PHP framework that is designed to serve as a foundation for not only web applications (like a CMS) but other types of software such as command line applications. The files that form the Joomla Platform are stored in a Distributed Version Control System (DVCS) called Git hosted at github.com

You can learn about how to get the Joomla Platform source code from the Git repository from the following page: `@todo` permalink to developer.joomla.org staging page

Because Git treats the concepts of file revision numbers differently than Subversion, the repository revision number is not required in files (that is, the `@version` tag is not necessary).

## Compliance Tool

The standards in this manual have been adopted across the Joomla project, including the Joomla Platform, the Joomla CMS and any other applications maintained by the project. These standards apply to source code, tests and (where applicable) documentation.

A custom Joomla sniff standard for PHP files is maintained by the Joomla project and available from the code repository. The Sniff is based on the standard outlined in this document. For more information about how code standards are enforced see the analysis appendix of the manual. For information on using the Sniff see the documentation stored in its repository.
