## Comments

This chapter covers inline commenting in more detail. Inline comments are all comments not included in doc blocs. The goal of in line commenting is to explain code in context. Such explanation may take many different forms.

Comments that are written in a readable and narrative style, especially when explaining a complex process, are encouraged. In general they should be placed close to the code explained rather than before the entire block of code.

Comments should be as sentence like as possible, which is to say that they should be complete and readable, not in short hand.

Comments are not a replacement for detailed doc blocks. Comments serve to explain the logic and structure of code itself rather than the use of code.

## Formatting of Comments

Comment blocks that introduce large sections of code and are more than 2 lines long should use `/* */` (C) style and should use `*` on each line with the same space/tab rules as doc blocks. If you need a large introduction consider whether this block should be separated into a method to reduce complexity and therefore providing a full docblock.

Comments should precede the code they refer to. As a corollary, comments should not be on the same line as the code to which they refer (which puts them after the code they reference). They should be on their own lines.

Don’t use a blank line between comments and the code they refer to (no space underneath a comment block).

Always have a single blank line before a comment or block of comments.

Comments should align with the code they refer to, using the same indenting as the line that follows the comment.

Comments should be indented with tabs (like code, not like doc blocks).

## Content of comments

Comments should use en-GB (See below).

Always have a space between // and the start of comment text.

New lines should always start with an upper case letter unless The line is a continuation of a complete sentence The term is code and is case sensitive.

Code that is included specifically to assure compatibility with other software (for example specific browsers or a specific version of the CMS or for backward compatibility reasons) should be clearly marked as such. If there is the intention to remove specific code at a future point, state that but do not use a deprecation tag or specify a release (this can be hard to predict).

Check spelling and grammar on all comments (see below).

Only end a line with a period if it is a full sentence.

Rather than use docblock tags use See:, Link: and Note: for comments if appropriate.

Do not use HTML in comments unless specifically related to the comment content.

Do not leave commented code unless there is a clearly explained reason for doing so. In such a case, a comment saying "Code intentionally commented" will prevent accidental removal.

Comments may include forward looking statements of how something will be extended or modified in the future, but not todos indicating that code is not complete or ready to use.

Remember that humor and sarcasm are difficult to translate.

## Acronyms

Capitalise all letters of acronyms such as HTML, XML, SQL, GMT, and UTC. This is an exception to the general use of en-GB rules.

## Common spelling and grammar errors for which to check.

Joomla contributors include many non-native speakers of en-GB which makes it understandable that there are sometimes spelling and grammar errors. At the same time, some people reading comments also are non native speakers and may even use automated translation to understand comments. This makes it very important that comments should follow proper en-GB spelling and grammar rules. Unfortunately, these can be tricky.

Wikipedia provides a good summary of common differences between en-US and en-GB. http://en.wikipedia.org/wiki/American\_and\_British\_English\_spelling\_differences Note that there are some instances where en-GB common usage (but not actual rules) varies slightly from en-AU and using en-AU is considered acceptable.

### S vs Z

en-GB most commonly uses `ise` where en-US would use `ize`, such as `normalise` instead of `normalize`. (Note that there are some exceptions to this rule and some differences between en-GB and en-AU in common usage.)

Use of apostrophes is one of the trickier parts of English.

### Lets versus let’s

Lets means permits or allows to:

```php
// This lets the user enter data
```

Let’s is a contraction for let us and means we are going to do this now:

```php
// Let's validate the field
```

### Its versus it’s

Its is the possessive form of it:

```php
// Get its ID
```

It’s is a contraction of it is

```php
// It's time to save
```

### The correct Joomla spelling of some commonly used words.

-   Dependant


