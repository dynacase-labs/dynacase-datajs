<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Aplication Api access
 *
 * @author Anakeen
 * @version $Id:  $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("DATA/Class.Application.php");
/**
 * Display info before download
 * @param Action &$action current action
 * @global string $id Http var : document for file to edit (SIMPLEFILE family)
 */
function application(Action & $action)
{
    $id = getHttpVars("id");
    $method = getHttpVars("method");
    $err = "";
    
    $out = new stdClass();
    /**
     * @var Fdl_Application $ou
     */
    $ou = null;
    switch (strtolower($method)) {
        case 'getparameter':
            $appName = getHttpVars("name");
            $err = initTheApplication($action, $appName, $ou);
            if ($ou) $out = $ou->getParameter($id);
            break;

        case 'setparameter':
            $appName = getHttpVars("name");
            $nv = getHttpVars("value");
            $err = initTheApplication($action, $appName, $ou);
            if ($ou) $out = $ou->setParameter($id, $nv);
            break;

        case 'getexecutableactions':
            $appName = getHttpVars("name");
            $err = initTheApplication($action, $appName, $ou);
            if ($out) {
                $out["actions"] = $ou->getexecutableactions();
            }
            break;

        case 'get':
            $appName = getHttpVars("name");
            $err = initTheApplication($action, $appName, $ou);
            if ($err) {
                $out->error = sprintf(_("application %s not defined") , $appName);
            } else {
                $out = $ou->getApplication();
            }
            
            break;

        default:
            $out->error = sprintf(_("method %s not defined") , $method);
        }
        
        $action->lay->noparse = true; // no need to parse after - increase performances
        $action->lay->template = json_encode($out);
    }
    
    function initTheApplication(&$action, $appName, &$fdlapp)
    {
        $err = '';
        if ($appName) {
            $gapp = new Application();
            $gapp->set($appName, $action->parent->parent, '', false);
            if ($gapp->id) {
                $fdlapp = new Fdl_Application($gapp);
            } else {
                $err = sprintf(_("application %s not defined") , $appName);
            }
        } else {
            $err = _("application name not set");
        }
        return $err;
    }
?>