<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2006 KUBO Atsuhiro <iteman@users.sourceforge.net>,
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

$version = '0.9.0';
$apiVersion = '0.7.0';
$releaseStability = 'beta';
$notes = 'Hi all,

A new release of Piece_Unity is now available.
For Piece_Unity, this will be a big release because this release includes a very important feature named "Action Continuation" and several powerful enhancements as follows.

"Action Continuation" is a very important feature for developers. This feature allows developers to write stateful action code via the properties without using flow attributes. In fact it allows stateful programming without thinking sessions. This means that the programming be nearer natural continuation programming, although it is limited the scope in the action.

Interceptor_Authentication plug-in provides a smart and simple solution for authentication.

Renderer_Flexy, Renderer_Smarty, and Renderer_PHP plug-ins provide a simple and useful layout system to reuse shared html layout across multiple pages.

Renderer_JSON plug-in allows to output view elements as JSON.

Also the example applications was restructured with new features as follows. (Sorry there are no JSON examples yet.)

A. Registration Applications

   1. A registration application. *non-exclusive*
   2. A registration application. *exclusive*
   3. A registration application with AHAH. *exclusive*

B. Authentication

   1. An authentication service. *exclusive*
   2. A resource which is protected by the above authentication service. *non-exclusive*

These examples will be available on the web one of these days.

And also other enhancements are included.

See the following release notes for details.

## Enhancements ##

### Kernel ###

##### Piece_Unity_URL #####

- A utility which is used to create the appropriate absolute URL from a relative/absolute URL. (Ticket #4)

##### Piece_Unity_Request #####

- Added support for accessing $_FILES contents as each parameter to support validation of files and images. (Ticket #10)

##### Piece_Unity_Context #####

- Added support for image input type. (Ticket #18)

##### Piece_Unity_Validation #####

- Changed so as to handle a Piece_Right_Results object by reference. (Ticket #20)
- Updated validate() to set a Piece_Unity_Context object as a payload to Piece_Right. (Ticket #19)

##### Piece_Unity_Session_Preload #####

- A class *pre*loader for restoring objects in session.

##### Piece_Unity_Session #####

- Added a feature to preload for restoring objects.

##### Piece_Unity_Plugin_Factory #####

- Added clearInstances() to clear the plug-in instances.

##### Piece_Unity_Error #####

- Added a constant PIECE_UNITY_ERROR_UNEXPECTED_VALUE.
- Added a constant PIECE_UNITY_ERROR_INVALID_OPERATION.

### Plug-ins ###

##### Interceptor_Authentication #####

- An interceptor to control the access to protected resources on Piece_Unity applications.

##### Renderer_JSON #####

- A renderer to output view elements as JSON.

##### Controller #####

- Changed to skip dispatcher when context has already view contents.

##### View #####

- Added a feature to replace the current view with a view which is given by a new configuration point forcedView.
- Added a built-in view element __url which is a Piece_Unity_URL object.

##### KernelConfigurator #####

- Added to preload Dispatcher_Continuation plug-in for restoring action instances in session.
- Added a configuration point nonSSLableServers to make a list of non-SSLable servers.

##### Dispatcher_Continuation #####

- Added a feature to store the action instances as a flow attribute in a flow execution, and restore the action instances when continuing the flow execution.
- Removed the configuration point sessionKey.
- Added getContinuationSessionKey() to get the session key for a continuation object.

##### Renderer_HTML #####

- An abstract renderer which is used to render HTML.

##### Renderer_Flexy, Renderer_PHP, Renderer_Smarty #####

- Added a feature to reuse shared html layout across multiple pages. (Ticket #2)

##### Controller, Dispatcher_Continuation #####

- Updated to publish the Piece_Flow_Continuation object as a view element if it exists even though what the specified dispatcher is. And the flow execution ticket key and the flow name key have always been available as each view element. (Ticket #26)

##### Renderer_Redirection #####

- Removed getURL().

### Example Applications ###

- Restructured applications with new features.';

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
$package->setSummary('A stateful and secure web application framework for PHP');
$package->setDescription('Piece_Unity is a stateful and secure web application framework for PHP.

Piece_Unity is a framework against the background of layered architecture, as of now, focuses on the presentation layer.

Piece_Unity allows stateful programming without thinking about sessions by storing and restoring states with a technology known as continuation server. It also provides high security and eases the burden of implementing security measures for applications by application flow control.');
$package->setChannel('pear.piece-framework.com');
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
$package->addPackageDepWithChannel('required', 'Piece_Flow', 'pear.piece-framework.com', '1.8.0');
$package->addPackageDepWithChannel('required', 'Cache_Lite', 'pear.php.net', '1.7.0');
$package->addPackageDepWithChannel('required', 'PEAR', 'pear.php.net', '1.4.3');
$package->addPackageDepWithChannel('required', 'Net_URL', 'pear.php.net', '1.0.14');
$package->addPackageDepWithChannel('required', 'Piece_Right', 'pear.piece-framework.com', '1.4.0');
$package->addPackageDepWithChannel('optional', 'Stagehand_TestRunner', 'pear.piece-framework.com', '0.4.0');
$package->addPackageDepWithChannel('optional', 'HTML_Template_Flexy', 'pear.php.net', '1.2.4');
$package->addPackageDepWithChannel('optional', 'Smarty', 'pearified.com', '1.6.8');
$package->addPackageDepWithChannel('optional', 'HTML_AJAX', 'pear.php.net', '0.5.0');
$package->addExtensionDep('optional', 'json');
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
