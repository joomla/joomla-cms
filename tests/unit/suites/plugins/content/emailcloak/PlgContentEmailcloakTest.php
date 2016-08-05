<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Plugins
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require JPATH_BASE . '/plugins/content/emailcloak/emailcloak.php';

/**
 * Test class for Email cloaking plugin.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Plugins
 * @since       3.6.2
 */
class PlgContentEmailcloakTest extends TestCaseDatabase
{
    /**
     * An instance of the class to test.
     *
     * @var    PlgContentEmailcloak
     * @since  3.6.2
     */
    protected $class;

    /**
     * Setup for testing.
     *
     * @return  void
     *
     * @since   3.6.2
     */
    public function setup()
    {
        JFactory::$application = $this->getMockCmsApp();
        JFactory::$session = $this->getMockSession();

        // force the cloak JS inline so that we can unit test it easier than messing with script head in document
        JFactory::getApplication()->input->server->set('HTTP_X_REQUESTED_WITH', 'xmlhttprequest');

        /**
         * Create a mock dispatcher instance
         *
         * @var $dispatcher Mock_JEventDispatcher_f5646d4b e.g
         */
        $dispatcher = TestCaseDatabase::getMockDispatcher();

        $plugin = array(
            'name'   => 'emailcloak',
            'type'   => 'Content',
            'params' => new \JRegistry
        );

        $this->class = new PlgContentEmailcloak($dispatcher, $plugin);
    }

    /**
     * Provides the data to test the constructor method.
     *
     * @return  array
     *
     * @since   3.4
     */
    public function dataTestOnContentPrepare()
    {
        return array(
            array('this should not be parsed as it has no (at) sign in it - see what I did there? ;)',
                '',
                'this should not be parsed as it has no (at) sign in it - see what I did there? ;)'
            ),
            array(
                '<a href="http://mce_host/ourdirectory/email@example.org">anytext</a>',

                "<span id=\"cloak__HASH__\">JLIB_HTML_CLOAKING</span><script type='text/javascript'>
				document.getElementById('cloak__HASH__').innerHTML = '';
				var prefix = '&#109;a' + 'i&#108;' + '&#116;o';
				var path = 'hr' + 'ef' + '=';
				var addy__HASH__ = '&#101;m&#97;&#105;l' + '&#64;';
				addy__HASH__ = addy__HASH__ + '&#101;x&#97;mpl&#101;' + '&#46;' + '&#111;rg';
				var addy_text__HASH__ = '&#97;nyt&#101;xt';document.getElementById('cloak__HASH__').innerHTML += '<a ' + path + '\'' + prefix + ':' + addy__HASH__ + '\'>'+addy_text__HASH__+'<\/a>';
		</script>
                ",

                "<a href='mailto:email@example.org'>anytext</a>"
            ),
            array(
                '<p><a href="mailto:joe@nowhere.com"><span style="font-style: 8pt;">Joe_fontsize8</span></a></p>',

                "<p><span id=\"cloak__HASH__\">JLIB_HTML_CLOAKING</span><script type='text/javascript'>
                document.getElementById('cloak__HASH__').innerHTML = '';
				var prefix = 'ma' + 'il' + 'to';
				var path = 'hr' + 'ef' + '=';
				var addy__HASH__ = 'joe' + '@';
				addy__HASH__ = addy__HASH__ + 'nowhere' + '.' + 'com';
				var addy_text__HASH__ = '<span style=\"font-style: 8pt;\">Joe_fontsize8</span>';document.getElementById('cloak__HASH__').innerHTML += '<a ' + path + '\'' + prefix + ':' + addy__HASH__ + '\'>'+addy_text__HASH__+'<\/a>';
		</script></p>
                "
            ),
            array(
                '<p><a href="mailto:joe@nowhere13.com?subject= A text"><span style="font-size: 14pt;">Joe_subject_ fontsize13</span></a></p>',

                "
                <p><span id=\"cloak__HASH__\">JLIB_HTML_CLOAKING</span><script type='text/javascript'>
                document.getElementById('cloak__HASH__').innerHTML = '';
				var prefix = 'ma' + 'il' + 'to';
				var path = 'hr' + 'ef' + '=';
				var addy__HASH__ = 'joe' + '@';
				addy__HASH__ = addy__HASH__ + 'nowhere13' + '.' + 'com?subject= A text';
				var addy_text__HASH__ = '<span style=\"font-size: 14pt;\">Joe_subject_ fontsize13</span>';document.getElementById('cloak__HASH__').innerHTML += '<a ' + path + '\'' + prefix + ':' + addy__HASH__ + '\'?subject= A text>'+addy_text__HASH__+'<\/a>';				
		</script></p>
                ",

                '<a href=\'mailto:joe@nowhere13.com?subject= A text\'?subject= A text><span style="font-size: 14pt;">Joe_subject_ fontsize13</span></a>'
            ),


//            [
//                '<p><a href="mailto:joe@nowhere14.com"><span style="font-style: 14pt;">joe@nowhere14.com</span></a></p>',
//                '<span style="font-style: 8pt;">Joe_fontsize8</span>'
//            ],
//            [
//                '<p><a href="mailto:joe@nowhere16.com?subject= A text"><span style="font-size: 16pt;">joe@nowhere16.com</span></a></p>',
//                '<span style="font-style: 8pt;">Joe_fontsize8</span>'
//            ],
//            [
//                '<p><a href="mailto:joe@nowhere.com"><strong>something</strong></a></p>',
//                '<span style="font-style: 8pt;">Joe_fontsize8</span>'
//            ],
//            [
//                '<p><a href="mailto:joe@nobody.com"><strong>mymail@mysite.com</strong></a></p>',
//                '<span style="font-style: 8pt;">Joe_fontsize8</span>'
//            ],
//            [
//                '<p><a href="mailto:joe@nowhere.com?subject= A text"><strong><span style="font-size: 16px;">joe@nowhere.com</span></strong></a></p>',
//                '<span style="font-style: 8pt;">Joe_fontsize8</span>'
//            ],
//            [
//                '<p><a href="mailto:joe@nobody.com"><strong><span style="font-size: 14px;">mymail@mysite.com</span></strong></a></p>',
//                '<span style="font-style: 8pt;">Joe_fontsize8</span>'
//            ],
//            [
//                '<p><a href="mailto:joe@nobody.com"><strong><span style="font-size: 9px;">Joe Nobody</span></strong></a></p>',
//                '<span style="font-style: 8pt;">Joe_fontsize8</span>'
//            ],
//            [
//                '<p><a href="mailto:joe@nobody.com"><strong><span>strong and span</span></strong></a></p>',
//                '<span style="font-style: 8pt;">Joe_fontsize8</span>'
//            ],
//            [
//                '<a href="mailto:email@amail.com?subject=Text"><img src="path/to/something.jpg">email@amail.com</img></a>',
//                '<span style="font-style: 8pt;">Joe_fontsize8</span>'
//            ],
//            [
//                '<a href="http://mce_host/ourdirectory/email@example.org">email@example.org</a>',
//                '<a href="http://mce_host/ourdirectory/email@example.org">email@example.org</a>'
//            ],
//            [
//                '<a href="mailto:email@example.org">email@example.org</a>',
//                '<a href="mailto:email@example.org">email@example.org</a>'
//            ],
        );
    }

    /**
     * Tests PlgContentEmailcloakTest::_cloak()
     *
     * @param   string $input The text to test.
     * @param   string $expected The expectation of the filtering.
     *
     * @return  void
     *
     * @dataProvider  dataTestOnContentPrepare
     * @since         3.6.2
     */
    public function testOnContentPrepareWithRowNoFinder($input, $expectedJs, $expectedHTML = NULL)
    {
        $row = new \stdClass;
        $row->text = $input;
        $params = new JRegistry;

        // assert we have the correct event
        $this->assertInstanceOf('PlgContentEmailcloak', $this->class);

        // assert that we are getting a clean process
        $res = $this->class->onContentPrepare('com_content.article', $row, $params);
        $this->assertEquals(1, $res);


        // Get the md5 hash
        preg_match("/addy_text([0-9a-z]{32})/", $row->text, $output_array);

        // If we did some cloaking then test the JS
        if (count($output_array)) {
            $hash = $output_array[1];

            // assert the JLIB_HTML_CLOAKING span is intact
            $this->assertRegExp('/\<span\sid\=\"cloak[0-9a-z]{32}\"\>JLIB_HTML_CLOAKING\<\/span\>/', $row->text);
            $cloakHTML = '<span id="cloak' . $hash . '">JLIB_HTML_CLOAKING</span>';
            $this->assertContains($cloakHTML, $row->text);

            // need to do this to overcome whitespace comparison issue in phpunit for some reason...
            preg_match_all("/\<script type=\'text\/javascript\'\>(.*)<\/script>/ism", $row->text, $innerJS);
            $result = trim($innerJS[1][0]);

            preg_match_all("/\<script type=\'text\/javascript\'\>(.*)<\/script>/ism", $expectedJs, $innerJS);
            $expected = trim($innerJS[1][0]);

            // assert the render is as the expected render with injected hash
            $this->assertEquals(trim(str_replace('__HASH__', $hash, $expected)), $result);


            if (NULL !== $expectedHTML) {
                $html = $this->convertJStoHTML($result, $hash);
                $this->assertEquals($html, $expectedHTML);
            }
        }
    }

    /**
     * Because phpunit cannot evaluate JS like a browser rendering testing framework can
     * we convert the JS back to HTML by converting to PHP first
     * Yes its probably a fudge, but is better than nothing as Joomla has no JS testing framework
     *
     * @param string $js the resultant JS
     * @param string $hash the md5 hash
     * @return string $resultantHTML the resultant HTML that would be rendered by JS.
     */
    private function convertJStoHTML($js, $hash)
    {
        $resultantHTML = NULL;
        $debug = FALSE;

        $js = html_entity_decode($js);
        $js = str_replace(sprintf('document.getElementById(\'cloak%s\').innerHTML = \'\';', $hash), '', $js);
        $js = str_replace(sprintf('document.getElementById(\'cloak%s\').innerHTML +=', $hash), "\n\n" . '$resultantHTML = ', $js);
        $js = str_replace('var ', '$', $js);
        $js = str_replace("' + '", "", $js);
        $js = preg_replace("/\saddy/", '$addy', $js);
        $js = preg_replace(sprintf("/\'\+addy_text%s\+\'/", $hash), sprintf('\' . \$addy_text%s .\'', $hash), $js);
        $js = preg_replace(sprintf("/\+\$addy%s\s\+/", $hash), sprintf('\' . \$addy_text%s .\'', $hash), $js);
        $js = str_replace("+ path +", '. $path .', $js);
        $js = str_replace("+ prefix +", '. $prefix .', $js);
        $js = str_replace("+$", '.$', $js);
        $js = str_replace("\/", '/', $js);
        $js = str_replace(
            sprintf('$addy%s +', $hash),
            sprintf('$addy%s .', $hash),
            $js);

        // because with all those replaces, you and I will need this a lot :)
        if (TRUE === $debug) {
            echo "\n\n" . trim($js) . "\n\n";
            eval($js);
            echo "\n\n";
            var_dump(trim($resultantHTML));
            die;
        } else {
            // EVAL IS EVIL - I know - but here its not a security risk, and is 'ok'-ish.
            eval($js);
        }

        return trim($resultantHTML);
    }
}
