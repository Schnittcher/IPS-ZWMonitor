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
    $data = json_decode($JSONString);
    // Buffer decodieren und in eine Variable schreiben
    $Buffer = utf8_decode($data->Buffer);
    $this->SendDebug("Buffer JSON", $Buffer,0);
    $ZWConfig = json_decode($Buffer);


 $VisuCode = '<!DOCTYPE html>
<meta charset="utf-8">
<style>

.links line {
  stroke: #999;
  stroke-opacity: 0.6;
}

.nodes circle {
  stroke: #fff;
  stroke-width: 1.5px;
}

</style>
<svg width="960" height="600"></svg>
<script src="https://d3js.org/d3.v4.min.js"></script>
<script>';

$ZW_ConfiguratorID = $ZWConfig->ConfiguratorID ;
$ZW_GatewayID = $ZWConfig->GatewayID;
$ZW_Nodes = ZW_GetKnownDevices($ZW_ConfiguratorID);
//print_r($ZW_Nodes);
$i = 0;
$z = 0;
foreach ($ZW_Nodes as $ZW_Node) {
	$ZW_NodeName = IPS_GetObject($ZW_Node["InstanceID"])["ObjectName"];
	//print_r(ZW_RequestRoutingList($ZW_Node["InstanceID"]));

	if ($ZW_Node["NodeID"] <> 1) {
	$JSON["nodes"][$i] = array('id' => strval("Node ".$ZW_Node["NodeID"]),
				  'name' => strval($ZW_NodeName),
                  'group'   => 1);
	if ($ZW_Node["NodeSubID"] == 0) {
		$ZW_Routing = ZW_RequestRoutingList($ZW_Node["InstanceID"]);
		foreach ($ZW_Routing as $ZW_RoutingPoint) {
		if ($ZW_RoutingPoint <> 1) {
			$JSON["links"][$z] = array('source' => strval("Node ".$ZW_Node["NodeID"]),
		                  'target'   => strval("Node ".$ZW_RoutingPoint),
						  'value'	 =>  1);
			$z++;
		}
		}
			$i++;
		}
	}
}
$JSON = json_encode($JSON);
$VisuCode .= "var myjson ='";
$JSON = str_replace('"','\"',$JSON);
$VisuCode .= $JSON;
$VisuCode .= "';";
$VisuCode .= 'var svg = d3.select("svg"),
    width = +svg.attr("width"),
    height = +svg.attr("height");

var color = d3.scaleOrdinal(d3.schemeCategory20);

var simulation = d3.forceSimulation()
    .force("link", d3.forceLink().distance(80).id(function(d) { return d.id; }))
    .force("charge", d3.forceManyBody())
    .force("center", d3.forceCenter(width / 2, height / 2));

var graph = JSON.parse(myjson);

    var link = svg.append("g")
      .attr("class", "links")
    .selectAll("line")
    .data(graph.links)
    .enter().append("line")
      .attr("stroke-width", function(d) { return Math.sqrt(d.value); });

  var node = svg.append("g")
      .attr("class", "nodes")
    .selectAll("circle")
    .data(graph.nodes)
    .enter().append("circle")
      .attr("r", 5)
      .attr("fill", function(d) { return color(d.group); })
      .call(d3.drag()
          .on("start", dragstarted)
          .on("drag", dragged)
          .on("end", dragended))
		  .on("dblclick", connectedNodes);

  node.append("title")
      .text(function(d) { return d.name; });

  simulation
      .nodes(graph.nodes)
      .on("tick", ticked);

  simulation.force("link")
      .links(graph.links);

  function ticked() {
    link
        .attr("x1", function(d) { return d.source.x; })
        .attr("y1", function(d) { return d.source.y; })
        .attr("x2", function(d) { return d.target.x; })
        .attr("y2", function(d) { return d.target.y; });

    node
        .attr("cx", function(d) { return d.x; })
        .attr("cy", function(d) { return d.y; });
  }

function dragstarted(d) {
  if (!d3.event.active) simulation.alphaTarget(0.3).restart();
  d.fx = d.x;
  d.fy = d.y;
}

function dragged(d) {
  d.fx = d3.event.x;
  d.fy = d3.event.y;
}

function dragended(d) {
  if (!d3.event.active) simulation.alphaTarget(0);
  d.fx = null;
  d.fy = null;
}

//Toggle stores whether the highlighting is on
var toggle = 0;
//Create an array logging what is connected to what
var linkedByIndex = {};
for (i = 0; i < graph.nodes.length; i++) {
    linkedByIndex[i + "," + i] = 1;
};
graph.links.forEach(function (d) {
    linkedByIndex[d.source.index + "," + d.target.index] = 1;
});
//This function looks up whether a pair are neighbours
function neighboring(a, b) {
    return linkedByIndex[a.index + "," + b.index];
}
function connectedNodes() {
    if (toggle == 0) {
        //Reduce the opacity of all but the neighbouring nodes
        d = d3.select(this).node().__data__;
        node.style("opacity", function (o) {
            return neighboring(d, o) | neighboring(o, d) ? 1 : 0.1;
        });
        link.style("opacity", function (o) {
            return d.index==o.source.index | d.index==o.target.index ? 1 : 0.1;
        });
        //Reduce the op
        toggle = 1;
    } else {
        //Put them back to opacity=1
        node.style("opacity", 1);
        link.style("opacity", 1);
        toggle = 0;
    }
}
</script>';
SetValue($this->GetIDForIdent("MeshVisu") ,$VisuCode);
  }

  public function getVisu() {
    $SendData = json_encode(Array("DataID" => "{F24B2861-FD7E-4022-B02C-4D9B25233E0B}", "Buffer" => "getConfig"));
    $this->SendDebug("getVisu SendData JSON", $SendData,0);
    $this->SendDataToParent($SendData);
  }
}
?>
