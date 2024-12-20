<?php
/*
 If not stated otherwise in this file or this component's Licenses.txt file the
 following copyright and licenses apply:

 Copyright 2018 RDK Management

 Licensed under the Apache License, Version 2.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at

 http://www.apache.org/licenses/LICENSE-2.0

 Unless required by applicable law or agreed to in writing, software
 distributed under the License is distributed on an "AS IS" BASIS,
 WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 See the License for the specific language governing permissions and
 limitations under the License.
*/
?>
<?php include('includes/header.php'); ?>
<!-- $Id: lan.php 3158 2010-01-08 23:32:05Z slemoine $ -->
<div id="sub-header">
	<?php include('includes/userbar.php'); ?>
</div><!-- end #sub-header -->
<?php include('includes/nav.php'); ?>
<?php include('includes/utility.php'); ?>
<?php
$PartnerId = getStr("Device.DeviceInfo.X_RDKCENTRAL-COM_Syndication.PartnerId");
$HomeNetworkControl = getStr("Device.DeviceInfo.X_RDKCENTRAL-COM_Syndication.RDKB_UIBranding.LocalUI.HomeNetworkControl");
function getPort4XHSEnabled() {
	$rootObjName = "Device.X_CISCO_COM_MultiLAN.";
	$paramNameArray = array("Device.X_CISCO_COM_MultiLAN.");
	$mapping_array  = array("PrimaryLANBridge", "PrimaryLANBridgeHSPorts", "HomeSecurityBridge", "HomeSecurityBridgePorts");
	$multiLan = getParaValues($rootObjName, $paramNameArray, $mapping_array);
	if (!empty($multiLan)) {
		$pLanBridgeHSPortEnable = getStr($multiLan[0]["PrimaryLANBridge"].".Port.".$multiLan[0]["PrimaryLANBridgeHSPorts"].".Enable");
		$HSBridgePortEnable = getStr($multiLan[0]["HomeSecurityBridge"].".Port.".$multiLan[0]["HomeSecurityBridgePorts"].".Enable");
		return ($pLanBridgeHSPortEnable === 'false' && $HSBridgePortEnable === 'true');
	}
	return false;
}
$isPort4XHSEnabled = getPort4XHSEnabled();
$rootObjName = "Device.Ethernet.Interface.";
$paramNameArray = array("Device.Ethernet.Interface.");
$mapping_array  = array("Upstream", "Status", "MACAddress", "MaxBitRate");
$ethernetParam = getParaValues($rootObjName, $paramNameArray, $mapping_array, true);
$ethernetPort3Name = getStr("Device.Ethernet.Interface.3.Name");
$ethernetPort5Name = getStr("Device.Ethernet.Interface.5.Name");
?>
<script type="text/javascript">
var o_isPort4XHSEnabled = <?php echo $isPort4XHSEnabled ? 'true' : 'false'; ?>;
function onsavePort4() {
	var postData = {};
	postData.op = "savePort4XHS";
	postData.enable = $("#port2").prop("checked");
	jProgress('This may take several seconds', 60);
	$.ajax({
		type: 'POST',
		url: 'actionHandler/ajaxSet_hardware_lan.php',
		dataType: 'json',
		data: postData,
		success: function(data) {
			jHide();
			if (data.status != 'success') {
				var str = "Failed, please try again later.";
				if (data.msg) {
					str += '\nMessage: ' + data.msg;
				}
				jAlert(str);
				return;
			}
			else {
				window.location.reload(true);
			}
		},
		error: function() {
			/* restore the previous state */
			jHide();
			jAlert("Failure, please try again.");
		}
	});
}
function initEvents() {
	$("#saveXHSBtn").off("click").click(onsavePort4);
}
$(document).ready(function() {
    gateway.page.init("Gateway > Hardware > LAN Ethernet", "nav-lan");
	$("#port2").prop("checked", o_isPort4XHSEnabled);
	initEvents();
});
</script>
<div id="content">
	<h1>Gateway > Hardware > LAN Ethernet</h1>
	<div id="educational-tip">
		<p class="tip"> View information about the Gateway's Ethernet Ports. </p>
		<?php if (strstr($ethernetPort3Name, "erouter0")){ ?>
			<p class="hidden">The Gateway has 2 Gigabit (GbE) Ethernet Ports. When a device is connected to the Gateway with an Ethernet cable, you'll see an <i>Active</i> status for that port.</p>
		<?php }
		elseif (strstr($ethernetPort5Name, "erouter0")){ ?>
			<p class="hidden">The Gateway has 3 Gigabit (GbE) Ethernet Ports and one 2.5 (mGig) Ethernet Port. When a device is connected to the Gateway with an Ethernet cable, you'll see an <i>Active</i> status for that port.</p>
		<?php } ?>
	</div>
	<?php
	function NameMap($str,$i)
	{
		//true for Ethernet, False for Docsis
		$wan_enable = getStr("Device.Ethernet.X_RDKCENTRAL-COM_WAN.Enabled");
		$selectedOperationalMode = getStr("Device.X_RDKCENTRAL-COM_EthernetWAN.SelectedOperationalMode");
        $autoWanEnable= getStr("Device.DeviceInfo.X_RDKCENTRAL-COM_AutowanFeatureSupport");  
        $allowEthWan= getStr("Device.DeviceInfo.X_RDKCENTRAL-COM_Syndication.RDKB_UIBranding.AllowEthernetWAN");
        $wanPort= getStr("Device.Ethernet.X_RDKCENTRAL-COM_WAN.Port");  
	$currentOpMode = getStr("Device.X_RDKCENTRAL-COM_EthernetWAN.CurrentOperationalMode");
        switch ($str)
		{
			case "Up":
				if($allowEthWan=="true" && $i==($wanPort+1) && $autoWanEnable=="false"){
					if($wan_enable=="true")
						return "Active Ethernet WAN";
					else
						return "Active Docsis WAN";
				} else if(($autoWanEnable=="true") && ($i==($wanPort+1))){
					if(strtolower($selectedOperationalMode)=="ethernet"){
						return "Active Ethernet WAN";
					}else if(strtolower($selectedOperationalMode)=="docsis"){
						return "Active Docsis WAN";
					}else{
						if(strtolower($currentOpMode)=="ethernet"){
                                                        return  "Active Auto WAN";
                                                }else{
                                                        return "Active";
                                                }
					}
				} else {
					return "Active";
				}
				break;
			case "Down":
				return "Inactive";
				break;
			default:
				return $str;
		}
	}
	$ids = array_filter(explode(",",getInstanceIds("Device.Ethernet.Interface.")));
	/*if ($_DEBUG) {
		$ids = array("1", "2");
	}*/
	foreach ($ethernetParam as $id => $value)
	{	     
		if ("true" == $ethernetParam[$id]["Upstream"]){
			continue;		
		}
		echo '<div class="module forms block">';
		echo '<h2>LAN Ethernet Port '.$ids[$id].'</h2>';
		$dm = array(
			array("LAN Ethernet link status:", null, $ethernetParam[$id]["Status"]),
			array("MAC Address:", null, $ethernetParam[$id]["MACAddress"])
		);
		/* link speed */
		$lspeed = $ethernetParam[$id]["MaxBitRate"];
		$lunit = " Mbps";
		if (empty($lspeed)) {
			$lspeed = "Not Applicable";
			$lunit = "";
		}
		else if ((int)$lspeed < 0) {
			$lspeed = "Disconnected";
			$lunit = "";
		}
		/* zqiu
		else if ((int)$lspeed >= 1000) {
			$lspeed = floor((int)$lspeed / 1000);
			$lunit = " Gbps";
		} 
		*/
		array_push($dm, array("Connection Speed:", $lspeed.$lunit));
		for ($m=0, $i=0; $i<count($dm); $i++)
		{
			echo '<div class="form-row '.(($m++ % 2)?'odd':'').'" >';
			echo '<span class="readonlyLabel">'.$dm[$i][0].'</span>';
			echo '<span class="value">'.($dm[$i][1] === null ? NameMap($dm[$i][2],$ids[$id]) : $dm[$i][1]).'</span>';
			echo '</div>';
		}
		if ($ids[$id] === "2") {
			/* port 2 as home security port */
			//if($HomeNetworkControl == 'true'){
				if($PartnerId == 'comcast' || $PartnerId == 'test-partner'){

				echo '<div class="form-row odd ">'.
					'<label for="channel_selection">Associate Ethernet Port 2 to XFINITY HOME Network:</label>'.
					'<span class="checkbox"><input type="checkbox" id="port2" name="port2" /></span></br></br></br></br>'.
					'Note: Associating Ethernet Port 2 to XFINITY HOME network will remove the port from your home network.</br></br>'.
				'</div>'.
				'<div class="form-row odd" >'.
					'<div style="position:relative;right:-120px;"><input id="saveXHSBtn" type="button" value="Save" class="btn submit" /></div>'.
				'</div>';
				}
				else if($PartnerId == 'cox' || $PartnerId == 'rogers' || $PartnerId == 'shaw' || $PartnerId == 'videotron'){

				echo '<div class="form-row odd ">'.
                                        '<label for="channel_selection">Associate Ethernet Port 2 to HOME SECURITY Network:</label>'.
                                        '<span class="checkbox"><input type="checkbox" id="port2" name="port2" /></span></br></br></br></br>'.
                                        'Note: Associating Ethernet Port 2 to HOME SECURITY network will remove the port from your home network.</br></br>'.
                                '</div>'.
                                '<div class="form-row odd" >'.
                                        '<div style="position:relative;right:-120px;"><input id="saveXHSBtn" type="button" value="Save" class="btn submit" /></div>'.
                                '</div>';
				}
		        //}	
		}
		echo '</div>';
	}
	?>
</div><!-- end #content -->
<?php include('includes/footer.php'); ?>
