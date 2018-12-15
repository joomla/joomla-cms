/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

document.addEventListener('DOMContentLoaded', function() {
  var navs = document.querySelectorAll('.nav');
  [].forEach.call(navs, function(nav) {
    new setup_navigation(nav);
  });
});

var setup_navigation = function(nav, settings) {
  var settings = { menuHoverClass: 'show-menu', dir: 'ltr' };
	// Add ARIA role to menubar and menu items
	// nav.setAttribute('role', 'menubar');
  // nav.querySelectorAll('li').forEach(function(el,i) {
  //  el.setAttribute('role', 'menuitem');
  // });

	var top_level_childs = nav.querySelectorAll(':scope > li');

	// Set tabIndex to -1 so that top_level_childs can't receive focus until menu is open
	top_level_childs.forEach(function(topLevelEl,i) {
    var linkEl = topLevelEl.querySelector('a');
    if (linkEl) {
      linkEl.tabIndex = '0';
      linkEl.addEventListener('mouseover', topLevelMouseOver(topLevelEl, settings));
      linkEl.addEventListener('mouseout', topLevelMouseOut(topLevelEl, settings));
    }
    var spanEl = topLevelEl.querySelector('span');
    if (spanEl) {
      spanEl.tabIndex = '0';
      spanEl.addEventListener('mouseover', topLevelMouseOver(topLevelEl, settings));
      spanEl.addEventListener('mouseout', topLevelMouseOut(topLevelEl, settings));
    }
    topLevelEl.querySelectorAll('ul').forEach(function(el,j) {
      el.setAttribute('data-test','true');
      el.setAttribute('aria-hidden', 'true');
      el.setAttribute('role', 'menu');
      // Adding aria-haspopup for appropriate items
      if (el.children.length > 0) {
        el.parentElement.setAttribute('aria-haspopup', 'true');
      }
      el.querySelectorAll('li').forEach(function(liEl,j) {
        if (liEl.querySelector('a')) liEl.querySelector('a').tabIndex = '0';
        if (liEl.querySelector('span')) liEl.querySelector('span').tabIndex = '0';
      });
    });

    topLevelEl.addEventListener('mouseover', function(event) {
      var curEl = event.target;
      var ulChild = curEl.querySelector('ul');
      if (ulChild) {
        ulChild.setAttribute('aria-hidden', 'false');
        ulChild.classList.add(settings.menuHoverClass);
      }
    });

    topLevelEl.addEventListener('mouseout', function(event) {
      var curEl = event.target;
      var ulChild = curEl.querySelector('ul');
      if (ulChild) {
        ulChild.setAttribute('aria-hidden', 'true');
        ulChild.setAttribute('aria-hidden', 'true');
        ulChild.classList.remove(settings.menuHoverClass);
      }
    });

    topLevelEl.addEventListener('focus', function(event) {
      var curEl = event.target;
      var ulChild = curEl.querySelector('ul');
      if (ulChild) {
        ulChild.setAttribute('aria-hidden', 'true');
        ulChild.classList.add(settings.menuHoverClass);
      }
    });

    topLevelEl.addEventListener('blur', function(event) {
      var curEl = event.target;
      var ulChild = curEl.querySelector('ul');
      if (ulChild) {
        ulChild.setAttribute('aria-hidden', 'false');
        ulChild.classList.remove(settings.menuHoverClass);
      }
    });

    topLevelEl.addEventListener('keydown', function(event) {
      var keyName = event.key;
      var curEl = event.target;
      var curLiEl = curEl.parentElement;
      var curUlEl = curLiEl.parentElement;
      var prevLiEl = curLiEl.previousElementSibling;
      var nextLiEl = curLiEl.nextElementSibling;
      if (!prevLiEl) {
        prevLiEl = curUlEl.children[curUlEl.children.length - 1];
      }
      if (!nextLiEl) {
        nextLiEl = curUlEl.children[0];
      }
      console.log ("key pressed: " + keyName);
      switch (keyName) {
        case 'ArrowLeft':
            event.preventDefault();
            if (settings.dir == "rtl") {
              nextLiEl.children[0].focus();
            } else {
              prevLiEl.children[0].focus();
            }
          break;
          case 'ArrowRight':
              event.preventDefault();
              if (settings.dir === "rtl") {
                prevLiEl.children[0].focus();
              } else {
                nextLiEl.children[0].focus();
              }
            break;
          case 'ArrowUp':
              event.preventDefault();
              var parent = curLiEl.parentElement.parentElement;
              console.log(parent.nodeName);
              if (parent.nodeName == "LI") {
                parent.children[0].focus();
              } else {
                prevLiEl.children[0].focus();
              }
              break;
          case 'ArrowDown':
            event.preventDefault();
            if (curLiEl.classList.contains("parent")) {
              var child = curLiEl.querySelector('ul');
              if (child != null) {
                console.log("child: " + child);
                var childLi = child.querySelector('li');
                console.log("childLi: " + childLi);
                childLi.children[0].focus();
              }
              else {
                nextLiEl.children[0].focus();
              }
            } else {
              nextLiEl.children[0].focus();
            }
            break;
        default:
          break;
      }
    });
  });
}

function topLevelMouseOver (el, settings) {
  var ulChild = el.querySelector('ul');
  if (ulChild) {
    ulChild.setAttribute('aria-hidden', 'false');
    ulChild.classList.add(settings.menuHoverClass);
  }
}

function topLevelMouseOut (el, settings) {
  var ulChild = el.querySelector('ul');
  if (ulChild) {
    ulChild.setAttribute('aria-hidden', 'true');
    ulChild.setAttribute('aria-hidden', 'true');
    ulChild.classList.remove(settings.menuHoverClass);
  }
}
