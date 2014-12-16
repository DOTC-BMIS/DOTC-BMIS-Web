<?php
require_once( 'MPDF57/mpdf.php' );
date_default_timezone_set('Asia/Manila');
$date = date('m/d/Y');
if((strcmp($_GET['start'],''))!=0&&(strcmp('',$_GET['end']))!=0){	
	$start = strtotime($_GET['start']);
	$end = strtotime($_GET['end']);
	$starttime  = date("Y-m-d H:i:s",$start);
	$endtime = date("Y-m-d H:i:s",$end);
}
else{
	$starttime  = date("Y-m-d H:i:s",mktime(-1, date("i"), date("s"), date("m")  , date("d"), date("Y")));
	$endtime = date("Y-m-d H:i:s",mktime(0, date("i"), date("s"), date("m")  , date("d"), date("Y")));
}
$stylesheet = file_get_contents('style.css');

$html = "<div><img id = 'image' src='LTFRB.jpg' width = '60' height='60'><img src='DOTC.jpg' id='image2' width = '60' height='60'>";
$html .="<div id='header'><b>BUS MANAGEMENT INFORMATION SYSTEM COLORUM REPORT</b><br>DOTC-LTFRB</div></div>";
$html .= "<br><br><br><div>Date: <u>";
$html .= $date;
$html .="</u></div><br><br><br>";
$html .= "<div><table><tr><td><center>Date</center></td><td><center>Time</center></td><td><center>Plate Number</center></td><td><center>Road</center></td><td><center>Geo Location</center></td></tr>";

$db = new PDO('mysql:host=localhost;dbname=dotc_trip_logger;charset=utf8', 'root', 'E@c0mM2o13',array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
$sql = 'SELECT DISTINCT trip_name, id FROM trips WHERE possible_colorum > 0 AND date_added BETWEEN :start AND :end ORDER BY trip_name ASC';
$stmt = $db->prepare($sql);
$stmt->execute(array(':start'=>$starttime,':end'=>$endtime));
foreach($stmt as $row) {
	
	$sql2 = 'SELECT * FROM trip_logs WHERE trip_id = :tripid ORDER BY device_timestamp LIMIT 1';
	$stmt2 = $db->prepare($sql2);
	$stmt2->execute(array(':tripid'=>$row['id']));
	foreach($stmt2 as $row2){	
		$querydate = date("m/d/Y",strtotime($row2['device_timestamp']));
		$querytime = date("H:i:s",strtotime($row2['device_timestamp']));
		$html .= '<tr><td>';
		$html .= $querydate;
		$html .='</td>';
		$html .= '<td>';
		$html .= $querytime;
		$html .='</td>';
		$html .= '<td><a href="http://c3.eacomm.com:3001/colorumreport?plate='.$row2['plate_no'].'&timerange='.date("Y-m-d", strtotime($querydate)).' '.$querytime.' to '.date("Y-m-d", strtotime($querydate)).' 23:59:59" target="_blank">';
		$html .= $row2['plate_no'];
		$html .='</a></td>';
			
		$url = "http://maps.googleapis.com/maps/api/geocode/json?latlng=".$row2['latitude'].",".$row2['longitude'];
		$data = @file_get_contents($url);
		$jsondata = json_decode($data,true);
		foreach($jsondata['results']['0']['address_components'] as $element){
			 if (in_array("route", $element["types"])) {
				$street = $element["long_name"];
			}
		}
				
		$html .= '<td>';
		$html .= $street;
		$html .='</td>';
		$html .='<td>';
		$html .=$row2['latitude'].','.$row2['longitude'];
		$html .='</td></tr>';
	}
}

$html .= "</table></div>";

$mpdf = new mPDF();
$mpdf->WriteHTMl($stylesheet,1);
$mpdf->WriteHTML($html,2);
$mpdf->Output('ColorumReport.pdf',"I");
exit;
?>