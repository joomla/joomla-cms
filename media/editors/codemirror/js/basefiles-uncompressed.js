/* The Editor object manages the content of the editable frame. It
 * catches events, colours nodes, and indents lines. This file also
 * holds some functions for transforming arbitrary DOM structures into
 * plain sequences of <span> and <br> elements
 */

var internetExplorer = document.selection && window.ActiveXObject && /MSIE/.test(navigator.userAgent);
var webkit = /AppleWebKit/.test(navigator.userAgent);
var safari = /Apple Computer, Inc/.test(navigator.vendor);
var gecko = navigator.userAgent.match(/gecko\/(\d{8})/i);
if (gecko) gecko = Number(gecko[1]);
var mac = /Mac/.test(navigator.platform);

// TODO this is related to the backspace-at-end-of-line bug. Remove
// this if Opera gets their act together, make the version check more
// broad if they don't.
var brokenOpera = window.opera && /Version\/10.[56]/.test(navigator.userAgent);
// TODO remove this once WebKit 533 becomes less common.
var slowWebkit = /AppleWebKit\/533/.test(navigator.userAgent);

// Make sure a string does not contain two consecutive 'collapseable'
// whitespace characters.
function makeWhiteSpace(n) {
  var buffer = [], nb = true;
  for (; n > 0; n--) {
    buffer.push((nb || n == 1) ? nbsp : " ");
    nb ^= true;
  }
  return buffer.join("");
}

// Create a set of white-space characters that will not be collapsed
// by the browser, but will not break text-wrapping either.
function fixSpaces(string) {
  if (string.charAt(0) == " ") string = nbsp + string.slice(1);
  return string.replace(/\t/g, function() {return makeWhiteSpace(indentUnit);})
    .replace(/[ \u00a0]{2,}/g, function(s) {return makeWhiteSpace(s.length);});
}

function cleanText(text) {
  return text.replace(/\u00a0/g, " ").replace(/\u200b/g, "");
}

// Create a SPAN node with the expected properties for document part
// spans.
function makePartSpan(value) {
  var text = value;
  if (value.nodeType == 3) text = value.nodeValue;
  else value = document.createTextNode(text);

  var span = document.createElement("span");
  span.isPart = true;
  span.appendChild(value);
  span.currentText = text;
  return span;
}

function alwaysZero() {return 0;}

// On webkit, when the last BR of the document does not have text
// behind it, the cursor can not be put on the line after it. This
// makes pressing enter at the end of the document occasionally do
// nothing (or at least seem to do nothing). To work around it, this
// function makes sure the document ends with a span containing a
// zero-width space character. The traverseDOM iterator filters such
// character out again, so that the parsers won't see them. This
// function is called from a few strategic places to make sure the
// zwsp is restored after the highlighting process eats it.
var webkitLastLineHack = webkit ?
  function(container) {
    var last = container.lastChild;
    if (!last || !last.hackBR) {
      var br = document.createElement("br");
      br.hackBR = true;
      container.appendChild(br);
    }
  } : function() {};

function asEditorLines(string) {
  var tab = makeWhiteSpace(indentUnit);
  return map(string.replace(/\t/g, tab).replace(/\u00a0/g, " ").replace(/\r\n?/g, "\n").split("\n"), fixSpaces);
}

var Editor = (function(){
  // The HTML elements whose content should be suffixed by a newline
  // when converting them to flat text.
  var newlineElements = {"P": true, "DIV": true, "LI": true};

  // Helper function for traverseDOM. Flattens an arbitrary DOM node
  // into an array of textnodes and <br> tags.
  function simplifyDOM(root, atEnd) {
    var result = [];
    var leaving = true;

    function simplifyNode(node, top) {
      if (node.nodeType == 3) {
        var text = node.nodeValue = fixSpaces(node.nodeValue.replace(/[\r\u200b]/g, "").replace(/\n/g, " "));
        if (text.length) leaving = false;
        result.push(node);
      }
      else if (isBR(node) && node.childNodes.length == 0) {
        leaving = true;
        result.push(node);
      }
      else {
        for (var n = node.firstChild; n; n = n.nextSibling) simplifyNode(n);
        if (!leaving && newlineElements.hasOwnProperty(node.nodeName.toUpperCase())) {
          leaving = true;
          if (!atEnd || !top)
            result.push(document.createElement("br"));
        }
      }
    }

    simplifyNode(root, true);
    return result;
  }

  // Creates a MochiKit-style iterator that goes over a series of DOM
  // nodes. The values it yields are strings, the textual content of
  // the nodes. It makes sure that all nodes up to and including the
  // one whose text is being yielded have been 'normalized' to be just
  // <span> and <br> elements.
  function traverseDOM(start){
    var nodeQueue = [];

    // Create a function that can be used to insert nodes after the
    // one given as argument.
    function pointAt(node){
      var parent = node.parentNode;
      var next = node.nextSibling;
      return function(newnode) {
        parent.insertBefore(newnode, next);
      };
    }
    var point = null;

    // This an Opera-specific hack -- always insert an empty span
    // between two BRs, because Opera's cursor code gets terribly
    // confused when the cursor is between two BRs.
    var afterBR = true;

    // Insert a normalized node at the current point. If it is a text
    // node, wrap it in a <span>, and give that span a currentText
    // property -- this is used to cache the nodeValue, because
    // directly accessing nodeValue is horribly slow on some browsers.
    // The dirty property is used by the highlighter to determine
    // which parts of the document have to be re-highlighted.
    function insertPart(part){
      var text = "\n";
      if (part.nodeType == 3) {
        select.snapshotChanged();
        part = makePartSpan(part);
        text = part.currentText;
        afterBR = false;
      }
      else {
        if (afterBR && window.opera)
          point(makePartSpan(""));
        afterBR = true;
      }
      part.dirty = true;
      nodeQueue.push(part);
      point(part);
      return text;
    }

    // Extract the text and newlines from a DOM node, insert them into
    // the document, and return the textual content. Used to replace
    // non-normalized nodes.
    function writeNode(node, end) {
      var simplified = simplifyDOM(node, end);
      for (var i = 0; i < simplified.length; i++)
        simplified[i] = insertPart(simplified[i]);
      return simplified.join("");
    }

    // Check whether a node is a normalized <span> element.
    function partNode(node){
      if (node.isPart && node.childNodes.length == 1 && node.firstChild.nodeType == 3) {
        var text = node.firstChild.nodeValue;
        node.dirty = node.dirty || text != node.currentText;
        node.currentText = text;
        return !/[\n\t\r]/.test(node.currentText);
      }
      return false;
    }

    // Advance to next node, return string for current node.
    function next() {
      if (!start) throw StopIteration;
      var node = start;
      start = node.nextSibling;

      if (partNode(node)){
        nodeQueue.push(node);
        afterBR = false;
        return node.currentText;
      }
      else if (isBR(node)) {
        if (afterBR && window.opera)
          node.parentNode.insertBefore(makePartSpan(""), node);
        nodeQueue.push(node);
        afterBR = true;
        return "\n";
      }
      else {
        var end = !node.nextSibling;
        point = pointAt(node);
        removeElement(node);
        return writeNode(node, end);
      }
    }

    // MochiKit iterators are objects with a next function that
    // returns the next value or throws StopIteration when there are
    // no more values.
    return {next: next, nodes: nodeQueue};
  }

  // Determine the text size of a processed node.
  function nodeSize(node) {
    return isBR(node) ? 1 : node.currentText.length;
  }

  // Search backwards through the top-level nodes until the next BR or
  // the start of the frame.
  function startOfLine(node) {
    while (node && !isBR(node)) node = node.previousSibling;
    return node;
  }
  function endOfLine(node, container) {
    if (!node) node = container.firstChild;
    else if (isBR(node)) node = node.nextSibling;

    while (node && !isBR(node)) node = node.nextSibling;
    return node;
  }

  function time() {return new Date().getTime();}

  // Client interface for searching the content of the editor. Create
  // these by calling CodeMirror.getSearchCursor. To use, call
  // findNext on the resulting object -- this returns a boolean
  // indicating whether anything was found, and can be called again to
  // skip to the next find. Use the select and replace methods to
  // actually do something with the found locations.
  function SearchCursor(editor, pattern, from, caseFold) {
    this.editor = editor;
    this.history = editor.history;
    this.history.commit();
    this.valid = !!pattern;
    this.atOccurrence = false;
    if (caseFold == undefined) caseFold = typeof pattern == "string" && pattern == pattern.toLowerCase();

    function getText(node){
      var line = cleanText(editor.history.textAfter(node));
      return (caseFold ? line.toLowerCase() : line);
    }

    var topPos = {node: null, offset: 0}, self = this;
    if (from && typeof from == "object" && typeof from.character == "number") {
      editor.checkLine(from.line);
      var pos = {node: from.line, offset: from.character};
      this.pos = {from: pos, to: pos};
    }
    else if (from) {
      this.pos = {from: select.cursorPos(editor.container, true) || topPos,
                  to: select.cursorPos(editor.container, false) || topPos};
    }
    else {
      this.pos = {from: topPos, to: topPos};
    }

    if (typeof pattern != "string") { // Regexp match
      this.matches = function(reverse, node, offset) {
        if (reverse) {
          var line = getText(node).slice(0, offset), match = line.match(pattern), start = 0;
          while (match) {
            var ind = line.indexOf(match[0]);
            start += ind;
            line = line.slice(ind + 1);
            var newmatch = line.match(pattern);
            if (newmatch) match = newmatch;
            else break;
          }
        }
        else {
          var line = getText(node).slice(offset), match = line.match(pattern),
              start = match && offset + line.indexOf(match[0]);
        }
        if (match) {
          self.currentMatch = match;
          return {from: {node: node, offset: start},
                  to: {node: node, offset: start + match[0].length}};
        }
      };
      return;
    }

    if (caseFold) pattern = pattern.toLowerCase();
    // Create a matcher function based on the kind of string we have.
    var target = pattern.split("\n");
    this.matches = (target.length == 1) ?
      // For one-line strings, searching can be done simply by calling
      // indexOf or lastIndexOf on the current line.
      function(reverse, node, offset) {
        var line = getText(node), len = pattern.length, match;
        if (reverse ? (offset >= len && (match = line.lastIndexOf(pattern, offset - len)) != -1)
                    : (match = line.indexOf(pattern, offset)) != -1)
          return {from: {node: node, offset: match},
                  to: {node: node, offset: match + len}};
      } :
      // Multi-line strings require internal iteration over lines, and
      // some clunky checks to make sure the first match ends at the
      // end of the line and the last match starts at the start.
      function(reverse, node, offset) {
        var idx = (reverse ? target.length - 1 : 0), match = target[idx], line = getText(node);
        var offsetA = (reverse ? line.indexOf(match) + match.length : line.lastIndexOf(match));
        if (reverse ? offsetA >= offset || offsetA != match.length
                    : offsetA <= offset || offsetA != line.length - match.length)
          return;

        var pos = node;
        while (true) {
          if (reverse && !pos) return;
          pos = (reverse ? this.history.nodeBefore(pos) : this.history.nodeAfter(pos) );
          if (!reverse && !pos) return;

          line = getText(pos);
          match = target[reverse ? --idx : ++idx];

          if (idx > 0 && idx < target.length - 1) {
            if (line != match) return;
            else continue;
          }
          var offsetB = (reverse ? line.lastIndexOf(match) : line.indexOf(match) + match.length);
          if (reverse ? offsetB != line.length - match.length : offsetB != match.length)
            return;
          return {from: {node: reverse ? pos : node, offset: reverse ? offsetB : offsetA},
                  to: {node: reverse ? node : pos, offset: reverse ? offsetA : offsetB}};
        }
      };
  }

  SearchCursor.prototype = {
    findNext: function() {return this.find(false);},
    findPrevious: function() {return this.find(true);},

    find: function(reverse) {
      if (!this.valid) return false;

      var self = this, pos = reverse ? this.pos.from : this.pos.to,
          node = pos.node, offset = pos.offset;
      // Reset the cursor if the current line is no longer in the DOM tree.
      if (node && !node.parentNode) {
        node = null; offset = 0;
      }
      function savePosAndFail() {
        var pos = {node: node, offset: offset};
        self.pos = {from: pos, to: pos};
        self.atOccurrence = false;
        return false;
      }

      while (true) {
        if (this.pos = this.matches(reverse, node, offset)) {
          this.atOccurrence = true;
          return true;
        }

        if (reverse) {
          if (!node) return savePosAndFail();
          node = this.history.nodeBefore(node);
          offset = this.history.textAfter(node).length;
        }
        else {
          var next = this.history.nodeAfter(node);
          if (!next) {
            offset = this.history.textAfter(node).length;
            return savePosAndFail();
          }
          node = next;
          offset = 0;
        }
      }
    },

    select: function() {
      if (this.atOccurrence) {
        select.setCursorPos(this.editor.container, this.pos.from, this.pos.to);
        select.scrollToCursor(this.editor.container);
      }
    },

    replace: function(string) {
      if (this.atOccurrence) {
        var fragments = this.currentMatch;
        if (fragments)
          string = string.replace(/\\(\d)/, function(m, i){return fragments[i];});
        var end = this.editor.replaceRange(this.pos.from, this.pos.to, string);
        this.pos.to = end;
        this.atOccurrence = false;
      }
    },

    position: function() {
      if (this.atOccurrence)
        return {line: this.pos.from.node, character: this.pos.from.offset};
    }
  };

  // The Editor object is the main inside-the-iframe interface.
  function Editor(options) {
    this.options = options;
    window.indentUnit = options.indentUnit;
    var container = this.container = document.body;
    this.history = new UndoHistory(container, options.undoDepth, options.undoDelay, this);
    var self = this;

    if (!Editor.Parser)
      throw "No parser loaded.";
    if (options.parserConfig && Editor.Parser.configure)
      Editor.Parser.configure(options.parserConfig);

    if (!options.readOnly && !internetExplorer)
      select.setCursorPos(container, {node: null, offset: 0});

    this.dirty = [];
    this.importCode(options.content || "");
    this.history.onChange = options.onChange;

    if (!options.readOnly) {
      if (options.continuousScanning !== false) {
        this.scanner = this.documentScanner(options.passTime);
        this.delayScanning();
      }

      function setEditable() {
        // Use contentEditable instead of designMode on IE, since designMode frames
        // can not run any scripts. It would be nice if we could use contentEditable
        // everywhere, but it is significantly flakier than designMode on every
        // single non-IE browser.
        if (document.body.contentEditable != undefined && internetExplorer)
          document.body.contentEditable = "true";
        else
          document.designMode = "on";

        // Work around issue where you have to click on the actual
        // body of the document to focus it in IE, making focusing
        // hard when the document is small.
        if (internetExplorer && options.height != "dynamic")
          document.body.style.minHeight = (
            window.frameElement.clientHeight - 2 * document.body.offsetTop - 5) + "px";

        document.documentElement.style.borderWidth = "0";
        if (!options.textWrapping)
          container.style.whiteSpace = "nowrap";
      }

      // If setting the frame editable fails, try again when the user
      // focus it (happens when the frame is not visible on
      // initialisation, in Firefox).
      try {
        setEditable();
      }
      catch(e) {
        var focusEvent = addEventHandler(document, "focus", function() {
          focusEvent();
          setEditable();
        }, true);
      }

      addEventHandler(document, "keydown", method(this, "keyDown"));
      addEventHandler(document, "keypress", method(this, "keyPress"));
      addEventHandler(document, "keyup", method(this, "keyUp"));

      function cursorActivity() {self.cursorActivity(false);}
      addEventHandler(internetExplorer ? document.body : window, "mouseup", cursorActivity);
      addEventHandler(document.body, "cut", cursorActivity);

      // workaround for a gecko bug [?] where going forward and then
      // back again breaks designmode (no more cursor)
      if (gecko)
        addEventHandler(window, "pagehide", function(){self.unloaded = true;});

      addEventHandler(document.body, "paste", function(event) {
        cursorActivity();
        var text = null;
        try {
          var clipboardData = event.clipboardData || window.clipboardData;
          if (clipboardData) text = clipboardData.getData('Text');
        }
        catch(e) {}
        if (text !== null) {
          event.stop();
          self.replaceSelection(text);
          select.scrollToCursor(self.container);
        }
      });

      if (this.options.autoMatchParens)
        addEventHandler(document.body, "click", method(this, "scheduleParenHighlight"));
    }
    else if (!options.textWrapping) {
      container.style.whiteSpace = "nowrap";
    }
  }

  function isSafeKey(code) {
    return (code >= 16 && code <= 18) || // shift, control, alt
           (code >= 33 && code <= 40); // arrows, home, end
  }

  Editor.prototype = {
    // Import a piece of code into the editor.
    importCode: function(code) {
      var lines = asEditorLines(code), chunk = 1000;
      if (!this.options.incrementalLoading || lines.length < chunk) {
        this.history.push(null, null, lines);
        this.history.reset();
      }
      else {
        var cur = 0, self = this;
        function addChunk() {
          var chunklines = lines.slice(cur, cur + chunk);
          chunklines.push("");
          self.history.push(self.history.nodeBefore(null), null, chunklines);
          self.history.reset();
          cur += chunk;
          if (cur < lines.length)
            parent.setTimeout(addChunk, 1000);
        }
        addChunk();
      }
    },

    // Extract the code from the editor.
    getCode: function() {
      if (!this.container.firstChild)
        return "";

      var accum = [];
      select.markSelection();
      forEach(traverseDOM(this.container.firstChild), method(accum, "push"));
      select.selectMarked();
      // On webkit, don't count last (empty) line if the webkitLastLineHack BR is present
      if (webkit && this.container.lastChild.hackBR)
        accum.pop();
      webkitLastLineHack(this.container);
      return cleanText(accum.join(""));
    },

    checkLine: function(node) {
      if (node === false || !(node == null || node.parentNode == this.container || node.hackBR))
        throw parent.CodeMirror.InvalidLineHandle;
    },

    cursorPosition: function(start) {
      if (start == null) start = true;
      var pos = select.cursorPos(this.container, start);
      if (pos) return {line: pos.node, character: pos.offset};
      else return {line: null, character: 0};
    },

    firstLine: function() {
      return null;
    },

    lastLine: function() {
      var last = this.container.lastChild;
      if (last) last = startOfLine(last);
      if (last && last.hackBR) last = startOfLine(last.previousSibling);
      return last;
    },

    nextLine: function(line) {
      this.checkLine(line);
      var end = endOfLine(line, this.container);
      if (!end || end.hackBR) return false;
      else return end;
    },

    prevLine: function(line) {
      this.checkLine(line);
      if (line == null) return false;
      return startOfLine(line.previousSibling);
    },

    visibleLineCount: function() {
      var line = this.container.firstChild;
      while (line && isBR(line)) line = line.nextSibling; // BR heights are unreliable
      if (!line) return false;
      var innerHeight = (window.innerHeight
                         || document.documentElement.clientHeight
                         || document.body.clientHeight);
      return Math.floor(innerHeight / line.offsetHeight);
    },

    selectLines: function(startLine, startOffset, endLine, endOffset) {
      this.checkLine(startLine);
      var start = {node: startLine, offset: startOffset}, end = null;
      if (endOffset !== undefined) {
        this.checkLine(endLine);
        end = {node: endLine, offset: endOffset};
      }
      select.setCursorPos(this.container, start, end);
      select.scrollToCursor(this.container);
    },

    lineContent: function(line) {
      var accum = [];
      for (line = line ? line.nextSibling : this.container.firstChild;
           line && !isBR(line); line = line.nextSibling)
        accum.push(nodeText(line));
      return cleanText(accum.join(""));
    },

    setLineContent: function(line, content) {
      this.history.commit();
      this.replaceRange({node: line, offset: 0},
                        {node: line, offset: this.history.textAfter(line).length},
                        content);
      this.addDirtyNode(line);
      this.scheduleHighlight();
    },

    removeLine: function(line) {
      var node = line ? line.nextSibling : this.container.firstChild;
      while (node) {
        var next = node.nextSibling;
        removeElement(node);
        if (isBR(node)) break;
        node = next;
      }
      this.addDirtyNode(line);
      this.scheduleHighlight();
    },

    insertIntoLine: function(line, position, content) {
      var before = null;
      if (position == "end") {
        before = endOfLine(line, this.container);
      }
      else {
        for (var cur = line ? line.nextSibling : this.container.firstChild; cur; cur = cur.nextSibling) {
          if (position == 0) {
            before = cur;
            break;
          }
          var text = nodeText(cur);
          if (text.length > position) {
            before = cur.nextSibling;
            content = text.slice(0, position) + content + text.slice(position);
            removeElement(cur);
            break;
          }
          position -= text.length;
        }
      }

      var lines = asEditorLines(content);
      for (var i = 0; i < lines.length; i++) {
        if (i > 0) this.container.insertBefore(document.createElement("BR"), before);
        this.container.insertBefore(makePartSpan(lines[i]), before);
      }
      this.addDirtyNode(line);
      this.scheduleHighlight();
    },

    // Retrieve the selected text.
    selectedText: function() {
      var h = this.history;
      h.commit();

      var start = select.cursorPos(this.container, true),
          end = select.cursorPos(this.container, false);
      if (!start || !end) return "";

      if (start.node == end.node)
        return h.textAfter(start.node).slice(start.offset, end.offset);

      var text = [h.textAfter(start.node).slice(start.offset)];
      for (var pos = h.nodeAfter(start.node); pos != end.node; pos = h.nodeAfter(pos))
        text.push(h.textAfter(pos));
      text.push(h.textAfter(end.node).slice(0, end.offset));
      return cleanText(text.join("\n"));
    },

    // Replace the selection with another piece of text.
    replaceSelection: function(text) {
      this.history.commit();

      var start = select.cursorPos(this.container, true),
          end = select.cursorPos(this.container, false);
      if (!start || !end) return;

      end = this.replaceRange(start, end, text);
      select.setCursorPos(this.container, end);
      webkitLastLineHack(this.container);
    },

    cursorCoords: function(start, internal) {
      var sel = select.cursorPos(this.container, start);
      if (!sel) return null;
      var off = sel.offset, node = sel.node, self = this;
      function measureFromNode(node, xOffset) {
        var y = -(document.body.scrollTop || document.documentElement.scrollTop || 0),
            x = -(document.body.scrollLeft || document.documentElement.scrollLeft || 0) + xOffset;
        forEach([node, internal ? null : window.frameElement], function(n) {
          while (n) {x += n.offsetLeft; y += n.offsetTop;n = n.offsetParent;}
        });
        return {x: x, y: y, yBot: y + node.offsetHeight};
      }
      function withTempNode(text, f) {
        var node = document.createElement("SPAN");
        node.appendChild(document.createTextNode(text));
        try {return f(node);}
        finally {if (node.parentNode) node.parentNode.removeChild(node);}
      }

      while (off) {
        node = node ? node.nextSibling : this.container.firstChild;
        var txt = nodeText(node);
        if (off < txt.length)
          return withTempNode(txt.substr(0, off), function(tmp) {
            tmp.style.position = "absolute"; tmp.style.visibility = "hidden";
            tmp.className = node.className;
            self.container.appendChild(tmp);
            return measureFromNode(node, tmp.offsetWidth);
          });
        off -= txt.length;
      }
      if (node && isSpan(node))
        return measureFromNode(node, node.offsetWidth);
      else if (node && node.nextSibling && isSpan(node.nextSibling))
        return measureFromNode(node.nextSibling, 0);
      else
        return withTempNode("\u200b", function(tmp) {
          if (node) node.parentNode.insertBefore(tmp, node.nextSibling);
          else self.container.insertBefore(tmp, self.container.firstChild);
          return measureFromNode(tmp, 0);
        });
    },

    reroutePasteEvent: function() {
      if (this.capturingPaste || window.opera || (gecko && gecko >= 20101026)) return;
      this.capturingPaste = true;
      var te = window.frameElement.CodeMirror.textareaHack;
      var coords = this.cursorCoords(true, true);
      te.style.top = coords.y + "px";
      if (internetExplorer) {
        var snapshot = select.getBookmark(this.container);
        if (snapshot) this.selectionSnapshot = snapshot;
      }
      parent.focus();
      te.value = "";
      te.focus();

      var self = this;
      parent.setTimeout(function() {
        self.capturingPaste = false;
        window.focus();
        if (self.selectionSnapshot) // IE hack
          window.select.setBookmark(self.container, self.selectionSnapshot);
        var text = te.value;
        if (text) {
          self.replaceSelection(text);
          select.scrollToCursor(self.container);
        }
      }, 10);
    },

    replaceRange: function(from, to, text) {
      var lines = asEditorLines(text);
      lines[0] = this.history.textAfter(from.node).slice(0, from.offset) + lines[0];
      var lastLine = lines[lines.length - 1];
      lines[lines.length - 1] = lastLine + this.history.textAfter(to.node).slice(to.offset);
      var end = this.history.nodeAfter(to.node);
      this.history.push(from.node, end, lines);
      return {node: this.history.nodeBefore(end),
              offset: lastLine.length};
    },

    getSearchCursor: function(string, fromCursor, caseFold) {
      return new SearchCursor(this, string, fromCursor, caseFold);
    },

    // Re-indent the whole buffer
    reindent: function() {
      if (this.container.firstChild)
        this.indentRegion(null, this.container.lastChild);
    },

    reindentSelection: function(direction) {
      if (!select.somethingSelected()) {
        this.indentAtCursor(direction);
      }
      else {
        var start = select.selectionTopNode(this.container, true),
            end = select.selectionTopNode(this.container, false);
        if (start === false || end === false) return;
        this.indentRegion(start, end, direction, true);
      }
    },

    grabKeys: function(eventHandler, filter) {
      this.frozen = eventHandler;
      this.keyFilter = filter;
    },
    ungrabKeys: function() {
      this.frozen = "leave";
    },

    setParser: function(name, parserConfig) {
      Editor.Parser = window[name];
      parserConfig = parserConfig || this.options.parserConfig;
      if (parserConfig && Editor.Parser.configure)
        Editor.Parser.configure(parserConfig);

      if (this.container.firstChild) {
        forEach(this.container.childNodes, function(n) {
          if (n.nodeType != 3) n.dirty = true;
        });
        this.addDirtyNode(this.firstChild);
        this.scheduleHighlight();
      }
    },

    // Intercept enter and tab, and assign their new functions.
    keyDown: function(event) {
      if (this.frozen == "leave") {this.frozen = null; this.keyFilter = null;}
      if (this.frozen && (!this.keyFilter || this.keyFilter(event.keyCode, event))) {
        event.stop();
        this.frozen(event);
        return;
      }

      var code = event.keyCode;
      // Don't scan when the user is typing.
      this.delayScanning();
      // Schedule a paren-highlight event, if configured.
      if (this.options.autoMatchParens)
        this.scheduleParenHighlight();

      // The various checks for !altKey are there because AltGr sets both
      // ctrlKey and altKey to true, and should not be recognised as
      // Control.
      if (code == 13) { // enter
        if (event.ctrlKey && !event.altKey) {
          this.reparseBuffer();
        }
        else {
          select.insertNewlineAtCursor();
          var mode = this.options.enterMode;
          if (mode != "flat") this.indentAtCursor(mode == "keep" ? "keep" : undefined);
          select.scrollToCursor(this.container);
        }
        event.stop();
      }
      else if (code == 9 && this.options.tabMode != "default" && !event.ctrlKey) { // tab
        this.handleTab(!event.shiftKey);
        event.stop();
      }
      else if (code == 32 && event.shiftKey && this.options.tabMode == "default") { // space
        this.handleTab(true);
        event.stop();
      }
      else if (code == 36 && !event.shiftKey && !event.ctrlKey) { // home
        if (this.home()) event.stop();
      }
      else if (code == 35 && !event.shiftKey && !event.ctrlKey) { // end
        if (this.end()) event.stop();
      }
      // Only in Firefox is the default behavior for PgUp/PgDn correct.
      else if (code == 33 && !event.shiftKey && !event.ctrlKey && !gecko) { // PgUp
        if (this.pageUp()) event.stop();
      }
      else if (code == 34 && !event.shiftKey && !event.ctrlKey && !gecko) {  // PgDn
        if (this.pageDown()) event.stop();
      }
      else if ((code == 219 || code == 221) && event.ctrlKey && !event.altKey) { // [, ]
        this.highlightParens(event.shiftKey, true);
        event.stop();
      }
      else if (event.metaKey && !event.shiftKey && (code == 37 || code == 39)) { // Meta-left/right
        var cursor = select.selectionTopNode(this.container);
        if (cursor === false || !this.container.firstChild) return;

        if (code == 37) select.focusAfterNode(startOfLine(cursor), this.container);
        else {
          var end = endOfLine(cursor, this.container);
          select.focusAfterNode(end ? end.previousSibling : this.container.lastChild, this.container);
        }
        event.stop();
      }
      else if ((event.ctrlKey || event.metaKey) && !event.altKey) {
        if ((event.shiftKey && code == 90) || code == 89) { // shift-Z, Y
          select.scrollToNode(this.history.redo());
          event.stop();
        }
        else if (code == 90 || (safari && code == 8)) { // Z, backspace
          select.scrollToNode(this.history.undo());
          event.stop();
        }
        else if (code == 83 && this.options.saveFunction) { // S
          this.options.saveFunction();
          event.stop();
        }
        else if (code == 86 && !mac) { // V
          this.reroutePasteEvent();
        }
      }
    },

    // Check for characters that should re-indent the current line,
    // and prevent Opera from handling enter and tab anyway.
    keyPress: function(event) {
      var electric = this.options.electricChars && Editor.Parser.electricChars, self = this;
      // Hack for Opera, and Firefox on OS X, in which stopping a
      // keydown event does not prevent the associated keypress event
      // from happening, so we have to cancel enter and tab again
      // here.
      if ((this.frozen && (!this.keyFilter || this.keyFilter(event.keyCode || event.code, event))) ||
          event.code == 13 || (event.code == 9 && this.options.tabMode != "default") ||
          (event.code == 32 && event.shiftKey && this.options.tabMode == "default"))
        event.stop();
      else if (mac && (event.ctrlKey || event.metaKey) && event.character == "v") {
        this.reroutePasteEvent();
      }
      else if (electric && electric.indexOf(event.character) != -1)
        parent.setTimeout(function(){self.indentAtCursor(null);}, 0);
      // Work around a bug where pressing backspace at the end of a
      // line, or delete at the start, often causes the cursor to jump
      // to the start of the line in Opera 10.60.
      else if (brokenOpera) {
        if (event.code == 8) { // backspace
          var sel = select.selectionTopNode(this.container), self = this,
              next = sel ? sel.nextSibling : this.container.firstChild;
          if (sel !== false && next && isBR(next))
            parent.setTimeout(function(){
              if (select.selectionTopNode(self.container) == next)
                select.focusAfterNode(next.previousSibling, self.container);
            }, 20);
        }
        else if (event.code == 46) { // delete
          var sel = select.selectionTopNode(this.container), self = this;
          if (sel && isBR(sel)) {
            parent.setTimeout(function(){
              if (select.selectionTopNode(self.container) != sel)
                select.focusAfterNode(sel, self.container);
            }, 20);
          }
        }
      }
      // In 533.* WebKit versions, when the document is big, typing
      // something at the end of a line causes the browser to do some
      // kind of stupid heavy operation, creating delays of several
      // seconds before the typed characters appear. This very crude
      // hack inserts a temporary zero-width space after the cursor to
      // make it not be at the end of the line.
      else if (slowWebkit) {
        var sel = select.selectionTopNode(this.container),
            next = sel ? sel.nextSibling : this.container.firstChild;
        // Doesn't work on empty lines, for some reason those always
        // trigger the delay.
        if (sel && next && isBR(next) && !isBR(sel)) {
          var cheat = document.createTextNode("\u200b");
          this.container.insertBefore(cheat, next);
          parent.setTimeout(function() {
            if (cheat.nodeValue == "\u200b") removeElement(cheat);
            else cheat.nodeValue = cheat.nodeValue.replace("\u200b", "");
          }, 20);
        }
      }

      // Magic incantation that works abound a webkit bug when you
      // can't type on a blank line following a line that's wider than
      // the window.
      if (webkit && !this.options.textWrapping)
        setTimeout(function () {
          var node = select.selectionTopNode(self.container, true);
          if (node && node.nodeType == 3 && node.previousSibling && isBR(node.previousSibling)
              && node.nextSibling && isBR(node.nextSibling))
            node.parentNode.replaceChild(document.createElement("BR"), node.previousSibling);
        }, 50);
    },

    // Mark the node at the cursor dirty when a non-safe key is
    // released.
    keyUp: function(event) {
      this.cursorActivity(isSafeKey(event.keyCode));
    },

    // Indent the line following a given <br>, or null for the first
    // line. If given a <br> element, this must have been highlighted
    // so that it has an indentation method. Returns the whitespace
    // element that has been modified or created (if any).
    indentLineAfter: function(start, direction) {
      function whiteSpaceAfter(node) {
        var ws = node ? node.nextSibling : self.container.firstChild;
        if (!ws || !hasClass(ws, "whitespace")) return null;
        return ws;
      }

      // whiteSpace is the whitespace span at the start of the line,
      // or null if there is no such node.
      var self = this, whiteSpace = whiteSpaceAfter(start);
      var newIndent = 0, curIndent = whiteSpace ? whiteSpace.currentText.length : 0;

      var firstText = whiteSpace ? whiteSpace.nextSibling : (start ? start.nextSibling : this.container.firstChild);
      if (direction == "keep") {
        if (start) {
          var prevWS = whiteSpaceAfter(startOfLine(start.previousSibling))
          if (prevWS) newIndent = prevWS.currentText.length;
        }
      }
      else {
        // Sometimes the start of the line can influence the correct
        // indentation, so we retrieve it.
        var nextChars = (start && firstText && firstText.currentText) ? firstText.currentText : "";

        // Ask the lexical context for the correct indentation, and
        // compute how much this differs from the current indentation.
        if (direction != null && this.options.tabMode != "indent")
          newIndent = direction ? curIndent + indentUnit : Math.max(0, curIndent - indentUnit)
        else if (start)
          newIndent = start.indentation(nextChars, curIndent, direction, firstText);
        else if (Editor.Parser.firstIndentation)
          newIndent = Editor.Parser.firstIndentation(nextChars, curIndent, direction, firstText);
      }

      var indentDiff = newIndent - curIndent;

      // If there is too much, this is just a matter of shrinking a span.
      if (indentDiff < 0) {
        if (newIndent == 0) {
          if (firstText) select.snapshotMove(whiteSpace.firstChild, firstText.firstChild || firstText, 0);
          removeElement(whiteSpace);
          whiteSpace = null;
        }
        else {
          select.snapshotMove(whiteSpace.firstChild, whiteSpace.firstChild, indentDiff, true);
          whiteSpace.currentText = makeWhiteSpace(newIndent);
          whiteSpace.firstChild.nodeValue = whiteSpace.currentText;
        }
      }
      // Not enough...
      else if (indentDiff > 0) {
        // If there is whitespace, we grow it.
        if (whiteSpace) {
          whiteSpace.currentText = makeWhiteSpace(newIndent);
          whiteSpace.firstChild.nodeValue = whiteSpace.currentText;
          select.snapshotMove(whiteSpace.firstChild, whiteSpace.firstChild, indentDiff, true);
        }
        // Otherwise, we have to add a new whitespace node.
        else {
          whiteSpace = makePartSpan(makeWhiteSpace(newIndent));
          whiteSpace.className = "whitespace";
          if (start) insertAfter(whiteSpace, start);
          else this.container.insertBefore(whiteSpace, this.container.firstChild);
          select.snapshotMove(firstText && (firstText.firstChild || firstText),
                              whiteSpace.firstChild, newIndent, false, true);
        }
      }
      // Make sure cursor ends up after the whitespace
      else if (whiteSpace) {
	select.snapshotMove(whiteSpace.firstChild, whiteSpace.firstChild, newIndent, false);
      }
      if (indentDiff != 0) this.addDirtyNode(start);
    },

    // Re-highlight the selected part of the document.
    highlightAtCursor: function() {
      var pos = select.selectionTopNode(this.container, true);
      var to = select.selectionTopNode(this.container, false);
      if (pos === false || to === false) return false;

      select.markSelection();
      if (this.highlight(pos, endOfLine(to, this.container), true, 20) === false)
        return false;
      select.selectMarked();
      return true;
    },

    // When tab is pressed with text selected, the whole selection is
    // re-indented, when nothing is selected, the line with the cursor
    // is re-indented.
    handleTab: function(direction) {
      if (this.options.tabMode == "spaces" && !select.somethingSelected())
        select.insertTabAtCursor();
      else
        this.reindentSelection(direction);
    },

    // Custom home behaviour that doesn't land the cursor in front of
    // leading whitespace unless pressed twice.
    home: function() {
      var cur = select.selectionTopNode(this.container, true), start = cur;
      if (cur === false || !(!cur || cur.isPart || isBR(cur)) || !this.container.firstChild)
        return false;

      while (cur && !isBR(cur)) cur = cur.previousSibling;
      var next = cur ? cur.nextSibling : this.container.firstChild;
      if (next && next != start && next.isPart && hasClass(next, "whitespace"))
        select.focusAfterNode(next, this.container);
      else
        select.focusAfterNode(cur, this.container);

      select.scrollToCursor(this.container);
      return true;
    },

    // Some browsers (Opera) don't manage to handle the end key
    // properly in the face of vertical scrolling.
    end: function() {
      var cur = select.selectionTopNode(this.container, true);
      if (cur === false) return false;
      cur = endOfLine(cur, this.container);
      if (!cur) return false;
      select.focusAfterNode(cur.previousSibling, this.container);
      select.scrollToCursor(this.container);
      return true;
    },

    pageUp: function() {
      var line = this.cursorPosition().line, scrollAmount = this.visibleLineCount();
      if (line === false || scrollAmount === false) return false;
      // Try to keep one line on the screen.
      scrollAmount -= 2;
      for (var i = 0; i < scrollAmount; i++) {
        line = this.prevLine(line);
        if (line === false) break;
      }
      if (i == 0) return false; // Already at first line
      select.setCursorPos(this.container, {node: line, offset: 0});
      select.scrollToCursor(this.container);
      return true;
    },

    pageDown: function() {
      var line = this.cursorPosition().line, scrollAmount = this.visibleLineCount();
      if (line === false || scrollAmount === false) return false;
      // Try to move to the last line of the current page.
      scrollAmount -= 2;
      for (var i = 0; i < scrollAmount; i++) {
        var nextLine = this.nextLine(line);
        if (nextLine === false) break;
        line = nextLine;
      }
      if (i == 0) return false; // Already at last line
      select.setCursorPos(this.container, {node: line, offset: 0});
      select.scrollToCursor(this.container);
      return true;
    },

    // Delay (or initiate) the next paren highlight event.
    scheduleParenHighlight: function() {
      if (this.parenEvent) parent.clearTimeout(this.parenEvent);
      var self = this;
      this.parenEvent = parent.setTimeout(function(){self.highlightParens();}, 300);
    },

    // Take the token before the cursor. If it contains a character in
    // '()[]{}', search for the matching paren/brace/bracket, and
    // highlight them in green for a moment, or red if no proper match
    // was found.
    highlightParens: function(jump, fromKey) {
      var self = this, mark = this.options.markParen;
      if (typeof mark == "string") mark = [mark, mark];
      // give the relevant nodes a colour.
      function highlight(node, ok) {
        if (!node) return;
        if (!mark) {
          node.style.fontWeight = "bold";
          node.style.color = ok ? "#8F8" : "#F88";
        }
        else if (mark.call) mark(node, ok);
        else node.className += " " + mark[ok ? 0 : 1];
      }
      function unhighlight(node) {
        if (!node) return;
        if (mark && !mark.call)
          removeClass(removeClass(node, mark[0]), mark[1]);
        else if (self.options.unmarkParen)
          self.options.unmarkParen(node);
        else {
          node.style.fontWeight = "";
          node.style.color = "";
        }
      }
      if (!fromKey && self.highlighted) {
        unhighlight(self.highlighted[0]);
        unhighlight(self.highlighted[1]);
      }

      if (!window || !window.parent || !window.select) return;
      // Clear the event property.
      if (this.parenEvent) parent.clearTimeout(this.parenEvent);
      this.parenEvent = null;

      // Extract a 'paren' from a piece of text.
      function paren(node) {
        if (node.currentText) {
          var match = node.currentText.match(/^[\s\u00a0]*([\(\)\[\]{}])[\s\u00a0]*$/);
          return match && match[1];
        }
      }
      // Determine the direction a paren is facing.
      function forward(ch) {
        return /[\(\[\{]/.test(ch);
      }

      var ch, cursor = select.selectionTopNode(this.container, true);
      if (!cursor || !this.highlightAtCursor()) return;
      cursor = select.selectionTopNode(this.container, true);
      if (!(cursor && ((ch = paren(cursor)) || (cursor = cursor.nextSibling) && (ch = paren(cursor)))))
        return;
      // We only look for tokens with the same className.
      var className = cursor.className, dir = forward(ch), match = matching[ch];

      // Since parts of the document might not have been properly
      // highlighted, and it is hard to know in advance which part we
      // have to scan, we just try, and when we find dirty nodes we
      // abort, parse them, and re-try.
      function tryFindMatch() {
        var stack = [], ch, ok = true;
        for (var runner = cursor; runner; runner = dir ? runner.nextSibling : runner.previousSibling) {
          if (runner.className == className && isSpan(runner) && (ch = paren(runner))) {
            if (forward(ch) == dir)
              stack.push(ch);
            else if (!stack.length)
              ok = false;
            else if (stack.pop() != matching[ch])
              ok = false;
            if (!stack.length) break;
          }
          else if (runner.dirty || !isSpan(runner) && !isBR(runner)) {
            return {node: runner, status: "dirty"};
          }
        }
        return {node: runner, status: runner && ok};
      }

      while (true) {
        var found = tryFindMatch();
        if (found.status == "dirty") {
          this.highlight(found.node, endOfLine(found.node));
          // Needed because in some corner cases a highlight does not
          // reach a node.
          found.node.dirty = false;
          continue;
        }
        else {
          highlight(cursor, found.status);
          highlight(found.node, found.status);
          if (fromKey)
            parent.setTimeout(function() {unhighlight(cursor); unhighlight(found.node);}, 500);
          else
            self.highlighted = [cursor, found.node];
          if (jump && found.node)
            select.focusAfterNode(found.node.previousSibling, this.container);
          break;
        }
      }
    },

    // Adjust the amount of whitespace at the start of the line that
    // the cursor is on so that it is indented properly.
    indentAtCursor: function(direction) {
      if (!this.container.firstChild) return;
      // The line has to have up-to-date lexical information, so we
      // highlight it first.
      if (!this.highlightAtCursor()) return;
      var cursor = select.selectionTopNode(this.container, false);
      // If we couldn't determine the place of the cursor,
      // there's nothing to indent.
      if (cursor === false)
        return;
      select.markSelection();
      this.indentLineAfter(startOfLine(cursor), direction);
      select.selectMarked();
    },

    // Indent all lines whose start falls inside of the current
    // selection.
    indentRegion: function(start, end, direction, selectAfter) {
      var current = (start = startOfLine(start)), before = start && startOfLine(start.previousSibling);
      if (!isBR(end)) end = endOfLine(end, this.container);
      this.addDirtyNode(start);

      do {
        var next = endOfLine(current, this.container);
        if (current) this.highlight(before, next, true);
        this.indentLineAfter(current, direction);
        before = current;
        current = next;
      } while (current != end);
      if (selectAfter)
        select.setCursorPos(this.container, {node: start, offset: 0}, {node: end, offset: 0});
    },

    // Find the node that the cursor is in, mark it as dirty, and make
    // sure a highlight pass is scheduled.
    cursorActivity: function(safe) {
      // pagehide event hack above
      if (this.unloaded) {
        window.document.designMode = "off";
        window.document.designMode = "on";
        this.unloaded = false;
      }

      if (internetExplorer) {
        this.container.createTextRange().execCommand("unlink");
        clearTimeout(this.saveSelectionSnapshot);
        var self = this;
        this.saveSelectionSnapshot = setTimeout(function() {
          var snapshot = select.getBookmark(self.container);
          if (snapshot) self.selectionSnapshot = snapshot;
        }, 200);
      }

      var activity = this.options.onCursorActivity;
      if (!safe || activity) {
        var cursor = select.selectionTopNode(this.container, false);
        if (cursor === false || !this.container.firstChild) return;
        cursor = cursor || this.container.firstChild;
        if (activity) activity(cursor);
        if (!safe) {
          this.scheduleHighlight();
          this.addDirtyNode(cursor);
        }
      }
    },

    reparseBuffer: function() {
      forEach(this.container.childNodes, function(node) {node.dirty = true;});
      if (this.container.firstChild)
        this.addDirtyNode(this.container.firstChild);
    },

    // Add a node to the set of dirty nodes, if it isn't already in
    // there.
    addDirtyNode: function(node) {
      node = node || this.container.firstChild;
      if (!node) return;

      for (var i = 0; i < this.dirty.length; i++)
        if (this.dirty[i] == node) return;

      if (node.nodeType != 3)
        node.dirty = true;
      this.dirty.push(node);
    },

    allClean: function() {
      return !this.dirty.length;
    },

    // Cause a highlight pass to happen in options.passDelay
    // milliseconds. Clear the existing timeout, if one exists. This
    // way, the passes do not happen while the user is typing, and
    // should as unobtrusive as possible.
    scheduleHighlight: function() {
      // Timeouts are routed through the parent window, because on
      // some browsers designMode windows do not fire timeouts.
      var self = this;
      parent.clearTimeout(this.highlightTimeout);
      this.highlightTimeout = parent.setTimeout(function(){self.highlightDirty();}, this.options.passDelay);
    },

    // Fetch one dirty node, and remove it from the dirty set.
    getDirtyNode: function() {
      while (this.dirty.length > 0) {
        var found = this.dirty.pop();
        // IE8 sometimes throws an unexplainable 'invalid argument'
        // exception for found.parentNode
        try {
          // If the node has been coloured in the meantime, or is no
          // longer in the document, it should not be returned.
          while (found && found.parentNode != this.container)
            found = found.parentNode;
          if (found && (found.dirty || found.nodeType == 3))
            return found;
        } catch (e) {}
      }
      return null;
    },

    // Pick dirty nodes, and highlight them, until options.passTime
    // milliseconds have gone by. The highlight method will continue
    // to next lines as long as it finds dirty nodes. It returns
    // information about the place where it stopped. If there are
    // dirty nodes left after this function has spent all its lines,
    // it shedules another highlight to finish the job.
    highlightDirty: function(force) {
      // Prevent FF from raising an error when it is firing timeouts
      // on a page that's no longer loaded.
      if (!window || !window.parent || !window.select) return false;

      if (!this.options.readOnly) select.markSelection();
      var start, endTime = force ? null : time() + this.options.passTime;
      while ((time() < endTime || force) && (start = this.getDirtyNode())) {
        var result = this.highlight(start, endTime);
        if (result && result.node && result.dirty)
          this.addDirtyNode(result.node.nextSibling);
      }
      if (!this.options.readOnly) select.selectMarked();
      if (start) this.scheduleHighlight();
      return this.dirty.length == 0;
    },

    // Creates a function that, when called through a timeout, will
    // continuously re-parse the document.
    documentScanner: function(passTime) {
      var self = this, pos = null;
      return function() {
        // FF timeout weirdness workaround.
        if (!window || !window.parent || !window.select) return;
        // If the current node is no longer in the document... oh
        // well, we start over.
        if (pos && pos.parentNode != self.container)
          pos = null;
        select.markSelection();
        var result = self.highlight(pos, time() + passTime, true);
        select.selectMarked();
        var newPos = result ? (result.node && result.node.nextSibling) : null;
        pos = (pos == newPos) ? null : newPos;
        self.delayScanning();
      };
    },

    // Starts the continuous scanning process for this document after
    // a given interval.
    delayScanning: function() {
      if (this.scanner) {
        parent.clearTimeout(this.documentScan);
        this.documentScan = parent.setTimeout(this.scanner, this.options.continuousScanning);
      }
    },

    // The function that does the actual highlighting/colouring (with
    // help from the parser and the DOM normalizer). Its interface is
    // rather overcomplicated, because it is used in different
    // situations: ensuring that a certain line is highlighted, or
    // highlighting up to X milliseconds starting from a certain
    // point. The 'from' argument gives the node at which it should
    // start. If this is null, it will start at the beginning of the
    // document. When a timestamp is given with the 'target' argument,
    // it will stop highlighting at that time. If this argument holds
    // a DOM node, it will highlight until it reaches that node. If at
    // any time it comes across two 'clean' lines (no dirty nodes), it
    // will stop, except when 'cleanLines' is true. maxBacktrack is
    // the maximum number of lines to backtrack to find an existing
    // parser instance. This is used to give up in situations where a
    // highlight would take too long and freeze the browser interface.
    highlight: function(from, target, cleanLines, maxBacktrack){
      var container = this.container, self = this, active = this.options.activeTokens;
      var endTime = (typeof target == "number" ? target : null);

      if (!container.firstChild)
        return false;
      // Backtrack to the first node before from that has a partial
      // parse stored.
      while (from && (!from.parserFromHere || from.dirty)) {
        if (maxBacktrack != null && isBR(from) && (--maxBacktrack) < 0)
          return false;
        from = from.previousSibling;
      }
      // If we are at the end of the document, do nothing.
      if (from && !from.nextSibling)
        return false;

      // Check whether a part (<span> node) and the corresponding token
      // match.
      function correctPart(token, part){
        return !part.reduced && part.currentText == token.value && part.className == token.style;
      }
      // Shorten the text associated with a part by chopping off
      // characters from the front. Note that only the currentText
      // property gets changed. For efficiency reasons, we leave the
      // nodeValue alone -- we set the reduced flag to indicate that
      // this part must be replaced.
      function shortenPart(part, minus){
        part.currentText = part.currentText.substring(minus);
        part.reduced = true;
      }
      // Create a part corresponding to a given token.
      function tokenPart(token){
        var part = makePartSpan(token.value);
        part.className = token.style;
        return part;
      }

      function maybeTouch(node) {
        if (node) {
          var old = node.oldNextSibling;
          if (lineDirty || old === undefined || node.nextSibling != old)
            self.history.touch(node);
          node.oldNextSibling = node.nextSibling;
        }
        else {
          var old = self.container.oldFirstChild;
          if (lineDirty || old === undefined || self.container.firstChild != old)
            self.history.touch(null);
          self.container.oldFirstChild = self.container.firstChild;
        }
      }

      // Get the token stream. If from is null, we start with a new
      // parser from the start of the frame, otherwise a partial parse
      // is resumed.
      var traversal = traverseDOM(from ? from.nextSibling : container.firstChild),
          stream = stringStream(traversal),
          parsed = from ? from.parserFromHere(stream) : Editor.Parser.make(stream);

      function surroundedByBRs(node) {
        return (node.previousSibling == null || isBR(node.previousSibling)) &&
               (node.nextSibling == null || isBR(node.nextSibling));
      }

      // parts is an interface to make it possible to 'delay' fetching
      // the next DOM node until we are completely done with the one
      // before it. This is necessary because often the next node is
      // not yet available when we want to proceed past the current
      // one.
      var parts = {
        current: null,
        // Fetch current node.
        get: function(){
          if (!this.current)
            this.current = traversal.nodes.shift();
          return this.current;
        },
        // Advance to the next part (do not fetch it yet).
        next: function(){
          this.current = null;
        },
        // Remove the current part from the DOM tree, and move to the
        // next.
        remove: function(){
          container.removeChild(this.get());
          this.current = null;
        },
        // Advance to the next part that is not empty, discarding empty
        // parts.
        getNonEmpty: function(){
          var part = this.get();
          // Allow empty nodes when they are alone on a line, needed
          // for the FF cursor bug workaround (see select.js,
          // insertNewlineAtCursor).
          while (part && isSpan(part) && part.currentText == "") {
            // Leave empty nodes that are alone on a line alone in
            // Opera, since that browsers doesn't deal well with
            // having 2 BRs in a row.
            if (window.opera && surroundedByBRs(part)) {
              this.next();
              part = this.get();
            }
            else {
              var old = part;
              this.remove();
              part = this.get();
              // Adjust selection information, if any. See select.js for details.
              select.snapshotMove(old.firstChild, part && (part.firstChild || part), 0);
            }
          }

          return part;
        }
      };

      var lineDirty = false, prevLineDirty = true, lineNodes = 0;

      // This forEach loops over the tokens from the parsed stream, and
      // at the same time uses the parts object to proceed through the
      // corresponding DOM nodes.
      forEach(parsed, function(token){
        var part = parts.getNonEmpty();

        if (token.value == "\n"){
          // The idea of the two streams actually staying synchronized
          // is such a long shot that we explicitly check.
          if (!isBR(part))
            throw "Parser out of sync. Expected BR.";

          if (part.dirty || !part.indentation) lineDirty = true;
          maybeTouch(from);
          from = part;

          // Every <br> gets a copy of the parser state and a lexical
          // context assigned to it. The first is used to be able to
          // later resume parsing from this point, the second is used
          // for indentation.
          part.parserFromHere = parsed.copy();
          part.indentation = token.indentation || alwaysZero;
          part.dirty = false;

          // If the target argument wasn't an integer, go at least
          // until that node.
          if (endTime == null && part == target) throw StopIteration;

          // A clean line with more than one node means we are done.
          // Throwing a StopIteration is the way to break out of a
          // MochiKit forEach loop.
          if ((endTime != null && time() >= endTime) || (!lineDirty && !prevLineDirty && lineNodes > 1 && !cleanLines))
            throw StopIteration;
          prevLineDirty = lineDirty; lineDirty = false; lineNodes = 0;
          parts.next();
        }
        else {
          if (!isSpan(part))
            throw "Parser out of sync. Expected SPAN.";
          if (part.dirty)
            lineDirty = true;
          lineNodes++;

          // If the part matches the token, we can leave it alone.
          if (correctPart(token, part)){
            if (active && part.dirty) active(part, token, self);
            part.dirty = false;
            parts.next();
          }
          // Otherwise, we have to fix it.
          else {
            lineDirty = true;
            // Insert the correct part.
            var newPart = tokenPart(token);
            container.insertBefore(newPart, part);
            if (active) active(newPart, token, self);
            var tokensize = token.value.length;
            var offset = 0;
            // Eat up parts until the text for this token has been
            // removed, adjusting the stored selection info (see
            // select.js) in the process.
            while (tokensize > 0) {
              part = parts.get();
              var partsize = part.currentText.length;
              select.snapshotReplaceNode(part.firstChild, newPart.firstChild, tokensize, offset);
              if (partsize > tokensize){
                shortenPart(part, tokensize);
                tokensize = 0;
              }
              else {
                tokensize -= partsize;
                offset += partsize;
                parts.remove();
              }
            }
          }
        }
      });
      maybeTouch(from);
      webkitLastLineHack(this.container);

      // The function returns some status information that is used by
      // hightlightDirty to determine whether and where it has to
      // continue.
      return {node: parts.getNonEmpty(),
              dirty: lineDirty};
    }
  };

  return Editor;
})();

addEventHandler(window, "load", function() {
  var CodeMirror = window.frameElement.CodeMirror;
  var e = CodeMirror.editor = new Editor(CodeMirror.options);
  parent.setTimeout(method(CodeMirror, "init"), 0);
});

/* Functionality for finding, storing, and restoring selections
 *
 * This does not provide a generic API, just the minimal functionality
 * required by the CodeMirror system.
 */

// Namespace object.
var select = {};

(function() {
  select.ie_selection = document.selection && document.selection.createRangeCollection;

  // Find the 'top-level' (defined as 'a direct child of the node
  // passed as the top argument') node that the given node is
  // contained in. Return null if the given node is not inside the top
  // node.
  function topLevelNodeAt(node, top) {
    while (node && node.parentNode != top)
      node = node.parentNode;
    return node;
  }

  // Find the top-level node that contains the node before this one.
  function topLevelNodeBefore(node, top) {
    while (!node.previousSibling && node.parentNode != top)
      node = node.parentNode;
    return topLevelNodeAt(node.previousSibling, top);
  }

  var fourSpaces = "\u00a0\u00a0\u00a0\u00a0";

  select.scrollToNode = function(node, cursor) {
    if (!node) return;
    var element = node, body = document.body,
        html = document.documentElement,
        atEnd = !element.nextSibling || !element.nextSibling.nextSibling
                || !element.nextSibling.nextSibling.nextSibling;
    // In Opera (and recent Webkit versions), BR elements *always*
    // have a offsetTop property of zero.
    var compensateHack = 0;
    while (element && !element.offsetTop) {
      compensateHack++;
      element = element.previousSibling;
    }
    // atEnd is another kludge for these browsers -- if the cursor is
    // at the end of the document, and the node doesn't have an
    // offset, just scroll to the end.
    if (compensateHack == 0) atEnd = false;

    // WebKit has a bad habit of (sometimes) happily returning bogus
    // offsets when the document has just been changed. This seems to
    // always be 5/5, so we don't use those.
    if (webkit && element && element.offsetTop == 5 && element.offsetLeft == 5)
      return;

    var y = compensateHack * (element ? element.offsetHeight : 0), x = 0,
        width = (node ? node.offsetWidth : 0), pos = element;
    while (pos && pos.offsetParent) {
      y += pos.offsetTop;
      // Don't count X offset for <br> nodes
      if (!isBR(pos))
        x += pos.offsetLeft;
      pos = pos.offsetParent;
    }

    var scroll_x = body.scrollLeft || html.scrollLeft || 0,
        scroll_y = body.scrollTop || html.scrollTop || 0,
        scroll = false, screen_width = window.innerWidth || html.clientWidth || 0;

    if (cursor || width < screen_width) {
      if (cursor) {
        var off = select.offsetInNode(node), size = nodeText(node).length;
        if (size) x += width * (off / size);
      }
      var screen_x = x - scroll_x;
      if (screen_x < 0 || screen_x > screen_width) {
        scroll_x = x;
        scroll = true;
      }
    }
    var screen_y = y - scroll_y;
    if (screen_y < 0 || atEnd || screen_y > (window.innerHeight || html.clientHeight || 0) - 50) {
      scroll_y = atEnd ? 1e6 : y;
      scroll = true;
    }
    if (scroll) window.scrollTo(scroll_x, scroll_y);
  };

  select.scrollToCursor = function(container) {
    select.scrollToNode(select.selectionTopNode(container, true) || container.firstChild, true);
  };

  // Used to prevent restoring a selection when we do not need to.
  var currentSelection = null;

  select.snapshotChanged = function() {
    if (currentSelection) currentSelection.changed = true;
  };

  // Find the 'leaf' node (BR or text) after the given one.
  function baseNodeAfter(node) {
    var next = node.nextSibling;
    if (next) {
      while (next.firstChild) next = next.firstChild;
      if (next.nodeType == 3 || isBR(next)) return next;
      else return baseNodeAfter(next);
    }
    else {
      var parent = node.parentNode;
      while (parent && !parent.nextSibling) parent = parent.parentNode;
      return parent && baseNodeAfter(parent);
    }
  }

  // This is called by the code in editor.js whenever it is replacing
  // a text node. The function sees whether the given oldNode is part
  // of the current selection, and updates this selection if it is.
  // Because nodes are often only partially replaced, the length of
  // the part that gets replaced has to be taken into account -- the
  // selection might stay in the oldNode if the newNode is smaller
  // than the selection's offset. The offset argument is needed in
  // case the selection does move to the new object, and the given
  // length is not the whole length of the new node (part of it might
  // have been used to replace another node).
  select.snapshotReplaceNode = function(from, to, length, offset) {
    if (!currentSelection) return;

    function replace(point) {
      if (from == point.node) {
        currentSelection.changed = true;
        if (length && point.offset > length) {
          point.offset -= length;
        }
        else {
          point.node = to;
          point.offset += (offset || 0);
        }
      }
      else if (select.ie_selection && point.offset == 0 && point.node == baseNodeAfter(from)) {
        currentSelection.changed = true;
      }
    }
    replace(currentSelection.start);
    replace(currentSelection.end);
  };

  select.snapshotMove = function(from, to, distance, relative, ifAtStart) {
    if (!currentSelection) return;

    function move(point) {
      if (from == point.node && (!ifAtStart || point.offset == 0)) {
        currentSelection.changed = true;
        point.node = to;
        if (relative) point.offset = Math.max(0, point.offset + distance);
        else point.offset = distance;
      }
    }
    move(currentSelection.start);
    move(currentSelection.end);
  };

  // Most functions are defined in two ways, one for the IE selection
  // model, one for the W3C one.
  if (select.ie_selection) {
    function selRange() {
      var sel = document.selection;
      if (!sel) return null;
      if (sel.createRange) return sel.createRange();
      else return sel.createTextRange();
    }

    function selectionNode(start) {
      var range = selRange();
      range.collapse(start);

      function nodeAfter(node) {
        var found = null;
        while (!found && node) {
          found = node.nextSibling;
          node = node.parentNode;
        }
        return nodeAtStartOf(found);
      }

      function nodeAtStartOf(node) {
        while (node && node.firstChild) node = node.firstChild;
        return {node: node, offset: 0};
      }

      var containing = range.parentElement();
      if (!isAncestor(document.body, containing)) return null;
      if (!containing.firstChild) return nodeAtStartOf(containing);

      var working = range.duplicate();
      working.moveToElementText(containing);
      working.collapse(true);
      for (var cur = containing.firstChild; cur; cur = cur.nextSibling) {
        if (cur.nodeType == 3) {
          var size = cur.nodeValue.length;
          working.move("character", size);
        }
        else {
          working.moveToElementText(cur);
          working.collapse(false);
        }

        var dir = range.compareEndPoints("StartToStart", working);
        if (dir == 0) return nodeAfter(cur);
        if (dir == 1) continue;
        if (cur.nodeType != 3) return nodeAtStartOf(cur);

        working.setEndPoint("StartToEnd", range);
        return {node: cur, offset: size - working.text.length};
      }
      return nodeAfter(containing);
    }

    select.markSelection = function() {
      currentSelection = null;
      var sel = document.selection;
      if (!sel) return;
      var start = selectionNode(true),
          end = selectionNode(false);
      if (!start || !end) return;
      currentSelection = {start: start, end: end, changed: false};
    };

    select.selectMarked = function() {
      if (!currentSelection || !currentSelection.changed) return;

      function makeRange(point) {
        var range = document.body.createTextRange(),
            node = point.node;
        if (!node) {
          range.moveToElementText(document.body);
          range.collapse(false);
        }
        else if (node.nodeType == 3) {
          range.moveToElementText(node.parentNode);
          var offset = point.offset;
          while (node.previousSibling) {
            node = node.previousSibling;
            offset += (node.innerText || "").length;
          }
          range.move("character", offset);
        }
        else {
          range.moveToElementText(node);
          range.collapse(true);
        }
        return range;
      }

      var start = makeRange(currentSelection.start), end = makeRange(currentSelection.end);
      start.setEndPoint("StartToEnd", end);
      start.select();
    };

    select.offsetInNode = function(node) {
      var range = selRange();
      if (!range) return 0;
      var range2 = range.duplicate();
      try {range2.moveToElementText(node);} catch(e){return 0;}
      range.setEndPoint("StartToStart", range2);
      return range.text.length;
    };

    // Get the top-level node that one end of the cursor is inside or
    // after. Note that this returns false for 'no cursor', and null
    // for 'start of document'.
    select.selectionTopNode = function(container, start) {
      var range = selRange();
      if (!range) return false;
      var range2 = range.duplicate();
      range.collapse(start);
      var around = range.parentElement();
      if (around && isAncestor(container, around)) {
        // Only use this node if the selection is not at its start.
        range2.moveToElementText(around);
        if (range.compareEndPoints("StartToStart", range2) == 1)
          return topLevelNodeAt(around, container);
      }

      // Move the start of a range to the start of a node,
      // compensating for the fact that you can't call
      // moveToElementText with text nodes.
      function moveToNodeStart(range, node) {
        if (node.nodeType == 3) {
          var count = 0, cur = node.previousSibling;
          while (cur && cur.nodeType == 3) {
            count += cur.nodeValue.length;
            cur = cur.previousSibling;
          }
          if (cur) {
            try{range.moveToElementText(cur);}
            catch(e){return false;}
            range.collapse(false);
          }
          else range.moveToElementText(node.parentNode);
          if (count) range.move("character", count);
        }
        else {
          try{range.moveToElementText(node);}
          catch(e){return false;}
        }
        return true;
      }

      // Do a binary search through the container object, comparing
      // the start of each node to the selection
      var start = 0, end = container.childNodes.length - 1;
      while (start < end) {
        var middle = Math.ceil((end + start) / 2), node = container.childNodes[middle];
        if (!node) return false; // Don't ask. IE6 manages this sometimes.
        if (!moveToNodeStart(range2, node)) return false;
        if (range.compareEndPoints("StartToStart", range2) == 1)
          start = middle;
        else
          end = middle - 1;
      }

      if (start == 0) {
        var test1 = selRange(), test2 = test1.duplicate();
        try {
          test2.moveToElementText(container);
        } catch(exception) {
          return null;
        }
        if (test1.compareEndPoints("StartToStart", test2) == 0)
          return null;
      }
      return container.childNodes[start] || null;
    };

    // Place the cursor after this.start. This is only useful when
    // manually moving the cursor instead of restoring it to its old
    // position.
    select.focusAfterNode = function(node, container) {
      var range = document.body.createTextRange();
      range.moveToElementText(node || container);
      range.collapse(!node);
      range.select();
    };

    select.somethingSelected = function() {
      var range = selRange();
      return range && (range.text != "");
    };

    function insertAtCursor(html) {
      var range = selRange();
      if (range) {
        range.pasteHTML(html);
        range.collapse(false);
        range.select();
      }
    }

    // Used to normalize the effect of the enter key, since browsers
    // do widely different things when pressing enter in designMode.
    select.insertNewlineAtCursor = function() {
      insertAtCursor("<br>");
    };

    select.insertTabAtCursor = function() {
      insertAtCursor(fourSpaces);
    };

    // Get the BR node at the start of the line on which the cursor
    // currently is, and the offset into the line. Returns null as
    // node if cursor is on first line.
    select.cursorPos = function(container, start) {
      var range = selRange();
      if (!range) return null;

      var topNode = select.selectionTopNode(container, start);
      while (topNode && !isBR(topNode))
        topNode = topNode.previousSibling;

      var range2 = range.duplicate();
      range.collapse(start);
      if (topNode) {
        range2.moveToElementText(topNode);
        range2.collapse(false);
      }
      else {
        // When nothing is selected, we can get all kinds of funky errors here.
        try { range2.moveToElementText(container); }
        catch (e) { return null; }
        range2.collapse(true);
      }
      range.setEndPoint("StartToStart", range2);

      return {node: topNode, offset: range.text.length};
    };

    select.setCursorPos = function(container, from, to) {
      function rangeAt(pos) {
        var range = document.body.createTextRange();
        if (!pos.node) {
          range.moveToElementText(container);
          range.collapse(true);
        }
        else {
          range.moveToElementText(pos.node);
          range.collapse(false);
        }
        range.move("character", pos.offset);
        return range;
      }

      var range = rangeAt(from);
      if (to && to != from)
        range.setEndPoint("EndToEnd", rangeAt(to));
      range.select();
    }

    // Some hacks for storing and re-storing the selection when the editor loses and regains focus.
    select.getBookmark = function (container) {
      var from = select.cursorPos(container, true), to = select.cursorPos(container, false);
      if (from && to) return {from: from, to: to};
    };

    // Restore a stored selection.
    select.setBookmark = function(container, mark) {
      if (!mark) return;
      select.setCursorPos(container, mark.from, mark.to);
    };
  }
  // W3C model
  else {
    // Find the node right at the cursor, not one of its
    // ancestors with a suitable offset. This goes down the DOM tree
    // until a 'leaf' is reached (or is it *up* the DOM tree?).
    function innerNode(node, offset) {
      while (node.nodeType != 3 && !isBR(node)) {
        var newNode = node.childNodes[offset] || node.nextSibling;
        offset = 0;
        while (!newNode && node.parentNode) {
          node = node.parentNode;
          newNode = node.nextSibling;
        }
        node = newNode;
        if (!newNode) break;
      }
      return {node: node, offset: offset};
    }

    // Store start and end nodes, and offsets within these, and refer
    // back to the selection object from those nodes, so that this
    // object can be updated when the nodes are replaced before the
    // selection is restored.
    select.markSelection = function () {
      var selection = window.getSelection();
      if (!selection || selection.rangeCount == 0)
        return (currentSelection = null);
      var range = selection.getRangeAt(0);

      currentSelection = {
        start: innerNode(range.startContainer, range.startOffset),
        end: innerNode(range.endContainer, range.endOffset),
        changed: false
      };
    };

    select.selectMarked = function () {
      var cs = currentSelection;
      // on webkit-based browsers, it is apparently possible that the
      // selection gets reset even when a node that is not one of the
      // endpoints get messed with. the most common situation where
      // this occurs is when a selection is deleted or overwitten. we
      // check for that here.
      function focusIssue() {
        if (cs.start.node == cs.end.node && cs.start.offset == cs.end.offset) {
          var selection = window.getSelection();
          if (!selection || selection.rangeCount == 0) return true;
          var range = selection.getRangeAt(0), point = innerNode(range.startContainer, range.startOffset);
          return cs.start.node != point.node || cs.start.offset != point.offset;
        }
      }
      if (!cs || !(cs.changed || (webkit && focusIssue()))) return;
      var range = document.createRange();

      function setPoint(point, which) {
        if (point.node) {
          // Some magic to generalize the setting of the start and end
          // of a range.
          if (point.offset == 0)
            range["set" + which + "Before"](point.node);
          else
            range["set" + which](point.node, point.offset);
        }
        else {
          range.setStartAfter(document.body.lastChild || document.body);
        }
      }

      setPoint(cs.end, "End");
      setPoint(cs.start, "Start");
      selectRange(range);
    };

    // Helper for selecting a range object.
    function selectRange(range) {
      var selection = window.getSelection();
      if (!selection) return;
      selection.removeAllRanges();
      selection.addRange(range);
    }
    function selectionRange() {
      var selection = window.getSelection();
      if (!selection || selection.rangeCount == 0)
        return false;
      else
        return selection.getRangeAt(0);
    }

    // Finding the top-level node at the cursor in the W3C is, as you
    // can see, quite an involved process.
    select.selectionTopNode = function(container, start) {
      var range = selectionRange();
      if (!range) return false;

      var node = start ? range.startContainer : range.endContainer;
      var offset = start ? range.startOffset : range.endOffset;
      // Work around (yet another) bug in Opera's selection model.
      if (window.opera && !start && range.endContainer == container && range.endOffset == range.startOffset + 1 &&
          container.childNodes[range.startOffset] && isBR(container.childNodes[range.startOffset]))
        offset--;

      // For text nodes, we look at the node itself if the cursor is
      // inside, or at the node before it if the cursor is at the
      // start.
      if (node.nodeType == 3){
        if (offset > 0)
          return topLevelNodeAt(node, container);
        else
          return topLevelNodeBefore(node, container);
      }
      // Occasionally, browsers will return the HTML node as
      // selection. If the offset is 0, we take the start of the frame
      // ('after null'), otherwise, we take the last node.
      else if (node.nodeName.toUpperCase() == "HTML") {
        return (offset == 1 ? null : container.lastChild);
      }
      // If the given node is our 'container', we just look up the
      // correct node by using the offset.
      else if (node == container) {
        return (offset == 0) ? null : node.childNodes[offset - 1];
      }
      // In any other case, we have a regular node. If the cursor is
      // at the end of the node, we use the node itself, if it is at
      // the start, we use the node before it, and in any other
      // case, we look up the child before the cursor and use that.
      else {
        if (offset == node.childNodes.length)
          return topLevelNodeAt(node, container);
        else if (offset == 0)
          return topLevelNodeBefore(node, container);
        else
          return topLevelNodeAt(node.childNodes[offset - 1], container);
      }
    };

    select.focusAfterNode = function(node, container) {
      var range = document.createRange();
      range.setStartBefore(container.firstChild || container);
      // In Opera, setting the end of a range at the end of a line
      // (before a BR) will cause the cursor to appear on the next
      // line, so we set the end inside of the start node when
      // possible.
      if (node && !node.firstChild)
        range.setEndAfter(node);
      else if (node)
        range.setEnd(node, node.childNodes.length);
      else
        range.setEndBefore(container.firstChild || container);
      range.collapse(false);
      selectRange(range);
    };

    select.somethingSelected = function() {
      var range = selectionRange();
      return range && !range.collapsed;
    };

    select.offsetInNode = function(node) {
      var range = selectionRange();
      if (!range) return 0;
      range = range.cloneRange();
      range.setStartBefore(node);
      return range.toString().length;
    };

    select.insertNodeAtCursor = function(node) {
      var range = selectionRange();
      if (!range) return;

      range.deleteContents();
      range.insertNode(node);
      webkitLastLineHack(document.body);

      // work around weirdness where Opera will magically insert a new
      // BR node when a BR node inside a span is moved around. makes
      // sure the BR ends up outside of spans.
      if (window.opera && isBR(node) && isSpan(node.parentNode)) {
        var next = node.nextSibling, p = node.parentNode, outer = p.parentNode;
        outer.insertBefore(node, p.nextSibling);
        var textAfter = "";
        for (; next && next.nodeType == 3; next = next.nextSibling) {
          textAfter += next.nodeValue;
          removeElement(next);
        }
        outer.insertBefore(makePartSpan(textAfter, document), node.nextSibling);
      }
      range = document.createRange();
      range.selectNode(node);
      range.collapse(false);
      selectRange(range);
    }

    select.insertNewlineAtCursor = function() {
      select.insertNodeAtCursor(document.createElement("BR"));
    };

    select.insertTabAtCursor = function() {
      select.insertNodeAtCursor(document.createTextNode(fourSpaces));
    };

    select.cursorPos = function(container, start) {
      var range = selectionRange();
      if (!range) return;

      var topNode = select.selectionTopNode(container, start);
      while (topNode && !isBR(topNode))
        topNode = topNode.previousSibling;

      range = range.cloneRange();
      range.collapse(start);
      if (topNode)
        range.setStartAfter(topNode);
      else
        range.setStartBefore(container);

      var text = range.toString();
      return {node: topNode, offset: text.length};
    };

    select.setCursorPos = function(container, from, to) {
      var range = document.createRange();

      function setPoint(node, offset, side) {
        if (offset == 0 && node && !node.nextSibling) {
          range["set" + side + "After"](node);
          return true;
        }

        if (!node)
          node = container.firstChild;
        else
          node = node.nextSibling;

        if (!node) return;

        if (offset == 0) {
          range["set" + side + "Before"](node);
          return true;
        }

        var backlog = []
        function decompose(node) {
          if (node.nodeType == 3)
            backlog.push(node);
          else
            forEach(node.childNodes, decompose);
        }
        while (true) {
          while (node && !backlog.length) {
            decompose(node);
            node = node.nextSibling;
          }
          var cur = backlog.shift();
          if (!cur) return false;

          var length = cur.nodeValue.length;
          if (length >= offset) {
            range["set" + side](cur, offset);
            return true;
          }
          offset -= length;
        }
      }

      to = to || from;
      if (setPoint(to.node, to.offset, "End") && setPoint(from.node, from.offset, "Start"))
        selectRange(range);
    };
  }
})();

/* String streams are the things fed to parsers (which can feed them
 * to a tokenizer if they want). They provide peek and next methods
 * for looking at the current character (next 'consumes' this
 * character, peek does not), and a get method for retrieving all the
 * text that was consumed since the last time get was called.
 *
 * An easy mistake to make is to let a StopIteration exception finish
 * the token stream while there are still characters pending in the
 * string stream (hitting the end of the buffer while parsing a
 * token). To make it easier to detect such errors, the stringstreams
 * throw an exception when this happens.
 */

// Make a stringstream stream out of an iterator that returns strings.
// This is applied to the result of traverseDOM (see codemirror.js),
// and the resulting stream is fed to the parser.
var stringStream = function(source){
  // String that's currently being iterated over.
  var current = "";
  // Position in that string.
  var pos = 0;
  // Accumulator for strings that have been iterated over but not
  // get()-ed yet.
  var accum = "";
  // Make sure there are more characters ready, or throw
  // StopIteration.
  function ensureChars() {
    while (pos == current.length) {
      accum += current;
      current = ""; // In case source.next() throws
      pos = 0;
      try {current = source.next();}
      catch (e) {
        if (e != StopIteration) throw e;
        else return false;
      }
    }
    return true;
  }

  return {
    // peek: -> character
    // Return the next character in the stream.
    peek: function() {
      if (!ensureChars()) return null;
      return current.charAt(pos);
    },
    // next: -> character
    // Get the next character, throw StopIteration if at end, check
    // for unused content.
    next: function() {
      if (!ensureChars()) {
        if (accum.length > 0)
          throw "End of stringstream reached without emptying buffer ('" + accum + "').";
        else
          throw StopIteration;
      }
      return current.charAt(pos++);
    },
    // get(): -> string
    // Return the characters iterated over since the last call to
    // .get().
    get: function() {
      var temp = accum;
      accum = "";
      if (pos > 0){
        temp += current.slice(0, pos);
        current = current.slice(pos);
        pos = 0;
      }
      return temp;
    },
    // Push a string back into the stream.
    push: function(str) {
      current = current.slice(0, pos) + str + current.slice(pos);
    },
    lookAhead: function(str, consume, skipSpaces, caseInsensitive) {
      function cased(str) {return caseInsensitive ? str.toLowerCase() : str;}
      str = cased(str);
      var found = false;

      var _accum = accum, _pos = pos;
      if (skipSpaces) this.nextWhileMatches(/[\s\u00a0]/);

      while (true) {
        var end = pos + str.length, left = current.length - pos;
        if (end <= current.length) {
          found = str == cased(current.slice(pos, end));
          pos = end;
          break;
        }
        else if (str.slice(0, left) == cased(current.slice(pos))) {
          accum += current; current = "";
          try {current = source.next();}
          catch (e) {if (e != StopIteration) throw e; break;}
          pos = 0;
          str = str.slice(left);
        }
        else {
          break;
        }
      }

      if (!(found && consume)) {
        current = accum.slice(_accum.length) + current;
        pos = _pos;
        accum = _accum;
      }

      return found;
    },
    // Wont't match past end of line.
    lookAheadRegex: function(regex, consume) {
      if (regex.source.charAt(0) != "^")
        throw new Error("Regexps passed to lookAheadRegex must start with ^");

      // Fetch the rest of the line
      while (current.indexOf("\n", pos) == -1) {
        try {current += source.next();}
        catch (e) {if (e != StopIteration) throw e; break;}
      }
      var matched = current.slice(pos).match(regex);
      if (matched && consume) pos += matched[0].length;
      return matched;
    },

    // Utils built on top of the above
    // more: -> boolean
    // Produce true if the stream isn't empty.
    more: function() {
      return this.peek() !== null;
    },
    applies: function(test) {
      var next = this.peek();
      return (next !== null && test(next));
    },
    nextWhile: function(test) {
      var next;
      while ((next = this.peek()) !== null && test(next))
        this.next();
    },
    matches: function(re) {
      var next = this.peek();
      return (next !== null && re.test(next));
    },
    nextWhileMatches: function(re) {
      var next;
      while ((next = this.peek()) !== null && re.test(next))
        this.next();
    },
    equals: function(ch) {
      return ch === this.peek();
    },
    endOfLine: function() {
      var next = this.peek();
      return next == null || next == "\n";
    }
  };
};

// A framework for simple tokenizers. Takes care of newlines and
// white-space, and of getting the text from the source stream into
// the token object. A state is a function of two arguments -- a
// string stream and a setState function. The second can be used to
// change the tokenizer's state, and can be ignored for stateless
// tokenizers. This function should advance the stream over a token
// and return a string or object containing information about the next
// token, or null to pass and have the (new) state be called to finish
// the token. When a string is given, it is wrapped in a {style, type}
// object. In the resulting object, the characters consumed are stored
// under the content property. Any whitespace following them is also
// automatically consumed, and added to the value property. (Thus,
// content is the actual meaningful part of the token, while value
// contains all the text it spans.)

function tokenizer(source, state) {
  // Newlines are always a separate token.
  function isWhiteSpace(ch) {
    // The messy regexp is because IE's regexp matcher is of the
    // opinion that non-breaking spaces are no whitespace.
    return ch != "\n" && /^[\s\u00a0]*$/.test(ch);
  }

  var tokenizer = {
    state: state,

    take: function(type) {
      if (typeof(type) == "string")
        type = {style: type, type: type};

      type.content = (type.content || "") + source.get();
      if (!/\n$/.test(type.content))
        source.nextWhile(isWhiteSpace);
      type.value = type.content + source.get();
      return type;
    },

    next: function () {
      if (!source.more()) throw StopIteration;

      var type;
      if (source.equals("\n")) {
        source.next();
        return this.take("whitespace");
      }

      if (source.applies(isWhiteSpace))
        type = "whitespace";
      else
        while (!type)
          type = this.state(source, function(s) {tokenizer.state = s;});

      return this.take(type);
    }
  };
  return tokenizer;
}

/**
 * Storage and control for undo information within a CodeMirror
 * editor. 'Why on earth is such a complicated mess required for
 * that?', I hear you ask. The goal, in implementing this, was to make
 * the complexity of storing and reverting undo information depend
 * only on the size of the edited or restored content, not on the size
 * of the whole document. This makes it necessary to use a kind of
 * 'diff' system, which, when applied to a DOM tree, causes some
 * complexity and hackery.
 *
 * In short, the editor 'touches' BR elements as it parses them, and
 * the UndoHistory stores these. When nothing is touched in commitDelay
 * milliseconds, the changes are committed: It goes over all touched
 * nodes, throws out the ones that did not change since last commit or
 * are no longer in the document, and assembles the rest into zero or
 * more 'chains' -- arrays of adjacent lines. Links back to these
 * chains are added to the BR nodes, while the chain that previously
 * spanned these nodes is added to the undo history. Undoing a change
 * means taking such a chain off the undo history, restoring its
 * content (text is saved per line) and linking it back into the
 * document.
 */

// A history object needs to know about the DOM container holding the
// document, the maximum amount of undo levels it should store, the
// delay (of no input) after which it commits a set of changes, and,
// unfortunately, the 'parent' window -- a window that is not in
// designMode, and on which setTimeout works in every browser.
function UndoHistory(container, maxDepth, commitDelay, editor) {
  this.container = container;
  this.maxDepth = maxDepth; this.commitDelay = commitDelay;
  this.editor = editor;
  // This line object represents the initial, empty editor.
  var initial = {text: "", from: null, to: null};
  // As the borders between lines are represented by BR elements, the
  // start of the first line and the end of the last one are
  // represented by null. Since you can not store any properties
  // (links to line objects) in null, these properties are used in
  // those cases.
  this.first = initial; this.last = initial;
  // Similarly, a 'historyTouched' property is added to the BR in
  // front of lines that have already been touched, and 'firstTouched'
  // is used for the first line.
  this.firstTouched = false;
  // History is the set of committed changes, touched is the set of
  // nodes touched since the last commit.
  this.history = []; this.redoHistory = []; this.touched = []; this.lostundo = 0;
}

UndoHistory.prototype = {
  // Schedule a commit (if no other touches come in for commitDelay
  // milliseconds).
  scheduleCommit: function() {
    var self = this;
    parent.clearTimeout(this.commitTimeout);
    this.commitTimeout = parent.setTimeout(function(){self.tryCommit();}, this.commitDelay);
  },

  // Mark a node as touched. Null is a valid argument.
  touch: function(node) {
    this.setTouched(node);
    this.scheduleCommit();
  },

  // Undo the last change.
  undo: function() {
    // Make sure pending changes have been committed.
    this.commit();

    if (this.history.length) {
      // Take the top diff from the history, apply it, and store its
      // shadow in the redo history.
      var item = this.history.pop();
      this.redoHistory.push(this.updateTo(item, "applyChain"));
      this.notifyEnvironment();
      return this.chainNode(item);
    }
  },

  // Redo the last undone change.
  redo: function() {
    this.commit();
    if (this.redoHistory.length) {
      // The inverse of undo, basically.
      var item = this.redoHistory.pop();
      this.addUndoLevel(this.updateTo(item, "applyChain"));
      this.notifyEnvironment();
      return this.chainNode(item);
    }
  },

  clear: function() {
    this.history = [];
    this.redoHistory = [];
    this.lostundo = 0;
  },

  // Ask for the size of the un/redo histories.
  historySize: function() {
    return {undo: this.history.length, redo: this.redoHistory.length, lostundo: this.lostundo};
  },

  // Push a changeset into the document.
  push: function(from, to, lines) {
    var chain = [];
    for (var i = 0; i < lines.length; i++) {
      var end = (i == lines.length - 1) ? to : document.createElement("br");
      chain.push({from: from, to: end, text: cleanText(lines[i])});
      from = end;
    }
    this.pushChains([chain], from == null && to == null);
    this.notifyEnvironment();
  },

  pushChains: function(chains, doNotHighlight) {
    this.commit(doNotHighlight);
    this.addUndoLevel(this.updateTo(chains, "applyChain"));
    this.redoHistory = [];
  },

  // Retrieve a DOM node from a chain (for scrolling to it after undo/redo).
  chainNode: function(chains) {
    for (var i = 0; i < chains.length; i++) {
      var start = chains[i][0], node = start && (start.from || start.to);
      if (node) return node;
    }
  },

  // Clear the undo history, make the current document the start
  // position.
  reset: function() {
    this.history = []; this.redoHistory = []; this.lostundo = 0;
  },

  textAfter: function(br) {
    return this.after(br).text;
  },

  nodeAfter: function(br) {
    return this.after(br).to;
  },

  nodeBefore: function(br) {
    return this.before(br).from;
  },

  // Commit unless there are pending dirty nodes.
  tryCommit: function() {
    if (!window || !window.parent || !window.UndoHistory) return; // Stop when frame has been unloaded
    if (this.editor.highlightDirty()) this.commit(true);
    else this.scheduleCommit();
  },

  // Check whether the touched nodes hold any changes, if so, commit
  // them.
  commit: function(doNotHighlight) {
    parent.clearTimeout(this.commitTimeout);
    // Make sure there are no pending dirty nodes.
    if (!doNotHighlight) this.editor.highlightDirty(true);
    // Build set of chains.
    var chains = this.touchedChains(), self = this;

    if (chains.length) {
      this.addUndoLevel(this.updateTo(chains, "linkChain"));
      this.redoHistory = [];
      this.notifyEnvironment();
    }
  },

  // [ end of public interface ]

  // Update the document with a given set of chains, return its
  // shadow. updateFunc should be "applyChain" or "linkChain". In the
  // second case, the chains are taken to correspond the the current
  // document, and only the state of the line data is updated. In the
  // first case, the content of the chains is also pushed iinto the
  // document.
  updateTo: function(chains, updateFunc) {
    var shadows = [], dirty = [];
    for (var i = 0; i < chains.length; i++) {
      shadows.push(this.shadowChain(chains[i]));
      dirty.push(this[updateFunc](chains[i]));
    }
    if (updateFunc == "applyChain")
      this.notifyDirty(dirty);
    return shadows;
  },

  // Notify the editor that some nodes have changed.
  notifyDirty: function(nodes) {
    forEach(nodes, method(this.editor, "addDirtyNode"))
    this.editor.scheduleHighlight();
  },

  notifyEnvironment: function() {
    if (this.onChange) this.onChange(this.editor);
    // Used by the line-wrapping line-numbering code.
    if (window.frameElement && window.frameElement.CodeMirror.updateNumbers)
      window.frameElement.CodeMirror.updateNumbers();
  },

  // Link a chain into the DOM nodes (or the first/last links for null
  // nodes).
  linkChain: function(chain) {
    for (var i = 0; i < chain.length; i++) {
      var line = chain[i];
      if (line.from) line.from.historyAfter = line;
      else this.first = line;
      if (line.to) line.to.historyBefore = line;
      else this.last = line;
    }
  },

  // Get the line object after/before a given node.
  after: function(node) {
    return node ? node.historyAfter : this.first;
  },
  before: function(node) {
    return node ? node.historyBefore : this.last;
  },

  // Mark a node as touched if it has not already been marked.
  setTouched: function(node) {
    if (node) {
      if (!node.historyTouched) {
        this.touched.push(node);
        node.historyTouched = true;
      }
    }
    else {
      this.firstTouched = true;
    }
  },

  // Store a new set of undo info, throw away info if there is more of
  // it than allowed.
  addUndoLevel: function(diffs) {
    this.history.push(diffs);
    if (this.history.length > this.maxDepth) {
      this.history.shift();
      this.lostundo += 1;
    }
  },

  // Build chains from a set of touched nodes.
  touchedChains: function() {
    var self = this;

    // The temp system is a crummy hack to speed up determining
    // whether a (currently touched) node has a line object associated
    // with it. nullTemp is used to store the object for the first
    // line, other nodes get it stored in their historyTemp property.
    var nullTemp = null;
    function temp(node) {return node ? node.historyTemp : nullTemp;}
    function setTemp(node, line) {
      if (node) node.historyTemp = line;
      else nullTemp = line;
    }

    function buildLine(node) {
      var text = [];
      for (var cur = node ? node.nextSibling : self.container.firstChild;
           cur && (!isBR(cur) || cur.hackBR); cur = cur.nextSibling)
        if (!cur.hackBR && cur.currentText) text.push(cur.currentText);
      return {from: node, to: cur, text: cleanText(text.join(""))};
    }

    // Filter out unchanged lines and nodes that are no longer in the
    // document. Build up line objects for remaining nodes.
    var lines = [];
    if (self.firstTouched) self.touched.push(null);
    forEach(self.touched, function(node) {
      if (node && (node.parentNode != self.container || node.hackBR)) return;

      if (node) node.historyTouched = false;
      else self.firstTouched = false;

      var line = buildLine(node), shadow = self.after(node);
      if (!shadow || shadow.text != line.text || shadow.to != line.to) {
        lines.push(line);
        setTemp(node, line);
      }
    });

    // Get the BR element after/before the given node.
    function nextBR(node, dir) {
      var link = dir + "Sibling", search = node[link];
      while (search && !isBR(search))
        search = search[link];
      return search;
    }

    // Assemble line objects into chains by scanning the DOM tree
    // around them.
    var chains = []; self.touched = [];
    forEach(lines, function(line) {
      // Note that this makes the loop skip line objects that have
      // been pulled into chains by lines before them.
      if (!temp(line.from)) return;

      var chain = [], curNode = line.from, safe = true;
      // Put any line objects (referred to by temp info) before this
      // one on the front of the array.
      while (true) {
        var curLine = temp(curNode);
        if (!curLine) {
          if (safe) break;
          else curLine = buildLine(curNode);
        }
        chain.unshift(curLine);
        setTemp(curNode, null);
        if (!curNode) break;
        safe = self.after(curNode);
        curNode = nextBR(curNode, "previous");
      }
      curNode = line.to; safe = self.before(line.from);
      // Add lines after this one at end of array.
      while (true) {
        if (!curNode) break;
        var curLine = temp(curNode);
        if (!curLine) {
          if (safe) break;
          else curLine = buildLine(curNode);
        }
        chain.push(curLine);
        setTemp(curNode, null);
        safe = self.before(curNode);
        curNode = nextBR(curNode, "next");
      }
      chains.push(chain);
    });

    return chains;
  },

  // Find the 'shadow' of a given chain by following the links in the
  // DOM nodes at its start and end.
  shadowChain: function(chain) {
    var shadows = [], next = this.after(chain[0].from), end = chain[chain.length - 1].to;
    while (true) {
      shadows.push(next);
      var nextNode = next.to;
      if (!nextNode || nextNode == end)
        break;
      else
        next = nextNode.historyAfter || this.before(end);
      // (The this.before(end) is a hack -- FF sometimes removes
      // properties from BR nodes, in which case the best we can hope
      // for is to not break.)
    }
    return shadows;
  },

  // Update the DOM tree to contain the lines specified in a given
  // chain, link this chain into the DOM nodes.
  applyChain: function(chain) {
    // Some attempt is made to prevent the cursor from jumping
    // randomly when an undo or redo happens. It still behaves a bit
    // strange sometimes.
    var cursor = select.cursorPos(this.container, false), self = this;

    // Remove all nodes in the DOM tree between from and to (null for
    // start/end of container).
    function removeRange(from, to) {
      var pos = from ? from.nextSibling : self.container.firstChild;
      while (pos != to) {
        var temp = pos.nextSibling;
        removeElement(pos);
        pos = temp;
      }
    }

    var start = chain[0].from, end = chain[chain.length - 1].to;
    // Clear the space where this change has to be made.
    removeRange(start, end);

    // Insert the content specified by the chain into the DOM tree.
    for (var i = 0; i < chain.length; i++) {
      var line = chain[i];
      // The start and end of the space are already correct, but BR
      // tags inside it have to be put back.
      if (i > 0)
        self.container.insertBefore(line.from, end);

      // Add the text.
      var node = makePartSpan(fixSpaces(line.text));
      self.container.insertBefore(node, end);
      // See if the cursor was on this line. Put it back, adjusting
      // for changed line length, if it was.
      if (cursor && cursor.node == line.from) {
        var cursordiff = 0;
        var prev = this.after(line.from);
        if (prev && i == chain.length - 1) {
          // Only adjust if the cursor is after the unchanged part of
          // the line.
          for (var match = 0; match < cursor.offset &&
               line.text.charAt(match) == prev.text.charAt(match); match++){}
          if (cursor.offset > match)
            cursordiff = line.text.length - prev.text.length;
        }
        select.setCursorPos(this.container, {node: line.from, offset: Math.max(0, cursor.offset + cursordiff)});
      }
      // Cursor was in removed line, this is last new line.
      else if (cursor && (i == chain.length - 1) && cursor.node && cursor.node.parentNode != this.container) {
        select.setCursorPos(this.container, {node: line.from, offset: line.text.length});
      }
    }

    // Anchor the chain in the DOM tree.
    this.linkChain(chain);
    return start;
  }
};

/* A few useful utility functions. */

// Capture a method on an object.
function method(obj, name) {
  return function() {obj[name].apply(obj, arguments);};
}

// The value used to signal the end of a sequence in iterators.
var StopIteration = {toString: function() {return "StopIteration"}};

// Apply a function to each element in a sequence.
function forEach(iter, f) {
  if (iter.next) {
    try {while (true) f(iter.next());}
    catch (e) {if (e != StopIteration) throw e;}
  }
  else {
    for (var i = 0; i < iter.length; i++)
      f(iter[i]);
  }
}

// Map a function over a sequence, producing an array of results.
function map(iter, f) {
  var accum = [];
  forEach(iter, function(val) {accum.push(f(val));});
  return accum;
}

// Create a predicate function that tests a string againsts a given
// regular expression. No longer used but might be used by 3rd party
// parsers.
function matcher(regexp){
  return function(value){return regexp.test(value);};
}

// Test whether a DOM node has a certain CSS class.
function hasClass(element, className) {
  var classes = element.className;
  return classes && new RegExp("(^| )" + className + "($| )").test(classes);
}
function removeClass(element, className) {
  element.className = element.className.replace(new RegExp(" " + className + "\\b", "g"), "");
  return element;
}

// Insert a DOM node after another node.
function insertAfter(newNode, oldNode) {
  var parent = oldNode.parentNode;
  parent.insertBefore(newNode, oldNode.nextSibling);
  return newNode;
}

function removeElement(node) {
  if (node.parentNode)
    node.parentNode.removeChild(node);
}

function clearElement(node) {
  while (node.firstChild)
    node.removeChild(node.firstChild);
}

// Check whether a node is contained in another one.
function isAncestor(node, child) {
  while (child = child.parentNode) {
    if (node == child)
      return true;
  }
  return false;
}

// The non-breaking space character.
var nbsp = "\u00a0";
var matching = {"{": "}", "[": "]", "(": ")",
                "}": "{", "]": "[", ")": "("};

// Standardize a few unportable event properties.
function normalizeEvent(event) {
  if (!event.stopPropagation) {
    event.stopPropagation = function() {this.cancelBubble = true;};
    event.preventDefault = function() {this.returnValue = false;};
  }
  if (!event.stop) {
    event.stop = function() {
      this.stopPropagation();
      this.preventDefault();
    };
  }

  if (event.type == "keypress") {
    event.code = (event.charCode == null) ? event.keyCode : event.charCode;
    event.character = String.fromCharCode(event.code);
  }
  return event;
}

// Portably register event handlers.
function addEventHandler(node, type, handler, removeFunc) {
  function wrapHandler(event) {
    handler(normalizeEvent(event || window.event));
  }
  if (typeof node.addEventListener == "function") {
    node.addEventListener(type, wrapHandler, false);
    if (removeFunc) return function() {node.removeEventListener(type, wrapHandler, false);};
  }
  else {
    node.attachEvent("on" + type, wrapHandler);
    if (removeFunc) return function() {node.detachEvent("on" + type, wrapHandler);};
  }
}

function nodeText(node) {
  return node.textContent || node.innerText || node.nodeValue || "";
}

function nodeTop(node) {
  var top = 0;
  while (node.offsetParent) {
    top += node.offsetTop;
    node = node.offsetParent;
  }
  return top;
}

function isBR(node) {
  var nn = node.nodeName;
  return nn == "BR" || nn == "br";
}
function isSpan(node) {
  var nn = node.nodeName;
  return nn == "SPAN" || nn == "span";
}
