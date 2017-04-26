<?
require_once(__DIR__ . "/../functions.php");
class IPS_ZWMonitorNodeTest extends IPSModule {
  public function Create() {
    //Never delete this line!
    parent::Create();
    //Mit ZWMonitorSplitter vebrinden
    $this->RequireParent("{D10DFC0B-ED29-4EC1-B5B4-9975D2549B79}");
    $this->RegisterVariableString("MeshVisu", "Mesh Visualisierung", "~HTMLBox");
    $this->RegisterPropertyInteger("UpdateTimer", 15);
    $this->RegisterPropertyBoolean("BatteryNodes", false);
    $this->RegisterTimer("ZWVisuUpdate", 900000, 'ZWMVisu_getVisu($_IPS[\'TARGET\']);');
  }

  public function ApplyChanges() {
    //Never delete this line!
    parent::ApplyChanges();
  }

  public function ReceiveData($JSONString) {
    $this->SendDebug("ReceiveData JSON", $JSONString,0);
    $data = json_decode($JSONString);
    // Buffer decodieren und in eine Variable schreiben
    $Buffer = utf8_decode($data->Buffer);
    $this->SendDebug("Buffer JSON", $Buffer,0);
    $ZWConfig = json_decode($Buffer);
    $this->createVariablen($ZWConfig);
  }

  private function NodeTest() {


  }

  //private function createVariablenProfile() {


  //}


  private function createVariablen($ZWConfig) {
    $ZW_ConfiguratorID = $ZWConfig->ZW_ConfiguratorID ;
    $ZW_GatewayID = $ZWConfig->ZW_GatewayID;

    $ZW_Nodes = ZW_GetKnownDevices($ZW_ConfiguratorID);
    $BatteryNodes = getBatteryNodes($ZW_ConfiguratorID, $ZW_GatewayID);

    foreach ($ZW_Nodes as $ZW_Node) {
      if ($ZW_Node["NodeID"] <> 1 AND !in_array($ZW_Node["NodeID"], $BatteryNodes)) {
        $ZW_NodeName = IPS_GetObject($ZW_Node["InstanceID"])["ObjectName"];
        if (IPS_GetVariableIDByName("NodeID ".$ZW_Node["NodeID"],$this->InstanceID) == false) {
          $this->RegisterVariableBoolean("NodeID ".$ZW_Node["NodeID"], $ZW_NodeName);
        }
      }
    }
  }
}
?>
