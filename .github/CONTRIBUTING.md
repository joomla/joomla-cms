Contributing to the Joomla! CMS™
===============
You are welcome to submit a contribution for review and possible inclusion in the Joomla! CMS but, before it will be accepted, we ask that you follow these simple steps:

1) Open an issue on our [Issue Tracker](https://issues.joomla.org/) or here on GitHub. If you can, please provide a fix and create a pull request (PR) instead; this will automatically create an issue for you.

2) Follow the [Joomla! Coding Standards](https://joomla.github.io/coding-standards).

3) When filing an issue or opening a PR, please include a clear title and description.  The title should be a short summary of an issue and, if possible, should include a reference to an open issue.  For example, `Invalid Query in com_admin (Ref #1234)` would be sufficient.  All issues and PRs should include a description with as much detail as possible. 
If it is a PR, include what the issue is, what the PR is addressing, testing instructions and environmental information (PHP version, database driver and version, and other data you can retrieve from your site's system information view) in case the issue is specific to certain environments.  If additional information is needed, please be prepared to provide it as our community members review your submission.

4) Report security issues to the Joomla! Security Strike Team (JSST) at security@joomla.org or use the [JSST contact form](https://developer.joomla.org/contact-security-team.html). Please do not use the public tracker for security issues.

Please be patient as not all items will be tested immediately (remember, all bug testing for the Joomla! CMS is done by volunteers) and be receptive to feedback about your code.

#### Branches
PRs should usually be made to the `staging` branch as this contains the most recent version of the code.
There are other branches available which serve specific purposes.

| Branch | Purpose |
| ------ | ------- |
| staging | Current codebase. |
| master | Each commit made to staging gets tested if it passes unit tests and codestyle rules. It is then merged into master. This is done automatically. |
| 2.5.x | Branch for the Joomla 2.5.x series. Support for this version has ended, no patches are accepted here. |
| 3.6.x | Branch for the next minor Joomla version. New backward compatible features go into this branch. Commits to staging will be applied to this branch as well. |
