Contributing to the Joomla! CMS
===============
All contributions are welcome to be submitted for review for inclusion in the Joomla! CMS, but before they will be accepted, we ask that you follow these simple steps:

1) Open an issue on our [Issue Tracker](http://issues.joomla.org/) or here on GitHub. If you can provide a fix create a pull request instead, it will automatically create the issue for you.

2) Follow the [Joomla! Coding Standards](http://joomla.github.io/coding-standards)!

Please be patient as not all items will be tested immediately (remember, all bug testing for the Joomla! CMS is done by volunteers) and be receptive to feedback about your code.

#### Branches
Pull Requests should usually be made for the `staging` branch as this contains the most recent version of the code.
There are other branches available which serve specific purposes.

| Branch | Purpose |
| ------ | ------- |
| staging | Current codebase. |
| master | Each commit made to staging gets tested if it passes unit tests and codestyle rules and then merged into master. This is done automatically. |
| 2.5.x | Branch for the Joomla 2.5.x series. Currently in maintenance mode with EOL end of 2014. No new features are accepted here. |
| 3.2.x | Branch for the Joomla 3.2.x series. Currently in security mode with EOL Oct 2014. Only security issues are fixed. |
| 3.4-dev | Branch for the next minor Joomla version. New backward compatible features go into this branch. Commits to staging will be applied to this branch as well. |
