<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * docoments event pool notifier
 * @author Anakeen
 * @package FDL
 */

require_once ("WHAT/Lib.Common.php");
require_once ("WHAT/Class.Session.php");

function getNotifyHisto($date, $nextdelay = 5, $limit = 1000)
{
    $c = @pg_pconnect(sprintf("service='%s'", getServiceFreedom()));
    $r = null;
    if ($c) {
        $r = array(
            "date" => date("Y-m-d H:i:s") ,
            "delay" => $nextdelay,
            "sql" => ""
        );
        if (!quickSessionCheck($c)) {
            $r["error"] = "Authentication required";
        } else {
            if ($date && $date != "null") {
                $sql = sprintf("select * from doclog where level=4 and  date >= '%s' and date < '%s' limit %d", pg_escape_string($date) , pg_escape_string($r["date"]) , $limit);
                /* Hide original query to client */
                $r["sql"] = "--";
                $result = @pg_query($c, $sql);
                if ($result) {
                    $nbrows = pg_numrows($result);
                    if ($nbrows > 0) {
                        $r["notifications"] = pg_fetch_all($result);
                        foreach ($r["notifications"] as $k => $v) if ($v["arg"]) $r["notifications"][$k]["arg"] = unserialize($v["arg"]);
                    }
                } else {
                    /*
                     * Hide specific error details and send a generic error
                     * message, to prevent leaking database's informations.
                    */
                    $r["error"] = "Query error";
                }
            }
        }
        pg_close($c);
    }
    return json_encode($r);
}
/**
 * Quickly check the session is opened and does not belong to anonymous user
 * @param $conn
 * @return bool
 */
function quickSessionCheck($conn)
{
    if (!isset($_COOKIE[Session::PARAMNAME])) {
        return false;
    }
    if (($r = pg_query($conn, sprintf("SELECT 1 FROM sessions WHERE id = %s AND userid != 3", pg_escape_literal($_COOKIE[Session::PARAMNAME])))) === false) {
        return false;
    }
    if (pg_num_rows($r) > 0) {
        return true;
    }
    return false;
}
//$_POST["date"]='2009-11-19';
$a = getNotifyHisto($_POST["date"], 10);
print ($a);
