<?php 

use Monolog\Handler\SwiftMailerHandler;
/**
 * API for handeling authenitcation
 */

define('API_ACCESS'. true);

require_once('../database.php');
require_once __DIR__ . '/api_utils.php';

addCorsHeaders();
handleOptionsRequest();

switch ($action) 
{
    case 'login':
        Admin::handleLogin();
        break;
    case 'logout':
        Admin::handleLogout();
        break;
    default:
        sendErrorResponse('Unknown action', 'invalid_action', [], 400);
};


?>