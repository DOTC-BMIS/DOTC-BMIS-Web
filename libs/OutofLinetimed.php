<?php
require_once( 'MPDF57/mpdf.php' );
date_default_timezone_set('Asia/Manila');
$date = date('m/d/Y');
//$starttime  = gmdate("Y-m-d H:i:s",mktime(-date("H"), 0, 0, date("m")  , date("d"), date("Y")));
//$endtime = gmdate("Y-m-d H:i:s",mktime(-date("H")-1, 59, 59, date("m")  , date("d")+1, date("Y")));
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
$html .="<div id='header'><b>BUS MANAGEMENT INFORMATION SYSTEM OUT OF LINE REPORT</b><br>DOTC-LTFRB</div></div>";
$html .= "<br><br><br><div>Date: <u>";
$html .= $date;
$html .="</u></div><br><br><br>";
$html .= "<div><table><tr><td><center>Date</center></td><td><center>Time</center></td><td><center>Original Route</center></td><td><center>Operator</center></td><td><center>Plate Number</center></td><td><center>Road</center></td><td><center>GeoLocation</center></td></tr>";
$list = array();
$plates = array();

$db = new PDO('mysql:host=localhost;dbname=dotc_trip_logger;charset=utf8', 'root', 'E@c0mM2o13',array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
$sql = 'SELECT DISTINCT trip_name FROM trips WHERE date_added BETWEEN :start AND :end ORDER BY trip_name';
$stmt = $db->prepare($sql);
$db2 = new PDO('mysql:host=localhost;dbname=dotc_bms_mysql;charset=utf8', 'root', 'E@c0mM2o13',array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
$sql2 = 'SELECT name, gtfs_route_name, gtfs_route_id FROM dotc_unit_frnc WHERE plate_no = :plate';
$stmt2 = $db2->prepare($sql2);
$db3 = new PDO('mysql:host=localhost;dbname=dotc_gtfs_data;charset=utf8', 'root', 'E@c0mM2o13',array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
$sql4 = 'SELECT COUNT(*) AS counter FROM(( SELECT ( 6371 * acos( cos( radians( :latitude ) ) * cos( radians( `shape_pt_lat` ) ) * cos( radians( `shape_pt_lon` ) - radians( :longitude ) ) + sin( radians( :latitude2 ) ) * sin( radians( `shape_pt_lat` ) ) ) ) AS distance FROM `route_shapes_view` WHERE `route_id` = :route_id HAVING distance < 0.1 ) ) T';
$stmt4 = $db3->prepare($sql4);			
	
$stmt->execute(array(':start'=>$starttime,':end'=>$endtime));
foreach($stmt as $row) {
	$plates[] = $row['trip_name'];
}
foreach($plates as $row){
	$stmt2->execute(array(':plate'=>$row));
	foreach($stmt2 as $row2){
		$bms = array($row2);
	}
	$sql3 = 'SELECT latitude, longitude FROM trip_logs WHERE plate_no = :plate AND device_timestamp BETWEEN :start AND :end ORDER BY device_timestamp';
	$stmt3 = $db->prepare($sql3);
	$stmt3->execute(array(':plate'=>$row,':start'=>$starttime,':end'=>$endtime));
	$count = 0;
	while($count>=0&&($row2 = $stmt3->fetch())){
		$stmt4->execute(array(':latitude'=>$row2['latitude'],':longitude'=>$row2['longitude'],':latitude2'=>$row2['latitude'],':route_id'=>$bms[0]['gtfs_route_id']));
		foreach($stmt4 as $row3){
			$count =$row3['counter'];
			if($count<1){
				$list[]=$row2;
				$count=-1;
			}
		}
	}
	if($count==-1&&strcmp($list[0]['latitude'],'')!=0&&!empty($bms[0]['gtfs_route_id'])){
		$sql5 = 'SELECT date_added FROM trips WHERE trip_name = :plate AND date_added BETWEEN :start AND :end';
		$stmt5 = $db->prepare($sql5);
		$stmt5->execute(array(':plate'=>$row,':start'=>$starttime,':end'=>$endtime));
		$row3 = $stmt5->fetch();
		$querydate = date("m/d/Y",strtotime($row3['date_added']));
		$querytime = date("H:i:s",strtotime($row3['date_added']));
		$html .='<tr><td>';
		$html .=$querydate;
		$html .='</td>';
		$html .='<td>';
		$html .=$querytime;
		$html .='</td>';
		$html .='<td>';
		$html .=$bms[0]['gtfs_route_name'];
		$html .='</td>';
		$html .='<td>';
		$html .=$bms[0]['name'];
		$html .='</td>';
		$html .='<td><a href = "http://c3.eacomm.com:3001/OutOfLine?plate='.$row.'&timerange='.date("Y-m-d", strtotime($querydate)).' '.$querytime.' to '.date("Y-m-d", strtotime($querydate)).' 23:59:59" target="_blank">';
		$html .=$row;
		$html .='</a></td>';
		$url = "http://maps.googleapis.com/maps/api/geocode/json?latlng=".$list[0]['latitude'].",".$list[0]['longitude'];
		$data = @file_get_contents($url);
		$jsondata = json_decode($data,true);
		foreach($jsondata['results']['0']['address_components'] as $element){
			if (in_array("route", $element["types"])) {
				$sstreet = $element["long_name"];
			}
		}
		$html .='<td>';
		$html .=$sstreet;
		$html .='</td>';
		$html .='<td>';
		$html .=$list[0]['latitude'];
		$html .=' ';
		$html .=$list[0]['longitude'];
		$html .='</td></tr>';
	}
	$list = array();
}
$html .= "</table></div>";

$mpdf = new mPDF();
$mpdf->WriteHTMl($stylesheet,1);
$mpdf->WriteHTML($html,2);
$mpdf->Output('../files/pdf/OutofLine'.date('m').'-'.date('d').'-'.date('Y').'.pdf',"I");
exit;
?>