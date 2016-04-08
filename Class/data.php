<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Main program to activate action in WHAT software
 *
 * All HTTP requests call index.php to execute action within application
 *
 * @author Anakeen
 * @version $Id: index.php,v 1.64 2008/12/16 15:51:53 jerome Exp $
 * @package FDL
 * @subpackage
 */
/**
 */
global $DEBUGINFO;
$DEBUGINFO["mbstart"] = microtime(true);
include_once ('WHAT/autoload.php');
include_once ('WHAT/Lib.Main.php');
include_once ('WHAT/Class.AuthenticatorManager.php');

function dataAskAuthenticate()
{
    return isset($_REQUEST["app"]) && isset($_REQUEST["action"]) && isset($_REQUEST["method"]) && $_REQUEST["method"] === "authent" && $_REQUEST["app"] === "DATA" && $_REQUEST["action"] === "USER";
}

$authtype = getAuthType();

if ($authtype == 'apache') {
    // Apache has already handled the authentication
    global $_SERVER;
    if ($_SERVER['PHP_AUTH_USER'] == "") {
        header('HTTP/1.0 403 Forbidden');
        echo _("User must be authenticate");
        exit();
    }
} else {
    
    $status = AuthenticatorManager::checkAccess(null, true);
    switch ($status) {
        case 0: // it'good, user is authentified
            break;

        case -1:
            // User must change his password
            // $action->session->close();
            $o["error"] = _("not authenticated:ERRNO_BUG_639");
            print json_encode($o);
            exit(0);
            break;

        default:
            if (!dataAskAuthenticate()) {
                sleep(2); // wait for robots
                $o["error"] = _("not authenticated");
                print json_encode($o);
                exit(0);
            } else {
                $auth = AuthenticatorManager::$auth;
                if ($auth === false) {
                    throw new \Dcp\Exception("Could not get authenticator.");
                }
            }
    }
    $_SERVER['PHP_AUTH_USER'] = AuthenticatorManager::$auth->getAuthUser();
}

if (file_exists('maintenance.lock')) {
    if ($_SERVER['PHP_AUTH_USER'] != 'admin') {
        header("HTTP/1.0 503 Service Unavailable");
        $o["error"] = _("maintenance in progress");
        print json_encode($o);
        exit(0);
    }
}
#
# This is the main body of App manager
# It is used to launch application and
# function giving them all necessary environment
# element
#
#
// First control
if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('HTTP/1.0 403 Forbidden');
    $o["error"] = _("not authenticated");
    print json_encode($o);
    exit();
}
// ----------------------------------------
$DEBUGINFO["mbinit"] = microtime(true);
$action = null;
getmainAction(AuthenticatorManager::$auth, $action);
$action->debug = true;
$DEBUGINFO["mbaction"] = microtime(true);
try {
    executeAction($action);
}
catch(Exception $e) {
    $o["error"] = $e->getMessage();
    print json_encode($o);
}
$DEBUGINFO["mbend"] = microtime(true);
?>
