Contributing to the Joomla! CMS
===============
All contributions are welcome to be submitted for review for inclusion in the Joomla! CMS, but before they will be accepted, we ask that you follow these simple steps:

1) Open an item on the Joomlacode tracker in the appropriate area.
* CMS Bug Reports: http://joomlacode.org/gf/project/joomla/tracker/?action=TrackerItemBrowse&tracker_id=8103
* CMS Feature Requests: http://joomlacode.org/gf/project/joomla/tracker/?action=TrackerItemBrowse&tracker_id=8549

2) Follow the [Joomla! Coding Standards](http://joomla.github.io/coding-standards)!

3) After submitting the item to the Joomlacode tracker, add a link to the Joomlacode tracker item and the GitHub issue or pull request.

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
