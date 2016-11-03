<?php

/*
Copyright (C) 2016 Teclib'

This file is part of Armadito Plugin for GLPI.

Armadito Plugin for GLPI is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Armadito Plugin for GLPI is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with Armadito Plugin for GLPI. If not, see <http://www.gnu.org/licenses/>.

**/

include_once("toolbox.class.php");

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

class PluginArmaditoScanConfig extends CommonDBTM
{

    protected $id;
    protected $scan_name;
    protected $scan_path;
    protected $scan_options;
    protected $antivirus_id;

    static $rightname = 'plugin_armadito_scanconfigs';

    function __construct()
    {
    }

    function initFromForm($POST)
    {
        if(isset($POST["id"])) {
            $this->id = $POST["id"];
        }

        $this->antivirus_id = $POST["antivirus_id"];
        $this->scan_name    = $POST["scan_name"];
        $this->scan_path    = $POST["scan_path"];
        $this->scan_options = $POST["scan_options"];
    }

    function initFromDB($id)
    {
        if (!$this->getFromDB($id)) {
            throw new PluginArmaditoDbException('Scanconfig initFromDB failed.');
        }

        $this->id = $id;
        $this->scan_name    = $this->fields["scan_name"];
        $this->scan_path    = $this->fields["scan_path"];
        $this->scan_options = $this->fields["scan_options"];
        $this->antivirus_id = $this->fields["plugin_armadito_antiviruses_id"];
    }

    function validate()
    {
        if (!PluginArmaditoToolbox::isValidUnixPath($this->scan_path)) {
            throw new InvalidArgumentException("Invalid UNIX Scan path");
        }
    }

    function toJson()
    {
        return '{
                  "scanconfig_id": ' . $this->id . ',
                  "scan_name": "' . $this->scan_name . '",
                  "scan_path": "' . $this->scan_path . '",
                  "scan_options": "' . $this->scan_options . '"
               }';
    }

    function insertScanConfigInDB()
    {
        $dbmanager = new PluginArmaditoDbManager();

        $params["scan_name"]["type"]                      = "s";
        $params["scan_path"]["type"]                      = "s";
        $params["scan_options"]["type"]                   = "s";
        $params["plugin_armadito_antiviruses_id"]["type"] = "i";

        $query = "NewScanConfig";
        $dbmanager->addQuery($query, "INSERT", $this->getTable(), $params);
        $dbmanager->prepareQuery($query);
        $dbmanager->bindQuery($query);

        $this->setCommonQueryValues($dbmanager, $query);
        $dbmanager->executeQuery($query);
    }

    function updateScanConfigInDB()
    {
        $dbmanager = new PluginArmaditoDbManager();

        $params["scan_name"]["type"]                      = "s";
        $params["scan_path"]["type"]                      = "s";
        $params["scan_options"]["type"]                   = "s";
        $params["plugin_armadito_antiviruses_id"]["type"] = "i";
        $params["id"]["type"]                             = "i";

        $query = "UpdateScanConfig";
        $dbmanager->addQuery($query, "UPDATE", $this->getTable(), $params, "id");
        $dbmanager->prepareQuery($query);
        $dbmanager->bindQuery($query);

        $dbmanager = $this->setCommonQueryValues($dbmanager, $query);
        $dbmanager->setQueryValue($query, "id", $this->id);
        $dbmanager->executeQuery($query);
    }

    function setCommonQueryValues( $dbmanager, $query )
    {
        $dbmanager->setQueryValue($query, "scan_name", $this->scan_name);
        $dbmanager->setQueryValue($query, "scan_path", $this->scan_path);
        $dbmanager->setQueryValue($query, "scan_options", $this->scan_options);
        $dbmanager->setQueryValue($query, "plugin_armadito_antiviruses_id", $this->antivirus_id);
        return $dbmanager;
    }

    static function canDelete()
    {
        return true;
    }

    static function canCreate()
    {
        if (isset($_SESSION["glpi_plugin_armadito_profile"])) {
            return ($_SESSION["glpi_plugin_armadito_profile"]['armadito'] == 'w');
        }
        return false;
    }

    static function canView()
    {
        if (isset($_SESSION["glpi_plugin_armadito_profile"])) {
            return ($_SESSION["glpi_plugin_armadito_profile"]['armadito'] == 'w' || $_SESSION["glpi_plugin_armadito_profile"]['armadito'] == 'r');
        }
        return false;
    }

    static function getTypeName($nb = 0)
    {

        return __('Scan configuration', 'armadito');
    }

    function getSearchOptions()
    {
        $tab           = array();
        $tab['common'] = __('ScanConfig', 'armadito');

        $i = 1;

        $tab[$i]['table']         = $this->getTable();
        $tab[$i]['field']         = 'id';
        $tab[$i]['name']          = __('Scan Config Id', 'armadito');
        $tab[$i]['datatype']      = 'itemlink';
        $tab[$i]['itemlink_type'] = 'PluginArmaditoScanConfig';
        $tab[$i]['massiveaction'] = FALSE;

        $i++;

        $tab[$i]['table']         = $this->getTable();
        $tab[$i]['field']         = 'scan_name';
        $tab[$i]['name']          = __('Scan Name', 'armadito');
        $tab[$i]['datatype']      = 'text';
        $tab[$i]['massiveaction'] = FALSE;

        $i++;

        $tab[$i]['table']         = $this->getTable();
        $tab[$i]['field']         = 'scan_path';
        $tab[$i]['name']          = __('Scan Path', 'armadito');
        $tab[$i]['datatype']      = 'text';
        $tab[$i]['massiveaction'] = FALSE;

        $i++;

        $tab[$i]['table']         = $this->getTable();
        $tab[$i]['field']         = 'scan_options';
        $tab[$i]['name']          = __('Scan Options', 'armadito');
        $tab[$i]['datatype']      = 'text';
        $tab[$i]['massiveaction'] = FALSE;

        $i++;

        $tab[$i]['table']         = 'glpi_plugin_armadito_antiviruses';
        $tab[$i]['field']         = 'fullname';
        $tab[$i]['name']          = __('Antivirus', 'armadito');
        $tab[$i]['datatype']      = 'itemlink';
        $tab[$i]['itemlink_type'] = 'PluginArmaditoAntivirus';
        $tab[$i]['massiveaction'] = FALSE;

        return $tab;
    }

    static function getScanConfigsList()
    {
        global $DB;

        $configs = array();
        $query   = "SELECT id, scan_name FROM `glpi_plugin_armadito_scanconfigs`";
        $ret     = $DB->query($query);

        if (!$ret) {
            throw new InvalidArgumentException(sprintf('Error getScanConfigsList : %s', $DB->error()));
        }

        if ($DB->numrows($ret) > 0) {
            while ($data = $DB->fetch_assoc($ret)) {
                $configs[$data['id']] = $data['scan_name'];
            }
        }
        return $configs;
    }

    function showForm($id, $options = array())
    {
        $antiviruses = PluginArmaditoAntivirus::getAntivirusList();
        if (empty($antiviruses)) {
            PluginArmaditoScanConfig::showNoAntivirusForm();
            return;
        }

        $this->initForm($id, $options);
        $this->showFormHeader($options);

        echo "<input type='hidden' name='id' value='" . htmlspecialchars($this->fields["id"]) . "'/>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Scan name', 'armadito') . " :</td>";
        echo "<td>";
        echo "<input type='text' name='scan_name' value='" . htmlspecialchars($this->fields["scan_name"]) . "'/>";
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Antivirus', 'armadito') . " :</td>";
        echo "<td>";
        Dropdown::showFromArray("antivirus_id", $antiviruses);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Scan path', 'armadito') . " :</td>";
        echo "<td>";
        echo "<input type='text' name='scan_path' value='" . htmlspecialchars($this->fields["scan_path"]) . "'/>";
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Scan options', 'armadito') . " :</td>";
        echo "<td>";
        echo "<input type='text' name='scan_options' value='" . htmlspecialchars($this->fields["scan_options"]) . "'/>";
        echo "</td>";
        echo "</tr>";

        $this->showFormButtons($options);
        return true;
    }

    static function showNoScanConfigForm()
    {
        global $CFG_GLPI;

        if (Session::haveRight('plugin_armadito_scanconfigs', READ)) {
            $scanconfig_url = $CFG_GLPI['root_doc'] . PluginArmaditoScanConfig::getFormURL(false);
            echo "<b>No scan configuration found in database.</b><br>";
            echo "<a href=\"" . $scanconfig_url . "\"> Add a new configuration</a>";
        }
    }

    static function showNoAntivirusForm()
    {
        if (Session::haveRight('plugin_armadito_antiviruses', READ)) {
            echo "<b>No Antivirus found in database.</b><br>";
            echo "Please enroll a new device with Armadito Agent.<br>";
        }
    }
}
?>
