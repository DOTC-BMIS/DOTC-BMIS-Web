<?php
require_once( 'MPDF57/mpdf.php' );
date_default_timezone_set('Asia/Manila');
$date = date('m/d/Y');
//$starttime  = gmdate("Y-m-d H:i:s",mktime(16, 0, 0, 10  , 10, 2014));
//$endtime = gmdate("Y-m-d H:i:s",mktime(18, 00, 00, 10  , 10, 2014));
if((strcmp($_GET['start'],''))!=0&&(strcmp('',$_GET['end']))!=0){	
	$start = strtotime($_GET['start']);
	$end = strtotime($_GET['end']);
	$starttime  = date("Y-m-d H:i:s",$start);
	$endtime = date("Y-m-d H:i:s",$end);
}
else{
	$starttime  = gmdate("Y-m-d H:i:s",mktime(0, date("i"), date("s"), date("m")  , date("d"), date("Y")));
	$endtime = gmdate("Y-m-d H:i:s",mktime(0, date("i"), date("s"), date("m")  , date("d")+1, date("Y")));
}
$stylesheet = file_get_contents('style.css');
$html = "<div><img id = 'image' src='LTFRB.jpg' width = '60' height='60'><img src='DOTC.jpg' id='image2' width = '60' height='60'>";
$html .="<div id='header'><b>BUS MANAGEMENT INFORMATION SYSTEM SPEEDING REPORT</b><br>DOTC-LTFRB</div></div>";
$html .= "<br><br><br><div>Date: <u>";
$html .= $date;
$html .="</u></div><br><br><br>";
$html .= "<div><table><tr><td><center>Date</center></td><td><center>Start Time</center></td><td><center>End Time</center></td><td><center>Route</center></td><td><center>Case Number</center></td><td><center>Operator</center></td><td><center>Plate Number</center></td><td><center>Average Speed</center></td><td><center>Start Road</center></td><td><center>End Road</center></td></tr>";
$list = array();

$db = new PDO('mysql:host=localhost;dbname=dotc_trip_logger;charset=utf8', 'root', 'E@c0mM2o13',array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
$sql = 'SELECT DISTINCT device_timestamp, plate_no, phone_speed, latitude, longitude FROM trip_logs WHERE phone_speed > 60 AND device_timestamp BETWEEN :start AND :end ORDER BY plate_no , device_timestamp';
$stmt = $db->prepare($sql);
$stmt->execute(array(':start'=>$starttime,':end'=>$endtime));
foreach($stmt as $row) {
	if(sizeOf($list)==0){
		$list[] = array($row); 
	}
	else if(!(strcmp($list[0][0]['plate_no'],$row['plate_no'])) && (abs(strtotime($list[sizeOf($list)-1][0]['device_timestamp'])-strtotime($row['device_timestamp']))<6)){
		$list[] = array($row);
	}
	else{
		if(sizeOf($list)>4){
			$querydate = date("m/d/Y",strtotime($list[0][0]['device_timestamp']));
			$querystarttime = date("H:i:s",strtotime($list[0][0]['device_timestamp']));
			$queryendtime = date("H:i:s",strtotime($list[sizeOf($list)-1][0]['device_timestamp']));
			$html .= '<tr><td>';
			$html .= $querydate;
			$html .='</td>';
			$html .= '<td>';
			$html .= ' '.$querystarttime;
			$html .='</td>';
			$html .= '<td>';
			$html .= ' '.$queryendtime;
			$html .='</td>';
			
			$db2 = new PDO('mysql:host=localhost;dbname=dotc_bms_mysql;charset=utf8', 'root', 'E@c0mM2o13',array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
			$sql2 = 'SELECT name, case_no, gtfs_route_name FROM dotc_unit_frnc WHERE plate_no = :plate';
			$stmt2 = $db2->prepare($sql2);
			$stmt2->execute(array(':plate'=>$list[0][0]['plate_no']));
			foreach($stmt2 as $row2){
				$html .= '<td>';
				$html .= $row2['gtfs_route_name'];
				$html .='</td>';
				$html .= '<td>';
				$html .=$row2['case_no'];
				$html .='</td>';
				$html .= '<td>';
				$html .=$row2['name'];
				$html .='</td>';
			}
			$html .= '<td>';
			$html .= $list[0][0]['plate_no'];
			$html .='</td>';
			
			$avgovrspd = 0;
			foreach($list as $speed){
				$avgovrspd = $avgovrspd + $speed[0]['phone_speed'];
			}
			$avgovrspd = $avgovrspd/sizeOf($list);
			
			$html .='<td>';
			$html .= round($avgovrspd,2);
			$html .='</td>';
			
			$url = "http://maps.googleapis.com/maps/api/geocode/json?latlng=".$list[0][0]['latitude'].",".$list[0][0]['longitude'];
			$data = @file_get_contents($url);
			$jsondata = json_decode($data,true);
			foreach($jsondata['results']['0']['address_components'] as $element){
				if (in_array("route", $element["types"])) {
					$sstreet = $element["long_name"];
				}
			}
			$url = "http://maps.googleapis.com/maps/api/geocode/json?latlng=".$list[sizeOf($list)-1][0]['latitude'].",".$list[sizeOf($list)-1][0]['longitude'];
			$data = @file_get_contents($url);
			$jsondata = json_decode($data,true);
			foreach($jsondata['results']['0']['address_components'] as $element){
				if (in_array("route", $element["types"])) {
					$estreet = $element["long_name"];
				}
			}
			/*$url = "http://api.geonames.org/findNearbyStreetsOSMJSON?lat=".$row['latitude']."&lng=".$row['longitude']."&username=demo";
			$data = @file_get_contents($url);
			$jsondata = json_decode($data,true);
			$street = $jsondata['streetSegment'][0]["name"];*/
			
			$html .= '<td>';
			$html .= $sstreet;
			$html .= '</td>';
			$html .= '<td>';
			$html .= $estreet;
			$html .='</td></tr>';
			}
		$list = array();
		$list[] = array($row);
	}
}
$html .= "</table></div>";

$mpdf = new mPDF();
$mpdf->WriteHTMl($stylesheet,1);
$mpdf->WriteHTML($html,2);
$mpdf->Output('../files/pdf/SpeedingReport'.date('m').'-'.date('d').'-'.date('Y').'.pdf',"I");
exit;
?>