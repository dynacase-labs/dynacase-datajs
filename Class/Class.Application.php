<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Document Object Definition
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
class Fdl_Application
{
    /**
     * @var Application $_app
     */
    private $_app = null;
    public $id = 0;
    public $name = '';
    public $error='';
    function __construct(&$app)
    {
        if ($app) {
            $this->_app = $app;
            $this->id = $app->id;
            $this->name = $app->name;
        }
    }
    /**
     * return parameter value
     * @return string
     */
    function getParameter($key)
    {
        
        if (!$this->_app) {
            $this->error = sprintf(_("application not initialized"));
            return null;
        } else {
            $out=new stdClass();
            $out->value = $this->_app->getParam($key, null);
            if ($out->value === null) $this->error = sprintf(_("parameter %s not exists") , $key);
            $js = json_decode($out->value);
            if ($js) $out->value = $js;
        }
        $out->error = $this->error;
        return $out;
    }
    /**
     * return parameter value
     * @return string
     */
    function setParameter($key, $nv)
    {
        if (!$this->_app) {
            $this->error = sprintf(_("application not initialized"));
            return null;
        } else {
            $out=new stdClass();
            $op = new ParamDef("", $key);
            if ($op->isAffected()) {
                if ($op->isuser == "Y") {
                    $this->_app->setParamU($key, $nv);
                    $out->value = $this->_app->getParam($key);
                } else $this->error = sprintf(_("not authorized : parameter %s is not a user parameter") , $key);
            } else {
                $this->error = sprintf(_("parameter %s not exists") , $key);
            }
        }
        $out->error = $this->error;
        return $out;
    }
    /**
     * return properties of application
     */
    function getApplication()
    {
        $info = array(
            "id" => $this->_app->id,
            "name" => $this->_app->name,
            "description" => $this->_app->description ? _($this->_app->description) : '',
            "label" => $this->_app->short_name ? _($this->_app->short_name) : '',
            "icon" => $this->_app->icon ? 'Images/' . $this->_app->icon : '',
            "available" => ($this->_app->available != 'N') ,
            "version" => $this->_app->getParam("VERSION") ,
            "displayable" => ($this->_app->displayable != 'N')
        );
        
        return $info;
    }
    /**
     * return list of executable actions
     */
    function getExecutableActions()
    {
        
        $queryact = new QueryDb(getDbAccess(), "Action");
        $queryact->AddQuery(sprintf("id_application=%d", $this->id));
        $queryact->AddQuery("available!='N'");
        $listact = $queryact->Query(0, 0, "TABLE");
        $actions = array();
        foreach ($listact as $k => $v) {
            if ($this->_app->HasPermission($v["acl"])) {
                $actions[] = array(
                    "id" => $v["id"],
                    "name" => $v["name"],
                    "root" => ($v["root"] == "Y") ,
                    "label" => ($v["short_name"]) ? _($v["short_name"]) : ''
                );
            }
        }
        
        return $actions;
    }
}
?>
