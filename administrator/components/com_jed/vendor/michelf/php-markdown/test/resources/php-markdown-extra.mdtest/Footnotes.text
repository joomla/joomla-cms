This is the first paragraph.[^first]

[^first]:  This is the first note.

* List item one.[^second]
* List item two.[^third]

[^third]: This is the third note, defined out of order.
[^second]: This is the second note.
[^fourth]: This is the fourth note.

# Header[^fourth]

Some paragraph with a footnote[^1], and another[^2].

[^1]: Content for fifth footnote.
[^2]: Content for sixth footnote spaning on 
    three lines, with some span-level markup like
    _emphasis_, a [link][].

[link]: http://michelf.ca/

Another paragraph with a named footnote[^fn-name].

[^fn-name]:
    Footnote beginning on the line next to the marker.

This paragraph should not have a footnote marker since 
the footnote is undefined.[^3]

This paragraph has a second footnote marker to footnote number one.[^1]

This paragraph links to a footnote with plenty of 
block-level content.[^block]

[^block]:
	Paragraph.
	
	*   List item
	
	> Blockquote
	
	    Code block

This paragraph host the footnote reference within a 
footnote test[^reference].

[^reference]:
	This footnote has a footnote of its own.[^nested]

[^nested]:
	This footnote should appear even though it is referenced
	from another footnote. But [^reference] should be litteral
	since the footnote with that name has already been used.

 - - -

Testing unusual footnote name[^1$^!"'].

[^1$^!"']: Haha!

 - - -
 
Footnotes mixed with images[^image-mixed]
![1800 Travel][img6]
![1830 Travel][img7]

[img6]: images/MGR-1800-travel.jpeg "Travel Speeds in 1800"
[^image-mixed]: Footnote Content
[img7]: images/MGR-1830-travel.jpeg "Travel Speeds in 1830"
