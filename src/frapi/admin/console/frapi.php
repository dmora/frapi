#!/usr/bin/php -q
<?php
/*
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://getfrapi.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@getfrapi.com so we can send you a copy immediately.
 *
 * @license     New BSD
 * @copyright   echolibre ltd.
 * @package     frapi-admin
 * @subpackage  console
 */

// Define path to application directory
define('ROOT_PATH',        dirname(dirname(dirname(__FILE__))));
define('APPLICATION_PATH', ROOT_PATH . DIRECTORY_SEPARATOR .
       'admin' . DIRECTORY_SEPARATOR.'application');
define('CONSOLE_CONTROLLERS_PATH', APPLICATION_PATH . DIRECTORY_SEPARATOR .
       'modules' . DIRECTORY_SEPARATOR . 'console' . DIRECTORY_SEPARATOR .
       'controllers');

// Define application environment
define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

set_include_path('.' . PATH_SEPARATOR . ROOT_PATH . DIRECTORY_SEPARATOR . 'library' . PATH_SEPARATOR . get_include_path());

// Create application, bootstrap, and run
require_once 'Zend/Application.php';

require_once ROOT_PATH . DIRECTORY_SEPARATOR . 'library' .
DIRECTORY_SEPARATOR . 'Frapi' . DIRECTORY_SEPARATOR . 'AllFiles.php';

require_once ROOT_PATH . DIRECTORY_SEPARATOR . 'custom'. DIRECTORY_SEPARATOR . 'AllFiles.php';

$app = new Zend_Application(
    APPLICATION_ENV, APPLICATION_PATH . DIRECTORY_SEPARATOR . 'config' .
    DIRECTORY_SEPARATOR.'application.ini'
);
$app->bootstrap(array('config', 'db', 'defaultAutoloader'));

/**
 * Set our HTTP_HOST so hashing will work properly
 */
global $_SERVER;

$_SERVER['HTTP_HOST'] = '';
$configModel          = new Default_Model_Configuration();
$_SERVER['HTTP_HOST'] = $configModel->getKey('api_url');

// Valid things we can do
$routes = array(
    'action' => array(
        'add'    => 'action/add',
        'delete' => 'action/delete',
        'test'   => 'action/test',
    ),
    'actions' => array(
        'list' => 'action/list',
        'sync' => 'action/sync',
    ),
    'error' => array(
        'add'    => 'errors/add',
        'delete' => 'errors/delete',
    ),
    'errors' => array(
        'list' => 'errors/list',
    ),
    'partner' => array(
        'add'    => 'partner/add',
        'delete' => 'partner/delete',
    ),
    'partners' => array(
        'list' => 'partner/list',
    ),
);

if ($argc < 3) {
    echo 'Usage: frapi.php [action] [module] [options]' . PHP_EOL;
    exit();
}

$action = $argv[1];
$module = $argv[2];

if (!isset($routes[$module][$action])) {
    echo 'Invalid module or action.' . PHP_EOL;
    echo 'Valid options are:' . PHP_EOL;
    foreach ($routes as $module => $actions) {
        echo $module . PHP_EOL;
        foreach ($actions as $action_name => $controller) {
            echo "\t" . $action_name . PHP_EOL;
        }
    }
    exit;
}

// setup controller
$controller = Zend_Controller_Front::getInstance();
$controller->throwExceptions(true);
$controller->setControllerDirectory(CONSOLE_CONTROLLERS_PATH);

$request = new Zend_Controller_Request_Http();
$request->setRequestUri($routes[$module][$action]);

$response = new Zend_Controller_Response_Cli();

$controller->dispatch($request, $response);
