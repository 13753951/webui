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
<!-- $Id: wireless_network_configuration_edit.php 3160 2010-01-11 23:10:33Z slemoine $ -->
<div id="sub-header">
	<?php include('includes/userbar.php'); ?>
</div><!-- end #sub-header -->
<?php include('includes/nav.php'); ?>
<?php
//allow only mso to edit SSID 3,4 for Home Security
if(!($_GET['id'] == 1 || $_GET['id'] == 2)){
	echo '<script type="text/javascript">location.href="wireless_network_configuration.php";</script>';
	exit(0);
}
/*
* There are lots of historical code need to be clean up...
*/
// SSID 1,2 for Private, 3,4 for Home Security, 5,6 for Hot Spot
// put edit SSID which have similar layout into one page, but edit HotSpot SSID
$id		= isset($_GET['id']) ? $_GET['id'] : "1";
$rf		= (2 - intval($id)%2);	//1,3,5,7 == 1(2.4G); 2,4,6,8 == 2(5G)
$valid_ids	= array('1','2','3','4');
// check if the LowerLayers radio is enabled. if disable, no need to configure
if ("false" == getStr("Device.WiFi.Radio.$rf.Enable")) {
	/*if (!$_SESSION['_DEBUG'])*/
	echo '<script type="text/javascript">alert("Wi-Fi Radio is disabled internally, please contact administrator!\n\nYou will be redirected to Wi-Fi status page...");location.href="wireless_network_configuration.php";</script>';
	exit(0);
}
function KeyExtGet($root, $param)
{
	$raw_ret = DmExtGetStrsWithRootObj($root, $param);
	$key_ret = array();
	for ($i=1; $i<count($raw_ret); $i++)
	{
		$tmp = array_keys($param, $raw_ret[$i][0]);
		$key = $tmp[0];
		$val = $raw_ret[$i][1];
		$key_ret[$key] = $val;
	}
	return $key_ret;
}
$wifi_param = array(
	"radio_enable"		=> "Device.WiFi.SSID.$id.Enable",
        "freq_band"		=> "Device.WiFi.Radio.$id.OperatingFrequencyBand",
	"network_name"		=> "Device.WiFi.SSID.$id.SSID",
	"wireless_mode"		=> "Device.WiFi.Radio.$rf.OperatingStandards",
	"encrypt_mode"		=> "Device.WiFi.AccessPoint.$id.Security.ModeEnabled",
	"encrypt_method"	=> "Device.WiFi.AccessPoint.$id.Security.X_CISCO_COM_EncryptionMethod",
	"channel_automatic"	=> "Device.WiFi.Radio.$rf.AutoChannelEnable",
	"channel_number"	=> "Device.WiFi.Radio.$rf.Channel",
	"network_password"	=> "Device.WiFi.AccessPoint.$id.Security.X_COMCAST-COM_KeyPassphrase",
	"broadcastSSID"		=> "Device.WiFi.AccessPoint.$id.SSIDAdvertisementEnabled",
	"enableWMM"			=> "Device.WiFi.AccessPoint.$id.WMMEnable",
	"channel_bandwidth"	=> "Device.WiFi.Radio.$rf.OperatingChannelBandwidth",
	"ext_channel"		=> "Device.WiFi.Radio.$rf.ExtensionChannel",
	"network_pass_64"	=> "Device.WiFi.AccessPoint.$id.Security.X_CISCO_COM_WEPKey64Bit.1.WEPKey",
	"network_pass_128"	=> "Device.WiFi.AccessPoint.$id.Security.X_CISCO_COM_WEPKey128Bit.1.WEPKey",
	"possible_channels"	=> "Device.WiFi.Radio.$rf.PossibleChannels",
	"DefaultSSID"		=> "Device.WiFi.SSID.$id.X_COMCAST-COM_DefaultSSID",
	"DefaultKeyPassphrase"	=> "Device.WiFi.AccessPoint.$id.Security.X_COMCAST-COM_DefaultKeyPassphrase",
	"AccessPoint_4_Enable"	=> "Device.WiFi.AccessPoint.4.Enable",
	"Radio_".$rf."_Enable"	=> "Device.WiFi.Radio.$rf.Enable",
	"SupportedStandards"	=> "Device.WiFi.Radio.$rf.SupportedStandards",
	"DFS_Support1"		=> "Device.WiFi.Radio.$rf.X_COMCAST_COM_DFSSupport", //1-supported 0-not supported
	"DFS_Enable1"		=> "Device.WiFi.Radio.$rf.X_COMCAST_COM_DFSEnable",
	);
$wifi_value = KeyExtGet("Device.WiFi.", $wifi_param);
$radio_enable		= $wifi_value['radio_enable'];
$freq_band		= $wifi_value['freq_band'];
$radio_band		= (strstr($freq_band,"5G")) ? "5" : "2.4";
$network_name		= $wifi_value['network_name'];
$wireless_mode		= $wifi_value['wireless_mode'];
$encrypt_mode		= $wifi_value['encrypt_mode'];
$encrypt_method		= $wifi_value['encrypt_method'];
$channel_automatic	= $wifi_value['channel_automatic'];
$channel_number		= $wifi_value['channel_number'];
$network_password	= $wifi_value['network_password'];
$broadcastSSID 		= $wifi_value['broadcastSSID'];
$enableWMM			= $wifi_value['enableWMM'];
$channel_bandwidth	= $wifi_value['channel_bandwidth'];
$ext_channel		= $wifi_value['ext_channel'];
$network_pass_64	= $wifi_value['network_pass_64'];
$network_pass_128	= $wifi_value['network_pass_128'];
$possible_channels	= $wifi_value['possible_channels'];
$defaultSSID		= ($id == 3)?"":$wifi_value['DefaultSSID'];
$defaultKeyPassphrase	= ($id == 3)?"":$wifi_value['DefaultKeyPassphrase'];
$DFS_Support1		= $wifi_value['DFS_Support1'];
$DFS_Enable1		= $wifi_value['DFS_Enable1'];
$DFS_Support1 = "false" ; //Remove/disable DFS channels, DFS_Support1 1-supported 0-not supported
$partnerId = getStr("Device.DeviceInfo.X_RDKCENTRAL-COM_Syndication.PartnerId");
$Mesh_Enable 	= getStr("Device.DeviceInfo.X_RDKCENTRAL-COM_xOpsDeviceMgmt.Mesh.Enable");
$Mesh_State 	= getStr("Device.DeviceInfo.X_RDKCENTRAL-COM_xOpsDeviceMgmt.Mesh.State");
$Mesh_Mode = ($Mesh_Enable == 'true' && $Mesh_State == 'Full')?'true':'false';
if($id != 1 && $id != 2) $Mesh_Mode = 'false';
/*- In bridge mode don't show 'Mac filter settings ' -*/
if(strstr($_SESSION["lanMode"], "bridge-static") ) {
	if($_SESSION["loginuser"] != "mso")
	{
		unset($valid_ids[3]);
		unset($valid_ids[2]);
		unset($valid_ids[1]);
		unset($valid_ids[0]);
	}
	else
	{
		unset($valid_ids[1]);
		unset($valid_ids[0]);
	}
}
/*- if AccessPoint is not up then don't show in GUI -*/
else if(strstr($wifi_value['AccessPoint_4_Enable'], "false")) unset($valid_ids[3]);
if (!in_array($id, $valid_ids)) {
	echo '<script type="text/javascript">history.back();</script>';
	exit(0);
}
//if Radio.{i}.Enable is false, ALL SSIDs belong to that radio shows disabled, else depends on SSID.{i}.Enable
if ("false" == $wifi_value['Radio_'.$rf.'_Enable']){
	$radio_enable = "false";
}
//check if support 802.11ac
$supported_mode		= $wifi_value['SupportedStandards'];
if ("1-11"==$possible_channels)
$possible_channels = "1,2,3,4,5,6,7,8,9,10,11";
$security = "None";
if ("WEP-64" == $encrypt_mode){
		$security = "WEP_64";
		$network_password = $network_pass_64;
}
elseif ("WEP-128" == $encrypt_mode){
		$security = "WEP_128";
		$network_password = $network_pass_128;
}
elseif ("WPA-Personal" == $encrypt_mode){
	if ("TKIP" == $encrypt_method){
		$security = "WPA_PSK_TKIP";
	}
	else{
		$security = "WPA_PSK_AES";
	}
}
elseif ("WPA2-Personal" == $encrypt_mode){
	if ("TKIP" == $encrypt_method){
		$security = "WPA2_PSK_TKIP";
	}
	elseif ("AES" == $encrypt_method){
		$security = "WPA2_PSK_AES";
	}
	else{
		$security = "WPA2_PSK_TKIPAES";
	}
}
elseif ("WPA-WPA2-Personal" == $encrypt_mode){
		$security = "WPAWPA2_PSK_TKIPAES";
}
else{
		$security = "None";
}
//for SSID 1 and 2 don't show password for mso user
$password_mso_user = !(($id == 1 || $id == 2) && ($_SESSION["loginuser"] == "mso"));
$brandName = getStr("Device.DeviceInfo.X_RDKCENTRAL-COM_Syndication.RDKB_UIBranding.CloudUI.brandname");
$productName = getStr("Device.DeviceInfo.X_RDKCENTRAL-COM_Syndication.RDKB_UIBranding.CloudUI.productname");
$productLink = getStr("Device.DeviceInfo.X_RDKCENTRAL-COM_Syndication.RDKB_UIBranding.CloudUI.link");
?>
<script type="text/javascript">
function showDialog() {
	$Mesh_Mode = '<?php echo $Mesh_Mode; ?>';
	if($Mesh_Mode == 'true'){
		$("#pop_dialog").find("input[value='None']").prop("disabled", true);
		$("#pop_dialog").find("input[value='None']").next().css('color', 'grey');
	}
	$("#pop_dialog").find("input[value^='WEP']").nextUntil("input").toggleExt( "ac" == $("#wireless_mode").val() );
	$("#pop_dialog").find("input[value^='WEP']").toggleExt( "ac" == $("#wireless_mode").val() );
	$.virtualDialog({
		title: "<?php echo _("Wi-Fi Security Modes")?>",
		content: $("#pop_dialog"),
		footer: '<input id="pop_cancel" type="button" value="<?php echo _("Cancel")?>" style="margin-left: 31px;float: right;"/><input id="pop_ok" type="button" value="<?php echo _("Apply")?>" style="margin-left: 31px;float: right;" />',
		width: "600px"
	});
	//disable wep if 11n
	if ("n"==$("#wireless_mode").val() || "n,ac"==$("#wireless_mode").val() || "a,n,ac"==$("#wireless_mode").val() || "g,n"==$("#wireless_mode").val() || "b,g,n"==$("#wireless_mode").val()) {
		$("#pop_dialog").find("[value='WEP_64'],[value='WEP_128']").prop("disabled", true);
	}
	else {
		$("#pop_dialog *").prop("disabled", false);
	}
	//init status of this pop-up
	$("#pop_dialog").find("[value='WPAWPA2_PSK_TKIPAES']").prop("checked", true);
	$("#pop_ok").off("click").on("click", function(){
		var popSec = $("#pop_dialog [name='path']:checked").val();
		$("#security").find("[value^='WEP'],[value='None']").remove();
		if ("None" == popSec) {
			$("#security").prepend('<option value="None" title="<?php echo _("Open networks do not have a password.")?>"><?php echo _("Open (risky)")?></option>');
		}
		else if ("WEP_64" == popSec) {
			$("#security").prepend('<option value="WEP_64" title="<?php echo _("WEP  64 requires a  5 ASCII character or  10 hex character password. Hex means only the following characters can be used: ABCDEF0123456789.")?>"><?php echo _("WEP 64 (risky)")?></option>');
		}
		else if ("WEP_128" == popSec) {
			$("#security").prepend('<option value="WEP_128" title="<?php echo _("WEP 128 requires a 13 ASCII character or  26 hex character password. Hex means only the following characters can be used: ABCDEF0123456789.")?>"><?php echo _("WEP 128 (risky)")?></option>');
		}
		$("#security").val(popSec);
		$.virtualDialog("hide");
		// check settings
		fromOther = true;
		$("#security").change();
	});
	$("#pop_cancel").off("click").on("click", function(){
		//location.reload();
		$("#security").find("[value^='WEP'],[value='None']").remove();
		if ("None" == $security_val) {
			$("#security").prepend('<option value="None" title="<?php echo _("Open networks do not have a password.")?>"><?php echo _("Open (risky)")?></option>');
		}
		else if ("WEP_64" == $security_val) {
			$("#security").prepend('<option value="WEP_64" title="<?php echo _("WEP  64 requires a  5 ASCII character or  10 hex character password. Hex means only the following characters can be used: ABCDEF0123456789.")?>"><?php echo _("WEP 64 (risky)")?></option>');
		}
		else if ("WEP_128" == $security_val) {
			$("#security").prepend('<option value="WEP_128" title="<?php echo _("WEP 128 requires a 13 ASCII character or  26 hex character password. Hex means only the following characters can be used: ABCDEF0123456789.")?>"><?php echo _("WEP 128 (risky)")?></option>');
		}
		$("#security").val($security_val);
		$.virtualDialog("hide");
		// check settings
		$("#security").change();
	});	
}
	var fromOther;
$(document).ready(function() {
    gateway.page.init("Gateway > Connection > Wireless > Edit <?php echo _($radio_band); ?> GHz", "nav-wifi-config");
	$Mesh_Mode = '<?php echo $Mesh_Mode; ?>';
	var MeshEnable= '<?php echo $Mesh_Enable; ?>';
	var partner_id = '<?php echo $partnerId; ?>';
	$("#wireless_network_switch").radioswitch({
		id: "wireless-network-switch",
		radio_name: "wireless_network",
		id_on: "radio_enable",
		id_off: "radio_disabled",
		title_on: "<?php echo _("Enable radio")?>",
		title_off: "<?php echo _("Disable radio")?>",
		state: <?php echo ($radio_enable === "true" ? "true" : "false");?> ? "on" : "off"
	});
	init_form();
	fromOther = false;
	$(":radio[name='channel']").change(function() {		//alert("hahaha");
		if ($("#channel_automatic").is(":checked") || (MeshEnable=="true")) {
			$("#channel_number").prop("disabled", true);
			$("#channel_number").hide();
			$("#auto_channel_number").show();
		}
		else {
			$("#channel_number").prop("disabled", false);
			$("#channel_number").show();
			$("#auto_channel_number").hide();
		}
		$("#auto_channel_number").prop("disabled", true);
	}).trigger("change");
	$("#broadcastSSID").change(function() {
		var ssid_number			= "<?php echo $id; ?>";
		if (!$("#broadcastSSID").prop("checked") && ("1"==ssid_number || "2"==ssid_number))
		{
			jConfirm(
				"<?php echo _("WARNING:<br/> Disabling Broadcast Network Name (SSID) will disable Wi-Fi Protected Setup (WPS) functionality. Are you sure you want to change?")?>"
				, "<?php echo _("Are You Sure?")?>"
				,function(ret) {
				if(!ret) {
					$("#broadcastSSID").prop("checked", true)
				}
			});
		}	
	});
	$security_val = '<?php echo $security; ?>';
	var password_mso_user = '<?php echo $password_mso_user; ?>';
	$("#security").change(function() {
		if ("more" == $("#security").val()) {
			// only private(1,2) SSID have show-more option
			showDialog();
		}
		else {
			if (fromOther && ($("#security").val()=="WEP_64" || $("#security").val()=="WEP_128" || $("#security").val()=="None"))
			{
				var security_val = $("#security").val();
				jConfirm(
					"<?php echo _("WARNING:<br/>Changing the Security Mode to Open will disable Wi-Fi Protected Setup(WPS) functionality. Are you sure you want to change?")?>"
					, "<?php echo _("Are You Sure?")?>"
					,function(ret) {
					if(!ret) {
						$("#security").find("[value='"+ security_val +"']").remove();
						$('#security option[value="' + $security_val + '"]').prop('selected', true);
						if("None" == $security_val){
							$("#network_password").prop("disabled", true);
						} else {
                                                        var pass_val = $("#network_password").val();
                                                        $("#network_password").val(pass_val);
							$("#network_password").prop("disabled", false);
							$("#netPassword-footnote").text($("option:selected", $("#security")).attr("title"));
							if(password_mso_user){
								$("#div_password_show").show();
							}
						}
					} else {
						$security_val = security_val;
                                                  if("None" == $security_val){
                                                    $("#network_password").val("");
                                                    $("#div_password_show").hide();
                                                  }
                                                 if(!password_mso_user && "None" == $security_val){
                                                       $("#div_change_password").hide();
                                                      }

					}
				});
			}
			if ("None" == $("#security").val()) {
				$("#network_password").prop("disabled", true);
                                 $("#div_password_show").hide();
			}
			else {
				if(!password_mso_user){
					$("#network_password").val("");
					$("#network_password").prop("disabled", true);
					$("#div_change_password").show();
					$("#div_password_show").hide();
				}
				else {
					$("#network_password").prop("disabled", false);
					$("#div_change_password").hide();
					$("#div_password_show").show();
				}
			}
			$("#netPassword-footnote").text($("option:selected", $(this)).attr("title"));
		}
		if(!password_mso_user){
			$('#password_check').click(function() {
				$("#network_password").prop("disabled", !(this.checked));
			});
		}
		//cacheye user option before going to "more"
		if ("more" != $("#security").val() && "WEP_64" != $("#security").val() && "WEP_128" != $("#security").val() && "None" != $("#security").val()) {
			$security_val = $("#security").val();
		}
		$("#security").find("[value^='WEP'],[value='None']").prop('disabled',true);
		fromOther = false;
    	});
           if("None" == $("#security").val()) {
                 $("#network_password").val("");
           }
           if(!password_mso_user && "None" == $("#security").val()){
                  $("#div_change_password").hide();
           }

	$("#password_show").change(function() {
		var pass_val = $("#network_password").val();
		if ($("#password_show").is(":checked")) {
			document.getElementById("password_field").innerHTML = 
			'<input type="text"     size="23" id="network_password" name="network_password" class="text" value="" />';
			$("#network_password").val(pass_val);
		}
		else {
			document.getElementById("password_field").innerHTML = 
			'<input type="password" size="23" id="network_password" name="network_password" class="text" value="" />';
			$("#network_password").val(pass_val);
		}
		if ("None" == $("#security").val() || '<?php echo $Mesh_Mode; ?>' == 'true') {
			$("#network_password").prop("disabled", true);
		}
		else {
			$("#network_password").prop("disabled", false);
		}
	});
	$("#wireless_mode").change(function() {
		// ONLY deal WEP for UI-4.0
		if ("n"==$("#wireless_mode").val() || "n,ac"==$("#wireless_mode").val() || "a,n,ac"==$("#wireless_mode").val() || "g,n"==$("#wireless_mode").val() || "b,g,n"==$("#wireless_mode").val()) {
			if ($("#security").val()=="WEP_64" || $("#security").val()=="WEP_128"){
				$("#security").val("WPAWPA2_PSK_TKIPAES");
			}
			$("#security").find("[value='WEP_64'],[value='WEP_128']").prop("disabled", true);
		}
		else {
			$("#security option").prop("disabled", false);
		}
		$("#security").change();
    });
	$("[name='channel_bandwidth1']").change(function() {
		//enable all channel first
		$("#channel_number option").prop("disabled", false);
		if ("true"!="<?php echo $DFS_Support1; ?>" || "true"!="<?php echo $DFS_Enable1; ?>") {
			$("#channel_number").find("[value='52'],[value='56'],[value='60'],[value='64'],[value='100'],[value='104'],[value='108'],[value='112'],[value='116'],[value='120'],[value='124'],[value='128'],[value='132'],[value='136'],[value='140'],[value='144']").prop("disabled", true).prop("selected", false);
		}
		//disable some channel as per extension channel when NOT 20MHz in 5G (2.4G able to set channel and extension channel together)
		if (!$("#channel_bandwidth201").prop("checked")) {
			// NOT 20MHz, disable channel 165
			//40MHz
			if ($("#channel_bandwidth1").is(":checked")) {
				if ("BelowControlChannel" == "<?php echo $ext_channel; ?>"){
					$("#channel_number").val('40');
					$("#channel_number").find("[value='36'],[value='44'],[value='52'],[value='60'],[value='100'],[value='108'],[value='116'],[value='132'],[value='140'],[value='144'],[value='149'],[value='157'],[value='165']").prop("disabled", true).prop("selected", false);
				}	
				else{	//AboveControlChannel or Auto  //zqiu: exclude 116,140
					$("#channel_number").val('36');
					$("#channel_number").find("[value='40'],[value='48'],[value='56'],[value='64'],[value='104'],[value='112'],[value='116'],[value='136'],[value='140'],[value='144'],[value='153'],[value='161'],[value='165']").prop("disabled", true).prop("selected", false);
				}
			}
			//80MHz
			else if ($("#channel_bandwidth2").is(":checked")) {
				$("#channel_number").val('36');
				$("#channel_number").find("[value='116'],[value='120'],[value='124'],[value='128'],[value='132'],[value='136'],[value='140'],[value='144'],[value='165']").prop("disabled", true).prop("selected", false);			
			}
		}
		else {
			//if Channel Bandwidth is 20MHz DFS Channels (Channels 50 - 144) should be greyed out
			$("#channel_number").val('36');
			$("#channel_number [value='52'],[value='56'],[value='60'],[value='64'],[value='100'],[value='104'],[value='108'],[value='112'],[value='116'],[value='120'],[value='124'],[value='128'],[value='132'],[value='136'],[value='140'],[value='144']").prop("disabled", true);
		}
	});
	$("#channel_number").change(function() {
		if ("165" == $(this).val()){
			$('[name="channel_bandwidth1"]:not([value="20MHz"])').prop("disabled", true);
		}
		else{
			$('[name="channel_bandwidth1"]').prop("disabled", false);
		}
	}).trigger("change");
    $("#wireless_network_switch").change(function() {
		if ($(this).radioswitch("getState").on === false || ($Mesh_Mode=="true")) {
			$(":input:not('#save_settings, #restore-default-settings')").not(".radioswitch_cont input").prop("disabled", true);
		}
		else {
			$(":input").not(".radioswitch_cont input").prop("disabled", false);
			//only run when enabled
			$(":radio[name='channel']").change();
			$("#password_show").change();
			$("#wireless_mode").change();
		}
	}).trigger("change");
	// Warning for DFS channel (52-140)
	$("#channel_number").change(function(){
		var channel = $("#channel_number option:selected").val();
		if(channel >= 52 && channel <= 140 ) {
			jConfirm(
				"<?php echo _("WARNING:<br/> You are selecting a Dynamic Frequency Selection (DFS) Channel (52-140). Some Wi-Fi devices do not support DFS channels in the 5 GHz band. For those devices that do not support DFS channels, the 5 GHz Wi-Fi Network Name (SSID) will not be displayed on the list of available networks. Do you wish to continue?")?>"
				, "<?php echo _("Are You Sure?")?>"
				,function(ret) {
					if(!ret) {
						$("#channel_number").val('<?php echo $channel_number; ?>').attr("selected","selected");
					}
			});
		}
	});
//zqiu >>	
	$("#restore-default-settings").click(function() {
		var href = $(this).attr("href");
		var message = ($(this).attr("title").length > 0) ? "<?php echo _("Are you sure you want to ")?> " + $(this).attr("title") + "?" : "<?php echo _("Are you sure?")?>";
		//var info = new Array("btn4", "Wifi");
		var radioIndex="<?php echo $rf; ?>";
		var apIndex="<?php echo $id; ?>";
		var thisUser= "<?php echo $_SESSION["loginuser"]; ?>";
		if ("mso"==thisUser) {
			radioIndex="0"; //no need to restore radio
		}
		var info = new Array("FactoryResetRadioAndAp", radioIndex+";"+apIndex, thisUser);
		jConfirm(
		message+"<br/><br/><?php echo _("<strong>WARNING:</strong> Wi-Fi will be unavailable for at least 30 seconds!")?>"
		, "<?php echo _("Are You Sure?")?>"
		,function(ret) {
		if(ret) {
			setResetInfo(info);
		}
		});	
	});	
//zqiu <<	
/*
 *  Manage password field: open wep networks don't use passwords
 */
    $.validator.addMethod("wep_64", function(value, element, param) {
		return !param || /^[a-fA-F0-9]{10}$|^[\S]{5}$/i.test(value);
	}, "<?php echo _("5 Ascii characters or 10 Hex digits.")?>");
    $.validator.addMethod("wep_128", function(value, element, param) {
		return !param || /^[a-fA-F0-9]{26}$|^[\S]{13}$/i.test(value);
	}, "<?php echo _("13 Ascii characters or 26 Hex digits.")?>");
    $.validator.addMethod("wpa", function(value, element, param) {
		return !param || /^[ -~]{8,63}$|^[a-fA-F0-9]{64}$/i.test(value);
	}, "<?php echo _("8 to 63 ASCII characters or a 64 hex character password.")?>");
    $.validator.addMethod("wpa2", function(value, element, param) {
		return !param || /^[\S]{8,63}$/i.test(value);
	}, "<?php echo _("8 to 63 ASCII characters.")?>");
    $.validator.addMethod("ssid_name", function(value, element, param) {
		return !param || /^[ -~]{1,32}$/i.test(value);
	}, "<?php echo _("1 to 32 ASCII characters.")?>");
    $.validator.addMethod("not_only_spaces", function(value, element, param) {
    	var res = /^\s+$/.test(value);
    	return !res;
    }, "<?php echo _("SSID name cannot contain only spaces")?>");
    $.validator.addMethod("not_hhs", function(value, element, param) {
		//prevent users to set XHSXXX or Xfinityxxx as ssid
		return value.toLowerCase().indexOf("xhs-") != 0 && value.toLowerCase().indexOf("xh-") != 0;
	}, '<?php echo _('SSID name starting with "XHS-" and "XH-" are reserved !')?>');
    $.validator.addMethod("not_hhs2", function(value, element, param) {
		//prevent users to set optimumwifi or TWCWiFi  or CableWiFi or CoxWiFi or BHNWifi as ssid
		//zqiu:
		var str = value.replace(/[\.,-\/#@!$%\^&\*;:{}=+?\-_`~()"'\\|<>\[\]\s]/g,'').toLowerCase();
		return str.indexOf("cablewifi") == -1 && str.indexOf("twcwifi") == -1 && str.indexOf("optimumwifi") == -1 && str.indexOf("xfinity") == -1 && str.indexOf("xfinitywifi") == -1 ;
		//return value.toLowerCase().indexOf("optimumwifi")==-1 && value.toLowerCase().indexOf("twcwifi")==-1 && value.toLowerCase().indexOf("cablewifi")==-1;
	}, '<?php echo _('SSID containing "optimumwifi", "TWCWiFi", "cablewifi", "xfinity" and "xfinitywifi" are reserved !')?>');
    <?php 
    if (strpos($partnerId, "sky-") !== false) {
        echo "$.validator.addMethod('not_defaulSSID', function(value, element, param) {
    		//prevent users to set defaul-SSID as ssid
    		return true;
    	}, '');
        $.validator.addMethod('not_defaulPassword', function(value, element, param) {
    		//prevent users to set defaul-Password as Password
    		return true;
    	}, '');";
        
    } else {
        echo "$.validator.addMethod('not_defaulSSID', function(value, element, param) {
    		//prevent users to set defaul-SSID as ssid
    		return value.toLowerCase() != '".$defaultSSID."'.toLowerCase();
    	}, '"._('Choose a different Network Name (SSID) than the one provided on your gateway.')."');
        $.validator.addMethod('not_defaulPassword', function(value, element, param) {
    		//prevent users to set defaul-Password as Password
    		return value != '".$defaultKeyPassphrase."';
    	}, '"._('Choose a different Network Password than the one provided on your gateway.')."');";
	}
    ?>
    // XFSETUP HOME xfinitywifi cablewifi
    // a term starting with the following combination of text in uppercase or lowercase should not be allowed
	/* $.validator.addMethod("not_XFSETUP", function(value, element, param) {
		return value.toLowerCase().indexOf("xfsetup") != 0;
	}, 'SSID starting with "XFSETUP" is reserved !');*/
   /* $.validator.addMethod("not_HOME", function(value, element, param) {
		return value.toLowerCase().indexOf("home") != 0;
	}, 'SSID starting with "HOME" is reserved !');
*/
/*
wep 64 ==> 5 Ascii characters or 10 Hex digits
wep 128 ==> 13 Ascii characters or 26 Hex digits
wpapsk ==> 8 to 63 Ascii characters or 64 Hex digits
wpa2psk ==> 8 to 63 Ascii characters
*/
    $("#pageForm").validate({
    	debug: true,
    	rules: {
			network_password: {
			not_defaulPassword: true,
			/*required: function() {
    				return ($("#security option:selected").val() != "None");
    			}
    			,*/wep_64: function() {
    				return ($("#security option:selected").val() == "WEP_64");
    			}
    			,wep_128: function() {
					return ($("#security option:selected").val() == "WEP_128");
    			}
    			// ,wpa: function() {
    				// return ($("#security option:selected").val() == "WPA_PSK_TKIP" || $("#security option:selected").val() == "WPA_PSK_AES");
    			// }
    			// ,wpa2: function() {
    				// return ($("#security option:selected").val() == "WPA2_PSK_TKIP" || $("#security option:selected").val() == "WPA2_PSK_AES" || $("#security option:selected").val() == "WPA2_PSK_TKIPAES" || $("#security option:selected").val() == "WPAWPA2_PSK_TKIPAES");
    			// }
    			,wpa: function() {
    				return ($("#security option:selected").val() != "None" && $("#security option:selected").val() != "WEP_64" && $("#security option:selected").val() != "WEP_128");
    			}
	    	}
    	},
		submitHandler:function(form){
			click_save();
		}
    });
    if(MeshEnable == 'true'){
    	$('#channel_number,input[name=channel_bandwidth]').prop("disabled", true);
    }    
	//for Mesh WiFi integration
	if($Mesh_Mode == 'true'){
		//disable >> Channel Selection:, Channel:, Channel Bandwidth:
		$('#channel_automatic,#security, #channel_manual, #password_check, #password_show , #broadcastSSID ,#enableWMM ,#channel_number, input[name=channel_bandwidth]').prop("disabled", true);
		//disable >> Network Name (SSID):, Network Password:
		$('#network_name, #network_password').prop("disabled", true);
	}
});
function init_form()
{
	var ssid_number			= "<?php echo $id; ?>";
	var thisUser			= "<?php echo $_SESSION["loginuser"]; ?>";
	var radio_band			= "<?php echo $radio_band; ?>";
	var channel_bandwidth	= "<?php echo $channel_bandwidth; ?>";
	var ext_channel			= "<?php echo $ext_channel; ?>";
	var security			= "<?php echo $security; ?>";
	var DFS_Support1		= "<?php echo $DFS_Support1; ?>";
	var DFS_Enable1			= "<?php echo $DFS_Enable1; ?>";
	//show or hide divs as per user
	if ("mso"==thisUser){
		$("#div_wireless_mode").hide();
		$("#div_channel_switch").hide();
		$("#div_channel_number").hide();
		$("#div_password_show").hide();
	}
	else{
		$("#div_enableWMM").hide();
	}
	//re-style each div
	$('#pageForm > div').removeClass("odd");
	$('#pageForm > div:visible:even').addClass("odd");

	if ("true"!="<?php echo $DFS_Support1; ?>" || "true"!="<?php echo $DFS_Enable1; ?>") {
		$("#channel_number [value='52'],[value='56'],[value='60'],[value='64'],[value='100'],[value='104'],[value='108'],[value='112'],[value='116'],[value='120'],[value='124'],[value='128'],[value='132'],[value='136'],[value='140'],[value='144']").prop("disabled", true);
	}
	//disable some channel as per extension channel when NOT 20MHz, only when can't set extension channel
	if ("20MHz" != channel_bandwidth){
		//40MHz, exclude 80MHz for 5G
		if ("40MHz" == channel_bandwidth){
			if ("2.4"==radio_band){
				if ("BelowControlChannel"==ext_channel){	//alert("hahaha");
					$("#channel_number [value='1'],[value='2'],[value='3'],[value='4']").prop("disabled", true);
				}
				else{	//AboveControlChannel or Auto
					$("#channel_number [value='8'],[value='9'],[value='10'],[value='11']").prop("disabled", false);				
				}
			}
			else{
				if ("BelowControlChannel"==ext_channel){
					$("#channel_number [value='36'],[value='44'],[value='52'],[value='60'],[value='100'],[value='108'],[value='116'],[value='132'],[value='140'],[value='149'],[value='157'],[value='165']").prop("disabled", true);
				}	
				else{	//AboveControlChannel or Auto
					$("#channel_number [value='40'],[value='48'],[value='56'],[value='64'],[value='104'],[value='112'],[value='136'],[value='153'],[value='161']").prop("disabled", true);
				}	
			}
		}
		// NOT 20MHz, disable channel 165
		$("#channel_number").find("[value='165']").prop("disabled", true).prop("selected", false);
	}
	else {
		//if Channel Bandwidth is 20MHz DFS Channels (Channels 50 - 144) should be greyed out
		$("#channel_number [value='52'],[value='56'],[value='60'],[value='64'],[value='100'],[value='104'],[value='108'],[value='112'],[value='116'],[value='120'],[value='124'],[value='128'],[value='132'],[value='136'],[value='140'],[value='144']").prop("disabled", true);
	}
	//re-style for 802.11ac
	$("#wireless_mode").val("<?php echo $wireless_mode; ?>");
	//for home sevurity ssid, no editing SSID name, no restore button
	if ("3"==ssid_number || "4"==ssid_number){
		$("#network_name").hide().after('<span size="26" id="static_network_name"><b>'+$("#network_name").val()+'</b></span>');
		$("#restore-default-settings").hide();
	}
	//for UI-4.0, remove some security options
    if ("2.4"==radio_band){
        $("#security").find("[value='None'],[value='WPA_PSK_TKIP'],[value='WPA_PSK_AES'],[value='WPA2_PSK_TKIP'],[value='WPA2_PSK_TKIPAES'],[value='WEP_64'],[value='WEP_128']").remove();
    }
    else{
        $("#security").find("[value='None'],[value='WPA_PSK_TKIP'],[value='WPA_PSK_AES'],[value='WPA2_PSK_TKIP'],[value='WPA2_PSK_TKIPAES'],[value='WEP_64'],[value='WEP_128']").remove();
    }
	// for UI-4.0, add show-more
	if ("1"==ssid_number || "2"==ssid_number) {
		$("#security").find("[value^='WEP'],[value='None']").not(":selected").remove();
		$("#security").append('<option title="More Security Mode Options." value="more"><?php echo _("Show More Security Mode Options")?></option>');
	}
	// for UI-4.0, remove WEP on show-more
	if ("1"==ssid_number) {
		$("#pop_dialog").find("input[value^='WEP']").nextUntil("input").remove();
		$("#pop_dialog").find("input[value^='WEP']").remove();
	}
	if ("None" == security) {
		$("#security").prepend('<option value="None" title="<?php echo _("Open networks do not have a password.")?>"><?php echo _("Open (risky)")?></option>');
	}
	else if ("WEP_64" == security) {
		$("#security").prepend('<option value="WEP_64" title="<?php echo _("WEP  64 requires a  5 ASCII character or  10 hex character password. Hex means only the following characters can be used: ABCDEF0123456789.")?>"><?php echo _("WEP 64 (risky)")?></option>');
	}
	else if ("WEP_128" == security) {
		$("#security").prepend('<option value="WEP_128" title="<?php echo _("WEP 128 requires a 13 ASCII character or  26 hex character password. Hex means only the following characters can be used: ABCDEF0123456789.")?>"><?php echo _("WEP 128 (risky)")?></option>');
	}
	$("#security").val(security);
}
function addslashes( str ) {
	return (str + '').replace(/[\\]/g, '\\$&').replace(/["]/g, '\\\$&').replace(/\u0000/g, '\\0');
}
function click_save()
{
	var network_name_1= "<?php echo htmlspecialchars(addslashes($network_name)); ?>";
	var password_mso_user = '<?php echo $password_mso_user; ?>';
	var network_password = "";
	var rf = "<?php echo $radio_band == "2.4"? "": 1; ?>";
	var radio_enable		= $("#wireless_network_switch").radioswitch("getState").on;
	var network_name		= addslashes($("#network_name").val());
	var wireless_mode		= $("#wireless_mode").attr("value");
	//var security			= $("#security").val();
	var security_id 		= document.getElementById("security");
	var security 			= security_id.options[security_id.selectedIndex].value;
	var channel_automatic	= $("#channel_automatic").prop("checked");
	var channel_number		= $("#channel_number").attr("value");
	var password_update      = $("#password_check").prop("checked");
	if((password_mso_user) || password_update){
		network_password	= addslashes($("#network_password").val());
	}
	var broadcastSSID		= $("#broadcastSSID").prop("checked");
	var enableWMM			= $("#enableWMM").prop("checked");
	var channel_bandwidth	= $('[name="channel_bandwidth'+rf+'"]:checked').attr("value");
	var jsConfig = '{"radio_enable":"'+radio_enable
	+'", "network_name":"'+network_name
	+'", "wireless_mode":"'+wireless_mode
	+'", "security":"'+security
	+'", "channel_automatic":"'+channel_automatic
	+'", "channel_number":"'+channel_number
	+'", "network_password":"'+network_password
	+'", "broadcastSSID":"'+broadcastSSID
	+'", "channel_bandwidth":"'+channel_bandwidth
	+'", "enableWMM":"'+enableWMM
	+'", "ssid_number":"'+"<?php echo $id; ?>"
	+'", "password_update":"'+password_update
	+'", "thisUser":"'+"<?php echo $_SESSION["loginuser"]; ?>"
	+'"}';	
	// alert(jsConfig);
	jProgress('<?php echo _("This may take several seconds...")?>', 60);
	$.ajax({
		type: "POST",
		url: "actionHandler/ajaxSet_wireless_network_configuration_edit.php",
		data: { configInfo: jsConfig },
		success: function(msg) {
			jHide();
			msg_parseJSON = $.parseJSON(msg);
			// location.reload();
			if(msg_parseJSON.error_message){
				jAlert(msg_parseJSON.error_message);
				$("#network_name").val(network_name_1);
			} else location.href = 'wireless_network_configuration.php';
		},
		error: function(){            
			jHide();
			jAlert("<?php echo _("Please connect to Wi-Fi using new Network Name (SSID) and Password.")?>");
		}
	});
}
//zqiu >>
function setResetInfo(info) {
	var jsonInfo = '["' + info[0] + '","' + info[1]+ '","' + "<?php echo $_SESSION["loginuser"]; ?>" + '"]';
	jProgress('<?php echo _("This may take several seconds...")?>', 60);
	$.ajax({
		type: "POST",
		url: "actionHandler/ajaxSet_Reset_Restore.php",
		data: { resetInfo: jsonInfo },
		success: function(data){
			jHide();
			jProgress("<?php echo _("Restoring Wi-Fi Settings is in progress...")?>", 999999);
			setTimeout(function(){location.reload();}, 1 * 60 * 1000);
		},
		error: function(){  
			jHide();
			//jAlert("Failure, please try again.");
        }
	});
}
//zqiu <<
</script>
<div id="content">
	<h1><?php echo _("Gateway > Connection >  Wi-Fi > Edit")." "._($radio_band); ?> GHz</h1>
	<div id="educational-tip">
		<p class="tip"><?php echo sprintf(_("Manage your %s GHz network settings."),_($radio_band))?></p>
		<p class="hidden"><?php echo _("<strong>Network Name (SSID):</strong> Identifies your home network from other nearby networks. Your default name can be found on the bottom label of the Gateway, but can be changed for easier identification.")?></p>
		<p class="hidden"><?php echo sprintf(_("<strong>Mode:</strong>  %s GHz operates in b/g/n modes. Unless you have older Wi-Fi devices that use only 'b' mode, use the default 802.11 g/n for faster performance."),_($radio_band))?></p>
		<p class="hidden"><?php echo _("<strong>Security Mode:</strong> Secures data between your Wi-Fi devices and the Gateway. The default WPAWPA2-PSK (TKIP/AES) setting is compatible with most devices and provides the best security and performance.")?></p>
		<p class="hidden"><?php echo _("<strong>Channel Selection:</strong>  Channel to be used for your home Wi-Fi network. In Automatic mode (default), the Gateway will select the channel with the least amount of Wi-Fi interference. In Manual mode, you can choose the channel to be used.")?></p>
		<p class="hidden"><?php echo _("<strong>Network Password(Key):</strong> Required by Wi-Fi products to connect to your secure network. The default setting can be found on the bottom label of the Gateway.")?> </p>
		<p class="hidden"><?php echo _("<strong>Broadcast Network Name (SSID):</strong>  If enabled, the Network Name (SSID) will be shown in the list of available networks. (If unchecked, you'll need to enter the exact Network Name (SSID) to connect.)")?></p>
	</div>
	<div class="module forms">
		<form action="#TBD" method="post" id="pageForm">
		<h2><?php if ($id>2) echo sprintf(_("Public Wi-Fi Network Configuration (%s GHz)"),_($radio_band)); else echo sprintf(_("Private Wi-Fi Network Configuration (%s GHz)"),_($radio_band)); ?></h2>
		<?php
			if($Mesh_Mode=="true"){
		?>
			<div class="form-row odd">
				<div id="content" style="text-align: center;">
					<br>
					<h3 style="width:92%"><?php echo sprintf(_("Wi-Fi Mode, Security Mode, Channel Selection, Channel Mode, and Channel Bandwidth are being managed automatically to help optimize your home Wi-Fi network and improve Wi-Fi coverage. To edit your Wi-Fi Network Name & Network password, please download the %s %s app or visit <a href='http://%s'>%s"), $brandName,$productName,$productLink,$productLink) ?></a>.
					</h3>
					<br>
				</div>
			</div>
		<?php
			}
		?>
		<div class="form-row odd">
			<span class="readonlyLabel label"><?php echo _("Wireless Network:")?></span>
			<span id="wireless_network_switch"></span>
		</div>		
		<div class="form-row _network_name">
			<label for="network_name"><?php echo _("Network Name (SSID):")?></label>
			<input type="text" size="23" value="<?php echo htmlspecialchars($network_name);?>" id="network_name" name="network_name" class="text" />
		</div>
		<div class="form-row odd" id="div_wireless_mode">
			<label for="wireless_mode"><?php echo _("Mode:")?></label>
			<select name="wireless_mode" id="wireless_mode">
			<?php
		                //zqiu: add "selected"
		                // this IF condition is for SKY
				if(strpos($partnerId, "sky-") !== false){
					if("5"==$radio_band){
						if (strstr($supported_mode, "ac")){
							echo '<option value="a,n,ac" ';   echo (  "a,n,ac"==$wireless_mode)? 'selected':''; echo'>802.11 a/n/ac</option>';
						}
					}
					else{
						echo '<option value="b,g,n" '; echo ("b,g,n"==$wireless_mode)? 'selected':'';  echo'>802.11 b/g/n</option>';
					}
				}
				else{
					if ("5"==$radio_band){
						if (strstr($supported_mode, "ac")){
							echo '<option value="ac" ';     echo (    "ac"==$wireless_mode)? 'selected':''; echo'>802.11 ac</option>';
							echo '<option value="n,ac" ';   echo (  "n,ac"==$wireless_mode)? 'selected':''; echo'>802.11 n/ac</option>';
							echo '<option value="a,n,ac" '; echo ("a,n,ac"==$wireless_mode)? 'selected':''; echo'>802.11 a/n/ac</option>';
							echo '<option value="n" ';      echo (     "n"==$wireless_mode)? 'selected':''; echo'>802.11 n</option>';
						}
						else {
							echo '<option value="n" '   ; echo (     "n"==$wireless_mode)? 'selected':''; echo'>802.11 n</option>';
							echo '<option value="a,n" ' ; echo (     "a,n"==$wireless_mode)? 'selected':''; echo'>802.11 a/n</option>';
						}
					}
					else {
						//echo '<option value="n" ';     echo (    "n"==$wireless_mode)? 'selected':'';  echo '>802.11 n</option>';
						echo '<option value="g,n" ';   echo (  "g,n"==$wireless_mode)? 'selected':'';  echo'>802.11 g/n</option>';
						echo '<option value="b,g,n" '; echo ("b,g,n"==$wireless_mode)? 'selected':'';  echo'>802.11 b/g/n</option>';
					}
				}
			?>
			</select>
		</div>
		<div class="form-row">
			<label for="security"><?php echo _("Security Mode:")?></label>
			<select name="encryption_method" id="security">
				<option value="None" 				title="<?php echo _("Open networks do not have a password.")?>" 			<?php if ("None"==$security) echo "selected";?> ><?php echo _("Open (risky)")?></option>
				<option value="WEP_64" 				title="<?php echo _("WEP  64 requires a  5 ASCII character or  10 hex character password. Hex means only the following characters can be used: ABCDEF0123456789.")?>" <?php if ("WEP_64"==$security)              echo "selected";?> ><?php echo _("WEP 64 (risky")?>)</option>
				<option value="WEP_128" 			title="<?php echo _("WEP 128 requires a 13 ASCII character or  26 hex character password. Hex means only the following characters can be used: ABCDEF0123456789.")?>" <?php if ("WEP_128"==$security)             echo "selected";?> ><?php echo _("WEP 128 (risky)")?></option>
				<option value="WPA_PSK_TKIP" 		title="<?php echo _("WPA requires an 8-63 ASCII character or a 64 hex character password. Hex means only the following characters can be used: ABCDEF0123456789.")?>" <?php if ("WPA_PSK_TKIP"==$security)        echo "selected";?> >WPA-PSK (TKIP)</option>
				<option value="WPA_PSK_AES" 		title="<?php echo _("WPA requires an 8-63 ASCII character or a 64 hex character password. Hex means only the following characters can be used: ABCDEF0123456789.")?>" <?php if ("WPA_PSK_AES"==$security)         echo "selected";?> >WPA-PSK (AES)</option>
				<option value="WPA2_PSK_TKIP" 		title="<?php echo _("WPA requires an 8-63 ASCII character or a 64 hex character password. Hex means only the following characters can be used: ABCDEF0123456789.")?>" <?php if ("WPA2_PSK_TKIP"==$security)       echo "selected";?> >WPA2-PSK (TKIP)</option>
				<option value="WPA2_PSK_AES" 		title="<?php echo _("WPA requires an 8-63 ASCII character password or a 64 hex character password. Hex means only the following characters can be used: ABCDEF0123456789.")?>" <?php if ("WPA2_PSK_AES"==$security)        echo "selected";?> ><?php echo _("WPA2-PSK (AES)(Recommended)")?></option>
				<option value="WPA2_PSK_TKIPAES"	title="<?php echo _("WPA requires an 8-63 ASCII character or a 64 hex character password. Hex means only the following characters can be used: ABCDEF0123456789.")?>" <?php if ("WPA2_PSK_TKIPAES"==$security)    echo "selected";?> >WPA2-PSK (TKIP/AES)</option>
				<option value="WPAWPA2_PSK_TKIPAES" title="<?php echo _("WPA requires an 8-63 ASCII character password or a 64 hex character password. Hex means only the following characters can be used: ABCDEF0123456789.")?>" <?php if ("WPAWPA2_PSK_TKIPAES"==$security) echo "selected";?> >WPAWPA2-PSK (TKIP/AES)</option>
			</select>
			<p id="tip_security_mode" class="footnote">
			<?php
				if ("5"==$radio_band && strstr($supported_mode, "ac")){
					echo '<?php echo _("Please note 802.11 n/ac mode only compatible with AES and None encryption!!")?>';
				}
				else{
					echo '<?php echo _("Please note 802.11 n mode only compatible with AES and None encryption!!")?>';
				}
			?>
			</p>
		</div>
		<div class="form-row odd" id="div_channel_switch">
			<label for="channel_automatic"><?php echo _("Channel Selection:")?></label>
                        <?php
                if('5'!=$radio_band || (strpos($partnerId, "sky-")===false)) {
					echo '<input type="radio"  name="channel" value="auto" 	id="channel_automatic" checked="checked" /><b>'._("Automatic").'</b>';
				}
			?>
			<label for="channel_manual" class="acs-hide"></label>
			<input type="radio"  name="channel" value="manual"  id="channel_manual"    <?php if ("false"==$channel_automatic) echo 'checked="checked"';?> /><b><?php echo _("Manual")?></b>
		</div>
		<div id="div_channel_number" class="form-row manual-only">
			<label for="channel_number"><?php echo _("Channel:")?></label>
			<select name="channel_number" id="channel_number">
				<!--option value="36" selected="selected">36</option-->
				<?php
					//dynamic generate possible channels
					$channels = explode(",", $possible_channels);
					foreach ($channels as $val){
						echo '<option value="'.$val.($val==$channel_number ? '" selected="selected">' : '" >').$val.'</option>';
					}
				?>
			</select>
			<label for="auto_channel_number" class="acs-hide"></label>
			<select id="auto_channel_number" disabled="disabled"><option selected="selected" ><?php echo $channel_number; ?></option></select>
		</div>
		<div class="form-row odd" id="bandwidth_switch" style="<?php if ('mso'==$_SESSION['loginuser']) {echo 'display:none'; } else {echo 'display:block'; } ?>" >
		<?php if($radio_band == "2.4" ) { ?>
			<label for="channel_bandwidth20"><?php echo _("Channel Bandwidth:")?></label>
			<input type="radio"  name="channel_bandwidth" value="20MHz" id="channel_bandwidth20" checked="checked" /><b>20</b>
			<label for="channel_bandwidth" class="acs-hide"></label>
			<input type="radio"  name="channel_bandwidth" value="40MHz" id="channel_bandwidth" <?php if ("40MHz"==$channel_bandwidth) echo 'checked="checked"';?> /><b>20/40</b>
		<?php } else { ?>
			<label for="channel_bandwidth201"><?php echo _("Channel Bandwidth:")?></label>
                         <?php if(strpos($partnerId, "sky-") === false){
				echo '<input type="radio"  name="channel_bandwidth1" value="20MHz" id="channel_bandwidth201" checked="checked" /><b>20</b>';
					}?>
			<?php if (strstr($supported_mode, "ac")){ ?>
				<label for="channel_bandwidth1" class="acs-hide"></label>
				<input type="radio"  name="channel_bandwidth1" value="40MHz"  id="channel_bandwidth1" <?php if ("40MHz"==$channel_bandwidth) echo 'checked="checked"';?> /><b>20/40</b>
				<label for="channel_bandwidth2" class="acs-hide"></label>
				<input type="radio"  name="channel_bandwidth1" value="80MHz"  id="channel_bandwidth2" <?php if ("80MHz"==$channel_bandwidth) echo 'checked="checked"';?> /><b>20/40/80</b>
			<?php } else{ ?>
				<label for="channel_bandwidth1" class="acs-hide"></label>
				<input type="radio"  name="channel_bandwidth1" value="40MHz"  id="channel_bandwidth1" <?php if ("40MHz"==$channel_bandwidth) echo 'checked="checked"';?> /><b>20/40</b>
			<?php }	?>
		<?php } ?>
		</div>
		<div class="form-row" id="div_network_password">
			<label for="network_password"><?php echo _("Network Password:")?></label>
			<span id="password_field"><input type="password" size="23" id="network_password" name="network_password" class="text" value="<?php if($password_mso_user) echo htmlspecialchars($network_password); ?>" </span>
			<p id="netPassword-footnote" class="footnote"><?php echo _("8 to 63 ASCII characters or a 64 hex character password. Case sensitive.")?></p>
		</div>
		<div class="form-row" id="div_change_password">
			<label for="change_password"><?php echo _("Change Password:")?></label>
			<span class="checkbox"><input type="checkbox" id="password_check" name="password_check" /></span>
		</div>
		<div class="form-row odd" id="div_password_show">
			<label for="password_show"><?php echo _("Show Network Password:")?></label>
			<span class="checkbox"><input type="checkbox" id="password_show" name="password_show" /></span>
		</div>
		<div id="div_broadcastSSID" class="form-row">
			<label for="broadcastSSID"><?php echo _("Broadcast Network Name (SSID):")?></label>
			<span class="checkbox"><input type="checkbox" id="broadcastSSID" name="broadcastSSID" <?php if ("true" == $broadcastSSID) echo 'checked="checked"';?> /><b><?php echo _("Enabled")?></b></span>
		</div>
		<div id="div_enableWMM" class="form-row odd">
			<label for="enableWMM"><?php echo _("Enable WMM:")?></label>
			<span class="checkbox"><input type="checkbox" id="enableWMM" name="enableWMM"  <?php if ("true" == $enableWMM) echo 'checked="checked"';?> /><b><?php echo _("Enabled")?></b></span>
		</div>
		<div class="form-row form-btn">
			<input type="submit" class="btn confirm" id="save_settings" name="save_settings" value="<?php echo _("Save Settings")?>" />
			<!--input href="#" title="Restore Wi-Fi Module" id="restore-default-settings" name="restore_default_settings" type="button" style="text-transform: none;" value="RESTORE Wi-Fi SETTINGS" class="btn alt" /-->
		</div>
		</form>
	</div> <!-- end .module -->
</div><!-- end #content -->
<div id="pop_dialog" class="content_message" style="display: none;">
	<div class="form-row odd">
		<p style="color: green;"><b><?php echo _('The recommended security mode is "WPA2-PSK (AES)" as this mode gives best Wi-Fi performance and optimum security.')?></b></p>
	</div>
	<div class="form-row">
		<input name="path" id="path1" type="radio" value="WPAWPA2_PSK_TKIPAES"><b>WPAWPA2-PSK (TKIP/AES) </b><br><span><?php echo _("This mixed mode option will allow Wi-Fi devices to connect with WPA (with TKIP or AES encryption) or WPA2 (with TKIP or AES encryption). To achieve best Wi-Fi performance in this mode, the Wi-Fi devices must connect using WPA2 with AES encryption.")?></span><br>
		<input name="path" id="path2" type="radio" value="WPA2_PSK_AES" checked="checked"><b><?php echo _("WPA2-PSK (AES) (Recommended)")?></b><br><span><?php echo _("This is the recommended and default option as this mode gives best Wi-Fi performance and optimum security.Older Wi-Fi devices which doesn't support WPA2 and AES encryption will be unable to connect to your Wi-Fi network in this mode.")?></span><br>
		<!--input name="path" id="path3" type="radio" value="WEP_64"><b>WEP (64)</b><br><span style="color: red;">This is only applicable for legacy Wi-Fi devices. Using this option will impact your Wi-Fi performance and less secure. Select this option only if you have very old Wi-Fi device and if it does not support WPA or WPA2 option.</span><br>
		<input name="path" id="path4" type="radio" value="WEP_128"><b>WEP (128)</b><br><span style="color: red;">This is only applicable for legacy Wi-Fi devices. Using this option will impact your Wi-Fi performance and less secure.Select this option only if you have very old Wi-Fi device and if it does not support WPA or WPA2 option.</span><br-->
		<input name="path" id="path5" type="radio" value="None"><b><?php echo _("Open (Risky)")?></b><br><span style="color: red;"><?php echo _("This is not recommended as it is doesn't have any security and anybody can connect to your Wi-Fi network.")?></span><br>
		<br>
	</div>
</div>
<?php include('includes/footer.php'); ?>
