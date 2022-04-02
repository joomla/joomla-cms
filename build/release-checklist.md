# Joomla Release Checklist
The following document is intended to be a checklist for each release for use by the release lead. It should not include detailed explanations. These belong in the Release Procedure Documentation here: https://docs.joomla.org/Joomla:Release_procedure_and_checklist.

At all stages here it is assumed you have a copy of the joomla-cms repo downloaded. Your release branch is clean and in the code snippets below that you have two remotes - an `upstream` remote that points to this repo and a `security` remote which points to the security private repository. You should also ensure all your commits and tags are signed by a GPG key that GitHub recognises.

## Checklist (Release Candidate - Preparation)

- [ ] Agree Stable Announcement URL with the marketing team
- [ ] Create Release Candidate PR for joomla/update.joomla.org
- [ ] Check with Joomla Security Strike Team for any security patches
- [ ] Ensure Release FAQ Pages have been created on Documentation Site

## Checklist (Release Candidate - Release)

- [ ] Inform CMS Release Team in Glip highlighting anything that needs specific testing
- [ ] Inform CMS Maintenance Team in Glip that branches are locked for release. You should be the only person merging code changes at this point.
- [ ] Run build/bump.php to fix the since tags then push so other branches don't have conflicts when merging in changes.
```
git checkout 3.10-dev
git fetch upstream
git pull
# Note the version here should be the same as the existing version
php build/bump.php -v 3.10.X-dev
git commit -am 'fix since tags'
git push upstream 3.10-dev
```
- [ ] Build the release
```
git checkout <branch>
git fetch upstream
git pull
php build/deleted_file_check.php --from=3.10.X
cat build/deleted_files.txt -> script.php
php build/bump.php -v 3.10.X-rc1
git commit -am 'Joomla! 3.10.X Release Candidate 1'
git tag -s 3.10.X-rc1 -m 'Joomla! 3.10.X Release Candidate 1'
php build/build.php
cd build/tmp/packages
cat build/tmp/checksums.txt
php build/bump.php -v 3.10.X-rc2-dev
git commit -am 'reset to dev'
git push upstream --tags
```
- [ ] Create the RC release on GitHub
- [ ] Upload the packages to the GitHub release
- [ ] Publish GitHub release (remember to mark it as a pre-release!)
- [ ] `git push upstream 3.10-dev`
- [ ] Trigger new nightly build: [https://build.joomla.org](https://build.joomla.org:8443/job/cms_packaging/)
- [ ] Wait for `.org Build Notifications` to report back
- [ ] Merge the [joomla/update.joomla.org PR](https://github.com/joomla/update.joomla.org/pulls)
- [ ] Wait for `.org Build Notifications` to report back
- [ ] Inform `Contact the Social Media Team`, `CMS Release Team` and `CMS Maintainers` Glip channels about the RC
- [ ] Inform Translation Teams via eMail and Forum Thread (via co-ordinator)

### Extra Steps when there are security issues

- [ ] Build the private security RC release
```
git checkout 3.10-dev
git fetch upstream
git pull
# If multiple patches repeat the next two lines until all PRs are applied
git fetch security pull/<PRNUM>/head:<new_branch>
git merge --squash <new_branch>
git commit -am 'Prepare Joomla! 3.10.X'
php build/deleted_file_check.php --from=3.10.X
cat build/deleted_files.txt -> script.php
php build/bump.php -v 3.10.X-rc1-sec
git commit -am 'Joomla! 3.10.X Release Candidate 1 with security patches'
git tag -s 3.10.X-rc1-sec -m 'Joomla! 3.10.X Release Candidate 1 with security patches'
php build/build.php
cd build/tmp/packages
cat build/tmp/checksums.txt
```
- [ ] Upload the packages to a private location and provide download links in `CMS Release Team` and `JSST - Joomla! Security Strike Team` Glip Channels

## Checklist (Stable - Preparation)
If any extra code changes have been applied since the Release Candidate consider tagging a building a fresh Release Candidate alongside the final packages to help as many people test as possible.

- [ ] Create Stable PR for joomla/update.joomla.org:
- [ ] Create Stable PR for joomla/statistics-server:
- [ ] Inform `CMS Release Team` and `CMS Maintenance Team` Glip channels the release process has started
- [ ] Ensure the deleted file list in script.php is up to date (check `deleted_files.txt`, `deleted_folders.txt` and `renamed_files.txt` when generated are in `administrator/components/com_admin/script.php`)
```
git checkout 3.10-dev
git fetch upstream
git pull
php build/deleted_file_check.php --from=3.10.X
```
- [ ] Build the release (if any new security patches have arrived - apply them following the "Extra Steps" procedure above)
```
git checkout 3.10-dev
git fetch upstream
git pull
cat build/deleted_files.txt -> script.php
php build/bump.php -v 3.10.X
git commit -am 'Joomla! 3.10.X Stable'
git tag -s 3.10.X-rc1 -m 'Joomla! 3.10.X Stable'
php build/build.php
cd build/tmp/packages
cat build/tmp/checksums.txt
php build/bump.php -v 3.10.(X+1)-dev
git commit -am 'reset to dev'
# DO NOT PUSH YET!
```
- [ ] Upload the packages to a private location and provide download links in `CMS Release Team` (and `JSST - Joomla! Security Strike Team` if a security release) Glip Channel(s)
- [ ] Upload release packages to AWS S3
- [ ] Execute the `ars-create-cms-release.php` script on downloads server (`ssh` to the server and `cd` to the web root)
```
php cli/ars-create-cms-release.php --releaseVersion=3.10.X --releaseUrl=https://joomla.org/<insert_url> --userId=<downloads_site_user_name>
```
- [ ] Update hashes in the [update.joomla.org PR](https://github.com/joomla/update.joomla.org/pulls)

### If new packages are required
If any updates to packages are required at this point due to critical issues uncovered:

- [ ] Follow the new release package following the steps previously made
- [ ] If a non-security release tag a fresh release candidate and publish to GitHub following the documented process
- [ ] Upload the packages to a private location and provide download links in `CMS Release Team` (and `JSST - Joomla! Security Strike Team` if a security release) Glip Channel(s)
- [ ] Upload release packages to AWS S3
- [ ] Execute the `ars-get-hashes.php` script on downloads server (`ssh` to the server and `cd` to the web root)
```
php cli/ars-get-hashes.php --release=<ars_release_id>
```
- [ ] Update hashes in the [update.joomla.org PR](https://github.com/joomla/update.joomla.org/pulls)

## Checklist (Stable - Release)
- [ ] Inform `CMS Release Team` and `CMS Maintenance Team` (and `JSST - Joomla! Security Strike Team` if a security release) Glip channels the release process has started
- [ ] Create release folder on the update server
- [ ] Upload packages to the update server
- [ ] `git push upstream --tags`
- [ ] Create the Stable release on [GitHub](https://github.com/joomla/joomla-cms/releases)
- [ ] Upload the packages to the GitHub release
- [ ] Publish GitHub release
- [ ] Merge the [joomla/update.joomla.org PR](https://github.com/joomla/update.joomla.org/pulls)
- [ ] Wait for `.org build notifications` to report back and validate update.joomla.org CDN Cache has flushed
- [ ] Merge the [joomla/statistics-server PR](https://github.com/joomla/statistics-server/pulls)
- [ ] Wait for `.org build notifications` to report back
- [ ] Publish the release on [downloads.joomla.org](https://downloads.joomla.org/administrator/index.php?option=com_ars&view=Releases)
- [ ] Publish security articles on [developer.joomla.org](https://developer.joomla.org/administrator/index.php?option=com_content&view=articles) if a security release
- [ ] `clear cache` on developer.joomla.org if publishing security release articles
- [ ] Publish article on [joomla.org](https://joomla.org/administrator/index.php?option=com_content&view=articles)
- [ ] `clear cache` on joomla.org
- [ ] `git push upstream 3.10-dev`
- [ ] Trigger new nightly build: [https://build.joomla.org](https://build.joomla.org:8443/job/cms_packaging/)
- [ ] Trigger new api docs build: [https://build.joomla.org](https://build.joomla.org:8443/job/api.joomla.org/)
- [ ] Wait for `.org build notifications` to report back
- [ ] Inform `Contact the Social Media Team`, `CMS Release Team` and `CMS Maintenance Team` (and `JSST - Joomla! Security Strike Team` if a security release) Glip channels the release process is complete
- [ ] Check next release date with other release leads, Create Google Calendar + meet invites
- [ ] Inform `CMS Release Team` Glip Channel of the next expected release candidate and stable release date
- [ ] Update the Joomla Roadmap on [developer.joomla.org](https://developer.joomla.org/administrator/index.php?option=com_content&view=articles)
```
Joomla 3.x / Development Status: "Current Release"
Joomla 3.x / Development Status: "Upcoming Release"
Joomla 3.10 / Schedule: Remove old stable release
Joomla 3.10 / Schedule: Set new stable with announcement URL
Joomla 3.10 / Schedule: Add next stable release date
Joomla 4.x / Development Status: "Current Release"
Joomla 4.x / Development Status: "Upcoming Release"
Joomla 4.0 / Schedule: Remove old stable release
Joomla 4.0 / Schedule: Set new stable with announcement URL
Joomla 4.0 / Schedule: Add next stable release date
```
