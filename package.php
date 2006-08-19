<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2006, KUBO Atsuhiro <iteman@users.sourceforge.net>
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
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2006 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @link       http://piece-framework.com/piece-unity/
 * @see        PEAR_PackageFileManager2
 * @since      File available since Release 0.1.0
 */

require_once 'PEAR/PackageFileManager2.php';

PEAR::staticPushErrorHandling(PEAR_ERROR_CALLBACK, create_function('$error', 'var_dump($error); exit();'));

$version = '0.6.0';
$apiVersion = '0.6.0';
$releaseStability = 'beta';
$notes = "This release includes a lot of enhancements and a few defect fixes as follows:

<<< Enhancements >>>

Kernel:

* Piece_Unity_Context
- Added support for context attributes.
- Added setProxyPath() method for setting the proxy path if the application uses proxy servers.
- Added getProxyPath() method for getting the proxy path of the application.
- Added usingProxy() method for checking whether the application is accessed via reverse proxies.
- Added setContinuation()/getContinuation() methods for setting/getting the Piece_Flow_Continuation object for the current session.

* Piece_Unity_Plugin_Common
- Added _initialize() method as a template method, and changed the code so as to call it in the constructor.

Plug-ins:

* Interceptor_PieceRight
- An interceptor to set a Piece_Right object to the current application context.

* OutputBufferStack
- Added support for PHP built-in functions.

* Dispatcher_Simple
- Added error handling when include_once fail.

* Interceptor_ProxyBasePath
- Changed the code so as to use Piece_Unity_Context::usingProxy() and Piece_Unity_Context::getProxyPath() methods instead of _useProxy() method and 'path' configuration point.
- Removed _useProxy() method.
- Removed 'path' configuration point.

* KernelConfigurator
- Added 'proxyPath' configuratin point for setting the proxy path of applications.

* Renderer_Redirection
- A renderer which is used to redirect requests.

* View
- Added the code to overwrite the extension 'renderer' if the view string start with http(s)://.

* Dispatcher_Continuation
- Added the code to set the Piece_Flow_Continuation object to the current context.

* Interceptor_NullByteAttackPreventation
- An interceptor to prevent Null Byte Attack for applications.

* OutputFilter_JapaneseZ2H
- An output filter which can be used to converts Japanese JIS X0208 kana to JIS X0201 kana.

Example applications:

- Updated and improved the design.
- Removed the mailto: link to my e-mail address.
- Changed my e-mail address.
- Introduced form validation using Piece_Right.
- Adjusted to the new interface of Piece_Flow actions.

<<< Defect fixes >>>

Kernel:

* Piece_Unity_Context
- Fixed the problem that an event is ignored when the event name includes the characters other than letters or numbers.

Plug-ins:

* Dispatcher_Continuation
- Removed wrong error handling which disables the current callback.

* OutputBufferStack
- Changed the code to prevent strange behaviour.";

$package = new PEAR_PackageFileManager2();
$package->setOptions(array('filelistgenerator' => 'svn',
                           'changelogoldtonew' => false,
                           'simpleoutput'      => true,
                           'baseinstalldir'    => '/',
                           'packagefile'       => 'package2.xml',
                           'packagedirectory'  => '.',
                           'dir_roles'         => array('data' => 'data',
                                                        'tests' => 'test',
                                                        'docs' => 'doc'))
                     );

$package->setPackage('Piece_Unity');
$package->setPackageType('php');
$package->setSummary('A stateful and secure MVC framework for PHP');
$package->setDescription('Piece_Unity is a stateful and secure MVC framework for PHP. Piece_Unity has two major features. The first one is flow control and storing/restoring states with a technology known as continuation server - It based on Piece_Flow web flow engine. The second one is an Eclipse like plug-in system using extension points and configuration points.');
$package->setChannel('pear.hatotech.org');
$package->setLicense('BSD License (revised)',
                     'http://www.opensource.org/licenses/bsd-license.php'
                     );
$package->setAPIVersion($apiVersion);
$package->setAPIStability('beta');
$package->setReleaseVersion($version);
$package->setReleaseStability($releaseStability);
$package->setNotes($notes);
$package->setPhpDep('4.3.0');
$package->setPearinstallerDep('1.4.3');
$package->addMaintainer('lead', 'iteman', 'KUBO Atsuhiro', 'iteman@users.sourceforge.net');
$package->addIgnore(array('package.php', 'package.xml', 'package2.xml'));
$package->addGlobalReplacement('package-info', '@package_version@', 'version');
$package->generateContents();
$package1 = &$package->exportCompatiblePackageFile1();

if (array_key_exists(1, $_SERVER['argv'])
    && $_SERVER['argv'][1] == 'make'
    ) {
    $package->writePackageFile();
    $package1->writePackageFile();
} else {
    $package->debugPackageFile();
    $package1->debugPackageFile();
}

exit();

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
?>
