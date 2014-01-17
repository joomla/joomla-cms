<?php
/** Copyright 2006 ThoughtWorks, Inc
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * -----------------
 * This file has been automatically generated via XSL
 * -----------------
 *
 *
 *
 * @category   Testing
 * @package    Selenium
 * @author     Shin Ohno <ganchiku at gmail dot com>
 * @author     Bjoern Schotte <schotte at mayflower dot de>
 * @license    http://www.apache.org/licenses/LICENSE-2.0  Apache License, Version 2.0
 * @version    @package_version@
 * @see        http://www.openqa.org/selenium-rc/
 * @since      0.1
 */

/**
 * Selenium exception class
 */
require_once 'Testing/Selenium/Exception.php';

/**
 * Defines an object that runs Selenium commands.
 *
 *
 * <p>
 * <b>Element Locators</b>
 * </p><p>
 *
 * Element Locators tell Selenium which HTML element a command refers to.
 * The format of a locator is:
 * </p><p>
 *
 * <i>locatorType</i><b>=</b><i>argument</i>
 * </p>
 * <p>
 *
 * We support the following strategies for locating elements:
 *
 * </p>
 * <ul>
 *
 * <li>
 * <b>identifier</b>=<i>id</i>:
 * Select the element with the specified @id attribute. If no match is
 * found, select the first element whose @name attribute is <i>id</i>.
 * (This is normally the default; see below.)
 * </li>
 * <li>
 * <b>id</b>=<i>id</i>:
 * Select the element with the specified @id attribute.
 * </li>
 * <li>
 * <b>name</b>=<i>name</i>:
 * Select the first element with the specified @name attribute.
 *
 * <ul>
 *
 * <li>
 * username
 * </li>
 * <li>
 * name=username
 * </li>
 * </ul>
<p>
 * The name may optionally be followed by one or more <i>element-filters</i>, separated from the name by whitespace.  If the <i>filterType</i> is not specified, <b>value</b> is assumed.
 * </p>
 * <ul>
 *
 * <li>
 * name=flavour value=chocolate
 * </li>
 * </ul>
 * </li>
 * <li>
 * <b>dom</b>=<i>javascriptExpression</i>:
 *
 * Find an element by evaluating the specified string.  This allows you to traverse the HTML Document Object
 * Model using JavaScript.  Note that you must not return a value in this string; simply make it the last expression in the block.
 *
 * <ul>
 *
 * <li>
 * dom=document.forms['myForm'].myDropdown
 * </li>
 * <li>
 * dom=document.images[56]
 * </li>
 * <li>
 * dom=function foo() { return document.links[1]; }; foo();
 * </li>
 * </ul>
 * </li>
 * <li>
 * <b>xpath</b>=<i>xpathExpression</i>:
 * Locate an element using an XPath expression.
 *
 * <ul>
 *
 * <li>
 * xpath=//img[@alt='The image alt text']
 * </li>
 * <li>
 * xpath=//table[@id='table1']//tr[4]/td[2]
 * </li>
 * <li>
 * xpath=//a[contains(@href,'#id1')]
 * </li>
 * <li>
 * xpath=//a[contains(@href,'#id1')]/@class
 * </li>
 * <li>
 * xpath=(//table[@class='stylee'])//th[text()='theHeaderText']/../td
 * </li>
 * <li>
 * xpath=//input[@name='name2' and @value='yes']
 * </li>
 * <li>
 * xpath=//*[text()="right"]
 * </li>
 * </ul>
 * </li>
 * <li>
 * <b>link</b>=<i>textPattern</i>:
 * Select the link (anchor) element which contains text matching the
 * specified <i>pattern</i>.
 *
 * <ul>
 *
 * <li>
 * link=The link text
 * </li>
 * </ul>
 * </li>
 * <li>
 * <b>css</b>=<i>cssSelectorSyntax</i>:
 * Select the element using css selectors. Please refer to CSS2 selectors, CSS3 selectors for more information. You can also check the TestCssLocators test in the selenium test suite for an example of usage, which is included in the downloaded selenium core package.
 *
 * <ul>
 *
 * <li>
 * css=a[href="#id3"]
 * </li>
 * <li>
 * css=span#firstChild + span
 * </li>
 * </ul>
<p>
 * Currently the css selector locator supports all css1, css2 and css3 selectors except namespace in css3, some pseudo classes(:nth-of-type, :nth-last-of-type, :first-of-type, :last-of-type, :only-of-type, :visited, :hover, :active, :focus, :indeterminate) and pseudo elements(::first-line, ::first-letter, ::selection, ::before, ::after).
 * </p>
 * </li>
 * <li>
 * <b>ui</b>=<i>uiSpecifierString</i>:
 * Locate an element by resolving the UI specifier string to another locator, and evaluating it. See the Selenium UI-Element Reference for more details.
 *
 * <ul>
 *
 * <li>
 * ui=loginPages::loginButton()
 * </li>
 * <li>
 * ui=settingsPages::toggle(label=Hide Email)
 * </li>
 * <li>
 * ui=forumPages::postBody(index=2)//a[2]
 * </li>
 * </ul>
 * </li>
 * </ul><p>
 *
 * Without an explicit locator prefix, Selenium uses the following default
 * strategies:
 *
 * </p>
 * <ul>
 *
 * <li>
 * <b>dom</b>, for locators starting with "document."
 * </li>
 * <li>
 * <b>xpath</b>, for locators starting with "//"
 * </li>
 * <li>
 * <b>identifier</b>, otherwise
 * </li>
 * </ul>
 * <p>
 * <b>Element Filters</b>
 * </p><p>
 *
 * <p>
 * Element filters can be used with a locator to refine a list of candidate elements.  They are currently used only in the 'name' element-locator.
 * </p>
<p>
 * Filters look much like locators, ie.
 * </p>
<p>
 *
 * <i>filterType</i><b>=</b><i>argument</i>
 * </p>
 * <p>
 * Supported element-filters are:
 * </p>
<p>
 * <b>value=</b><i>valuePattern</i>
 * </p>
<p>
 *
 *
 * Matches elements based on their values.  This is particularly useful for refining a list of similarly-named toggle-buttons.
 * </p>
 * <p>
 * <b>index=</b><i>index</i>
 * </p>
<p>
 *
 *
 * Selects a single element based on its position in the list (offset from zero).
 * </p>
 *
 * </p>
 *
 * <p>
 * <b>String-match Patterns</b>
 * </p><p>
 *
 * Various Pattern syntaxes are available for matching string values:
 *
 * </p>
 * <ul>
 *
 * <li>
 * <b>glob:</b><i>pattern</i>:
 * Match a string against a "glob" (aka "wildmat") pattern. "Glob" is a
 * kind of limited regular-expression syntax typically used in command-line
 * shells. In a glob pattern, "*" represents any sequence of characters, and "?"
 * represents any single character. Glob patterns match against the entire
 * string.
 * </li>
 * <li>
 * <b>regexp:</b><i>regexp</i>:
 * Match a string using a regular-expression. The full power of JavaScript
 * regular-expressions is available.
 * </li>
 * <li>
 * <b>regexpi:</b><i>regexpi</i>:
 * Match a string using a case-insensitive regular-expression.
 * </li>
 * <li>
 * <b>exact:</b><i>string</i>:
 *
 * Match a string exactly, verbatim, without any of that fancy wildcard
 * stuff.
 * </li>
 * </ul><p>
 *
 * If no pattern prefix is specified, Selenium assumes that it's a "glob"
 * pattern.
 *
 * </p><p>
 *
 * For commands that return multiple values (such as verifySelectOptions),
 * the string being matched is a comma-separated list of the return values,
 * where both commas and backslashes in the values are backslash-escaped.
 * When providing a pattern, the optional matching syntax (i.e. glob,
 * regexp, etc.) is specified once, as usual, at the beginning of the
 * pattern.
 *
 * </p>
 *
 * @package Selenium
 * @author Shin Ohno <ganchiku at gmail dot com>
 * @author Bjoern Schotte <schotte at mayflower dot de>
 */
class Testing_Selenium
{
    /**
     * @var    string
     * @access private
     */
    private $browser;

    /**
     * @var    string
     * @access private
     */
    private $browserUrl;

    /**
     * @var    string
     * @access private
     */
    private $host;

    /**
     * @var    int
     * @access private
     */
    private $port;

    /**
     * @var    string
     * @access private
     */
    private $sessionId;

    /**
     * @var    string
     * @access private
     */
    private $timeout;

    /**
     * Constructor
     *
     * @param string $browser
     * @param string $browserUrl
     * @param string $host
     * @param int $port
     * @param int $timeout
     * @access public
     * @throws Testing_Selenium_Exception
     */
    public function __construct($browser, $browserUrl, $host = 'localhost', $port = 4444, $timeout = 30000)
    {
        $this->browser = $browser;
        $this->browserUrl = $browserUrl;
        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout;
    }

    /**
     * Run the browser and set session id.
     *
     * @access public
     * @return void
     */
    public function start()
    {
        $this->sessionId = $this->getString("getNewBrowserSession", array($this->browser, $this->browserUrl));
        return $this->sessionId;
    }

    /**
     * Close the browser and set session id null
     *
     * @access public
     * @return void
     */
    public function stop()
    {
        $this->doCommand("testComplete");
        $this->sessionId = null;
    }

    /**
     * Clicks on a link, button, checkbox or radio button. If the click action
     * causes a new page to load (like a link usually does), call
     * waitForPageToLoad.
     *
     * @access public
     * @param string $locator an element locator
     */
    public function click($locator)
    {
        $this->doCommand("click", array($locator));
    }


    /**
     * Double clicks on a link, button, checkbox or radio button. If the double click action
     * causes a new page to load (like a link usually does), call
     * waitForPageToLoad.
     *
     * @access public
     * @param string $locator an element locator
     */
    public function doubleClick($locator)
    {
        $this->doCommand("doubleClick", array($locator));
    }


    /**
     * Simulates opening the context menu for the specified element (as might happen if the user "right-clicked" on the element).
     *
     * @access public
     * @param string $locator an element locator
     */
    public function contextMenu($locator)
    {
        $this->doCommand("contextMenu", array($locator));
    }


    /**
     * Clicks on a link, button, checkbox or radio button. If the click action
     * causes a new page to load (like a link usually does), call
     * waitForPageToLoad.
     *
     * @access public
     * @param string $locator an element locator
     * @param string $coordString specifies the x,y position (i.e. - 10,20) of the mouse      event relative to the element returned by the locator.
     */
    public function clickAt($locator, $coordString)
    {
        $this->doCommand("clickAt", array($locator, $coordString));
    }


    /**
     * Doubleclicks on a link, button, checkbox or radio button. If the action
     * causes a new page to load (like a link usually does), call
     * waitForPageToLoad.
     *
     * @access public
     * @param string $locator an element locator
     * @param string $coordString specifies the x,y position (i.e. - 10,20) of the mouse      event relative to the element returned by the locator.
     */
    public function doubleClickAt($locator, $coordString)
    {
        $this->doCommand("doubleClickAt", array($locator, $coordString));
    }


    /**
     * Simulates opening the context menu for the specified element (as might happen if the user "right-clicked" on the element).
     *
     * @access public
     * @param string $locator an element locator
     * @param string $coordString specifies the x,y position (i.e. - 10,20) of the mouse      event relative to the element returned by the locator.
     */
    public function contextMenuAt($locator, $coordString)
    {
        $this->doCommand("contextMenuAt", array($locator, $coordString));
    }


    /**
     * Explicitly simulate an event, to trigger the corresponding "on<i>event</i>"
     * handler.
     *
     * @access public
     * @param string $locator an element locator
     * @param string $eventName the event name, e.g. "focus" or "blur"
     */
    public function fireEvent($locator, $eventName)
    {
        $this->doCommand("fireEvent", array($locator, $eventName));
    }


    /**
     * Move the focus to the specified element; for example, if the element is an input field, move the cursor to that field.
     *
     * @access public
     * @param string $locator an element locator
     */
    public function focus($locator)
    {
        $this->doCommand("focus", array($locator));
    }


    /**
     * Simulates a user pressing and releasing a key.
     *
     * @access public
     * @param string $locator an element locator
     * @param string $keySequence Either be a string("\" followed by the numeric keycode  of the key to be pressed, normally the ASCII value of that key), or a single  character. For example: "w", "\119".
     */
    public function keyPress($locator, $keySequence)
    {
        $this->doCommand("keyPress", array($locator, $keySequence));
    }


    /**
     * Press the shift key and hold it down until doShiftUp() is called or a new page is loaded.
     *
     * @access public
     */
    public function shiftKeyDown()
    {
        $this->doCommand("shiftKeyDown", array());
    }


    /**
     * Release the shift key.
     *
     * @access public
     */
    public function shiftKeyUp()
    {
        $this->doCommand("shiftKeyUp", array());
    }


    /**
     * Press the meta key and hold it down until doMetaUp() is called or a new page is loaded.
     *
     * @access public
     */
    public function metaKeyDown()
    {
        $this->doCommand("metaKeyDown", array());
    }


    /**
     * Release the meta key.
     *
     * @access public
     */
    public function metaKeyUp()
    {
        $this->doCommand("metaKeyUp", array());
    }


    /**
     * Press the alt key and hold it down until doAltUp() is called or a new page is loaded.
     *
     * @access public
     */
    public function altKeyDown()
    {
        $this->doCommand("altKeyDown", array());
    }


    /**
     * Release the alt key.
     *
     * @access public
     */
    public function altKeyUp()
    {
        $this->doCommand("altKeyUp", array());
    }


    /**
     * Press the control key and hold it down until doControlUp() is called or a new page is loaded.
     *
     * @access public
     */
    public function controlKeyDown()
    {
        $this->doCommand("controlKeyDown", array());
    }


    /**
     * Release the control key.
     *
     * @access public
     */
    public function controlKeyUp()
    {
        $this->doCommand("controlKeyUp", array());
    }


    /**
     * Simulates a user pressing a key (without releasing it yet).
     *
     * @access public
     * @param string $locator an element locator
     * @param string $keySequence Either be a string("\" followed by the numeric keycode  of the key to be pressed, normally the ASCII value of that key), or a single  character. For example: "w", "\119".
     */
    public function keyDown($locator, $keySequence)
    {
        $this->doCommand("keyDown", array($locator, $keySequence));
    }


    /**
     * Simulates a user releasing a key.
     *
     * @access public
     * @param string $locator an element locator
     * @param string $keySequence Either be a string("\" followed by the numeric keycode  of the key to be pressed, normally the ASCII value of that key), or a single  character. For example: "w", "\119".
     */
    public function keyUp($locator, $keySequence)
    {
        $this->doCommand("keyUp", array($locator, $keySequence));
    }


    /**
     * Simulates a user hovering a mouse over the specified element.
     *
     * @access public
     * @param string $locator an element locator
     */
    public function mouseOver($locator)
    {
        $this->doCommand("mouseOver", array($locator));
    }


    /**
     * Simulates a user moving the mouse pointer away from the specified element.
     *
     * @access public
     * @param string $locator an element locator
     */
    public function mouseOut($locator)
    {
        $this->doCommand("mouseOut", array($locator));
    }


    /**
     * Simulates a user pressing the left mouse button (without releasing it yet) on
     * the specified element.
     *
     * @access public
     * @param string $locator an element locator
     */
    public function mouseDown($locator)
    {
        $this->doCommand("mouseDown", array($locator));
    }


    /**
     * Simulates a user pressing the right mouse button (without releasing it yet) on
     * the specified element.
     *
     * @access public
     * @param string $locator an element locator
     */
    public function mouseDownRight($locator)
    {
        $this->doCommand("mouseDownRight", array($locator));
    }


    /**
     * Simulates a user pressing the left mouse button (without releasing it yet) at
     * the specified location.
     *
     * @access public
     * @param string $locator an element locator
     * @param string $coordString specifies the x,y position (i.e. - 10,20) of the mouse      event relative to the element returned by the locator.
     */
    public function mouseDownAt($locator, $coordString)
    {
        $this->doCommand("mouseDownAt", array($locator, $coordString));
    }


    /**
     * Simulates a user pressing the right mouse button (without releasing it yet) at
     * the specified location.
     *
     * @access public
     * @param string $locator an element locator
     * @param string $coordString specifies the x,y position (i.e. - 10,20) of the mouse      event relative to the element returned by the locator.
     */
    public function mouseDownRightAt($locator, $coordString)
    {
        $this->doCommand("mouseDownRightAt", array($locator, $coordString));
    }


    /**
     * Simulates the event that occurs when the user releases the mouse button (i.e., stops
     * holding the button down) on the specified element.
     *
     * @access public
     * @param string $locator an element locator
     */
    public function mouseUp($locator)
    {
        $this->doCommand("mouseUp", array($locator));
    }


    /**
     * Simulates the event that occurs when the user releases the right mouse button (i.e., stops
     * holding the button down) on the specified element.
     *
     * @access public
     * @param string $locator an element locator
     */
    public function mouseUpRight($locator)
    {
        $this->doCommand("mouseUpRight", array($locator));
    }


    /**
     * Simulates the event that occurs when the user releases the mouse button (i.e., stops
     * holding the button down) at the specified location.
     *
     * @access public
     * @param string $locator an element locator
     * @param string $coordString specifies the x,y position (i.e. - 10,20) of the mouse      event relative to the element returned by the locator.
     */
    public function mouseUpAt($locator, $coordString)
    {
        $this->doCommand("mouseUpAt", array($locator, $coordString));
    }


    /**
     * Simulates the event that occurs when the user releases the right mouse button (i.e., stops
     * holding the button down) at the specified location.
     *
     * @access public
     * @param string $locator an element locator
     * @param string $coordString specifies the x,y position (i.e. - 10,20) of the mouse      event relative to the element returned by the locator.
     */
    public function mouseUpRightAt($locator, $coordString)
    {
        $this->doCommand("mouseUpRightAt", array($locator, $coordString));
    }


    /**
     * Simulates a user pressing the mouse button (without releasing it yet) on
     * the specified element.
     *
     * @access public
     * @param string $locator an element locator
     */
    public function mouseMove($locator)
    {
        $this->doCommand("mouseMove", array($locator));
    }


    /**
     * Simulates a user pressing the mouse button (without releasing it yet) on
     * the specified element.
     *
     * @access public
     * @param string $locator an element locator
     * @param string $coordString specifies the x,y position (i.e. - 10,20) of the mouse      event relative to the element returned by the locator.
     */
    public function mouseMoveAt($locator, $coordString)
    {
        $this->doCommand("mouseMoveAt", array($locator, $coordString));
    }


    /**
     * Sets the value of an input field, as though you typed it in.
     *
     * <p>
     * Can also be used to set the value of combo boxes, check boxes, etc. In these cases,
     * value should be the value of the option selected, not the visible text.
     * </p>
     *
     * @access public
     * @param string $locator an element locator
     * @param string $value the value to type
     */
    public function type($locator, $value)
    {
        $this->doCommand("type", array($locator, $value));
    }


    /**
     * Simulates keystroke events on the specified element, as though you typed the value key-by-key.
     *
     * <p>
     * This is a convenience method for calling keyDown, keyUp, keyPress for every character in the specified string;
     * this is useful for dynamic UI widgets (like auto-completing combo boxes) that require explicit key events.
     * </p><p>
     * Unlike the simple "type" command, which forces the specified value into the page directly, this command
     * may or may not have any visible effect, even in cases where typing keys would normally have a visible effect.
     * For example, if you use "typeKeys" on a form element, you may or may not see the results of what you typed in
     * the field.
     * </p><p>
     * In some cases, you may need to use the simple "type" command to set the value of the field and then the "typeKeys" command to
     * send the keystroke events corresponding to what you just typed.
     * </p>
     *
     * @access public
     * @param string $locator an element locator
     * @param string $value the value to type
     */
    public function typeKeys($locator, $value)
    {
        $this->doCommand("typeKeys", array($locator, $value));
    }


    /**
     * Set execution speed (i.e., set the millisecond length of a delay which will follow each selenium operation).  By default, there is no such delay, i.e.,
     * the delay is 0 milliseconds.
     *
     * @access public
     * @param string $value the number of milliseconds to pause after operation
     */
    public function setSpeed($value)
    {
        $this->doCommand("setSpeed", array($value));
    }


    /**
     * Get execution speed (i.e., get the millisecond length of the delay following each selenium operation).  By default, there is no such delay, i.e.,
     * the delay is 0 milliseconds.
     *
     * See also setSpeed.
     *
     * @access public
     * @return string the execution speed in milliseconds.
     */
    public function getSpeed()
    {
        return $this->getString("getSpeed", array());
    }


    /**
     * Check a toggle-button (checkbox/radio)
     *
     * @access public
     * @param string $locator an element locator
     */
    public function check($locator)
    {
        $this->doCommand("check", array($locator));
    }


    /**
     * Uncheck a toggle-button (checkbox/radio)
     *
     * @access public
     * @param string $locator an element locator
     */
    public function uncheck($locator)
    {
        $this->doCommand("uncheck", array($locator));
    }


    /**
     * Select an option from a drop-down using an option locator.
     *
     * <p>
     *
     * Option locators provide different ways of specifying options of an HTML
     * Select element (e.g. for selecting a specific option, or for asserting
     * that the selected option satisfies a specification). There are several
     * forms of Select Option Locator.
     *
     * </p>
     * <ul>
     *
     * <li>
     * <b>label</b>=<i>labelPattern</i>:
     * matches options based on their labels, i.e. the visible text. (This
     * is the default.)
     *
     * <ul>
     *
     * <li>
     * label=regexp:^[Oo]ther
     * </li>
     * </ul>
     * </li>
     * <li>
     * <b>value</b>=<i>valuePattern</i>:
     * matches options based on their values.
     *
     * <ul>
     *
     * <li>
     * value=other
     * </li>
     * </ul>
     * </li>
     * <li>
     * <b>id</b>=<i>id</i>:
     *
     * matches options based on their ids.
     *
     * <ul>
     *
     * <li>
     * id=option1
     * </li>
     * </ul>
     * </li>
     * <li>
     * <b>index</b>=<i>index</i>:
     * matches an option based on its index (offset from zero).
     *
     * <ul>
     *
     * <li>
     * index=2
     * </li>
     * </ul>
     * </li>
     * </ul><p>
     *
     * If no option locator prefix is provided, the default behaviour is to match on <b>label</b>.
     *
     * </p>
     *
     * @access public
     * @param string $selectLocator an element locator identifying a drop-down menu
     * @param string $optionLocator an option locator (a label by default)
     */
    public function select($selectLocator, $optionLocator)
    {
        $this->doCommand("select", array($selectLocator, $optionLocator));
    }


    /**
     * Add a selection to the set of selected options in a multi-select element using an option locator.
     *
     * @see #doSelect for details of option locators
     *
     * @access public
     * @param string $locator an element locator identifying a multi-select box
     * @param string $optionLocator an option locator (a label by default)
     */
    public function addSelection($locator, $optionLocator)
    {
        $this->doCommand("addSelection", array($locator, $optionLocator));
    }


    /**
     * Remove a selection from the set of selected options in a multi-select element using an option locator.
     *
     * @see #doSelect for details of option locators
     *
     * @access public
     * @param string $locator an element locator identifying a multi-select box
     * @param string $optionLocator an option locator (a label by default)
     */
    public function removeSelection($locator, $optionLocator)
    {
        $this->doCommand("removeSelection", array($locator, $optionLocator));
    }


    /**
     * Unselects all of the selected options in a multi-select element.
     *
     * @access public
     * @param string $locator an element locator identifying a multi-select box
     */
    public function removeAllSelections($locator)
    {
        $this->doCommand("removeAllSelections", array($locator));
    }


    /**
     * Submit the specified form. This is particularly useful for forms without
     * submit buttons, e.g. single-input "Search" forms.
     *
     * @access public
     * @param string $formLocator an element locator for the form you want to submit
     */
    public function submit($formLocator)
    {
        $this->doCommand("submit", array($formLocator));
    }


    /**
     * Opens an URL in the test frame. This accepts both relative and absolute
     * URLs.
     *
     * The "open" command waits for the page to load before proceeding,
     * ie. the "AndWait" suffix is implicit.
     *
     * <i>Note</i>: The URL must be on the same domain as the runner HTML
     * due to security restrictions in the browser (Same Origin Policy). If you
     * need to open an URL on another domain, use the Selenium Server to start a
     * new browser session on that domain.
     *
     * @access public
     * @param string $url the URL to open; may be relative or absolute
     */
    public function open($url)
    {
        $this->doCommand("open", array($url));
    }


    /**
     * Opens a popup window (if a window with that ID isn't already open).
     * After opening the window, you'll need to select it using the selectWindow
     * command.
     *
     * <p>
     * This command can also be a useful workaround for bug SEL-339.  In some cases, Selenium will be unable to intercept a call to window.open (if the call occurs during or before the "onLoad" event, for example).
     * In those cases, you can force Selenium to notice the open window's name by using the Selenium openWindow command, using
     * an empty (blank) url, like this: openWindow("", "myFunnyWindow").
     * </p>
     *
     * @access public
     * @param string $url the URL to open, which can be blank
     * @param string $windowID the JavaScript window ID of the window to select
     */
    public function openWindow($url, $windowID)
    {
        $this->doCommand("openWindow", array($url, $windowID));
    }


    /**
     * Selects a popup window using a window locator; once a popup window has been selected, all
     * commands go to that window. To select the main window again, use null
     * as the target.
     *
     * <p>
     *
     *
     * Window locators provide different ways of specifying the window object:
     * by title, by internal JavaScript "name," or by JavaScript variable.
     *
     * </p>
     * <ul>
     *
     * <li>
     * <b>title</b>=<i>My Special Window</i>:
     * Finds the window using the text that appears in the title bar.  Be careful;
     * two windows can share the same title.  If that happens, this locator will
     * just pick one.
     *
     * </li>
     * <li>
     * <b>name</b>=<i>myWindow</i>:
     * Finds the window using its internal JavaScript "name" property.  This is the second
     * parameter "windowName" passed to the JavaScript method window.open(url, windowName, windowFeatures, replaceFlag)
     * (which Selenium intercepts).
     *
     * </li>
     * <li>
     * <b>var</b>=<i>variableName</i>:
     * Some pop-up windows are unnamed (anonymous), but are associated with a JavaScript variable name in the current
     * application window, e.g. "window.foo = window.open(url);".  In those cases, you can open the window using
     * "var=foo".
     *
     * </li>
     * </ul><p>
     *
     * If no window locator prefix is provided, we'll try to guess what you mean like this:
     * </p><p>
     * 1.) if windowID is null, (or the string "null") then it is assumed the user is referring to the original window instantiated by the browser).
     * </p><p>
     * 2.) if the value of the "windowID" parameter is a JavaScript variable name in the current application window, then it is assumed
     * that this variable contains the return value from a call to the JavaScript window.open() method.
     * </p><p>
     * 3.) Otherwise, selenium looks in a hash it maintains that maps string names to window "names".
     * </p><p>
     * 4.) If <i>that</i> fails, we'll try looping over all of the known windows to try to find the appropriate "title".
     * Since "title" is not necessarily unique, this may have unexpected behavior.
     * </p><p>
     * If you're having trouble figuring out the name of a window that you want to manipulate, look at the Selenium log messages
     * which identify the names of windows created via window.open (and therefore intercepted by Selenium).  You will see messages
     * like the following for each window as it is opened:
     * </p><p>
     * <code>debug: window.open call intercepted; window ID (which you can use with selectWindow()) is "myNewWindow"</code>
     * </p><p>
     * In some cases, Selenium will be unable to intercept a call to window.open (if the call occurs during or before the "onLoad" event, for example).
     * (This is bug SEL-339.)  In those cases, you can force Selenium to notice the open window's name by using the Selenium openWindow command, using
     * an empty (blank) url, like this: openWindow("", "myFunnyWindow").
     * </p>
     *
     * @access public
     * @param string $windowID the JavaScript window ID of the window to select
     */
    public function selectWindow($windowID)
    {
        $this->doCommand("selectWindow", array($windowID));
    }


    /**
     * Simplifies the process of selecting a popup window (and does not offer
     * functionality beyond what <code>selectWindow()</code> already provides).
     *
     * <ul>
     *
     * <li>
     * If <code>windowID</code> is either not specified, or specified as
     * "null", the first non-top window is selected. The top window is the one
     * that would be selected by <code>selectWindow()</code> without providing a
     * <code>windowID</code> . This should not be used when more than one popup
     * window is in play.
     * </li>
     * <li>
     * Otherwise, the window will be looked up considering
     * <code>windowID</code> as the following in order: 1) the "name" of the
     * window, as specified to <code>window.open()</code>; 2) a javascript
     * variable which is a reference to a window; and 3) the title of the
     * window. This is the same ordered lookup performed by
     * <code>selectWindow</code> .
     * </li>
     * </ul>
     *
     * @access public
     * @param string $windowID an identifier for the popup window, which can take on a                  number of different meanings
     */
    public function selectPopUp($windowID)
    {
        $this->doCommand("selectPopUp", array($windowID));
    }


    /**
     * Selects the main window. Functionally equivalent to using
     * <code>selectWindow()</code> and specifying no value for
     * <code>windowID</code>.
     *
     * @access public
     */
    public function deselectPopUp()
    {
        $this->doCommand("deselectPopUp", array());
    }


    /**
     * Selects a frame within the current window.  (You may invoke this command
     * multiple times to select nested frames.)  To select the parent frame, use
     * "relative=parent" as a locator; to select the top frame, use "relative=top".
     * You can also select a frame by its 0-based index number; select the first frame with
     * "index=0", or the third frame with "index=2".
     *
     * <p>
     * You may also use a DOM expression to identify the frame you want directly,
     * like this: <code>dom=frames["main"].frames["subframe"]</code>
     * </p>
     *
     * @access public
     * @param string $locator an element locator identifying a frame or iframe
     */
    public function selectFrame($locator)
    {
        $this->doCommand("selectFrame", array($locator));
    }


    /**
     * Determine whether current/locator identify the frame containing this running code.
     *
     * <p>
     * This is useful in proxy injection mode, where this code runs in every
     * browser frame and window, and sometimes the selenium server needs to identify
     * the "current" frame.  In this case, when the test calls selectFrame, this
     * routine is called for each frame to figure out which one has been selected.
     * The selected frame will return true, while all others will return false.
     * </p>
     *
     * @access public
     * @param string $currentFrameString starting frame
     * @param string $target new frame (which might be relative to the current one)
     * @return boolean true if the new frame is this code's window
     */
    public function getWhetherThisFrameMatchFrameExpression($currentFrameString, $target)
    {
        return $this->getBoolean("getWhetherThisFrameMatchFrameExpression", array($currentFrameString, $target));
    }


    /**
     * Determine whether currentWindowString plus target identify the window containing this running code.
     *
     * <p>
     * This is useful in proxy injection mode, where this code runs in every
     * browser frame and window, and sometimes the selenium server needs to identify
     * the "current" window.  In this case, when the test calls selectWindow, this
     * routine is called for each window to figure out which one has been selected.
     * The selected window will return true, while all others will return false.
     * </p>
     *
     * @access public
     * @param string $currentWindowString starting window
     * @param string $target new window (which might be relative to the current one, e.g., "_parent")
     * @return boolean true if the new window is this code's window
     */
    public function getWhetherThisWindowMatchWindowExpression($currentWindowString, $target)
    {
        return $this->getBoolean("getWhetherThisWindowMatchWindowExpression", array($currentWindowString, $target));
    }


    /**
     * Waits for a popup window to appear and load up.
     *
     * @access public
     * @param string $windowID the JavaScript window "name" of the window that will appear (not the text of the title bar)                 If unspecified, or specified as "null", this command will                 wait for the first non-top window to appear (don't rely                 on this if you are working with multiple popups                 simultaneously).
     * @param string $timeout a timeout in milliseconds, after which the action will return with an error.                If this value is not specified, the default Selenium                timeout will be used. See the setTimeout() command.
     */
    public function waitForPopUp($windowID, $timeout)
    {
        $this->doCommand("waitForPopUp", array($windowID, $timeout));
    }


    /**
     * <p>
     *
     * By default, Selenium's overridden window.confirm() function will
     * return true, as if the user had manually clicked OK; after running
     * this command, the next call to confirm() will return false, as if
     * the user had clicked Cancel.  Selenium will then resume using the
     * default behavior for future confirmations, automatically returning
     * true (OK) unless/until you explicitly call this command for each
     * confirmation.
     *
     * </p><p>
     *
     * Take note - every time a confirmation comes up, you must
     * consume it with a corresponding getConfirmation, or else
     * the next selenium operation will fail.
     *
     * </p>
     *
     * @access public
     */
    public function chooseCancelOnNextConfirmation()
    {
        $this->doCommand("chooseCancelOnNextConfirmation", array());
    }


    /**
     * <p>
     *
     * Undo the effect of calling chooseCancelOnNextConfirmation.  Note
     * that Selenium's overridden window.confirm() function will normally automatically
     * return true, as if the user had manually clicked OK, so you shouldn't
     * need to use this command unless for some reason you need to change
     * your mind prior to the next confirmation.  After any confirmation, Selenium will resume using the
     * default behavior for future confirmations, automatically returning
     * true (OK) unless/until you explicitly call chooseCancelOnNextConfirmation for each
     * confirmation.
     *
     * </p><p>
     *
     * Take note - every time a confirmation comes up, you must
     * consume it with a corresponding getConfirmation, or else
     * the next selenium operation will fail.
     *
     * </p>
     *
     * @access public
     */
    public function chooseOkOnNextConfirmation()
    {
        $this->doCommand("chooseOkOnNextConfirmation", array());
    }


    /**
     * Instructs Selenium to return the specified answer string in response to
     * the next JavaScript prompt [window.prompt()].
     *
     * @access public
     * @param string $answer the answer to give in response to the prompt pop-up
     */
    public function answerOnNextPrompt($answer)
    {
        $this->doCommand("answerOnNextPrompt", array($answer));
    }


    /**
     * Simulates the user clicking the "back" button on their browser.
     *
     * @access public
     */
    public function goBack()
    {
        $this->doCommand("goBack", array());
    }


    /**
     * Simulates the user clicking the "Refresh" button on their browser.
     *
     * @access public
     */
    public function refresh()
    {
        $this->doCommand("refresh", array());
    }


    /**
     * Simulates the user clicking the "close" button in the titlebar of a popup
     * window or tab.
     *
     * @access public
     */
    public function close()
    {
        $this->doCommand("close", array());
    }


    /**
     * Has an alert occurred?
     *
     * <p>
     *
     * This function never throws an exception
     *
     * </p>
     *
     * @access public
     * @return boolean true if there is an alert
     */
    public function isAlertPresent()
    {
        return $this->getBoolean("isAlertPresent", array());
    }


    /**
     * Has a prompt occurred?
     *
     * <p>
     *
     * This function never throws an exception
     *
     * </p>
     *
     * @access public
     * @return boolean true if there is a pending prompt
     */
    public function isPromptPresent()
    {
        return $this->getBoolean("isPromptPresent", array());
    }


    /**
     * Has confirm() been called?
     *
     * <p>
     *
     * This function never throws an exception
     *
     * </p>
     *
     * @access public
     * @return boolean true if there is a pending confirmation
     */
    public function isConfirmationPresent()
    {
        return $this->getBoolean("isConfirmationPresent", array());
    }


    /**
     * Retrieves the message of a JavaScript alert generated during the previous action, or fail if there were no alerts.
     *
     * <p>
     * Getting an alert has the same effect as manually clicking OK. If an
     * alert is generated but you do not consume it with getAlert, the next Selenium action
     * will fail.
     * </p><p>
     * Under Selenium, JavaScript alerts will NOT pop up a visible alert
     * dialog.
     * </p><p>
     * Selenium does NOT support JavaScript alerts that are generated in a
     * page's onload() event handler. In this case a visible dialog WILL be
     * generated and Selenium will hang until someone manually clicks OK.
     * </p>
     *
     * @access public
     * @return string The message of the most recent JavaScript alert
     */
    public function getAlert()
    {
        return $this->getString("getAlert", array());
    }


    /**
     * Retrieves the message of a JavaScript confirmation dialog generated during
     * the previous action.
     *
     * <p>
     *
     * By default, the confirm function will return true, having the same effect
     * as manually clicking OK. This can be changed by prior execution of the
     * chooseCancelOnNextConfirmation command.
     *
     * </p><p>
     *
     * If an confirmation is generated but you do not consume it with getConfirmation,
     * the next Selenium action will fail.
     *
     * </p><p>
     *
     * NOTE: under Selenium, JavaScript confirmations will NOT pop up a visible
     * dialog.
     *
     * </p><p>
     *
     * NOTE: Selenium does NOT support JavaScript confirmations that are
     * generated in a page's onload() event handler. In this case a visible
     * dialog WILL be generated and Selenium will hang until you manually click
     * OK.
     *
     * </p>
     *
     * @access public
     * @return string the message of the most recent JavaScript confirmation dialog
     */
    public function getConfirmation()
    {
        return $this->getString("getConfirmation", array());
    }


    /**
     * Retrieves the message of a JavaScript question prompt dialog generated during
     * the previous action.
     *
     * <p>
     * Successful handling of the prompt requires prior execution of the
     * answerOnNextPrompt command. If a prompt is generated but you
     * do not get/verify it, the next Selenium action will fail.
     * </p><p>
     * NOTE: under Selenium, JavaScript prompts will NOT pop up a visible
     * dialog.
     * </p><p>
     * NOTE: Selenium does NOT support JavaScript prompts that are generated in a
     * page's onload() event handler. In this case a visible dialog WILL be
     * generated and Selenium will hang until someone manually clicks OK.
     * </p>
     *
     * @access public
     * @return string the message of the most recent JavaScript question prompt
     */
    public function getPrompt()
    {
        return $this->getString("getPrompt", array());
    }


    /**
     * Gets the absolute URL of the current page.
     *
     * @access public
     * @return string the absolute URL of the current page
     */
    public function getLocation()
    {
        return $this->getString("getLocation", array());
    }


    /**
     * Gets the title of the current page.
     *
     * @access public
     * @return string the title of the current page
     */
    public function getTitle()
    {
        return $this->getString("getTitle", array());
    }


    /**
     * Gets the entire text of the page.
     *
     * @access public
     * @return string the entire text of the page
     */
    public function getBodyText()
    {
        return $this->getString("getBodyText", array());
    }


    /**
     * Gets the (whitespace-trimmed) value of an input field (or anything else with a value parameter).
     * For checkbox/radio elements, the value will be "on" or "off" depending on
     * whether the element is checked or not.
     *
     * @access public
     * @param string $locator an element locator
     * @return string the element value, or "on/off" for checkbox/radio elements
     */
    public function getValue($locator)
    {
        return $this->getString("getValue", array($locator));
    }


    /**
     * Gets the text of an element. This works for any element that contains
     * text. This command uses either the textContent (Mozilla-like browsers) or
     * the innerText (IE-like browsers) of the element, which is the rendered
     * text shown to the user.
     *
     * @access public
     * @param string $locator an element locator
     * @return string the text of the element
     */
    public function getText($locator)
    {
        return $this->getString("getText", array($locator));
    }


    /**
     * Briefly changes the backgroundColor of the specified element yellow.  Useful for debugging.
     *
     * @access public
     * @param string $locator an element locator
     */
    public function highlight($locator)
    {
        $this->doCommand("highlight", array($locator));
    }


    /**
     * Gets the result of evaluating the specified JavaScript snippet.  The snippet may
     * have multiple lines, but only the result of the last line will be returned.
     *
     * <p>
     * Note that, by default, the snippet will run in the context of the "selenium"
     * object itself, so <code>this</code> will refer to the Selenium object.  Use <code>window</code> to
     * refer to the window of your application, e.g. <code>window.document.getElementById('foo')</code>
     * </p><p>
     * If you need to use
     * a locator to refer to a single element in your application page, you can
     * use <code>this.browserbot.findElement("id=foo")</code> where "id=foo" is your locator.
     * </p>
     *
     * @access public
     * @param string $script the JavaScript snippet to run
     * @return string the results of evaluating the snippet
     */
    public function getEval($script)
    {
        return $this->getString("getEval", array($script));
    }


    /**
     * Gets whether a toggle-button (checkbox/radio) is checked.  Fails if the specified element doesn't exist or isn't a toggle-button.
     *
     * @access public
     * @param string $locator an element locator pointing to a checkbox or radio button
     * @return boolean true if the checkbox is checked, false otherwise
     */
    public function isChecked($locator)
    {
        return $this->getBoolean("isChecked", array($locator));
    }


    /**
     * Gets the text from a cell of a table. The cellAddress syntax
     * tableLocator.row.column, where row and column start at 0.
     *
     * @access public
     * @param string $tableCellAddress a cell address, e.g. "foo.1.4"
     * @return string the text from the specified cell
     */
    public function getTable($tableCellAddress)
    {
        return $this->getString("getTable", array($tableCellAddress));
    }


    /**
     * Gets all option labels (visible text) for selected options in the specified select or multi-select element.
     *
     * @access public
     * @param string $selectLocator an element locator identifying a drop-down menu
     * @return array an array of all selected option labels in the specified select drop-down
     */
    public function getSelectedLabels($selectLocator)
    {
        return $this->getStringArray("getSelectedLabels", array($selectLocator));
    }


    /**
     * Gets option label (visible text) for selected option in the specified select element.
     *
     * @access public
     * @param string $selectLocator an element locator identifying a drop-down menu
     * @return string the selected option label in the specified select drop-down
     */
    public function getSelectedLabel($selectLocator)
    {
        return $this->getString("getSelectedLabel", array($selectLocator));
    }


    /**
     * Gets all option values (value attributes) for selected options in the specified select or multi-select element.
     *
     * @access public
     * @param string $selectLocator an element locator identifying a drop-down menu
     * @return array an array of all selected option values in the specified select drop-down
     */
    public function getSelectedValues($selectLocator)
    {
        return $this->getStringArray("getSelectedValues", array($selectLocator));
    }


    /**
     * Gets option value (value attribute) for selected option in the specified select element.
     *
     * @access public
     * @param string $selectLocator an element locator identifying a drop-down menu
     * @return string the selected option value in the specified select drop-down
     */
    public function getSelectedValue($selectLocator)
    {
        return $this->getString("getSelectedValue", array($selectLocator));
    }


    /**
     * Gets all option indexes (option number, starting at 0) for selected options in the specified select or multi-select element.
     *
     * @access public
     * @param string $selectLocator an element locator identifying a drop-down menu
     * @return array an array of all selected option indexes in the specified select drop-down
     */
    public function getSelectedIndexes($selectLocator)
    {
        return $this->getStringArray("getSelectedIndexes", array($selectLocator));
    }


    /**
     * Gets option index (option number, starting at 0) for selected option in the specified select element.
     *
     * @access public
     * @param string $selectLocator an element locator identifying a drop-down menu
     * @return string the selected option index in the specified select drop-down
     */
    public function getSelectedIndex($selectLocator)
    {
        return $this->getString("getSelectedIndex", array($selectLocator));
    }


    /**
     * Gets all option element IDs for selected options in the specified select or multi-select element.
     *
     * @access public
     * @param string $selectLocator an element locator identifying a drop-down menu
     * @return array an array of all selected option IDs in the specified select drop-down
     */
    public function getSelectedIds($selectLocator)
    {
        return $this->getStringArray("getSelectedIds", array($selectLocator));
    }


    /**
     * Gets option element ID for selected option in the specified select element.
     *
     * @access public
     * @param string $selectLocator an element locator identifying a drop-down menu
     * @return string the selected option ID in the specified select drop-down
     */
    public function getSelectedId($selectLocator)
    {
        return $this->getString("getSelectedId", array($selectLocator));
    }


    /**
     * Determines whether some option in a drop-down menu is selected.
     *
     * @access public
     * @param string $selectLocator an element locator identifying a drop-down menu
     * @return boolean true if some option has been selected, false otherwise
     */
    public function isSomethingSelected($selectLocator)
    {
        return $this->getBoolean("isSomethingSelected", array($selectLocator));
    }


    /**
     * Gets all option labels in the specified select drop-down.
     *
     * @access public
     * @param string $selectLocator an element locator identifying a drop-down menu
     * @return array an array of all option labels in the specified select drop-down
     */
    public function getSelectOptions($selectLocator)
    {
        return $this->getStringArray("getSelectOptions", array($selectLocator));
    }


    /**
     * Gets the value of an element attribute. The value of the attribute may
     * differ across browsers (this is the case for the "style" attribute, for
     * example).
     *
     * @access public
     * @param string $attributeLocator an element locator followed by an @ sign and then the name of the attribute, e.g. "foo@bar"
     * @return string the value of the specified attribute
     */
    public function getAttribute($attributeLocator)
    {
        return $this->getString("getAttribute", array($attributeLocator));
    }


    /**
     * Verifies that the specified text pattern appears somewhere on the rendered page shown to the user.
     *
     * @access public
     * @param string $pattern a pattern to match with the text of the page
     * @return boolean true if the pattern matches the text, false otherwise
     */
    public function isTextPresent($pattern)
    {
        return $this->getBoolean("isTextPresent", array($pattern));
    }


    /**
     * Verifies that the specified element is somewhere on the page.
     *
     * @access public
     * @param string $locator an element locator
     * @return boolean true if the element is present, false otherwise
     */
    public function isElementPresent($locator)
    {
        return $this->getBoolean("isElementPresent", array($locator));
    }


    /**
     * Determines if the specified element is visible. An
     * element can be rendered invisible by setting the CSS "visibility"
     * property to "hidden", or the "display" property to "none", either for the
     * element itself or one if its ancestors.  This method will fail if
     * the element is not present.
     *
     * @access public
     * @param string $locator an element locator
     * @return boolean true if the specified element is visible, false otherwise
     */
    public function isVisible($locator)
    {
        return $this->getBoolean("isVisible", array($locator));
    }


    /**
     * Determines whether the specified input element is editable, ie hasn't been disabled.
     * This method will fail if the specified element isn't an input element.
     *
     * @access public
     * @param string $locator an element locator
     * @return boolean true if the input element is editable, false otherwise
     */
    public function isEditable($locator)
    {
        return $this->getBoolean("isEditable", array($locator));
    }


    /**
     * Returns the IDs of all buttons on the page.
     *
     * <p>
     * If a given button has no ID, it will appear as "" in this array.
     * </p>
     *
     * @access public
     * @return array the IDs of all buttons on the page
     */
    public function getAllButtons()
    {
        return $this->getStringArray("getAllButtons", array());
    }


    /**
     * Returns the IDs of all links on the page.
     *
     * <p>
     * If a given link has no ID, it will appear as "" in this array.
     * </p>
     *
     * @access public
     * @return array the IDs of all links on the page
     */
    public function getAllLinks()
    {
        return $this->getStringArray("getAllLinks", array());
    }


    /**
     * Returns the IDs of all input fields on the page.
     *
     * <p>
     * If a given field has no ID, it will appear as "" in this array.
     * </p>
     *
     * @access public
     * @return array the IDs of all field on the page
     */
    public function getAllFields()
    {
        return $this->getStringArray("getAllFields", array());
    }


    /**
     * Returns an array of JavaScript property values from all known windows having one.
     *
     * @access public
     * @param string $attributeName name of an attribute on the windows
     * @return array the set of values of this attribute from all known windows.
     */
    public function getAttributeFromAllWindows($attributeName)
    {
        return $this->getStringArray("getAttributeFromAllWindows", array($attributeName));
    }


    /**
     * deprecated - use dragAndDrop instead
     *
     * @access public
     * @param string $locator an element locator
     * @param string $movementsString offset in pixels from the current location to which the element should be moved, e.g., "+70,-300"
     */
    public function dragdrop($locator, $movementsString)
    {
        $this->doCommand("dragdrop", array($locator, $movementsString));
    }


    /**
     * Configure the number of pixels between "mousemove" events during dragAndDrop commands (default=10).
     * <p>
     * Setting this value to 0 means that we'll send a "mousemove" event to every single pixel
     * in between the start location and the end location; that can be very slow, and may
     * cause some browsers to force the JavaScript to timeout.
     * </p><p>
     * If the mouse speed is greater than the distance between the two dragged objects, we'll
     * just send one "mousemove" at the start location and then one final one at the end location.
     * </p>
     *
     * @access public
     * @param string $pixels the number of pixels between "mousemove" events
     */
    public function setMouseSpeed($pixels)
    {
        $this->doCommand("setMouseSpeed", array($pixels));
    }


    /**
     * Returns the number of pixels between "mousemove" events during dragAndDrop commands (default=10).
     *
     * @access public
     * @return number the number of pixels between "mousemove" events during dragAndDrop commands (default=10)
     */
    public function getMouseSpeed()
    {
        return $this->getNumber("getMouseSpeed", array());
    }


    /**
     * Drags an element a certain distance and then drops it
     *
     * @access public
     * @param string $locator an element locator
     * @param string $movementsString offset in pixels from the current location to which the element should be moved, e.g., "+70,-300"
     */
    public function dragAndDrop($locator, $movementsString)
    {
        $this->doCommand("dragAndDrop", array($locator, $movementsString));
    }


    /**
     * Drags an element and drops it on another element
     *
     * @access public
     * @param string $locatorOfObjectToBeDragged an element to be dragged
     * @param string $locatorOfDragDestinationObject an element whose location (i.e., whose center-most pixel) will be the point where locatorOfObjectToBeDragged  is dropped
     */
    public function dragAndDropToObject($locatorOfObjectToBeDragged, $locatorOfDragDestinationObject)
    {
        $this->doCommand("dragAndDropToObject", array($locatorOfObjectToBeDragged, $locatorOfDragDestinationObject));
    }


    /**
     * Gives focus to the currently selected window
     *
     * @access public
     */
    public function windowFocus()
    {
        $this->doCommand("windowFocus", array());
    }


    /**
     * Resize currently selected window to take up the entire screen
     *
     * @access public
     */
    public function windowMaximize()
    {
        $this->doCommand("windowMaximize", array());
    }


    /**
     * Returns the IDs of all windows that the browser knows about in an array.
     *
     * @access public
     * @return array Array of identifiers of all windows that the browser knows about.
     */
    public function getAllWindowIds()
    {
        return $this->getStringArray("getAllWindowIds", array());
    }


    /**
     * Returns the names of all windows that the browser knows about in an array.
     *
     * @access public
     * @return array Array of names of all windows that the browser knows about.
     */
    public function getAllWindowNames()
    {
        return $this->getStringArray("getAllWindowNames", array());
    }


    /**
     * Returns the titles of all windows that the browser knows about in an array.
     *
     * @access public
     * @return array Array of titles of all windows that the browser knows about.
     */
    public function getAllWindowTitles()
    {
        return $this->getStringArray("getAllWindowTitles", array());
    }


    /**
     * Returns the entire HTML source between the opening and
     * closing "html" tags.
     *
     * @access public
     * @return string the entire HTML source
     */
    public function getHtmlSource()
    {
        return $this->getString("getHtmlSource", array());
    }


    /**
     * Moves the text cursor to the specified position in the given input element or textarea.
     * This method will fail if the specified element isn't an input element or textarea.
     *
     * @access public
     * @param string $locator an element locator pointing to an input element or textarea
     * @param string $position the numerical position of the cursor in the field; position should be 0 to move the position to the beginning of the field.  You can also set the cursor to -1 to move it to the end of the field.
     */
    public function setCursorPosition($locator, $position)
    {
        $this->doCommand("setCursorPosition", array($locator, $position));
    }


    /**
     * Get the relative index of an element to its parent (starting from 0). The comment node and empty text node
     * will be ignored.
     *
     * @access public
     * @param string $locator an element locator pointing to an element
     * @return number of relative index of the element to its parent (starting from 0)
     */
    public function getElementIndex($locator)
    {
        return $this->getNumber("getElementIndex", array($locator));
    }


    /**
     * Check if these two elements have same parent and are ordered siblings in the DOM. Two same elements will
     * not be considered ordered.
     *
     * @access public
     * @param string $locator1 an element locator pointing to the first element
     * @param string $locator2 an element locator pointing to the second element
     * @return boolean true if element1 is the previous sibling of element2, false otherwise
     */
    public function isOrdered($locator1, $locator2)
    {
        return $this->getBoolean("isOrdered", array($locator1, $locator2));
    }


    /**
     * Retrieves the horizontal position of an element
     *
     * @access public
     * @param string $locator an element locator pointing to an element OR an element itself
     * @return number of pixels from the edge of the frame.
     */
    public function getElementPositionLeft($locator)
    {
        return $this->getNumber("getElementPositionLeft", array($locator));
    }


    /**
     * Retrieves the vertical position of an element
     *
     * @access public
     * @param string $locator an element locator pointing to an element OR an element itself
     * @return number of pixels from the edge of the frame.
     */
    public function getElementPositionTop($locator)
    {
        return $this->getNumber("getElementPositionTop", array($locator));
    }


    /**
     * Retrieves the width of an element
     *
     * @access public
     * @param string $locator an element locator pointing to an element
     * @return number width of an element in pixels
     */
    public function getElementWidth($locator)
    {
        return $this->getNumber("getElementWidth", array($locator));
    }


    /**
     * Retrieves the height of an element
     *
     * @access public
     * @param string $locator an element locator pointing to an element
     * @return number height of an element in pixels
     */
    public function getElementHeight($locator)
    {
        return $this->getNumber("getElementHeight", array($locator));
    }


    /**
     * Retrieves the text cursor position in the given input element or textarea; beware, this may not work perfectly on all browsers.
     *
     * <p>
     * Specifically, if the cursor/selection has been cleared by JavaScript, this command will tend to
     * return the position of the last location of the cursor, even though the cursor is now gone from the page.  This is filed as SEL-243.
     * </p>
     * This method will fail if the specified element isn't an input element or textarea, or there is no cursor in the element.
     *
     * @access public
     * @param string $locator an element locator pointing to an input element or textarea
     * @return number the numerical position of the cursor in the field
     */
    public function getCursorPosition($locator)
    {
        return $this->getNumber("getCursorPosition", array($locator));
    }


    /**
     * Returns the specified expression.
     *
     * <p>
     * This is useful because of JavaScript preprocessing.
     * It is used to generate commands like assertExpression and waitForExpression.
     * </p>
     *
     * @access public
     * @param string $expression the value to return
     * @return string the value passed in
     */
    public function getExpression($expression)
    {
        return $this->getString("getExpression", array($expression));
    }


    /**
     * Returns the number of nodes that match the specified xpath, eg. "//table" would give
     * the number of tables.
     *
     * @access public
     * @param string $xpath the xpath expression to evaluate. do NOT wrap this expression in a 'count()' function; we will do that for you.
     * @return number the number of nodes that match the specified xpath
     */
    public function getXpathCount($xpath)
    {
        return $this->getNumber("getXpathCount", array($xpath));
    }


    /**
     * Temporarily sets the "id" attribute of the specified element, so you can locate it in the future
     * using its ID rather than a slow/complicated XPath.  This ID will disappear once the page is
     * reloaded.
     *
     * @access public
     * @param string $locator an element locator pointing to an element
     * @param string $identifier a string to be used as the ID of the specified element
     */
    public function assignId($locator, $identifier)
    {
        $this->doCommand("assignId", array($locator, $identifier));
    }


    /**
     * Specifies whether Selenium should use the native in-browser implementation
     * of XPath (if any native version is available); if you pass "false" to
     * this function, we will always use our pure-JavaScript xpath library.
     * Using the pure-JS xpath library can improve the consistency of xpath
     * element locators between different browser vendors, but the pure-JS
     * version is much slower than the native implementations.
     *
     * @access public
     * @param string $allow boolean, true means we'll prefer to use native XPath; false means we'll only use JS XPath
     */
    public function allowNativeXpath($allow)
    {
        $this->doCommand("allowNativeXpath", array($allow));
    }


    /**
     * Specifies whether Selenium will ignore xpath attributes that have no
     * value, i.e. are the empty string, when using the non-native xpath
     * evaluation engine. You'd want to do this for performance reasons in IE.
     * However, this could break certain xpaths, for example an xpath that looks
     * for an attribute whose value is NOT the empty string.
     *
     * The hope is that such xpaths are relatively rare, but the user should
     * have the option of using them. Note that this only influences xpath
     * evaluation when using the ajaxslt engine (i.e. not "javascript-xpath").
     *
     * @access public
     * @param string $ignore boolean, true means we'll ignore attributes without value                        at the expense of xpath "correctness"; false means                        we'll sacrifice speed for correctness.
     */
    public function ignoreAttributesWithoutValue($ignore)
    {
        $this->doCommand("ignoreAttributesWithoutValue", array($ignore));
    }


    /**
     * Runs the specified JavaScript snippet repeatedly until it evaluates to "true".
     * The snippet may have multiple lines, but only the result of the last line
     * will be considered.
     *
     * <p>
     * Note that, by default, the snippet will be run in the runner's test window, not in the window
     * of your application.  To get the window of your application, you can use
     * the JavaScript snippet <code>selenium.browserbot.getCurrentWindow()</code>, and then
     * run your JavaScript in there
     * </p>
     *
     * @access public
     * @param string $script the JavaScript snippet to run
     * @param string $timeout a timeout in milliseconds, after which this command will return with an error
     */
    public function waitForCondition($script, $timeout)
    {
        $this->doCommand("waitForCondition", array($script, $timeout));
    }


    /**
     * Specifies the amount of time that Selenium will wait for actions to complete.
     *
     * <p>
     * Actions that require waiting include "open" and the "waitFor*" actions.
     * </p>
     * The default timeout is 30 seconds.
     *
     * @access public
     * @param string $timeout a timeout in milliseconds, after which the action will return with an error
     */
    public function setTimeout($timeout)
    {
        $this->doCommand("setTimeout", array($timeout));
    }


    /**
     * Waits for a new page to load.
     *
     * <p>
     * You can use this command instead of the "AndWait" suffixes, "clickAndWait", "selectAndWait", "typeAndWait" etc.
     * (which are only available in the JS API).
     * </p><p>
     * Selenium constantly keeps track of new pages loading, and sets a "newPageLoaded"
     * flag when it first notices a page load.  Running any other Selenium command after
     * turns the flag to false.  Hence, if you want to wait for a page to load, you must
     * wait immediately after a Selenium command that caused a page-load.
     * </p>
     *
     * @access public
     * @param string $timeout a timeout in milliseconds, after which this command will return with an error
     */
    public function waitForPageToLoad($timeout)
    {
        $this->doCommand("waitForPageToLoad", array($timeout));
    }


    /**
     * Waits for a new frame to load.
     *
     * <p>
     * Selenium constantly keeps track of new pages and frames loading,
     * and sets a "newPageLoaded" flag when it first notices a page load.
     * </p>
     *
     * See waitForPageToLoad for more information.
     *
     * @access public
     * @param string $frameAddress FrameAddress from the server side
     * @param string $timeout a timeout in milliseconds, after which this command will return with an error
     */
    public function waitForFrameToLoad($frameAddress, $timeout)
    {
        $this->doCommand("waitForFrameToLoad", array($frameAddress, $timeout));
    }


    /**
     * Return all cookies of the current page under test.
     *
     * @access public
     * @return string all cookies of the current page under test
     */
    public function getCookie()
    {
        return $this->getString("getCookie", array());
    }


    /**
     * Returns the value of the cookie with the specified name, or throws an error if the cookie is not present.
     *
     * @access public
     * @param string $name the name of the cookie
     * @return string the value of the cookie
     */
    public function getCookieByName($name)
    {
        return $this->getString("getCookieByName", array($name));
    }


    /**
     * Returns true if a cookie with the specified name is present, or false otherwise.
     *
     * @access public
     * @param string $name the name of the cookie
     * @return boolean true if a cookie with the specified name is present, or false otherwise.
     */
    public function isCookiePresent($name)
    {
        return $this->getBoolean("isCookiePresent", array($name));
    }


    /**
     * Create a new cookie whose path and domain are same with those of current page
     * under test, unless you specified a path for this cookie explicitly.
     *
     * @access public
     * @param string $nameValuePair name and value of the cookie in a format "name=value"
     * @param string $optionsString options for the cookie. Currently supported options include 'path', 'max_age' and 'domain'.      the optionsString's format is "path=/path/, max_age=60, domain=.foo.com". The order of options are irrelevant, the unit      of the value of 'max_age' is second.  Note that specifying a domain that isn't a subset of the current domain will      usually fail.
     */
    public function createCookie($nameValuePair, $optionsString)
    {
        $this->doCommand("createCookie", array($nameValuePair, $optionsString));
    }


    /**
     * Delete a named cookie with specified path and domain.  Be careful; to delete a cookie, you
     * need to delete it using the exact same path and domain that were used to create the cookie.
     * If the path is wrong, or the domain is wrong, the cookie simply won't be deleted.  Also
     * note that specifying a domain that isn't a subset of the current domain will usually fail.
     *
     * Since there's no way to discover at runtime the original path and domain of a given cookie,
     * we've added an option called 'recurse' to try all sub-domains of the current domain with
     * all paths that are a subset of the current path.  Beware; this option can be slow.  In
     * big-O notation, it operates in O(n*m) time, where n is the number of dots in the domain
     * name and m is the number of slashes in the path.
     *
     * @access public
     * @param string $name the name of the cookie to be deleted
     * @param string $optionsString options for the cookie. Currently supported options include 'path', 'domain'      and 'recurse.' The optionsString's format is "path=/path/, domain=.foo.com, recurse=true".      The order of options are irrelevant. Note that specifying a domain that isn't a subset of      the current domain will usually fail.
     */
    public function deleteCookie($name, $optionsString)
    {
        $this->doCommand("deleteCookie", array($name, $optionsString));
    }


    /**
     * Calls deleteCookie with recurse=true on all cookies visible to the current page.
     * As noted on the documentation for deleteCookie, recurse=true can be much slower
     * than simply deleting the cookies using a known domain/path.
     *
     * @access public
     */
    public function deleteAllVisibleCookies()
    {
        $this->doCommand("deleteAllVisibleCookies", array());
    }


    /**
     * Sets the threshold for browser-side logging messages; log messages beneath this threshold will be discarded.
     * Valid logLevel strings are: "debug", "info", "warn", "error" or "off".
     * To see the browser logs, you need to
     * either show the log window in GUI mode, or enable browser-side logging in Selenium RC.
     *
     * @access public
     * @param string $logLevel one of the following: "debug", "info", "warn", "error" or "off"
     */
    public function setBrowserLogLevel($logLevel)
    {
        $this->doCommand("setBrowserLogLevel", array($logLevel));
    }


    /**
     * Creates a new "script" tag in the body of the current test window, and
     * adds the specified text into the body of the command.  Scripts run in
     * this way can often be debugged more easily than scripts executed using
     * Selenium's "getEval" command.  Beware that JS exceptions thrown in these script
     * tags aren't managed by Selenium, so you should probably wrap your script
     * in try/catch blocks if there is any chance that the script will throw
     * an exception.
     *
     * @access public
     * @param string $script the JavaScript snippet to run
     */
    public function runScript($script)
    {
        $this->doCommand("runScript", array($script));
    }


    /**
     * Defines a new function for Selenium to locate elements on the page.
     * For example,
     * if you define the strategy "foo", and someone runs click("foo=blah"), we'll
     * run your function, passing you the string "blah", and click on the element
     * that your function
     * returns, or throw an "Element not found" error if your function returns null.
     *
     * We'll pass three arguments to your function:
     *
     * <ul>
     *
     * <li>
     * locator: the string the user passed in
     * </li>
     * <li>
     * inWindow: the currently selected window
     * </li>
     * <li>
     * inDocument: the currently selected document
     * </li>
     * </ul>
     * The function must return null if the element can't be found.
     *
     * @access public
     * @param string $strategyName the name of the strategy to define; this should use only   letters [a-zA-Z] with no spaces or other punctuation.
     * @param string $functionDefinition a string defining the body of a function in JavaScript.   For example: <code>return inDocument.getElementById(locator);</code>
     */
    public function addLocationStrategy($strategyName, $functionDefinition)
    {
        $this->doCommand("addLocationStrategy", array($strategyName, $functionDefinition));
    }


    /**
     * Saves the entire contents of the current window canvas to a PNG file.
     * Contrast this with the captureScreenshot command, which captures the
     * contents of the OS viewport (i.e. whatever is currently being displayed
     * on the monitor), and is implemented in the RC only. Currently this only
     * works in Firefox when running in chrome mode, and in IE non-HTA using
     * the EXPERIMENTAL "Snapsie" utility. The Firefox implementation is mostly
     * borrowed from the Screengrab! Firefox extension. Please see
     * http://www.screengrab.org and http://snapsie.sourceforge.net/ for
     * details.
     *
     * @access public
     * @param string $filename the path to the file to persist the screenshot as. No                  filename extension will be appended by default.                  Directories will not be created if they do not exist,                    and an exception will be thrown, possibly by native                  code.
     * @param string $kwargs a kwargs string that modifies the way the screenshot                  is captured. Example: "background=#CCFFDD" .                  Currently valid options:                  <dl>
<dt>background</dt>
<dd>the background CSS for the HTML document. This                     may be useful to set for capturing screenshots of                     less-than-ideal layouts, for example where absolute                     positioning causes the calculation of the canvas                     dimension to fail and a black background is exposed                     (possibly obscuring black text).</dd>
</dl>
     */
    public function captureEntirePageScreenshot($filename, $kwargs)
    {
        $this->doCommand("captureEntirePageScreenshot", array($filename, $kwargs));
    }


    /**
     * Executes a command rollup, which is a series of commands with a unique
     * name, and optionally arguments that control the generation of the set of
     * commands. If any one of the rolled-up commands fails, the rollup is
     * considered to have failed. Rollups may also contain nested rollups.
     *
     * @access public
     * @param string $rollupName the name of the rollup command
     * @param string $kwargs keyword arguments string that influences how the                    rollup expands into commands
     */
    public function rollup($rollupName, $kwargs)
    {
        $this->doCommand("rollup", array($rollupName, $kwargs));
    }


    /**
     * Loads script content into a new script tag in the Selenium document. This
     * differs from the runScript command in that runScript adds the script tag
     * to the document of the AUT, not the Selenium document. The following
     * entities in the script content are replaced by the characters they
     * represent:
     *
     *     &lt;
     *     &gt;
     *     &amp;
     *
     * The corresponding remove command is removeScript.
     *
     * @access public
     * @param string $scriptContent the Javascript content of the script to add
     * @param string $scriptTagId (optional) the id of the new script tag. If                       specified, and an element with this id already                       exists, this operation will fail.
     */
    public function addScript($scriptContent, $scriptTagId)
    {
        $this->doCommand("addScript", array($scriptContent, $scriptTagId));
    }


    /**
     * Removes a script tag from the Selenium document identified by the given
     * id. Does nothing if the referenced tag doesn't exist.
     *
     * @access public
     * @param string $scriptTagId the id of the script element to remove.
     */
    public function removeScript($scriptTagId)
    {
        $this->doCommand("removeScript", array($scriptTagId));
    }


    /**
     * Allows choice of one of the available libraries.
     *
     * @access public
     * @param string $libraryName name of the desired library Only the following three can be chosen: <ul>
<li>"ajaxslt" - Google's library</li>
<li>"javascript-xpath" - Cybozu Labs' faster library</li>
<li>"default" - The default library.  Currently the default library is "ajaxslt" .</li>
</ul> If libraryName isn't one of these three, then  no change will be made.
     */
    public function useXpathLibrary($libraryName)
    {
        $this->doCommand("useXpathLibrary", array($libraryName));
    }


    /**
     * Writes a message to the status bar and adds a note to the browser-side
     * log.
     *
     * @access public
     * @param string $context the message to be sent to the browser
     */
    public function setContext($context)
    {
        $this->doCommand("setContext", array($context));
    }


    /**
     * Sets a file input (upload) field to the file listed in fileLocator
     *
     * @access public
     * @param string $fieldLocator an element locator
     * @param string $fileLocator a URL pointing to the specified file. Before the file  can be set in the input field (fieldLocator), Selenium RC may need to transfer the file    to the local machine before attaching the file in a web page form. This is common in selenium  grid configurations where the RC server driving the browser is not the same  machine that started the test.   Supported Browsers: Firefox ("*chrome") only.
     */
    public function attachFile($fieldLocator, $fileLocator)
    {
        $this->doCommand("attachFile", array($fieldLocator, $fileLocator));
    }


    /**
     * Captures a PNG screenshot to the specified file.
     *
     * @access public
     * @param string $filename the absolute path to the file to be written, e.g. "c:\blah\screenshot.png"
     */
    public function captureScreenshot($filename)
    {
        $this->doCommand("captureScreenshot", array($filename));
    }


    /**
     * Capture a PNG screenshot.  It then returns the file as a base 64 encoded string.
     *
     * @access public
     * @return string The base 64 encoded string of the screen shot (PNG file)
     */
    public function captureScreenshotToString()
    {
        return $this->getString("captureScreenshotToString", array());
    }


    /**
     * Downloads a screenshot of the browser current window canvas to a
     * based 64 encoded PNG file. The <i>entire</i> windows canvas is captured,
     * including parts rendered outside of the current view port.
     *
     * Currently this only works in Mozilla and when running in chrome mode.
     *
     * @access public
     * @param string $kwargs A kwargs string that modifies the way the screenshot is captured. Example: "background=#CCFFDD". This may be useful to set for capturing screenshots of less-than-ideal layouts, for example where absolute positioning causes the calculation of the canvas dimension to fail and a black background is exposed  (possibly obscuring black text).
     * @return string The base 64 encoded string of the page screenshot (PNG file)
     */
    public function captureEntirePageScreenshotToString($kwargs)
    {
        return $this->getString("captureEntirePageScreenshotToString", array($kwargs));
    }


    /**
     * Kills the running Selenium Server and all browser sessions.  After you run this command, you will no longer be able to send
     * commands to the server; you can't remotely start the server once it has been stopped.  Normally
     * you should prefer to run the "stop" command, which terminates the current browser session, rather than
     * shutting down the entire server.
     *
     * @access public
     */
    public function shutDownSeleniumServer()
    {
        $this->doCommand("shutDownSeleniumServer", array());
    }


    /**
     * Retrieve the last messages logged on a specific remote control. Useful for error reports, especially
     * when running multiple remote controls in a distributed environment. The maximum number of log messages
     * that can be retrieve is configured on remote control startup.
     *
     * @access public
     * @return string The last N log messages as a multi-line string.
     */
    public function retrieveLastRemoteControlLogs()
    {
        return $this->getString("retrieveLastRemoteControlLogs", array());
    }


    /**
     * Simulates a user pressing a key (without releasing it yet) by sending a native operating system keystroke.
     * This function uses the java.awt.Robot class to send a keystroke; this more accurately simulates typing
     * a key on the keyboard.  It does not honor settings from the shiftKeyDown, controlKeyDown, altKeyDown and
     * metaKeyDown commands, and does not target any particular HTML element.  To send a keystroke to a particular
     * element, focus on the element first before running this command.
     *
     * @access public
     * @param string $keycode an integer keycode number corresponding to a java.awt.event.KeyEvent; note that Java keycodes are NOT the same thing as JavaScript keycodes!
     */
    public function keyDownNative($keycode)
    {
        $this->doCommand("keyDownNative", array($keycode));
    }


    /**
     * Simulates a user releasing a key by sending a native operating system keystroke.
     * This function uses the java.awt.Robot class to send a keystroke; this more accurately simulates typing
     * a key on the keyboard.  It does not honor settings from the shiftKeyDown, controlKeyDown, altKeyDown and
     * metaKeyDown commands, and does not target any particular HTML element.  To send a keystroke to a particular
     * element, focus on the element first before running this command.
     *
     * @access public
     * @param string $keycode an integer keycode number corresponding to a java.awt.event.KeyEvent; note that Java keycodes are NOT the same thing as JavaScript keycodes!
     */
    public function keyUpNative($keycode)
    {
        $this->doCommand("keyUpNative", array($keycode));
    }


    /**
     * Simulates a user pressing and releasing a key by sending a native operating system keystroke.
     * This function uses the java.awt.Robot class to send a keystroke; this more accurately simulates typing
     * a key on the keyboard.  It does not honor settings from the shiftKeyDown, controlKeyDown, altKeyDown and
     * metaKeyDown commands, and does not target any particular HTML element.  To send a keystroke to a particular
     * element, focus on the element first before running this command.
     *
     * @access public
     * @param string $keycode an integer keycode number corresponding to a java.awt.event.KeyEvent; note that Java keycodes are NOT the same thing as JavaScript keycodes!
     */
    public function keyPressNative($keycode)
    {
        $this->doCommand("keyPressNative", array($keycode));
    }


    protected function doCommand($verb, $args = array())
    {
        $url = sprintf('http://%s:%s/selenium-server/driver/?cmd=%s', $this->host, $this->port, urlencode($verb));
        for ($i = 0; $i < count($args); $i++) {
            $argNum = strval($i + 1);
            $url .= sprintf('&%s=%s', $argNum, urlencode(trim($args[$i])));
        }

        if (isset($this->sessionId)) {
            $url .= sprintf('&%s=%s', 'sessionId', $this->sessionId);
        }

        if (!$handle = fopen($url, 'r')) {
            throw new Testing_Selenium_Exception('Cannot connected to Selenium RC Server');
        }

        stream_set_blocking($handle, false);
        $response = stream_get_contents($handle);
        fclose($handle);

        return $response;
    }

    private function getNumber($verb, $args = array())
    {
        $result = $this->getString($verb, $args);

        if (!is_numeric($result)) {
            throw new Testing_Selenium_Exception('result is not numeric.');
        }
        return $result;
    }

    protected function getString($verb, $args = array())
    {
        $result = $this->doCommand($verb, $args);
        return (strlen($result) > 3) ? substr($result, 3) : '';
    }

    private function getStringArray($verb, $args = array())
    {
        $csv = $this->getString($verb, $args);
        $token = '';
        $tokens = array();
        $letters = preg_split('//', $csv, -1, PREG_SPLIT_NO_EMPTY);
        for ($i = 0; $i < count($letters); $i++) {
            $letter = $letters[$i];
            switch($letter) {
            case '\\':
                $i++;
                $letter = $letters[$i];
                $token = $token . $letter;
                break;
            case ',':
                array_push($tokens, $token);
                $token = '';
                break;
            default:
                $token = $token . $letter;
                break;
            }
        }
        array_push($tokens, $token);
        return $tokens;
    }

    private function getBoolean($verb, $args = array())
    {
        $result = $this->getString($verb, $args);
        switch ($result) {
        case 'true':
            return true;
        case 'false':
            return false;
        default:
            throw new Testing_Selenium_Exception('result is neither "true" or "false": ' . $result);
        }
    }
}
?>
