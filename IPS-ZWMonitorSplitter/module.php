<?
class IPS_ZWMonitorSplitter extends IPSModule {
  public function Create() {
    //Never delete this line!
    parent::Create();
    $this->RegisterPropertyInteger("ZW_ConfiguratorID", 0);
    $this->RegisterPropertyInteger("ZW_GatewayID", 0);
  }

  public function ApplyChanges() {
    //Never delete this line!
    parent::ApplyChanges();
  }
  public function ForwardData($JSONString) {
    $this->SendDebug("ForwardData JSON", $JSONString,0);
    $data = json_decode($JSONString);
    // Buffer decodieren und in eine Variable schreiben
    $Buffer = utf8_decode($data->Buffer);
    if ($Buffer  == "getConfig") {
      $config = $this->getConfig();
      $config = json_encode($config);
      $this->SendDataToChildren(json_encode(Array("DataID" => "{B263258A-9B90-4303-AC84-70D8DBEEF4DD}", "Buffer" => $config)));
    }
  }

  private function getConfig() {
      $config["ZW_ConfiguratorID"] = $this->ReadPropertyInteger("ZW_ConfiguratorID");
      $config["ZW_GatewayID"] = $this->ReadPropertyInteger("ZW_GatewayID");
      return $config;
  }
}
?>
