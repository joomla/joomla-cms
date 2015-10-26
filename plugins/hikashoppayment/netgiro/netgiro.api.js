/**************************************************************************************************************
*   Netgiro JavaScript api v1.2
*
*   Disclaimer:
*   Copyright 2013 Expertia ehf.
*
*   Licensed under the Apache License, Version 2.0 (the "License");
*   you may not use this file except in compliance with the License.
*   You may obtain a copy of the License at
*
*     http://www.apache.org/licenses/LICENSE-2.0
*
*   Unless required by applicable law or agreed to in writing, software
*   distributed under the License is distributed on an "AS IS" BASIS,
*   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
*   See the License for the specific language governing permissions and
*   limitations under the License.
*
*
*   Description:
*   This api provides properties and methods for easier integration of Netgiro branding in provider web pages.
*
*   Usage:
*   Include this script on pages you wish to show netgiro branding messages and images.
*   Access object using netgiro.<functionOrPropertyName>
*
*   1.  Integrating default netgiro html element
*
*       To draw netgiro buttons, you need to call netgiro.branding.init(<appId>,<emelentId>);
*       Where <elementId> is optional parameter.
*       Depending on <elementId>, api behaves like this
*           - If <elementId> is not provided and somewhere on the page exists element with id="netgiro-branding-container" api will append all other elements inside <elementId> 
*           - If <elementId> is not provided and element with id="netgiro-branding-container" does not exist api will create it and append it to the end of the page
*           - If <elementId> is porovided, api will append all other elements inside <elementId> 
*
*   2.  Netgiro elements and classes
*       
*       Netgiro elements are defined in object netgiro.elements
*        
*       netgiro.elements = {
*	        p1Container: "netgiro-branding-p1",
*	        p2Container: "netgiro-branding-p2",
*	        p3Container: "netgiro-branding-p3",        
*       };
*
*       Netgiro classes are defined in object netgiro.classes
*
*       netgiro.classes = {
*	        container: "netgiro-container",
*	        containerTop: "netgiro-container-top",
*           title: "netgiro-title",
*           text: "netgiro-text",
*           message: "netgiro-message",
*           logo: "netgiro-logo",
*           image: "netgiro-image"
*       }
*
*   3.  Options
*		
*		Depending on options that provider has enabled he will need to show different payment options.
*		Object netgiro.branding.options holds all payment options that can be shown.
*
*		netgiro.branding.options = {
*			showP1: true,
*			showP2: true,
*			showP3: true
*		};
*		
*		By default, all options are shown. If you need to disable any of them just set it before you call netgiro.branding.init function.
*
*		showP1 = show pay later option (14 days delay in payment)
*		showP2 = show partial payments
*		showP3 = show partial payments without interest
*       
*       You can override any of the CSS classes, or you can replace them with your own. 
*       To replace them, just set your class names before you call netgiro.branding.init function.*
*
*       Same is valid for HTML elements
*
*
*
*   Requirements:
*   jQuery 1.4+
*
**************************************************************************************************************/
//branding
(function (netgiro, $, undefined) {

    //#region Public properties

    //Object for storing branding information (texts, logo urls, etc)
    netgiro.branding = {
        p1Title: "Netgíró reikningur",
        p2Title: "Netgíró raðgreiðslur",
        p3Title: "Netgíró vaxtalausar raðgreiðslur",
        p1Text: "Reikningur er sendur i netbanka, greiða þarf innan 14 daga.",
        p2Text: "Greiða fer fram í öruguu vefsvæði Netgíró.",
        p3Text: "Greiða fer fram í öruguu vefsvæði Netgíró.",
        p1LogoUrl: "https://api.netgiro.is/images/netgiro_re.png",
        p2LogoUrl: "https://api.netgiro.is/images/netgiro_ra.png",
        p3LogoUrl: "https://api.netgiro.is/images/netgiro_va_ra.png",
    };
    //Object with branding display options
    netgiro.branding.options = {
        showP1: true,
        showP2: true,
        showP3: true
    };
    //Object for storing element id's
    netgiro.elements = {
        p1Container: "netgiro-branding-p1",
        p1Message: "netgiro-branding-p1-message",
        p1Title: "netgiro-branding-p1-title",
        p1Text: "netgiro-branding-p1-text",
        p1Logo: "netgiro-branding-p1-logo",
        p1Image: "netgiro-branding-p1-image",

        p2Container: "netgiro-branding-p2",
        p2Message: "netgiro-branding-p2-message",
        p2Title: "netgiro-branding-p2-title",
        p2Text: "netgiro-branding-p2-text",
        p2Logo: "netgiro-branding-p2-logo",
        p2Image: "netgiro-branding-p2-image",

        p3Container: "netgiro-branding-p3",
        p3Message: "netgiro-branding-p3-message",
        p3Title: "netgiro-branding-p3-title",
        p3Text: "netgiro-branding-p3-text",
        p3Logo: "netgiro-branding-p3-logo",
        p3Image: "netgiro-branding-p3-image",
    };

    netgiro.classes = {
        container: "netgiro-container",
        containerTop: "netgiro-container-top",
        title: "netgiro-title",
        text: "netgiro-text",
        message: "netgiro-message",
        logo: "netgiro-logo",
        image: "netgiro-image"
    };

    var htmlElement = function(id, cssClass, template) {
        var that = this;

        that.id = id;
        that.class = cssClass;
        that.htmlTemplate = template;

        return that;
    };

    var defaultStyle = '<style type="text/css">' +
	'.netgiro-container{ ' +
		'color: #656565;' +
		'background-color: #f3f3f3;' +
		'border-bottom: 1px solid #d1d2d4;' +
		'border-left: 1px solid #d1d2d4;' +
		'border-right: 1px solid #d1d2d4;' +
		'}' +
	'.netgiro-container-top{' +
		'border-top: 1px solid #d1d2d4;' +
		'}' +
	'.netgiro-title{' +
		'font-size: 1.8em;' +
		'font-weight: bold;' +
		'font-family: inherit;' +
		'line-height: 40px;' +
		'margin:10px 0;' +
		'color: inherit;' +
		'}' +
	'.netgiro-text{' +
		'font-size: 1.17em;' +
        'font-weight: 400;' +
		'margin: 0 0 10px 10px;' +
		'}' +
	'.netgiro-message{' +
		'display: inline-block;' +
		'padding-left: 40px;' +
		'}' +
	'.netgiro-logo{' +
		'display: inline-block;' +
		'padding: 8px;' +
		'float: right;' +
		'}' +
	'.netgiro-image{' +
		'}' +
	'</style>';

    //#endregion

    //#region Private properties

    var getDataUrl = "//api.netgiro.is/Home/GetNetgiroMessages?appId=";

    var containerTemplate = '<div id="{0}" class="{1}"></div>';
    var messageTemplate = '<div id="{0}" class="{1}"></div>';
    var titleTemplate = '<div id="{0}" class="{1}"></div>';
    var textTemplate = '<div id="{0}" class="{1}"></div>';
    var logoTemplate = '<div id="{0}" class="{1}"></div>';
    var imageTemplate = '<img id="{0}" class="{1}" alt="Netgiro" src="{2}">';

    //Netgiro utility
    var utils = {
        createHtmlElement: function (template) {
            for (var i = 1; i < arguments.length; i++) {
                template = template.replace('{' + (i - 1) + '}', arguments[i]);
            }
            return template;
        },

        getOrCreateElement: function (elementId, $parent, html) {
            var $element = $("#" + elementId);

            if ($element.length == 0) {
                //if element does not exist, create it
                var elementHtml = "<div id='" + elementId + "' ></div>";
                if (html)
                    elementHtml = html;

                $parent.append(elementHtml);
                $element = $("#" + elementId);
            }

            return $element;
        }
    };

    //#endregion

    //#region Private methods

    //Load all branding data from api
    function loadBrandingData(appId) {
        $.ajax({
            url: getDataUrl + appId,
            type: "GET",
            contentType: 'application/json',
            async: false,
            success: function (data) {
                //Populate properties
                for (var prop in data) {
                    netgiro.branding[prop] = data[prop] || netgiro.branding[prop];
                }
            },
            error: function (data) {
                console.log(data);
            }
        });
    }

    //#endregion

    //#region Public methods

    var createNetgiroElements = function(containerElementId) {
        var $container = null;

        if (containerElementId)
            //Put netgiro elements inside container element
            $container = $("#" + containerElementId);
        else {
            //Try to find container with id = "netgiro-branding-container"
            $container = utils.getOrCreateElement("netgiro-branding-container", $("body"));
        }
        //Create P1 html
        if (netgiro.branding.options.showP1) {
            var $p1Element = utils.getOrCreateElement(
                netgiro.elements.p1Container,
                $container,
                utils.createHtmlElement(
                    containerTemplate,
                    netgiro.elements.p1Container,
                    netgiro.classes.container + " " + netgiro.classes.containerTop
                )
            );

            var $p1Message = utils.getOrCreateElement(
                netgiro.elements.p1Message,
                $p1Element,
                utils.createHtmlElement(
                    messageTemplate,
                    netgiro.elements.p1Message,
                    netgiro.classes.message
                )
            );

            var $p1Title = utils.getOrCreateElement(
                netgiro.elements.p1Title,
                $p1Message,
                utils.createHtmlElement(
                    titleTemplate,
                    netgiro.elements.p1Title,
                    netgiro.classes.title
                )
            );

            var $p1Text = utils.getOrCreateElement(
                netgiro.elements.p1Text,
                $p1Message,
                utils.createHtmlElement(
                    textTemplate,
                    netgiro.elements.p1Text,
                    netgiro.classes.text
                )
            );

            var $p1Logo = utils.getOrCreateElement(
                netgiro.elements.p1Logo,
                $p1Element,
                utils.createHtmlElement(
                    logoTemplate,
                    netgiro.elements.p1Logo,
                    netgiro.classes.logo
                )
            );

            var $p1Image = utils.getOrCreateElement(
                netgiro.elements.p1Image,
                $p1Logo,
                utils.createHtmlElement(
                    imageTemplate,
                    netgiro.elements.p1Image,
                    netgiro.classes.image
                )
            );

        }
        //Create P2 html
        if (netgiro.branding.options.showP2) {
            var $p2Element = utils.getOrCreateElement(
                netgiro.elements.p2Container,
                $container,
                utils.createHtmlElement(
                    containerTemplate,
                    netgiro.elements.p2Container,
                    netgiro.classes.container + " " + ($container.children("." + netgiro.classes.containerTop).length > 0 ? " " : netgiro.classes.containerTop)
                )
            );

            var $p2Message = utils.getOrCreateElement(
                netgiro.elements.p2Message,
                $p2Element,
                utils.createHtmlElement(
                    messageTemplate,
                    netgiro.elements.p2Message,
                    netgiro.classes.message
                )
            );

            var $p2Title = utils.getOrCreateElement(
                netgiro.elements.p2Title,
                $p2Message,
                utils.createHtmlElement(
                    titleTemplate,
                    netgiro.elements.p2Title,
                    netgiro.classes.title
                )
            );

            var $p2Text = utils.getOrCreateElement(
                netgiro.elements.p2Text,
                $p2Message,
                utils.createHtmlElement(
                    textTemplate,
                    netgiro.elements.p2Text,
                    netgiro.classes.text
                )
            );

            var $p2Logo = utils.getOrCreateElement(
                netgiro.elements.p2Logo,
                $p2Element,
                utils.createHtmlElement(
                    logoTemplate,
                    netgiro.elements.p2Logo,
                    netgiro.classes.logo
                )
            );

            var $p2Image = utils.getOrCreateElement(
                netgiro.elements.p2Image,
                $p2Logo,
                utils.createHtmlElement(
                    imageTemplate,
                    netgiro.elements.p2Image,
                    netgiro.classes.image
                )
            );
        }
        //Create P3 html
        if (netgiro.branding.options.showP3) {
            var $p3Element = utils.getOrCreateElement(
                netgiro.elements.p3Container,
                $container,
                utils.createHtmlElement(
                    containerTemplate,
                    netgiro.elements.p3Container,
                    netgiro.classes.container + " " + ($container.children("." + netgiro.classes.containerTop).length > 0 ? " " : netgiro.classes.containerTop)
                )
            );

            var $p3Message = utils.getOrCreateElement(
                netgiro.elements.p3Message,
                $p3Element,
                utils.createHtmlElement(
                    messageTemplate,
                    netgiro.elements.p3Message,
                    netgiro.classes.message
                )
            );

            var $p3Title = utils.getOrCreateElement(
                netgiro.elements.p3Title,
                $p3Message,
                utils.createHtmlElement(
                    titleTemplate,
                    netgiro.elements.p3Title,
                    netgiro.classes.title
                )
            );

            var $p3Text = utils.getOrCreateElement(
                netgiro.elements.p3Text,
                $p3Message,
                utils.createHtmlElement(
                    textTemplate,
                    netgiro.elements.p3Text,
                    netgiro.classes.text
                )
            );

            var $p3Logo = utils.getOrCreateElement(
                netgiro.elements.p3Logo,
                $p3Element,
                utils.createHtmlElement(
                    logoTemplate,
                    netgiro.elements.p3Logo,
                    netgiro.classes.logo
                )
            );

            var $p3Image = utils.getOrCreateElement(
                netgiro.elements.p3Image,
                $p3Logo,
                utils.createHtmlElement(
                    imageTemplate,
                    netgiro.elements.p3Image,
                    netgiro.classes.image
                )
            );
        }
    };

    var injectNetgriroContent = function() {
        if (netgiro.branding.options.showP1) {
            $("#" + netgiro.elements.p1Title).html(netgiro.branding.p1Title);
            $("#" + netgiro.elements.p1Text).html(netgiro.branding.p1Text);
            $("#" + netgiro.elements.p1Image).attr("src", netgiro.branding.p1LogoUrl);
        }

        if (netgiro.branding.options.showP2) {
            $("#" + netgiro.elements.p2Title).html(netgiro.branding.p2Title);
            $("#" + netgiro.elements.p2Text).html(netgiro.branding.p2Text);
            $("#" + netgiro.elements.p2Image).attr("src", netgiro.branding.p2LogoUrl);
        }

        if (netgiro.branding.options.showP3) {
            $("#" + netgiro.elements.p3Title).html(netgiro.branding.p3Title);
            $("#" + netgiro.elements.p3Text).html(netgiro.branding.p3Text);
            $("#" + netgiro.elements.p3Image).attr("src", netgiro.branding.p3LogoUrl);
        }
    };

    //Populates elements with default netgio id's with default text content
    netgiro.populateBranding = function (containerElementId) {

        //inject styles
        $("head").append(defaultStyle);

        //Create elements
        createNetgiroElements(containerElementId);
        //Inject content
        injectNetgriroContent();
    };



    //Returns netgiro branding div with default layout
    netgiro.getHtmlElement = function () {
        return createHtmlElement(netgiro.branding.titleText, netgiro.branding.bodyText);
    };

    //Initalize library
    netgiro.branding.init = function (appId, containerElementId) {
        //Load current branding texts
        //loadBrandingData(appId);
        //Draw branding html
        netgiro.populateBranding(containerElementId);
    };

    //#endregion

}(window.netgiro = window.netgiro || {}, jQuery));

//Netgiro PayButton
(function (netgiro, $, undefined) {

    //Settings
    netgiro.paybutton = {
        applicationId: "YOUR APP ID",
        requestUrl: "YOUR REQUEST URL",
        returnUrl: "YOUR RETURN URL",
        requestCode: new Date().getTime()
    };

    $(document).on("click", ".netgiro-pay", function () {
        requestAccessToken();
    });

    //Builds request query string
    function getQueryString() {
        var queryString = "?";
        for (prop in netgiro.paybutton) {
            queryString += encodeURIComponent(prop) + "=" + encodeURIComponent(netgiro.paybutton[prop]) + "&";
        }

        return queryString;
    }

    function requestAccessToken() {
        $.ajax({
            type: "POST",
            url: netgiro.paybutton.requestUrl,
            data: { requestCode: netgiro.paybutton.requestCode },
            success: function (result) {
                openPopupWindow();
            },
            error: function (result) {

            }
        });
    }

    //Opens popup window with login form
    function openPopupWindow() {
        //Get current window size
        var currentWindow = window.self;

        var windowWidth = 400;
        var windowHeight = 500;
        //Calculate left,top position for popup

        var dualScreenLeft = currentWindow.screenLeft ? currentWindow.screenLeft : screen.left;
        var dualScreenTop = currentWindow.screenTop ? currentWindow.screenTop : screen.top;

        var windowLeft = Math.ceil(currentWindow.innerWidth / 2 - windowWidth / 2) + dualScreenLeft;
        var windowTop = 100 + dualScreenTop;

        var windowFeatures = "location=yes,status=yes,";
        windowFeatures += "width=" + windowWidth + ",";
        windowFeatures += "height=" + windowHeight + ",";
        windowFeatures += "left=" + windowLeft + ",";
        windowFeatures += "top=" + windowTop;
        var windowName = "netgiro_pay_window";
        var windowUrl = "http://api.netgiro.is/Secure/Login" + getQueryString();

        var loginWindow = window.open(windowUrl, windowName, windowFeatures);

        if (!loginWindow || loginWindow.screenLeft <= 0) {
            alert("Login window failed to open. Check if popup's are enabled for this site.");
        }

        loginWindow.focus();
    }

}(window.netgiro = window.netgiro || {}, jQuery));