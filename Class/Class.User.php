<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * User Document Object Definition
 *
 * @author Anakeen
 * @version $Id:  $
 * @package FDL
 */
/**
 */

/**
 * Document Class
 *
 */
class Fdl_User
{
    private $_user = null;
    public $error = '';
    
    function __construct(&$user)
    {
        if ($user) {
            $this->_user = $user;
        }
    }
    /**
     * return document list
     * @return array Document
     */
    function getUser()
    {
        
        if (!$this->_user) {
            $this->error = sprintf(_("user not initialized"));
            return null;
        } else {
            $ti = array(
                "id",
                "mail",
                "fid",
                "firstname",
                "lastname",
                "login"
            );
            $out = new stdClass();
            $info = new stdClass();
            foreach ($ti as $i) $info->$i = $this->_user->$i;
            $info->locale = getParam("CORE_LANG");
            $out->info = $info;
            $out->localeFormat = getLocaleConfig();
        }
        $out->error = $this->error;
        return $out;
    }
}
?>