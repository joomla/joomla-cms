Contributing to the Joomla! Platform
===============
All contributions are welcome to be submitted for review for inclusion in the Joomla! Platform, but before they will be accepted, we ask that you follow these simple steps:

1) Submitted code must follow the Joomla! Code Standards
* Please use the resources at http://docs.joomla.org/Coding_style_and_standards for more information about the code standards.

2) Submitted code must not cause existing unit tests to fail
* If a change in a method causes a change in the expected behavior, the unit test should also be updated to match the change.

3) Unit tests are *highly* encouraged for all pull requests
* The Joomla Platform uses PHPUnit for its unit testing.  Please review the PHPUnit manual at http://www.phpunit.de/manual/current/en/index.html.
* Whether your pull request is a bug fix or introduces new classes or methods to the Platform, we ask that you include unit tests for your changes
* We understand that not all users submitting pull requests will be proficient with PHPUnit.  The maintainers and community as a whole are a helpful group and can help you with writing tests.
* Although bug fixes may be accepted without unit tests (so long as existing tests do not fail with your change), new classes will not be accepted without tests to validate its functionality.

4) Documentation is *highly* encouraged
* The Platform Manual is contained in the docs directory of this repo and is written in Markdown format.
* When submitting new packages, documentation will be required with your pull request.  Please use the existing documentation for examples.
* We understand that not all code is documented at this time.  Feel free to expand on the existing documentation by adding to existing chapters or submitting new chapters.

Please be patient as not all items will be tested or reviewed immediately by a Platform maintainer.  There is an automated pull request tester online at http://developer.joomla.org/pulls/ which runs the unit tests and checks the code style of every request; please use this resource to ensure your pull does not cause test failures and follows the coding standard.

Lastly, please be receptive to feedback about your change.  The maintainers and other community members may make suggestions or ask questions about your change.  This is part of the review process, and helps everyone to understand what is happening, why it is happening, and potentially optimize your code.
