<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2006-2009 KUBO Atsuhiro <kubo@iteman.jp>,
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    Piece_Unity
 * @copyright  2006-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 0.2.0
 */

// {{{ Piece_Unity_Plugin_Renderer_HTML_CompatibilityTests

/**
 * Renderer_HTML compatibility tests.
 *
 * @package    Piece_Unity
 * @copyright  2006-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 0.2.0
 */
abstract class Piece_Unity_Plugin_Renderer_HTML_CompatibilityTests extends Piece_Unity_PHPUnit_TestCase
{

    // {{{ properties

    /**#@+
     * @access public
     */

    public static $hasWarnings = false;

    /**#@-*/

    /**#@+
     * @access protected
     */

    protected $target;
    protected $exclusiveDirectory;

    /**#@-*/

    /**#@+
     * @access private
     */

    /**#@-*/

    /**#@+
     * @access public
     */

    public function setUp()
    {
        parent::setUp();
        self::$hasWarnings = false;
    }

    public function tearDown()
    {
        Piece_Unity_Plugin_Renderer_HTML_CompatibilityTests::_removeDirectoryRecursively("{$this->exclusiveDirectory}/compiled-templates");
    }

    /**
     * @test
     */
    public function renderTheContents()
    {
        $this->context->setView($this->target . 'Example');
        $this->context->viewElement->setElement('content', 'This is a dynamic content.');

        $this->assertEquals("This is a test for rendering dynamic pages.\nThis is a dynamic content.", $this->render());
    }

    /**
     * @test
     */
    public function removeRelativePaths()
    {
        $this->context->setView('../RelativePathVulnerability');
        $this->context->viewElement->setElement('content', 'This is a dynamic content.');

        set_error_handler(create_function('$code, $message, $file, $line', "
if (\$code == E_USER_WARNING) {
    Piece_Unity_Plugin_Renderer_HTML_CompatibilityTests::\$hasWarnings = true;
}
"));

        try {
            $this->render();
            restore_error_handler();

            $this->fail('An expected exception has not been raised');
        } catch (Piece_Unity_Service_Rendering_NotFoundException $e) {
            $this->assertTrue(self::$hasWarnings);

            restore_error_handler();
        }
    }

    /**
     * @test
     */
    public function keepReferences()
    {
        $this->context->setView($this->target . 'KeepingReference');
        $foo = array();
        $this->context->viewElement->setElementByRef('foo', $foo);
        $this->render();

        $this->assertArrayHasKey('bar', $foo);
        $this->assertEquals('baz', $foo['bar']);
    }

    /**
     * @test
     */
    public function raiseAnExceptionIfTheTemplateIsNotFound()
    {
        $this->context->setView('NonExistingView');
        set_error_handler(create_function('$code, $message, $file, $line', "
if (\$code == E_USER_WARNING) {
    Piece_Unity_Plugin_Renderer_HTML_CompatibilityTests::\$hasWarnings = true;
}
"));

        try {
            $this->render();
            restore_error_handler();

            $this->fail('An expected exception has not been raised');
        } catch (Piece_Unity_Service_Rendering_NotFoundException $e) {
            $this->assertTrue(self::$hasWarnings);

            restore_error_handler();
        }
    }

    /**
     * @test
     */
    public function renderTheContentsWithTheLayout()
    {
        $this->context->setView($this->target . 'LayoutContent');
        $viewElement = $this->context->viewElement;
        $viewElement->setElement('foo', 'This is an element for the content.');
        $viewElement->setElement('bar', 'This is an element for the layout.');
        $this->addExtension('useLayout', true);
        $this->addExtension('layoutView', $this->target . 'Layout');
        $this->addExtension('layoutDirectory', $this->exclusiveDirectory . '/templates/Layout');
        $this->addExtension('layoutCompileDirectory', $this->exclusiveDirectory . '/compiled-templates/Layout');

        $this->assertEquals('<html>
  <body>
    <h1>This is an element for the layout.</h1>
    This is an element for the content.
  </body>
</html>', trim($this->render()));
    }

    /**
     * @test
     */
    public function turnOffTheLayoutIfEnabled()
    {
        $this->_assertTurnOffLayoutByHTTPAccept(true, 'This is an element for the content.');
    }

    /**
     * @test
     */
    public function notTurnOffTheLayoutIfDisabled()
    {
        $this->_assertTurnOffLayoutByHTTPAccept(false, '<html>
  <body>
    <h1>This is an element for the layout.</h1>
    This is an element for the content.
  </body>
</html>');
    }

    /**
     * @test
     */
    public function renderTheFallbackViewIfEnabled()
    {
        $this->context->setView('NonExistingView');
        $viewElement = $this->context->viewElement;
        $viewElement->setElement('content', 'This is a dynamic content.');
        $this->addExtension('useFallback', true);
        $this->addExtension('fallbackView', 'Fallback');
        $this->addExtension('fallbackDirectory', $this->exclusiveDirectory . '/templates/Fallback');
        $this->addExtension('fallbackCompileDirectory', $this->exclusiveDirectory . '/compiled-templates/Fallback');

        set_error_handler(create_function('$code, $message, $file, $line', "
if (\$code == E_USER_WARNING) {
    Piece_Unity_Plugin_Renderer_HTML_CompatibilityTests::\$hasWarnings = true;
}
"));
        $output = $this->render();
        restore_error_handler();

        $this->assertEquals('<html>
  <body>
    <p>This is a test for fallback.</p>
  </body>
</html>', rtrim($output));
        $this->assertTrue(self::$hasWarnings);

    }

    /**
     * @test
     * @since Method available since Release 1.3.0
     */
    public function useAnUnderScoreAsADirectorySeparatorInTheViewString()
    {
        $this->context->setView('Foo_Bar_Baz');
        $this->context->viewElement->setElement('content', 'This is a dynamic content.');
        $this->configureForLayeredStructure();

        $this->assertEquals('Hello, World!', rtrim($this->render()));
    }

    /**#@-*/

    /**#@+
     * @access protected
     */

    /**
     * @since Method available since Release 1.0.0
     */
    protected function render()
    {
        ob_start();
        $this->materializeFeature()->render();
        $buffer = ob_get_contents();
        ob_end_clean();

        return $buffer;
    }

    /**
     * @since Method available since Release 1.3.0
     */
    abstract protected function configureForLayeredStructure();

    /**#@-*/

    /**#@+
     * @access private
     */

    private function _assertTurnOffLayoutByHTTPAccept($turnOffLayoutByHTTPAccept, $result)
    {
        $this->context->setView($this->target . 'LayoutContent');
        $viewElement = $this->context->viewElement;
        $viewElement->setElement('foo', 'This is an element for the content.');
        $viewElement->setElement('bar', 'This is an element for the layout.');
        $this->addExtension('turnOffLayoutByHTTPAccept', $turnOffLayoutByHTTPAccept);
        $this->addExtension('useLayout', true);
        $this->addExtension('layoutView', $this->target . 'Layout');
        $this->addExtension('layoutDirectory', $this->exclusiveDirectory . '/templates/Layout');
        $this->addExtension('layoutCompileDirectory', $this->exclusiveDirectory . '/compiled-templates/Layout');
        $_SERVER['HTTP_ACCEPT'] = 'application/x-piece-html-fragment';

        $this->assertEquals($result, rtrim($this->render()));
    }

    /**
     * @since Method available since Release 1.3.0
     */
    private static function _removeDirectoryRecursively($directory, $rootDirectory = null)
    {
        if (is_null($rootDirectory)) {
            $rootDirectory = $directory;
        }

        if (!is_dir($directory)) {
            return;
        }

        foreach (scandir($directory) as $file) {
            if ($file == '.'
                || $file == '..'
                || $file == 'README'
                || $file == '.svn'
                ) {
                continue;
            }

            $file = $directory . '/' . $file;

            if (is_dir($file)) {
                Piece_Unity_Plugin_Renderer_HTML_CompatibilityTests::_removeDirectoryRecursively($file, $rootDirectory);
            } elseif (is_file($file) && substr(basename($file), 0, 1) != '.') {
                @unlink($file);
            }
        }

        if ($directory != $rootDirectory) {
            @rmdir($directory);
        }
    }

    /**#@-*/

    // }}}
}

// }}}

/*
 * Local Variables:
 * mode: php
 * coding: iso-8859-1
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * indent-tabs-mode: nil
 * End:
 */
