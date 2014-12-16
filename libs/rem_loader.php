<?php 
require_once("rem_lib.php");

$act = $_GET['act'];

if($act == 1)
{
  if($_GET['id'] != "")
  {
    $id = $_GET['id'];
  }
  else
  {   
    $id = 0;
  }
   
  if($_GET['remarks'] != "")
  {
    $remarks = $_GET['remarks'];
  }
  
  $sql = "
  UPDATE dotc_pub_unit
  SET remarks = '". addslashes($remarks) ."'
  WHERE id = ".$id;
  $rs = mysql_query($sql);
  if($rs && $id > 0)
  {
    echo '<div id="remarks"><b style="color: #888">Remarks was successfully updated.</b></div>';
  }
}
elseif($act == 2) //When click the EDIT button for franchise
{
  
  if($_GET['id'] != "")
  {
    $id = $_GET['id'];
  }
  else
  {   
    $id = 0;
  }
   
  if($_GET['remarks'] != "")
  {
    $remarks = $_GET['remarks'];
  }
  
  $sql = "
  SELECT 
  remarks
  FROM dotc_pub_frnc
  WHERE id = ".$id."
  LIMIT 1
  ";
  $rs = mysql_query($sql);
  $num_row = mysql_num_rows($rs);
  if($num_row > 0)
  {                   
    $r = mysql_fetch_array($rs);
    echo "<div id=\"remarks\"><textarea name=\"remarks\" id=\"txtremarks\">".$r['remarks']."</textarea></div>";
  }
  else
  {                   
    $r = mysql_fetch_array($rs);
    echo "<div id=\"remarks\"><textarea name=\"remarks\" id=\"txtremarks\"></textarea></div>";
  }
}  
elseif($act == 3) //When click the SAVE Changes button for franchise
{
  
  if($_GET['id'] != "")
  {
    $id = $_GET['id'];
  }
  else
  {   
    $id = 0;
  }
   
  if($_GET['case_no'] != "")
  {
    $case_no = $_GET['case_no'];
  }
  
  if($_GET['remarks'] != "")
  {
    $remarks = $_GET['remarks'];
  }
  
  $sql = "
  UPDATE dotc_pub_frnc
  SET remarks = '". addslashes($remarks) ."'
  WHERE id = ".$id;
  $rs = mysql_query($sql) or die("1 ".mysql_error());
  if($rs)
  { 
      $sql2 = "
      UPDATE dotc_pub_unit
      SET remarks = '". addslashes($remarks) ."'
      WHERE case_no = '".$case_no."'
      ";
      $rs2 = mysql_query($sql2) or die($sql2." ".mysql_error());
      echo  $sql2."<br>";
  }
}  
?>