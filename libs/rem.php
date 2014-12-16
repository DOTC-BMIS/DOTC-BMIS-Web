<?php
require_once("rem_lib.php");
if($_GET['id'] != "")
{
  $id = $_GET['id'];
}
else
{   
  $id = 0;
} 

  $sql = "
  SELECT remarks 
  FROM dotc_pub_unit
  WHERE id = ".$id;
  $rs = mysql_query($sql);
  $r = mysql_fetch_array($rs);
?>
<html>
<body>
<table class='dotc_table_data' cellpadding=0 cellspacing=0 width='100%'>
  <tr valign='top'>
    <td>
      <div id="remarks"></div>
    </td>         
  </tr>
  <tr valign='top'>
    <td>
      <textarea cols="80" rows="3" id="txtremarks"><?php echo stripslashes($r['remarks']); ?></textarea>
    </td>         
  </tr>
  <tr valign='top'>
    <td style="text-align: right">
      <a href='javascript: void(0)' class='dotc_test_cp_no' onClick="getRemarks(document.getElementById('txtremarks').value,<?php echo $id; ?>);"><img src='images/save or write-h40.png' /></a>
    </td>
  </tr>
</table>  
<script>
function getRemarks(remarks,id)
{
//alert(id);
  
  var xmlhttp;
  if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
    xmlhttp=new XMLHttpRequest();
  }
  else
  {// code for IE6, IE5
    xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
  xmlhttp.onreadystatechange=function()
  {
    console.log(xmlhttp.readyState + " : " + xmlhttp.status);
    if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
      document.getElementById("remarks").innerHTML=xmlhttp.responseText;
    }
    if (xmlhttp.readyState!=4 && xmlhttp.status!=200)
    {
      document.getElementById("remarks").innerHTML= '<div id="remarks"></div>';
    }
  }
  xmlhttp.open("GET","libs/rem_loader.php?act=1&remarks="+remarks+"&id="+id,true);
  xmlhttp.send(); 
}
</script>
</body>
</html>