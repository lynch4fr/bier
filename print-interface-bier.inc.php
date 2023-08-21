<?php

use App\Models\Port;
use App\Models\PortAdsl;
use App\Models\PortVdsl;
use LibreNMS\Config;
use LibreNMS\Util\IP;
use LibreNMS\Util\Number;

//class pour le presentation à la Bier
print '<style type="text/css">
.tg  {border-collapse:collapse;border-spacing:0;}
.tgfutur  {table-layout: fixed;width:100%;max-width: 500px;}
.tg td{font-family:Arial,text-overflow:ellipsis sans-serif;font-size:14px;padding:10px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;border-color:black;text-align:center;}
.tg tdfutur{overflow:hidden;white-space:nowrap;}
</style>
';

// Lecture et mise en cache des ports
    global $port_index_cache;
    $ports = DeviceCache::getPrimary()->ports()->orderBy('ifIndex')->isValid()->get();
    foreach ($ports as $key => $port) {
        $port_index_cache[$port['device_id']][$port['ifIndex']] = $port;
    }

// DEBUG
#echo "$ports";

$unit0PortCount = 0;
$unit1PortCount = 0;
$unit2PortCount = 0;
$unit3PortCount = 0;
$unit4PortCount = 0;

//Compte de le nombre port par unit
foreach ($ports as $port) {
    if (strpos($port["ifDescr"], "Unit 1") !== false && strpos($port["ifDescr"], "Port") !== false) {
        $unit1PortCount++;
    }
    if (strpos($port["ifDescr"], "Unit 2") !== false && strpos($port["ifDescr"], "Port") !== false) {
        $unit2PortCount++;
    }
    if (strpos($port["ifDescr"], "Unit 3") !== false && strpos($port["ifDescr"], "Port") !== false) {
        $unit3PortCount++;
    }
    if (strpos($port["ifDescr"], "Unit 4") !== false && strpos($port["ifDescr"], "Port") !== false) {
        $unit4PortCount++;
    }
    if (strpos($port["ifDescr"], "Unit 1") === false && strpos($port["ifDescr"], "Port") !== false) {
        $unit0PortCount++;
    }
}
//////////////////////////////////////////
// UNIT=1 &| UNIT=2 &| UNIT=3 &| UNIT=4 //
/////////////////////////////////////////

////////////
// UNIT 1 //
////////////
if ($unit1PortCount > 0) {
    $j = 0;
    $j1 = $j;
    echo '<div style="text-align: center; width: 100%; background-color: rgb(0, 138, 12);">';
    echo '<span style="color: white; font-weight: bold; font-size: 20px;">';
    echo "Number of 'Port' occurrences in 'Unit 1': " . $unit1PortCount . "<br>";
    echo '</div>';
    echo "<table class='tg'>";
    for ($i = 0; $i < 6; $i++) {
        echo "<tr>";
        for ($j = $j1; $j < $unit1PortCount+$j1; $j=$j+2) {
// HAUT UNIT=1 //
// Affiche le NUMERO de port du HAUT
            if ($i === 0) { 
                $ifName = $ports[$j]["ifName"];
                if (strpos($ifName, "ifc") === 0) {
                    $ifName = substr($ifName, 12);
                    $ifName = rtrim($ifName, ")");
	            $ifName = str_replace(" Port: ", "/", $ifName);
		    $port=$ports[$j];
	            $port['hostname'] = $device['hostname'];
                    echo '<td>' . generate_port_link($port, $ifName). "</td>";
        	}else{
		$port=$ports[$j];
	        $port['hostname'] = $device['hostname'];
                echo '<td>' . generate_port_link($port, $ports[$j]['ifName']). "</td>";
		}
// Affiche le NOM du port du HAUT
            } elseif ($i === 1) {
                if ( strpos($ports[$j]["ifAlias"], "uplink") !== false || strpos($ports[$j]["ifAlias"], "downlink") !== false ) {
		}
		echo "<td><p class=box-desc>" . substr($ports[$j]["ifAlias"], 0, 28) . "</p></td>";
// Affiche le VLAN du port du HAUT
            } elseif ($i === 2) {
	$vlans = dbFetchColumn(
	    'SELECT vlan,untagged FROM `ports_vlans` AS PV, vlans AS V ' .
	    'WHERE PV.`port_id`=? AND PV.`device_id`=? AND V.`vlan_vlan`=PV.vlan AND V.device_id = PV.device_id',
	    [$ports[$j]['port_id'], $device['device_id']]
	);
	$vlan_count = count($vlans);
	//DEBUG	echo $vlan_count;
	//DEBUG	echo " ";
	//DEBUG	echo $ports[$j]['port_id'];
	//DEBUG	echo " ";
	//DEBUG        echo implode(', ', $vlans);
	//DEBUG	echo " | ";
// Couleur du port
	$vlan = implode(', ', $vlans);
	if (preg_match('/3[0-9][0-9][0-9]/', $vlan)) { //Vlan fx-etu 
	$vlanColor = 'rgb(255, 149, 14)';
	}elseif (preg_match('/4[6-8]/', $vlan) || $vlan == 17) { // Vlan mgmt
	$vlanColor = 'rgb(230, 255, 0)';
	}elseif (preg_match('/1[0-2][0-9][0-9]/', $vlan)) { // Vlan fx-per 
	$vlanColor = 'rgb(71, 184, 184)';
	}elseif (preg_match('/4[2]/', $vlan)) { // Vlan corefipe 
	$vlanColor = 'rgb(71, 184, 184)';
	}elseif (preg_match('/4[4]/', $vlan) || preg_match('/1[6]/',$vlan)) { // Vlan mfp, imp
	$vlanColor = 'rgb(208, 38, 251)';
	}elseif (preg_match('/2[6]/', $vlan) || preg_match('/1[5]/', $vlan)) { // Vlan gtb, video
	$vlanColor = 'rgb(255, 71, 114)';
	}elseif (preg_match('/1[0]/', $vlan)) { // Vlan mass 
	$vlanColor = 'rgb(204, 255, 255)';
	}elseif (preg_match('/3[6]/', $vlan)) { // Vlan vdpj 
	$vlanColor = 'rgb(239, 146, 148)';
	}elseif (preg_match('/2[4]/', $vlan)) { // Vlan tel seul 
	$vlanColor = 'rgb(255, 102, 51)';
	}else{
	$vlanColor = 'rgb(255, 255, 255)';
	}
	if (preg_match('/2[4]/', $vlan)) { // Vlan tel + vlan x
	$vlanColorTel = 'rgb(255, 102, 51) 50%';
	}else{
	$vlanColorTel = 'rgb(255, 255, 255) 100%';
	}
        if ( strpos($ports[$j]["ifAlias"], "uplink") !== false || strpos($ports[$j]["ifAlias"], "downlink") !== false ) { // Port Uplink/Downlink
	$vlanColor = 'rgb(153, 153, 255)';
	$vlanColorTel = 'rgb(153, 153, 255) 100%';
	}
// Verifie si le port est untagged
	$nativevlans = dbFetchRows(
	    'SELECT vlan,untagged FROM `ports_vlans` AS PV, vlans AS V ' .
	    'WHERE PV.`port_id`=? AND PV.`device_id`=? AND V.`vlan_vlan`=PV.vlan AND V.device_id = PV.device_id',
	    [$ports[$j]['port_id'], $device['device_id']]
	);
	$untaggedvlan = null;
	foreach ($nativevlans as $row){
		if ($row['untagged'] === 1){
		$untaggedvlan = "<b>(U: ".$row['vlan'].")</b>";
		break;
		}
	}
// Affiche le port
		if ($vlan_count > 2) {
		    echo "<td style='background: linear-gradient(315deg, $vlanColor 50%, $vlanColorTel)'>";
		    echo '<p class=box-desc><span class=purple><a href="';
		    echo \LibreNMS\Util\Url::deviceUrl((int) $device['device_id'], ['tab' => 'vlans']);
		    echo '" title="';
		    echo implode(', ', $vlans);
		    echo '">VLANs: ';
		    echo $vlan_count. "<br>";
		    echo $untaggedvlan;
		    echo '</a></span></p>';
		}elseif ($vlan_count == 2) {
		    echo "<td style='background: linear-gradient(315deg, $vlanColor 50%, $vlanColorTel)'>";
		    echo '<p class=box-desc><span class=blue>';
		    echo 'VLANs: ';
		    echo implode(', ', $vlans) ."<br>";
		    echo  $untaggedvlan;
		    echo '</a></span></p>';
		} elseif ($vlan_count == 1 || $ports[$j]['ifVlan']) {
		    echo "<td style='background: linear-gradient(315deg, $vlanColor 100%, $vlanColorTel)'>";
		    echo '<p class=box-desc><span class=blue>';
		    echo 'VLAN: ';
		    echo implode(', ', $vlans) . "<br>";
		    echo $untaggedvlan;
		    ##echo $vlan_count;
		    echo '</span></p>';
		}else{
	            echo "<td>";
		    echo '<p class=box-desc><span class=blue>';
		    echo '</span></p>';
		}//end if
	echo "</td>";
// BAS UNIT=1 //
// Affiche le VLAN de port du BAS 
            } elseif ($i === 3) {
        #        echo "<td>" . $ports[$j+1]["ifVlan"] . "</td>";
	$vlans = dbFetchColumn(
	    'SELECT vlan FROM `ports_vlans` AS PV, vlans AS V ' .
	    'WHERE PV.`port_id`=? AND PV.`device_id`=? AND V.`vlan_vlan`=PV.vlan AND V.device_id = PV.device_id',
	    [$ports[$j+1]['port_id'], $device['device_id']]
	);
	$vlan_count = count($vlans);
//Couleur du port
	$vlan = implode(', ', $vlans);
	if (preg_match('/3[0-9][0-9][0-9]/', $vlan)) { //Vlan fx-etu 
	$vlanColor = 'rgb(255, 149, 14)';
	}elseif (preg_match('/4[6-8]/', $vlan) || $vlan == 17) { // Vlan mgmt
	$vlanColor = 'rgb(230, 255, 0)';
	}elseif (preg_match('/1[0-2][0-9][0-9]/', $vlan)) { // Vlan fx-per 
	$vlanColor = 'rgb(71, 184, 184)';
	}elseif (preg_match('/4[2]/', $vlan)) { // Vlan corefipe 
	$vlanColor = 'rgb(71, 184, 184)';
	}elseif (preg_match('/4[4]/', $vlan) || preg_match('/1[6]/',$vlan)) { // Vlan mfp, imp
	$vlanColor = 'rgb(208, 38, 251)';
	}elseif (preg_match('/2[6]/', $vlan) || preg_match('/1[5]/', $vlan)) { // Vlan gtb, video
	$vlanColor = 'rgb(255, 71, 114)';
	}elseif (preg_match('/1[0]/', $vlan)) { // Vlan mass 
	$vlanColor = 'rgb(204, 255, 255)';
	}elseif (preg_match('/3[6]/', $vlan)) { // Vlan vdpj 
	$vlanColor = 'rgb(239, 146, 148)';
	}elseif (preg_match('/2[4]/', $vlan)) { // Vlan tel seul 
	$vlanColor = 'rgb(255, 102, 51)';
	}else{
	$vlanColor = 'rgb(255, 255, 255)';
	}
	if (preg_match('/2[4]/', $vlan)) { // Vlan tel + vlan x
	$vlanColorTel = 'rgb(255, 102, 51) 50%';
	}else{
	$vlanColorTel = 'rgb(255, 255, 255) 100%';
	}
        if ( strpos($ports[$j+1]["ifAlias"], "uplink") !== false || strpos($ports[$j+1]["ifAlias"], "downlink") !== false ) { // Port Uplink/Downlink
	$vlanColor = 'rgb(153, 153, 255)';
	$vlanColorTel = 'rgb(153, 153, 255) 100%';
	}
// Verifie si le port est untagged
	$nativevlans = dbFetchRows(
	    'SELECT vlan,untagged FROM `ports_vlans` AS PV, vlans AS V ' .
	    'WHERE PV.`port_id`=? AND PV.`device_id`=? AND V.`vlan_vlan`=PV.vlan AND V.device_id = PV.device_id',
	    [$ports[$j+1]['port_id'], $device['device_id']]
	);
	$untaggedvlan = null;
	foreach ($nativevlans as $row){
		if ($row['untagged'] === 1){
		$untaggedvlan = "<b>(U: ".$row['vlan'].")</b>";
		break;
		}
	}
//Affiche le port
		if ($vlan_count > 2) {
		    echo "<td style='background: linear-gradient(315deg, $vlanColor 50%, $vlanColorTel)'>";
		    echo '<p class=box-desc><span class=purple><a href="';
		    echo \LibreNMS\Util\Url::deviceUrl((int) $device['device_id'], ['tab' => 'vlans']);
		    echo '" title="';
		    echo implode(', ', $vlans);
		    echo '">VLANs: ';
		    echo $vlan_count. "<br>";
		    echo $untaggedvlan;
		    echo '</a></span></p>';
		}elseif ($vlan_count == 2) {
		    echo "<td style='background: linear-gradient(315deg, $vlanColor 50%, $vlanColorTel)'>";
		    echo '<p class=box-desc><span class=blue>';
		    echo 'VLANs: ';
		    echo implode(', ', $vlans). "<br>";
	            echo $untaggedvlan;
		    echo '</a></span></p>';
		#}elseif ($vlan_count == 2) {
		#    echo "<td style='background: linear-gradient(315deg, $vlanColor 50%, $vlanColorTel)'>";
		#    echo '<p class=box-desc><span class=purple><a href="';
		#    echo \LibreNMS\Util\Url::deviceUrl((int) $device['device_id'], ['tab' => 'vlans']);
		#    echo '" title="';
		#    echo '">VLANs: ';
		#    echo implode(', ', $vlans);
		#    echo '</a></span></p>';
		} elseif ($vlan_count == 1 || $ports[$j+1]['ifVlan']) {
		    echo "<td style='background: linear-gradient(315deg, $vlanColor 100%, $vlanColorTel)'>";
		    echo '<p class=box-desc><span class=blue>';
		    echo 'VLAN: ';
		    echo implode(', ', $vlans). "<br>";
	            echo $untaggedvlan;
		    #echo $vlan_count;
		    echo '</span></p>';
		}else{
	            echo "<td>";
		    echo '<p class=box-desc><span class=blue>';
		    echo '</span></p>';
		}//end if
	echo "</td>";
// Affiche le NOM de port du BAS
            } elseif ($i === 4) {
                echo "<td>" . substr($ports[$j+1]["ifAlias"], 0, 28) . "</td>";
// Affiche le NUMERO de port du BAS
            } elseif ($i === 5) {
                $ifName = $ports[$j+1]["ifName"];
                if (strpos($ifName, "ifc") === 0) {
                    $ifName = substr($ifName, 12);
                    $ifName = rtrim($ifName, ")");
	            $ifName = str_replace(" Port: ", "/", $ifName);
		    $port=$ports[$j+1];
	            $port['hostname'] = $device['hostname'];
                    echo '<td>' . generate_port_link($port, $ifName). "</td>";
        	}else{
		$port=$ports[$j+1];
	        $port['hostname'] = $device['hostname'];
                echo '<td>' . generate_port_link($port, $ports[$j+1]['ifName']). "</td>";
		}
            }
       } 
        echo "</tr>";
    }
    echo "</table>";

////////////
// UNIT 2 //
////////////
    echo "<br>";
    echo '<div style="text-align: center; width: 100%; background-color: rgb(0, 138, 12);">';
    echo '<span style="color: white; font-weight: bold; font-size: 20px;">';
    echo "Number of 'Port' occurrences in 'Unit 2': " . $unit2PortCount . "<br>";
    echo '</div>';
if ($unit2PortCount != 0 ){ // START IF UNIT
    $j2 = $j;
    echo "<table class='tg'>";
    for ($i = 0; $i < 6; $i++) {
        echo "<tr>";
        for ($j = $j2 ; $j < $unit2PortCount+$j2; $j=$j+2) {
// HAUT UNIT=2 //
// Affiche le NUMERO de port du HAUT
            if ($i === 0) { 
                $ifName = $ports[$j]["ifName"];
                if (strpos($ifName, "ifc") === 0) {
                    $ifName = substr($ifName, 13);
                    $ifName = rtrim($ifName, ")");
	            $ifName = str_replace(" Port: ", "/", $ifName);
		    $port=$ports[$j];
	            $port['hostname'] = $device['hostname'];
                    echo '<td>' . generate_port_link($port, $ifName). "</td>";
        	}else{
		$port=$ports[$j];
	        $port['hostname'] = $device['hostname'];
                echo '<td>' . generate_port_link($port, $ports[$j]['ifName']). "</td>";
		}
// Affiche le NOM du port du HAUT
            } elseif ($i === 1) {
                echo "<td><p class=box-desc>" . substr($ports[$j]["ifAlias"], 0, 28) . "</p></td>";
// Affiche le VLAN du port du HAUT
            } elseif ($i === 2) {
	$vlans = dbFetchColumn(
	    'SELECT vlan FROM `ports_vlans` AS PV, vlans AS V ' .
	    'WHERE PV.`port_id`=? AND PV.`device_id`=? AND V.`vlan_vlan`=PV.vlan AND V.device_id = PV.device_id',
	    [$ports[$j]['port_id'], $device['device_id']]
	);
	$vlan_count = count($vlans);
#	echo "<td>";
//Couleur du port
	$vlan = implode(', ', $vlans);
	if (preg_match('/3[0-9][0-9][0-9]/', $vlan)) { //Vlan fx-etu 
	$vlanColor = 'rgb(255, 149, 14)';
	}elseif (preg_match('/4[6-8]/', $vlan) || $vlan == 17) { // Vlan mgmt
	$vlanColor = 'rgb(230, 255, 0)';
	}elseif (preg_match('/1[0-2][0-9][0-9]/', $vlan)) { // Vlan fx-per 
	$vlanColor = 'rgb(71, 184, 184)';
	}elseif (preg_match('/4[2]/', $vlan)) { // Vlan corefipe 
	$vlanColor = 'rgb(71, 184, 184)';
	}elseif (preg_match('/4[4]/', $vlan) || preg_match('/1[6]/',$vlan)) { // Vlan mfp, imp
	$vlanColor = 'rgb(208, 38, 251)';
	}elseif (preg_match('/2[6]/', $vlan) || preg_match('/1[5]/', $vlan)) { // Vlan gtb, video
	$vlanColor = 'rgb(255, 71, 114)';
	}elseif (preg_match('/1[0]/', $vlan)) { // Vlan mass 
	$vlanColor = 'rgb(204, 255, 255)';
	}elseif (preg_match('/3[6]/', $vlan)) { // Vlan vdpj 
	$vlanColor = 'rgb(239, 146, 148)';
	}elseif (preg_match('/2[4]/', $vlan)) { // Vlan tel seul 
	$vlanColor = 'rgb(255, 102, 51)';
	}else{
	$vlanColor = 'rgb(255, 255, 255)';
	}
	if (preg_match('/2[4]/', $vlan)) { // Vlan tel + vlan x
	$vlanColorTel = 'rgb(255, 102, 51) 50%';
	}else{
	$vlanColorTel = 'rgb(255, 255, 255) 100%';
	}
        if ( strpos($ports[$j]["ifAlias"], "uplink") !== false || strpos($ports[$j]["ifAlias"], "downlink") !== false ) { // Port Uplink/Downlink
	$vlanColor = 'rgb(153, 153, 255)';
	$vlanColorTel = 'rgb(153, 153, 255) 100%';
	}
// Verifie si le port est untagged
	$nativevlans = dbFetchRows(
	    'SELECT vlan,untagged FROM `ports_vlans` AS PV, vlans AS V ' .
	    'WHERE PV.`port_id`=? AND PV.`device_id`=? AND V.`vlan_vlan`=PV.vlan AND V.device_id = PV.device_id',
	    [$ports[$j]['port_id'], $device['device_id']]
	);
	$untaggedvlan = null;
	foreach ($nativevlans as $row){
		if ($row['untagged'] === 1){
		$untaggedvlan = "<b>(U: ".$row['vlan'].")</b>";
		break;
		}
	}
//Affiche le port
		if ($vlan_count > 2) {
		    echo "<td style='background: linear-gradient(315deg, $vlanColor 50%, $vlanColorTel)'>";
		    echo '<p class=box-desc><span class=purple><a href="';
		    echo \LibreNMS\Util\Url::deviceUrl((int) $device['device_id'], ['tab' => 'vlans']);
		    echo '" title="';
		    echo implode(', ', $vlans);
		    echo '">VLANs: ';
		    echo $vlan_count. "<br>";
		    echo $untaggedvlan;
		    echo '</a></span></p>';
		}elseif ($vlan_count == 2) {
		    echo "<td style='background: linear-gradient(315deg, $vlanColor 50%, $vlanColorTel)'>";
		    echo '<p class=box-desc><span class=blue>';
		    echo 'VLANs: ';
		    echo implode(', ', $vlans). "<br>";
		    echo $untaggedvlan;
		    echo '</a></span></p>';
		} elseif ($vlan_count == 1 || $ports[$j]['ifVlan']) {
		    echo "<td style='background: linear-gradient(315deg, $vlanColor 100%, $vlanColorTel)'>";
		    #echo '<p class=box-desc><span class=blue>';
		    echo 'VLAN: ';
		    echo implode(', ', $vlans). "<br>";
		    echo $untaggedvlan;
		    #echo $vlan_count;
		    echo '</span></p>';
		}else{
	            echo "<td>";
		    echo '<p class=box-desc><span class=blue>';
		    echo '</span></p>';
		}//end if
	echo "</td>";
// BAS UNIT=2 //
// Affiche le VLAN de port du BAS 
            } elseif ($i === 3) {
	$vlans = dbFetchColumn(
	    'SELECT vlan FROM `ports_vlans` AS PV, vlans AS V ' .
	    'WHERE PV.`port_id`=? AND PV.`device_id`=? AND V.`vlan_vlan`=PV.vlan AND V.device_id = PV.device_id',
	    [$ports[$j+1]['port_id'], $device['device_id']]
	);
	$vlan_count = count($vlans);
#	echo "<td>";
//Couleur du port
	$vlan = implode(', ', $vlans);
	if (preg_match('/3[0-9][0-9][0-9]/', $vlan)) { //Vlan fx-etu 
	$vlanColor = 'rgb(255, 149, 14)';
	}elseif (preg_match('/4[6-8]/', $vlan) || $vlan == 17) { // Vlan mgmt
	$vlanColor = 'rgb(230, 255, 0)';
	}elseif (preg_match('/1[0-2][0-9][0-9]/', $vlan)) { // Vlan fx-per 
	$vlanColor = 'rgb(71, 184, 184)';
	}elseif (preg_match('/4[2]/', $vlan)) { // Vlan corefipe 
	$vlanColor = 'rgb(71, 184, 184)';
	}elseif (preg_match('/4[4]/', $vlan) || preg_match('/1[6]/',$vlan)) { // Vlan mfp, imp
	$vlanColor = 'rgb(208, 38, 251)';
	}elseif (preg_match('/2[6]/', $vlan) || preg_match('/1[5]/', $vlan)) { // Vlan gtb, video
	$vlanColor = 'rgb(255, 71, 114)';
	}elseif (preg_match('/1[0]/', $vlan)) { // Vlan mass 
	$vlanColor = 'rgb(204, 255, 255)';
	}elseif (preg_match('/3[6]/', $vlan)) { // Vlan vdpj 
	$vlanColor = 'rgb(239, 146, 148)';
	}elseif (preg_match('/2[4]/', $vlan)) { // Vlan tel seul 
	$vlanColor = 'rgb(255, 102, 51)';
	}else{
	$vlanColor = 'rgb(255, 255, 255)';
	}
	if (preg_match('/2[4]/', $vlan)) { // Vlan tel + vlan x
	$vlanColorTel = 'rgb(255, 102, 51) 50%';
	}else{
	$vlanColorTel = 'rgb(255, 255, 255) 100%';
	}
        if ( strpos($ports[$j+1]["ifAlias"], "link") !== false || strpos($ports[$j+1]["ifAlias"], "link") !== false ) { // Port Uplink/Downlink
	$vlanColor = 'rgb(153, 153, 255)';
	$vlanColorTel = 'rgb(153, 153, 255) 100%';
	}	
// Verifie si le port est untagged
	$nativevlans = dbFetchRows(
	    'SELECT vlan,untagged FROM `ports_vlans` AS PV, vlans AS V ' .
	    'WHERE PV.`port_id`=? AND PV.`device_id`=? AND V.`vlan_vlan`=PV.vlan AND V.device_id = PV.device_id',
	    [$ports[$j+1]['port_id'], $device['device_id']]
	);
	$untaggedvlan = null;
	foreach ($nativevlans as $row){
		if ($row['untagged'] === 1){
		$untaggedvlan = "<b>(U: ".$row['vlan'].")</b>";
		break;
		}
	}
//Affiche le port
		if ($vlan_count > 2) {
		    echo "<td style='background: linear-gradient(315deg, $vlanColor 50%, $vlanColorTel)'>";
		    echo '<p class=box-desc><span class=purple><a href="';
		    echo \LibreNMS\Util\Url::deviceUrl((int) $device['device_id'], ['tab' => 'vlans']);
		    echo '" title="';
		    echo implode(', ', $vlans);
		    echo '">VLANs: ';
		    echo $vlan_count. "<br>";
		    echo $untaggedvlan;
		    echo '</a></span></p>';
		}elseif ($vlan_count == 2) {
		    echo "<td style='background: linear-gradient(315deg, $vlanColor 50%, $vlanColorTel)'>";
		    echo '<p class=box-desc><span class=blue>';
		    echo 'VLANs: ';
		    echo implode(', ', $vlans). "<br>";
		    echo $untaggedvlan;
		    echo '</a></span></p>';
		} elseif ($vlan_count == 1 || $ports[$j+1]['ifVlan']) {
		    echo "<td style='background: linear-gradient(315deg, $vlanColor 100%, $vlanColorTel)'>";
		    echo '<p class=box-desc><span class=blue>';
		    echo 'VLAN: ';
		    echo implode(', ', $vlans). "<br>";
		    echo $untaggedvlan;
		    #echo $vlan_count;
		    echo '</span></p>';
	        }else{
                    echo "<td>";
                    echo '<p class=box-desc><span class=blue>';
                    echo '</span></p>';
		}//end if
	echo "</td>";
// Affiche le NOM de port du BAS
            } elseif ($i === 4) {
                echo "<td>" . substr($ports[$j+1]["ifAlias"], 0, 28) . "</td>";
// Affiche le NUMERO de port du BAS
            } elseif ($i === 5) {
                $ifName = $ports[$j+1]["ifName"];
                if (strpos($ifName, "ifc") === 0) {
                    $ifName = substr($ifName, 13);
                    $ifName = rtrim($ifName, ")");
	            $ifName = str_replace(" Port: ", "/", $ifName);
		    $port=$ports[$j+1];
	            $port['hostname'] = $device['hostname'];
                    echo '<td>' . generate_port_link($port, $ifName). "</td>";
        	}else{
		$port=$ports[$j+1];
	        $port['hostname'] = $device['hostname'];
                echo '<td>' . generate_port_link($port, $ports[$j+1]['ifName']). "</td>";
		}
            }
       } 
	echo "</tr>";
    }
    echo "</table>";
} // END IF UNIT

////////////
// UNIT 3 //
////////////
    echo "<br>";
    echo '<div style="text-align: center; width: 100%; background-color: rgb(0, 138, 12);">';
    echo '<span style="color: white; font-weight: bold; font-size: 20px;">';
    echo "Number of 'Port' occurrences in 'Unit 3': " . $unit3PortCount . "<br>";
    echo '</div>';
if ($unit3PortCount != 0 ){ // START IF UNIT
    $j3 = $j;
    echo "<table class='tg'>";
    for ($i = 0; $i < 6; $i++) {
        echo "<tr>";
        for ($j = $j3 ; $j < $unit3PortCount+$j3; $j=$j+2) {
// HAUT UNIT=3 //
// Affiche le NUMERO de port du HAUT
            if ($i === 0) { 
                $ifName = $ports[$j]["ifName"];
                if (strpos($ifName, "ifc") === 0) {
                    $ifName = substr($ifName, 13);
                    $ifName = rtrim($ifName, ")");
	            $ifName = str_replace(" Port: ", "/", $ifName);
		    $port=$ports[$j];
	            $port['hostname'] = $device['hostname'];
                    echo '<td>' . generate_port_link($port, $ifName). "</td>";
        	}else{
		$port=$ports[$j];
	        $port['hostname'] = $device['hostname'];
                echo '<td>' . generate_port_link($port, $ports[$j]['ifName']). "</td>";
		}
// Affiche le NOM du port du HAUT
            } elseif ($i === 1) {
                echo "<td><p class=box-desc>" . substr($ports[$j]["ifAlias"], 0, 28) . "</p></td>";
// Affiche le VLAN du port du HAUT
            } elseif ($i === 2) {
	$vlans = dbFetchColumn(
	    'SELECT vlan FROM `ports_vlans` AS PV, vlans AS V ' .
	    'WHERE PV.`port_id`=? AND PV.`device_id`=? AND V.`vlan_vlan`=PV.vlan AND V.device_id = PV.device_id',
	    [$ports[$j]['port_id'], $device['device_id']]
	);
	$vlan_count = count($vlans);
#	echo "<td>";
//Couleur du port
	$vlan = implode(', ', $vlans);
	if (preg_match('/3[0-9][0-9][0-9]/', $vlan)) { //Vlan fx-etu 
	$vlanColor = 'rgb(255, 149, 14)';
	}elseif (preg_match('/4[6-8]/', $vlan) || $vlan == 17) { // Vlan mgmt
	$vlanColor = 'rgb(230, 255, 0)';
	}elseif (preg_match('/1[0-2][0-9][0-9]/', $vlan)) { // Vlan fx-per 
	$vlanColor = 'rgb(71, 184, 184)';
	}elseif (preg_match('/4[2]/', $vlan)) { // Vlan corefipe 
	$vlanColor = 'rgb(71, 184, 184)';
	}elseif (preg_match('/4[4]/', $vlan) || preg_match('/1[6]/',$vlan)) { // Vlan mfp, imp
	$vlanColor = 'rgb(208, 38, 251)';
	}elseif (preg_match('/2[6]/', $vlan) || preg_match('/1[5]/', $vlan)) { // Vlan gtb, video
	$vlanColor = 'rgb(255, 71, 114)';
	}elseif (preg_match('/1[0]/', $vlan)) { // Vlan mass 
	$vlanColor = 'rgb(204, 255, 255)';
	}elseif (preg_match('/3[6]/', $vlan)) { // Vlan vdpj 
	$vlanColor = 'rgb(239, 146, 148)';
	}elseif (preg_match('/2[4]/', $vlan)) { // Vlan tel seul 
	$vlanColor = 'rgb(255, 102, 51)';
	}else{
	$vlanColor = 'rgb(255, 255, 255)';
	}
	if (preg_match('/2[4]/', $vlan)) { // Vlan tel + vlan x
	$vlanColorTel = 'rgb(255, 102, 51) 50%';
	}else{
	$vlanColorTel = 'rgb(255, 255, 255) 100%';
	}
        if ( strpos($ports[$j]["ifAlias"], "uplink") !== false || strpos($ports[$j]["ifAlias"], "downlink") !== false ) { // Port Uplink/Downlink
	$vlanColor = 'rgb(153, 153, 255)';
	$vlanColorTel = 'rgb(153, 153, 255) 100%';
	}
// Verifie si le port est untagged
	$nativevlans = dbFetchRows(
	    'SELECT vlan,untagged FROM `ports_vlans` AS PV, vlans AS V ' .
	    'WHERE PV.`port_id`=? AND PV.`device_id`=? AND V.`vlan_vlan`=PV.vlan AND V.device_id = PV.device_id',
	    [$ports[$j]['port_id'], $device['device_id']]
	);
	$untaggedvlan = null;
	foreach ($nativevlans as $row){
		if ($row['untagged'] === 1){
		$untaggedvlan = "<b>(U: ".$row['vlan'].")</b>";
		break;
		}
	}
//Affiche le port
		if ($vlan_count > 2) {
		    echo "<td style='background: linear-gradient(315deg, $vlanColor 50%, $vlanColorTel)'>";
		    echo '<p class=box-desc><span class=purple><a href="';
		    echo \LibreNMS\Util\Url::deviceUrl((int) $device['device_id'], ['tab' => 'vlans']);
		    echo '" title="';
		    echo implode(', ', $vlans);
		    echo '">VLANs: ';
		    echo $vlan_count. "<br>";
		    echo $untaggedvlan;
		    echo '</a></span></p>';
		}elseif ($vlan_count == 2) {
		    echo "<td style='background: linear-gradient(315deg, $vlanColor 50%, $vlanColorTel)'>";
		    echo '<p class=box-desc><span class=blue>';
		    echo 'VLANs: ';
		    echo implode(', ', $vlans). "<br>";
		    echo $untaggedvlan;
		    echo '</a></span></p>';
		} elseif ($vlan_count == 1 || $ports[$j]['ifVlan']) {
		    echo "<td style='background: linear-gradient(315deg, $vlanColor 100%, $vlanColorTel)'>";
		    #echo '<p class=box-desc><span class=blue>';
		    echo 'VLAN: ';
		    echo implode(', ', $vlans). "<br>";
		    echo $untaggedvlan;
		    #echo $vlan_count;
		    echo '</span></p>';
		}else{
	            echo "<td>";
		    echo '<p class=box-desc><span class=blue>';
		    echo '</span></p>';
		}//end if
	echo "</td>";
// BAS UNIT=3 //
// Affiche le VLAN de port du BAS 
            } elseif ($i === 3) {
	$vlans = dbFetchColumn(
	    'SELECT vlan FROM `ports_vlans` AS PV, vlans AS V ' .
	    'WHERE PV.`port_id`=? AND PV.`device_id`=? AND V.`vlan_vlan`=PV.vlan AND V.device_id = PV.device_id',
	    [$ports[$j+1]['port_id'], $device['device_id']]
	);
	$vlan_count = count($vlans);
#	echo "<td>";
//Couleur du port
	$vlan = implode(', ', $vlans);
	if (preg_match('/3[0-9][0-9][0-9]/', $vlan)) { //Vlan fx-etu 
	$vlanColor = 'rgb(255, 149, 14)';
	}elseif (preg_match('/4[6-8]/', $vlan) || $vlan == 17) { // Vlan mgmt
	$vlanColor = 'rgb(230, 255, 0)';
	}elseif (preg_match('/1[0-2][0-9][0-9]/', $vlan)) { // Vlan fx-per 
	$vlanColor = 'rgb(71, 184, 184)';
	}elseif (preg_match('/4[2]/', $vlan)) { // Vlan corefipe 
	$vlanColor = 'rgb(71, 184, 184)';
	}elseif (preg_match('/4[4]/', $vlan) || preg_match('/1[6]/',$vlan)) { // Vlan mfp, imp
	$vlanColor = 'rgb(208, 38, 251)';
	}elseif (preg_match('/2[6]/', $vlan) || preg_match('/1[5]/', $vlan)) { // Vlan gtb, video
	$vlanColor = 'rgb(255, 71, 114)';
	}elseif (preg_match('/1[0]/', $vlan)) { // Vlan mass 
	$vlanColor = 'rgb(204, 255, 255)';
	}elseif (preg_match('/3[6]/', $vlan)) { // Vlan vdpj 
	$vlanColor = 'rgb(239, 146, 148)';
	}elseif (preg_match('/2[4]/', $vlan)) { // Vlan tel seul 
	$vlanColor = 'rgb(255, 102, 51)';
	}else{
	$vlanColor = 'rgb(255, 255, 255)';
	}
	if (preg_match('/2[4]/', $vlan)) { // Vlan tel + vlan x
	$vlanColorTel = 'rgb(255, 102, 51) 50%';
	}else{
	$vlanColorTel = 'rgb(255, 255, 255) 100%';
	}
        if ( strpos($ports[$j+1]["ifAlias"], "link") !== false || strpos($ports[$j+1]["ifAlias"], "link") !== false ) { // Port Uplink/Downlink
	$vlanColor = 'rgb(153, 153, 255)';
	$vlanColorTel = 'rgb(153, 153, 255) 100%';
	}	
// Verifie si le port est untagged
	$nativevlans = dbFetchRows(
	    'SELECT vlan,untagged FROM `ports_vlans` AS PV, vlans AS V ' .
	    'WHERE PV.`port_id`=? AND PV.`device_id`=? AND V.`vlan_vlan`=PV.vlan AND V.device_id = PV.device_id',
	    [$ports[$j+1]['port_id'], $device['device_id']]
	);
	$untaggedvlan = null;
	foreach ($nativevlans as $row){
		if ($row['untagged'] === 1){
		$untaggedvlan = "<b>(U: ".$row['vlan'].")</b>";
		break;
		}
	}
//Affiche le port
		if ($vlan_count > 2) {
		    echo "<td style='background: linear-gradient(315deg, $vlanColor 50%, $vlanColorTel)'>";
		    echo '<p class=box-desc><span class=purple><a href="';
		    echo \LibreNMS\Util\Url::deviceUrl((int) $device['device_id'], ['tab' => 'vlans']);
		    echo '" title="';
		    echo implode(', ', $vlans);
		    echo '">VLANs: ';
		    echo $vlan_count. "<br>";
		    echo $untaggedvlan;
		    echo '</a></span></p>';
		}elseif ($vlan_count == 2) {
		    echo "<td style='background: linear-gradient(315deg, $vlanColor 50%, $vlanColorTel)'>";
		    echo '<p class=box-desc><span class=blue>';
		    echo 'VLANs: ';
		    echo implode(', ', $vlans). "<br>";
		    echo $untaggedvlan;
		    echo '</a></span></p>';
		} elseif ($vlan_count == 1 || $ports[$j+1]['ifVlan']) {
		    echo "<td style='background: linear-gradient(315deg, $vlanColor 100%, $vlanColorTel)'>";
		    echo '<p class=box-desc><span class=blue>';
		    echo 'VLAN: ';
		    echo implode(', ', $vlans). "<br>";
		    echo $untaggedvlan;
		    #echo $vlan_count;
		    echo '</span></p>';
	        }else{
                    echo "<td>";
                    echo '<p class=box-desc><span class=blue>';
                    echo '</span></p>';
		}//end if
	echo "</td>";
// Affiche le NOM de port du BAS
            } elseif ($i === 4) {
                echo "<td>" . substr($ports[$j+1]["ifAlias"], 0, 28) . "</td>";
// Affiche le NUMERO de port du BAS
            } elseif ($i === 5) {
                $ifName = $ports[$j+1]["ifName"];
                if (strpos($ifName, "ifc") === 0) {
                    $ifName = substr($ifName, 13);
                    $ifName = rtrim($ifName, ")");
	            $ifName = str_replace(" Port: ", "/", $ifName);
		    $port=$ports[$j+1];
	            $port['hostname'] = $device['hostname'];
                    echo '<td>' . generate_port_link($port, $ifName). "</td>";
        	}else{
		$port=$ports[$j+1];
	        $port['hostname'] = $device['hostname'];
                echo '<td>' . generate_port_link($port, $ports[$j+1]['ifName']). "</td>";
		}
            }
       } 
	echo "</tr>";
    }
    echo "</table>";
} // END IF UNIT

////////////
// UNIT 4 //
////////////
    echo "<br>";
    echo '<div style="text-align: center; width: 100%; background-color: rgb(0, 138, 12);">';
    echo '<span style="color: white; font-weight: bold; font-size: 20px;">';
    echo "Number of 'Port' occurrences in 'Unit 4': " . $unit4PortCount . "<br>";
    echo '</div>';
if ($unit4PortCount != 0 ){ // START IF UNIT
    $j4 = $j;
    echo "<table class='tg'>";
    for ($i = 0; $i < 6; $i++) {
        echo "<tr>";
        for ($j = $j4 ; $j < $unit3PortCount+$j4; $j=$j+2) {
// HAUT UNIT=4 //
// Affiche le NUMERO de port du HAUT
            if ($i === 0) { 
                $ifName = $ports[$j]["ifName"];
                if (strpos($ifName, "ifc") === 0) {
                    $ifName = substr($ifName, 13);
                    $ifName = rtrim($ifName, ")");
	            $ifName = str_replace(" Port: ", "/", $ifName);
		    $port=$ports[$j];
	            $port['hostname'] = $device['hostname'];
                    echo '<td>' . generate_port_link($port, $ifName). "</td>";
        	}else{
		$port=$ports[$j];
	        $port['hostname'] = $device['hostname'];
                echo '<td>' . generate_port_link($port, $ports[$j]['ifName']). "</td>";
		}
// Affiche le NOM du port du HAUT
            } elseif ($i === 1) {
                echo "<td><p class=box-desc>" . substr($ports[$j]["ifAlias"], 0, 28) . "</p></td>";
// Affiche le VLAN du port du HAUT
            } elseif ($i === 2) {
	$vlans = dbFetchColumn(
	    'SELECT vlan FROM `ports_vlans` AS PV, vlans AS V ' .
	    'WHERE PV.`port_id`=? AND PV.`device_id`=? AND V.`vlan_vlan`=PV.vlan AND V.device_id = PV.device_id',
	    [$ports[$j]['port_id'], $device['device_id']]
	);
	$vlan_count = count($vlans);
#	echo "<td>";
//Couleur du port
	$vlan = implode(', ', $vlans);
	if (preg_match('/3[0-9][0-9][0-9]/', $vlan)) { //Vlan fx-etu 
	$vlanColor = 'rgb(255, 149, 14)';
	}elseif (preg_match('/4[6-8]/', $vlan) || $vlan == 17) { // Vlan mgmt
	$vlanColor = 'rgb(230, 255, 0)';
	}elseif (preg_match('/1[0-2][0-9][0-9]/', $vlan)) { // Vlan fx-per 
	$vlanColor = 'rgb(71, 184, 184)';
	}elseif (preg_match('/4[2]/', $vlan)) { // Vlan corefipe 
	$vlanColor = 'rgb(71, 184, 184)';
	}elseif (preg_match('/4[4]/', $vlan) || preg_match('/1[6]/',$vlan)) { // Vlan mfp, imp
	$vlanColor = 'rgb(208, 38, 251)';
	}elseif (preg_match('/2[6]/', $vlan) || preg_match('/1[5]/', $vlan)) { // Vlan gtb, video
	$vlanColor = 'rgb(255, 71, 114)';
	}elseif (preg_match('/1[0]/', $vlan)) { // Vlan mass 
	$vlanColor = 'rgb(204, 255, 255)';
	}elseif (preg_match('/3[6]/', $vlan)) { // Vlan vdpj 
	$vlanColor = 'rgb(239, 146, 148)';
	}elseif (preg_match('/2[4]/', $vlan)) { // Vlan tel seul 
	$vlanColor = 'rgb(255, 102, 51)';
	}else{
	$vlanColor = 'rgb(255, 255, 255)';
	}
	if (preg_match('/2[4]/', $vlan)) { // Vlan tel + vlan x
	$vlanColorTel = 'rgb(255, 102, 51) 50%';
	}else{
	$vlanColorTel = 'rgb(255, 255, 255) 100%';
	}
        if ( strpos($ports[$j]["ifAlias"], "uplink") !== false || strpos($ports[$j]["ifAlias"], "downlink") !== false ) { // Port Uplink/Downlink
	$vlanColor = 'rgb(153, 153, 255)';
	$vlanColorTel = 'rgb(153, 153, 255) 100%';
	}
// Verifie si le port est untagged
	$nativevlans = dbFetchRows(
	    'SELECT vlan,untagged FROM `ports_vlans` AS PV, vlans AS V ' .
	    'WHERE PV.`port_id`=? AND PV.`device_id`=? AND V.`vlan_vlan`=PV.vlan AND V.device_id = PV.device_id',
	    [$ports[$j]['port_id'], $device['device_id']]
	);
	$untaggedvlan = null;
	foreach ($nativevlans as $row){
		if ($row['untagged'] === 1){
		$untaggedvlan = "<b>(U: ".$row['vlan'].")</b>";
		break;
		}
	}
//Affiche le port
		if ($vlan_count > 2) {
		    echo "<td style='background: linear-gradient(315deg, $vlanColor 50%, $vlanColorTel)'>";
		    echo '<p class=box-desc><span class=purple><a href="';
		    echo \LibreNMS\Util\Url::deviceUrl((int) $device['device_id'], ['tab' => 'vlans']);
		    echo '" title="';
		    echo implode(', ', $vlans);
		    echo '">VLANs: ';
		    echo $vlan_count. "<br>";
		    echo $untaggedvlan;
		    echo '</a></span></p>';
		}elseif ($vlan_count == 2) {
		    echo "<td style='background: linear-gradient(315deg, $vlanColor 50%, $vlanColorTel)'>";
		    echo '<p class=box-desc><span class=blue>';
		    echo 'VLANs: ';
		    echo implode(', ', $vlans). "<br>";
		    echo $untaggedvlan;
		    echo '</a></span></p>';
		} elseif ($vlan_count == 1 || $ports[$j]['ifVlan']) {
		    echo "<td style='background: linear-gradient(315deg, $vlanColor 100%, $vlanColorTel)'>";
		    #echo '<p class=box-desc><span class=blue>';
		    echo 'VLAN: ';
		    echo implode(', ', $vlans). "<br>";
		    echo $untaggedvlan;
		    #echo $vlan_count;
		    echo '</span></p>';
		}else{
	            echo "<td>";
		    echo '<p class=box-desc><span class=blue>';
		    echo '</span></p>';
		}//end if
	echo "</td>";
// BAS UNIT=4 //
// Affiche le VLAN de port du BAS 
            } elseif ($i === 3) {
	$vlans = dbFetchColumn(
	    'SELECT vlan FROM `ports_vlans` AS PV, vlans AS V ' .
	    'WHERE PV.`port_id`=? AND PV.`device_id`=? AND V.`vlan_vlan`=PV.vlan AND V.device_id = PV.device_id',
	    [$ports[$j+1]['port_id'], $device['device_id']]
	);
	$vlan_count = count($vlans);
#	echo "<td>";
//Couleur du port
	$vlan = implode(', ', $vlans);
	if (preg_match('/3[0-9][0-9][0-9]/', $vlan)) { //Vlan fx-etu 
	$vlanColor = 'rgb(255, 149, 14)';
	}elseif (preg_match('/4[6-8]/', $vlan) || $vlan == 17) { // Vlan mgmt
	$vlanColor = 'rgb(230, 255, 0)';
	}elseif (preg_match('/1[0-2][0-9][0-9]/', $vlan)) { // Vlan fx-per 
	$vlanColor = 'rgb(71, 184, 184)';
	}elseif (preg_match('/4[2]/', $vlan)) { // Vlan corefipe 
	$vlanColor = 'rgb(71, 184, 184)';
	}elseif (preg_match('/4[4]/', $vlan) || preg_match('/1[6]/',$vlan)) { // Vlan mfp, imp
	$vlanColor = 'rgb(208, 38, 251)';
	}elseif (preg_match('/2[6]/', $vlan) || preg_match('/1[5]/', $vlan)) { // Vlan gtb, video
	$vlanColor = 'rgb(255, 71, 114)';
	}elseif (preg_match('/1[0]/', $vlan)) { // Vlan mass 
	$vlanColor = 'rgb(204, 255, 255)';
	}elseif (preg_match('/3[6]/', $vlan)) { // Vlan vdpj 
	$vlanColor = 'rgb(239, 146, 148)';
	}elseif (preg_match('/2[4]/', $vlan)) { // Vlan tel seul 
	$vlanColor = 'rgb(255, 102, 51)';
	}else{
	$vlanColor = 'rgb(255, 255, 255)';
	}
	if (preg_match('/2[4]/', $vlan)) { // Vlan tel + vlan x
	$vlanColorTel = 'rgb(255, 102, 51) 50%';
	}else{
	$vlanColorTel = 'rgb(255, 255, 255) 100%';
	}
        if ( strpos($ports[$j+1]["ifAlias"], "link") !== false || strpos($ports[$j+1]["ifAlias"], "link") !== false ) { // Port Uplink/Downlink
	$vlanColor = 'rgb(153, 153, 255)';
	$vlanColorTel = 'rgb(153, 153, 255) 100%';
	}	
// Verifie si le port est untagged
	$nativevlans = dbFetchRows(
	    'SELECT vlan,untagged FROM `ports_vlans` AS PV, vlans AS V ' .
	    'WHERE PV.`port_id`=? AND PV.`device_id`=? AND V.`vlan_vlan`=PV.vlan AND V.device_id = PV.device_id',
	    [$ports[$j+1]['port_id'], $device['device_id']]
	);
	$untaggedvlan = null;
	foreach ($nativevlans as $row){
		if ($row['untagged'] === 1){
		$untaggedvlan = "<b>(U: ".$row['vlan'].")</b>";
		break;
		}
	}
//Affiche le port
		if ($vlan_count > 2) {
		    echo "<td style='background: linear-gradient(315deg, $vlanColor 50%, $vlanColorTel)'>";
		    echo '<p class=box-desc><span class=purple><a href="';
		    echo \LibreNMS\Util\Url::deviceUrl((int) $device['device_id'], ['tab' => 'vlans']);
		    echo '" title="';
		    echo implode(', ', $vlans);
		    echo '">VLANs: ';
		    echo $vlan_count. "<br>";
		    echo $untaggedvlan;
		    echo '</a></span></p>';
		}elseif ($vlan_count == 2) {
		    echo "<td style='background: linear-gradient(315deg, $vlanColor 50%, $vlanColorTel)'>";
		    echo '<p class=box-desc><span class=blue>';
		    echo 'VLANs: ';
		    echo implode(', ', $vlans). "<br>";
		    echo $untaggedvlan;
		    echo '</a></span></p>';
		} elseif ($vlan_count == 1 || $ports[$j+1]['ifVlan']) {
		    echo "<td style='background: linear-gradient(315deg, $vlanColor 100%, $vlanColorTel)'>";
		    echo '<p class=box-desc><span class=blue>';
		    echo 'VLAN: ';
		    echo implode(', ', $vlans). "<br>";
		    echo $untaggedvlan;
		    #echo $vlan_count;
		    echo '</span></p>';
	        }else{
                    echo "<td>";
                    echo '<p class=box-desc><span class=blue>';
                    echo '</span></p>';
		}//end if
	echo "</td>";
// Affiche le NOM de port du BAS
            } elseif ($i === 4) {
                echo "<td>" . substr($ports[$j+1]["ifAlias"], 0, 28) . "</td>";
// Affiche le NUMERO de port du BAS
            } elseif ($i === 5) {
                $ifName = $ports[$j+1]["ifName"];
                if (strpos($ifName, "ifc") === 0) {
                    $ifName = substr($ifName, 13);
                    $ifName = rtrim($ifName, ")");
	            $ifName = str_replace(" Port: ", "/", $ifName);
		    $port=$ports[$j+1];
	            $port['hostname'] = $device['hostname'];
                    echo '<td>' . generate_port_link($port, $ifName). "</td>";
        	}else{
		$port=$ports[$j+1];
	        $port['hostname'] = $device['hostname'];
                echo '<td>' . generate_port_link($port, $ports[$j+1]['ifName']). "</td>";
		}
            }
       } 
	echo "</tr>";
    }
    echo "</table>";
} // END IF UNIT

} else {

//////////////////
// UNIT=0 ALONE //
/////////////////
    echo "<br>";
    echo '<div style="text-align: center; width: 100%; background-color: rgb(0, 138, 12);">';
    echo '<span style="color: white; font-weight: bold; font-size: 20px;">';
    echo "Number of 'Port' occurrences in 'Unit 0': " . $unit0PortCount . "<br>";
    echo '</div>';
    echo "<table class='tg'>";
    for ($i = 0; $i < 6; $i++) {
        echo "<tr>";
        for ($j = 0; $j < $unit0PortCount; $j=$j+2) {
// HAUT UNIT=0 //
// Affiche le NUMERO de port du HAUT
            if ($i === 0) { 
                $ifName = $ports[$j]["ifName"];
                if (strpos($ifName, "ifc") === 0) {
                    $ifName = substr($ifName, 12);
                    $ifName = rtrim($ifName, ")");
	            $ifName = str_replace(" Port: ", "/", $ifName);
		    $port=$ports[$j];
	            $port['hostname'] = $device['hostname'];
                    echo '<td>' . generate_port_link($port, $ifName). "</td>";
        	}else{
		$port=$ports[$j];
	        $port['hostname'] = $device['hostname'];
                echo '<td>' . generate_port_link($port, $ports[$j]['ifName']). "</td>";
		}
// Affiche le NOM du port du HAUT
            } elseif ($i === 1) {
                echo "<td><p class=box-desc>" . substr($ports[$j]["ifAlias"], 0, 28) . "</p></td>";
// Affiche le VLAN du port du HAUT
            } elseif ($i === 2) {
	$vlans = dbFetchColumn(
	    'SELECT vlan FROM `ports_vlans` AS PV, vlans AS V ' .
	    'WHERE PV.`port_id`=? AND PV.`device_id`=? AND V.`vlan_vlan`=PV.vlan AND V.device_id = PV.device_id',
	    [$ports[$j]['port_id'], $device['device_id']]
	);
	$vlan_count = count($vlans);
#	echo "<td>";
//Couleur du port
	$vlan = implode(', ', $vlans);
	if (preg_match('/3[0-9][0-9][0-9]/', $vlan)) { //Vlan fx-etu 
	$vlanColor = 'rgb(255, 149, 14)';
	}elseif (preg_match('/4[6-8]/', $vlan) || $vlan == 17) { // Vlan mgmt
	$vlanColor = 'rgb(230, 255, 0)';
	}elseif (preg_match('/1[0-2][0-9][0-9]/', $vlan)) { // Vlan fx-per 
	$vlanColor = 'rgb(71, 184, 184)';
	}elseif (preg_match('/4[2]/', $vlan)) { // Vlan corefipe 
	$vlanColor = 'rgb(71, 184, 184)';
	}elseif (preg_match('/4[4]/', $vlan) || preg_match('/1[6]/',$vlan)) { // Vlan mfp, imp
	$vlanColor = 'rgb(208, 38, 251)';
	}elseif (preg_match('/2[6]/', $vlan) || preg_match('/1[5]/', $vlan)) { // Vlan gtb, video
	$vlanColor = 'rgb(255, 71, 114)';
	}elseif (preg_match('/1[0]/', $vlan)) { // Vlan mass 
	$vlanColor = 'rgb(204, 255, 255)';
	}elseif (preg_match('/3[6]/', $vlan)) { // Vlan vdpj 
	$vlanColor = 'rgb(239, 146, 148)';
	}elseif (preg_match('/2[4]/', $vlan)) { // Vlan tel seul 
	$vlanColor = 'rgb(255, 102, 51)';
	}else{
	$vlanColor = 'rgb(255, 255, 255)';
	}
	if (preg_match('/2[4]/', $vlan)) { // Vlan tel + vlan x
	$vlanColorTel = 'rgb(255, 102, 51) 50%';
	}else{
	$vlanColorTel = 'rgb(255, 255, 255) 100%';
	}
        if ( strpos($ports[$j]["ifAlias"], "link") !== false || strpos($ports[$j]["ifAlias"], "link") !== false ) { // Port Uplink/Downlink
	$vlanColor = 'rgb(153, 153, 255)';
	$vlanColorTel = 'rgb(153, 153, 255) 100%';
	}	
// Verifie si le port est untagged
	$nativevlans = dbFetchRows(
	    'SELECT vlan,untagged FROM `ports_vlans` AS PV, vlans AS V ' .
	    'WHERE PV.`port_id`=? AND PV.`device_id`=? AND V.`vlan_vlan`=PV.vlan AND V.device_id = PV.device_id',
	    [$ports[$j]['port_id'], $device['device_id']]
	);
	$untaggedvlan = null;
	foreach ($nativevlans as $row){
		if ($row['untagged'] === 1){
		$untaggedvlan = "<b>(U: ".$row['vlan'].")</b>";
		break;
		}
	}
//Affiche le port
		if ($vlan_count > 2) {
		    echo "<td style='background: linear-gradient(315deg, $vlanColor 50%, $vlanColorTel)'>";
		    echo '<p class=box-desc><span class=purple><a href="';
		    echo \LibreNMS\Util\Url::deviceUrl((int) $device['device_id'], ['tab' => 'vlans']);
		    echo '" title="';
		    echo implode(', ', $vlans);
		    echo '">VLANs: ';
		    echo $vlan_count. "<br>";
		    echo $untaggedvlan;
		    echo '</a></span></p>';
		}elseif ($vlan_count == 2) {
		    echo "<td style='background: linear-gradient(315deg, $vlanColor 50%, $vlanColorTel)'>";
		    echo '<p class=box-desc><span class=blue>';
		    echo 'VLANs: ';
		    echo implode(', ', $vlans). "<br>";
		    echo $untaggedvlan;
		    echo '</a></span></p>';
		} elseif ($vlan_count == 1 || $ports[$j]['ifVlan']) {
		    echo "<td style='background: linear-gradient(315deg, $vlanColor 100%, $vlanColorTel)'>";
		    echo '<p class=box-desc><span class=blue>';
		    echo 'VLAN: ';
		    echo implode(', ', $vlans). "<br>";
		    echo $untaggedvlan;
		    echo '</span></p>';
	        }else{
                    echo "<td>";
                    echo '<p class=box-desc><span class=blue>';
                    echo '</span></p>';
		}//end if
	echo "</td>";
// BAS UNIT=0 //
// Affiche le VLAN de port du BAS 
            } elseif ($i === 3) {
	$vlans = dbFetchColumn(
	    'SELECT vlan FROM `ports_vlans` AS PV, vlans AS V ' .
	    'WHERE PV.`port_id`=? AND PV.`device_id`=? AND V.`vlan_vlan`=PV.vlan AND V.device_id = PV.device_id',
	    [$ports[$j+1]['port_id'], $device['device_id']]
	);
	$vlan_count = count($vlans);
#	echo "<td>";
//Couleur du port
	$vlan = implode(', ', $vlans);
	if (preg_match('/3[0-9][0-9][0-9]/', $vlan)) { //Vlan fx-etu 
	$vlanColor = 'rgb(255, 149, 14)';
	}elseif (preg_match('/4[6-8]/', $vlan) || $vlan == 17) { // Vlan mgmt
	$vlanColor = 'rgb(230, 255, 0)';
	}elseif (preg_match('/1[0-2][0-9][0-9]/', $vlan)) { // Vlan fx-per 
	$vlanColor = 'rgb(71, 184, 184)';
	}elseif (preg_match('/4[2]/', $vlan)) { // Vlan corefipe 
	$vlanColor = 'rgb(71, 184, 184)';
	}elseif (preg_match('/4[4]/', $vlan) || preg_match('/1[6]/',$vlan)) { // Vlan mfp, imp
	$vlanColor = 'rgb(208, 38, 251)';
	}elseif (preg_match('/2[6]/', $vlan) || preg_match('/1[5]/', $vlan)) { // Vlan gtb, video
	$vlanColor = 'rgb(255, 71, 114)';
	}elseif (preg_match('/1[0]/', $vlan)) { // Vlan mass 
	$vlanColor = 'rgb(204, 255, 255)';
	}elseif (preg_match('/3[6]/', $vlan)) { // Vlan vdpj 
	$vlanColor = 'rgb(239, 146, 148)';
	}elseif (preg_match('/2[4]/', $vlan)) { // Vlan tel seul 
	$vlanColor = 'rgb(255, 102, 51)';
	}else{
	$vlanColor = 'rgb(255, 255, 255)';
	}
	if (preg_match('/2[4]/', $vlan)) { // Vlan tel + vlan x
	$vlanColorTel = 'rgb(255, 102, 51) 50%';
	}else{
	$vlanColorTel = 'rgb(255, 255, 255) 100%';
	}
        if ( strpos($ports[$j+1]["ifAlias"], "link") !== false || strpos($ports[$j+1]["ifAlias"], "link") !== false ) { // Port Uplink/Downlink
	$vlanColor = 'rgb(153, 153, 255)';
	$vlanColorTel = 'rgb(153, 153, 255) 100%';
	}	
// Verifie si le port est untagged
	$nativevlans = dbFetchRows(
	    'SELECT vlan,untagged FROM `ports_vlans` AS PV, vlans AS V ' .
	    'WHERE PV.`port_id`=? AND PV.`device_id`=? AND V.`vlan_vlan`=PV.vlan AND V.device_id = PV.device_id',
	    [$ports[$j+1]['port_id'], $device['device_id']]
	);
	$untaggedvlan = null;
	foreach ($nativevlans as $row){
		if ($row['untagged'] === 1){
		$untaggedvlan = "<b>(U: ".$row['vlan'].")</b>";
		break;
		}
	}
//Affiche le port
		if ($vlan_count > 2) {
		    echo "<td style='background: linear-gradient(315deg, $vlanColor 50%, $vlanColorTel)'>";
		    echo '<p class=box-desc><span class=purple><a href="';
		    echo \LibreNMS\Util\Url::deviceUrl((int) $device['device_id'], ['tab' => 'vlans']);
		    echo '" title="';
		    echo implode(', ', $vlans);
		    echo '">VLANs: ';
		    echo $vlan_count. "<br>";
		    echo $untaggedvlan;
		    echo '</a></span></p>';
		}elseif ($vlan_count == 2) {
		    echo "<td style='background: linear-gradient(315deg, $vlanColor 50%, $vlanColorTel)'>";
		    echo '<p class=box-desc><span class=blue>';
		    echo 'VLANs: ';
		    echo implode(', ', $vlans). "<br>";
		    echo $untaggedvlan;
		    echo '</a></span></p>';
		} elseif ($vlan_count == 1 || $ports[$j+1]['ifVlan']) {
		    echo "<td style='background: linear-gradient(315deg, $vlanColor 100%, $vlanColorTel)'>";
		    echo '<p class=box-desc><span class=blue>';
		    echo 'VLAN: ';
		    echo implode(', ', $vlans). "<br>";
		    echo $untaggedvlan;
		    echo '</span></p>';
	        }else{
                    echo "<td>";
                    echo '<p class=box-desc><span class=blue>';
                    echo '</span></p>';
		}//end if
	echo "</td>";
// Affiche le NOM de port du BAS
            } elseif ($i === 4) {
                echo "<td>" . substr($ports[$j+1]["ifAlias"], 0, 28) . "</td>";
// Affiche le NUMERO de port du BAS
            } elseif ($i === 5) {
        	$port=$ports[$j+1];
	        $port['hostname'] = $device['hostname'];
                echo '<td>' . generate_port_link($port, $ports[$j+1]['ifName']). "</td>";
	    }
        }
        echo "</tr>";
    }
}
?>
