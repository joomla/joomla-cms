Contributing to the Joomla! CMS™
===============
You are welcome to submit a contribution for review and possible inclusion in the Joomla! CMS but, before it will be accepted, we ask that you follow these simple guidelines:

* If you have a feature request, then please open a discussion to define the feature request and discuss possible solutions. Discussions can be converted into issues when the request is defined sufficiently that a developer can start coding the feature. In this process you will get feedback from the maintainers, if the feature is something for the Joomla core distribution or not.

* If you have found a bug, open an issue on our [Issue Tracker](https://issues.joomla.org/) or here on GitHub. If you can, please provide a fix and create a pull request (PR) instead; this will automatically create an issue for you so you do not have to create an issue, if you are creating a pull request.

* Follow the [Joomla! Coding Standards](https://manual.joomla.org/docs/get-started/codestyle) for code contributions.

* When filing an issue or opening a Pull Request(PR), please include a clear title and description. The title should be a short summary of the issue. For example, `Invalid Query in com_admin`. All issues and PRs should include a description with as much detail as possible. If it is a PR, include what the issue is, what the PR is addressing, testing instructions and environmental information (PHP version, database driver and version, and other data you can retrieve from your site's system information view) in case the issue is specific to certain environments. If additional information is needed, please be prepared to provide it as our community members review your submission.

* Report security issues to the Joomla! Security Strike Team (JSST) at security@joomla.org or use the [JSST contact form](https://developer.joomla.org/contact-security-team.html). Please do not use the public tracker for security issues. Find [here](https://github.com/joomla/joomla-cms?tab=security-ov-file#readme) more information about the [Security Policies and Procedures](https://github.com/joomla/joomla-cms?tab=security-ov-file#readme).

Please be patient as not all items will be viewed or tested immediately (remember, all bug testing for the Joomla! CMS is done by volunteers) and be receptive to feedback about your code.

#### Branches
Bug fixing PRs should be made to the `5.4-dev` branch. Merged bugfixes will be upmerged into the current branches. New features that do not break backwards compatibility should be made to the `6.1-dev`.


| Branch  | Purpose                                                                                 |
|---------|-----------------------------------------------------------------------------------------|
| 5.4-dev | Branch for the current 5.x Joomla version.                                              |
| 6.0-dev | Branch for the current 6.x Joomla version. Bugfixes only for 6.x go into this branch.   |
| 6.1-dev | Branch for the next minor 6.x Joomla version. New features have to go into this branch. |
