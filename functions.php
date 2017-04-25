<?
function getBatteryNodes($ZW_ConfiguratorID, $ZW_GatewayID) {
	$ZW_Nodes = ZW_GetKnownDevices($ZW_ConfiguratorID);
	$BatteryNodes = array();
	$i = 0;
	foreach ($ZW_Nodes as $ZW_Node) {
		if ($ZW_Node["InstanceID"] > 0) {
		$NodeClasses =IPS_GetProperty($ZW_Node["InstanceID"], 'NodeClasses');
		$NodeClasses = substr($NodeClasses, 1, -1);
		$NodeClasses = explode(",", $NodeClasses);

		  //Class 128 = Battery
		  if(in_array("128",$NodeClasses)){
		  $BatteryNodes[$i] = $ZW_Node["NodeID"];
		  $i++;
		  }
		}
	}
	return $BatteryNodes;
}
?>
