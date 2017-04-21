<?
class IPS_ZWMonitorVisu extends IPSModule {
  public function Create() {
    //Never delete this line!
    parent::Create();
    //Mit ZWMonitorSplitter vebrinden
    $this->ConnectParent("{A9EAA472-5694-49FA-8D90-1D5AC1A89915}");
  }

  public function ApplyChanges() {
    //Never delete this line!
    parent::ApplyChanges();
    $this->RegisterVariableString("MeshVisu", "Mesh Visualisierung", "~HTMLBox");
  }

  public function ReceiveData($JSONString) {
    $this->SendDebug("ReceiveData JSON", $JSONString,0);
  }

  public function getVisu() {
    $SendData = json_encode(Array("DataID" => "{F24B2861-FD7E-4022-B02C-4D9B25233E0B}", "Buffer" => "getConfig"));
    $this->SendDebug("getVisu SendData JSON", $SendData,0);
    $this->SendDataToParent($SendData);
  }
}
?>
