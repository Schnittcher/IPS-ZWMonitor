<?
require_once(__DIR__ . "/../functions.php");
class IPS_ZWMonitorNodeTest extends IPSModule {
  public function Create() {
    //Never delete this line!
    parent::Create();
    //Mit ZWMonitorSplitter vebrinden
    $this->RequireParent("{D10DFC0B-ED29-4EC1-B5B4-9975D2549B79}");
    $this->RegisterPropertyInteger("UpdateTimer", 15);
    $this->RegisterPropertyBoolean("BatteryNodes", false);
    $this->RegisterTimer("ZWNodeTestUpdate", 900000, 'ZWMNodeTest_NodeTestStart($_IPS[\'TARGET\']);');
  }

  public function ApplyChanges() {
    //Never delete this line!
    parent::ApplyChanges();
    $this->createVariablenProfile();
    $timer = $this->ReadPropertyInteger("UpdateTimer") * 60000;
    if ($this->ReadPropertyInteger("UpdateTimer") < 5) {
      $this->SetStatus(201);
      return false;
    }
    $this->SetTimerInterval("ZWNodeTestUpdate", $timer);
    $this->NodeTestStart();
  }

  public function ReceiveData($JSONString) {
    $this->SendDebug("ReceiveData JSON", $JSONString,0);
    $data = json_decode($JSONString);
    // Buffer decodieren und in eine Variable schreiben
    $Buffer = utf8_decode($data->Buffer);
    $this->SendDebug("Buffer JSON", $Buffer,0);
    $ZWConfig = json_decode($Buffer);
    $this->createVariablen($ZWConfig);
    $this->NodeTest($ZWConfig);
  }

  //Führ den NodeTest durch
  public function NodeTest($ZWConfig) {
    $ZW_ConfiguratorID = $ZWConfig->ZW_ConfiguratorID ;
    $ZW_GatewayID = $ZWConfig->ZW_GatewayID;

    $ZW_Nodes = ZW_GetKnownDevices($ZW_ConfiguratorID);
    $BatteryNodes = getBatteryNodes($ZW_ConfiguratorID, $ZW_GatewayID);

    foreach ($ZW_Nodes as $ZW_Node) {
      if ($ZW_Node["NodeID"] <> 1 AND !in_array($ZW_Node["NodeID"], $BatteryNodes)) {
        $NodeStatus = ZW_Test($ZW_Node["InstanceID"]);
        $NodeStatusVariable = @IPS_GetObjectIDByIdent("NodeID".$ZW_Node["NodeID"],$this->InstanceID);
        SetValue($NodeStatusVariable,$NodeStatus);
      }
    }
  }

  //Holt die Config aus dem Splitter und startet somit den NodeTest
  public function NodeTestStart() {
    $SendData = json_encode(Array("DataID" => "{F24B2861-FD7E-4022-B02C-4D9B25233E0B}", "Buffer" => "getConfig"));
    $this->SendDebug("getVisu SendData JSON", $SendData,0);
    $this->SendDataToParent($SendData);
  }

  private function createVariablenProfile() {
    $VariablenProfile = IPS_GetVariableProfileList();
      if (!in_array("ZWMNodeTest",$VariablenProfile)) {
      IPS_CreateVariableProfile("ZWMNodeTest", 0);
      IPS_SetVariableProfileAssociation("ZWMNodeTest", 1, "OK", "", 0x99FF33);
      IPS_SetVariableProfileAssociation("ZWMNodeTest", 0, "Nicht OK", "", 0xFF0000);
    }
  }

  //Legt die Variablen für den Status an, wenn diese noch nicht vorhanden sind
  private function createVariablen($ZWConfig) {
    $ZW_ConfiguratorID = $ZWConfig->ZW_ConfiguratorID ;
    $ZW_GatewayID = $ZWConfig->ZW_GatewayID;

    $ZW_Nodes = ZW_GetKnownDevices($ZW_ConfiguratorID);
    $BatteryNodes = getBatteryNodes($ZW_ConfiguratorID, $ZW_GatewayID);

    foreach ($ZW_Nodes as $ZW_Node) {
      if ($ZW_Node["NodeID"] <> 1 AND !in_array($ZW_Node["NodeID"], $BatteryNodes)) {
        $ZW_NodeName = IPS_GetObject($ZW_Node["InstanceID"])["ObjectName"];
        if (@IPS_GetVariableIDByName("NodeID".$ZW_Node["NodeID"],$this->InstanceID) == false) {
          $this->RegisterVariableBoolean("NodeID".$ZW_Node["NodeID"], $ZW_NodeName,"ZWMNodeTest");
        }
      }
    }
  }
}
?>
