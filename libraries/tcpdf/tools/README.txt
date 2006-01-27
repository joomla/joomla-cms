

Setting up a Truetype Unicode font for usage with TCPDF:
  1) Generate the font's .ufm metrics file by processing it with the provided 
     ttf2ufm program (modified ttf2pt1). For example:
     $ ttf2ufm -a -F myfont.ttf 
     NOTE: ensure the ttf fontfile name is all lowercase
  2) Run makefontuni.php with the .ttf and .ufm filenames as argument:
     $ php -q makefontuni.php myfont.ttf myfont.ufm
  3) Copy the resulting .php, .z and .ctg.z file to the language/pdf_fonts
     directory.
  4) Edit the language.xml file metadata tag <pdfFontName> to have the value
     of the font name

