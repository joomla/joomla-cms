# Characterization tests for the current com_tags behaviour

These tests do not describe how tagging *should* work. They describe how it works today, so that a later
change to the tagging model can be reviewed by diffing the expectations: whatever a follow-up pull
request has to edit here is a behaviour it changes, and whatever it leaves alone is a behaviour it keeps.

Where the current behaviour looks wrong it is recorded as it is and marked with a `// Characterization:`
comment explaining why it looks wrong. Nothing here is a bug fix and no production file was touched.

## How to run

```
./libraries/vendor/bin/phpunit --testsuite Unit
./libraries/vendor/bin/phpunit --testsuite Integration
npx cypress run --spec tests/System/integration/site/components/com_tag/TagItemInheritance.cy.js
```

The integration tests need a database, see [tests/Integration/README.md](../../../Integration/README.md).
They add `tests/Integration/datasets/{mysqli,pgsql}/tags.sql`, which creates `#__tags`,
`#__contentitem_tag_map` and `#__content_types`.

## The groups

### Router, segments — `tests/Unit/Component/Tags/Site/Service/RouterSegmentsTest.php`

| Test | Behaviour pinned down |
|---|---|
| `testSingleTagSegmentUsesTheAliasAndNotThePath` | A tag is addressed in the URL by its bare alias, never by its path, even when it has a parent. |
| `testTagsWithTheSameAliasUnderDifferentParentsShareASegment` | The router cannot tell two equally aliased tags apart; nesting is not represented in the URL at all. |
| `testSeveralTagsProduceOneSegmentEach` | Several tags become one URL segment per tag. |
| `testSeveralSegmentsParseIntoAListOfIds` | Several segments parse back into a list of tag ids and the `tag` view. |
| `testRoundTripPreservesTheSetOfTags` | Build followed by parse addresses the same tags, but the ids come back as `id:alias` strings rather than the integers that went in. |
| `testRoundTripOfASingleNestedTag` | The same holds for a single nested tag. |
| `testUnknownAliasIsLeftForTheApplication` | An unknown alias sets no variables and is handed back to the application. |
| `testParsingStopsAtTheFirstUnknownAlias` | Parsing stops at the first unknown segment, so valid tags behind it are never looked at. |
| `testNumericSegmentsAreTakenAsIds` | Numeric segments are taken as tag ids as strings and are never validated. |
| `testCommaSeparatedSegmentIsTakenAsOneListOfIds` | A comma separated segment is stored unsplit and ends the parsing. |
| `testNumericSegmentAfterAnAliasIsNotConsumed` | Once an alias matched, a following numeric segment is no longer read as an id. |
| `testWithoutAMenuItemTheFirstSegmentIsTheView` | Without a com_tags menu item the first segment is the view. |
| `testWithoutAMenuItemALoneViewSegmentProducesOnlyTheView` | A lone view segment yields only the view. |
| `testWithoutAMenuItemTheViewIsPartOfTheSegments` | Without a menu item the view is pushed into the segments too. |
| `testMenuItemMatchingTheRequestedTagsExactlyProducesNoSegments` | A menu item that addresses exactly the requested tags absorbs them and leaves the URL empty. |
| `testMenuItemAddressingADifferentSetOfTagsRepeatsEveryTag` | As soon as one extra tag is requested, every tag is repeated in the URL. |
| `testTagMenuItemSeedsTheParsedTagList` | The tags of the menu item are merged with the tags in the URL, mixing plain ids and `id:alias` strings. |

### Router, menu item selection — `tests/Unit/Component/Tags/Site/Service/RouterMenuSelectionTest.php`

This is the area a change to the tagging model touches most, so it is pinned down in detail.

| Test | Behaviour pinned down |
|---|---|
| `testSingleMatchingMenuItemIsSelected` | The one menu item that matches is used. |
| `testMenuItemIsMatchedIndependentlyOfTheTagOrder` | Both sides are sorted, so the order the tags are requested in does not matter. |
| `testSingleTagMenuItemIsSelectedForASupersetWhateverItsMatchType` | A menu item with exactly one tag is reused for a wider request whatever its match type is, because its combination key and its per tag key are the same array key. |
| `testMultiTagMenuItemInAllModeIsNotSelectedForAPartialOverlap` | A menu item with two or more tags in "all" mode only matches its exact combination. |
| `testExactCombinationWinsOverASingleTagMenuItem` | The exact combination is looked up first, so the more specific menu item wins. |
| `testLastRegisteredMenuItemWinsForASharedTag` | When two "any" menu items claim the same tag, the one that comes back from the menu last wins. |
| `testMenuItemIsSelectedAlthoughItsTagsAreNotASubsetOfTheRequest` | An overlap of a single tag is enough; a menu item can be selected although it shows a tag that was never requested. |
| `testMenuItemWithoutAnyOverlapIsNotSelected` | No overlap means no match. |
| `testGenericTagsMenuItemIsUsedAsFallback` | A "list of all tags" menu item without a parent is the catch all. |
| `testDefaultMenuItemIsUsedWhenNothingMatches` | With nothing else to use the router falls back to the site home page, which the router itself flags as a bug to be removed in 7.0. |
| `testNoMenuItemIsSelectedUnderStrictRouting` | With strict routing on, no menu item is chosen at all. |
| `testExplicitItemidIsKept` | An Itemid that is already in the query is never replaced. |
| `testTagsViewMenuItemIsSelectedByParentId` | A tags view menu item is found through its parent tag. |
| `testTagsViewMenuItemCoversDescendantsOfItsParentTag` | A tags view menu item is expanded over the whole subtree below its parent tag. |
| `testTagsViewFallsBackToTheGenericList` | A tags view request without a matching parent falls back to the generic list. |
| `testLookupRegistersTagMenuItemsByTheirExactCombination` | The shape of the lookup table itself, including the numeric key collapse. |

### Tag table — `tests/Integration/Component/Tags/Administrator/Table/TagTableTest.php`

| Test | Behaviour pinned down |
|---|---|
| `testTagCanBeStoredUnderAnArbitraryParent` | A tag can be stored at any depth and the nested set is maintained. |
| `testSiblingsMayNotShareAnAlias` | Two tags under the same parent may not share an alias. |
| `testAliasUniquenessIsGlobalAndNotScopedToSiblings` | **The alias check is global.** Two tags under *different* parents may not share an alias either. This is the opposite of what a nested taxonomy would normally do, and it is what makes a single alias segment sufficient to identify a tag in a URL. |
| `testStoreDoesNotComposeThePath` | The table never composes the path; that is done by `TagModel::save()` calling `rebuildPath()` and `rebuild()`. |
| `testPathIsComposedFromTheAncestorAliases` | `rebuildPath()` joins the aliases of all ancestors and strips the root. |
| `testRenamingAParentAliasUpdatesTheDescendantPaths` | Renaming a parent and rebuilding updates the paths of the whole subtree. |
| `testEmptyAliasFallsBackToTheTitle` | An empty alias is derived from the title. |

### Tags helper — `tests/Integration/Libraries/Cms/Helper/TagsHelperTest.php`

| Test | Behaviour pinned down |
|---|---|
| `testGetTagTreeArrayReturnsTheWholeSubtree` | `getTagTreeArray()` answers with a tag and all of its descendants. |
| `testGetTagTreeArrayOfALeafReturnsOnlyTheLeaf` | A leaf answers with itself. |
| `testGetTagTreeArrayAppendsToTheGivenArray` | The result is appended to the array handed in and may contain duplicates. |
| `testGetItemTagsDoesNotIncludeAncestorTags` | **`getItemTags()` returns only directly assigned tags.** Tagging is not inherited from ancestors. |
| `testGetItemTagsReturnsEveryAssignedTag` | Every directly assigned tag is returned. |
| `testGetItemTagsIgnoresUnpublishedTags` | An unpublished tag is dropped. |
| `testCreateTagsFromFieldCreatesNewTagsBelowTheRoot` | A `#new#` entry always becomes a child of the root tag, so tags typed into the field are always top level. |
| `testCreateTagsFromFieldReusesAnExistingTitle` | An existing title is reused wherever in the tree it lives. |
| `testCreateTagsFromFieldReturnsNothingForAnEmptyField` | An empty field answers with `null` rather than an array. |
| `testPostStoreIsANoOpWithoutTags` | An unset `newTags` and an empty `newTags` are treated the same and touch nothing. |
| `testPostStoreProcessForwardsToPostStore` | The deprecated `postStoreProcess()` raises a deprecation, forwards, and discards the result. |
| `testGetTagItemsQueryJoinsCategories` | The query joins `#__categories` and keeps uncategorised items through an explicit `core_catid = 0` branch. |

### Tag form field — `tests/Unit/Libraries/Cms/Form/Field/TagFieldTest.php`

| Test | Behaviour pinned down |
|---|---|
| `testOptionsAreNotIndentedButCarryTheTitlePath` | In the default mode the labels are not indented, but each nested tag gets its ancestors prepended as a slash separated path of titles. |
| `testNestedModeIndentsTheLabels` | In nested mode the labels are indented with one `- ` per level. |
| `testOptionsKeepTheTagLevel` | Every option keeps the level of its tag. |
| `testOptionsAreOrderedByTheNestedSet` | The options are ordered by `lft`, that is in tree order, not alphabetically. |
| `testConfigurationDecidesTheNestedMode` | With no `mode` attribute the com_tags configuration decides, and the non nested state is reported as `null` rather than `false`. |

### Frontend tag view — `tests/System/integration/site/components/com_tag/TagItemInheritance.cy.js`

| Test | Behaviour pinned down |
|---|---|
| `can list only the items tagged with the parent tag itself` | A tag page lists only the items that carry that tag directly and never the items of its children. |
| `can list only the items tagged with the child tag itself` | The same in the other direction. |

## Notes on the test setup

The com_tags router assembles its menu item lookup inside its constructor, which needs a booted
application, the component table and the plugin table. The unit tests therefore build the router without
invoking the constructor and inject its collaborators by reflection, see `TagsRouterFixtureTrait`. That
keeps the tests free of a database and, more importantly, required no change to the router itself.
