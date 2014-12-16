<?php
require_once( 'libs/class.DOTC_BMS.php' );
$dotc = new DOTC_BMS;

if( !$dotc->checkSession() ){
	$dotc->redirect( 'login.php' );
} else{
	if( !$dotc->checkIfUserIsAllowedOnThisPage() ){
		$dotc->redirect( $dotc->firstUrl() );
	}
}

# $dotc->debug( $_SESSION['user_details'] );
?>
<html>
<head>
	<title>DOTC BMS - Main</title>
	<link href='styles/main.css' rel='StyleSheet' />
	<link rel='stylesheet' type='text/css' href='scripts/jquery-ui-1.10.3.custom/css/flick/jquery-ui-1.10.3.custom.min.css' />
	<script type='text/javascript' src='scripts/jquery-1.11.1.js'></script>
	<script type='text/javascript' src='scripts/jquery-ui-1.10.3.custom/js/jquery-ui-1.10.3.custom.min.js'></script>
	<script type='text/javascript' src='scripts/jquery-upload.js'></script>
	<script type='text/javascript' src='scripts/mustache.js/mustache.js'></script>
	<script type='text/javascript' src='scripts/handlebars.js'></script>
	<script type='text/javascript' src='scripts/handlebars.swag.js'></script>
	<script type='text/javascript' src='scripts/jQuery-slimScroll-1.1.0/jquery.slimscroll.min.js'></script>
	<script type='text/javascript' src='scripts/config.js'></script>
	<script type='text/javascript'>
		$(function(){
			var _page = {
				list : 1,
				search : 1
			};
			var getFranchise = false;
			var searchFranchise = false;
			
			function FETCHFRANCHISES( page ){
				if( getFranchise ){
					getFranchise.abort();
				}
			
				getFranchise = $.get(
					ServerConfig.BMS_Node + 'franchise_PAGINATE/' + page,
					function( data ){
						$( '#franchise_table' ).html( data );
						_page.list = +page;
						
						$( '.franchise_paginate' ).each(function(){
							$( this ).click(function(){
								if( _page.list != +$( this ).attr( 'data-page' ) ){
									FETCHFRANCHISES( $( this ).attr( 'data-page' ) );
								}
							});
						});
						
						EDITFRANCHISE();
					}
				);
			}
			
			function EDITFRANCHISE(){
				$( '.edit_franchise' ).button().each(function(){
					var _this = $( this );
					$( this ).unbind( 'click' ).click(function(){
						$( 'input:text, textarea', '#franchise_edit' ).val( '' );
						$( 'input[name=id]', '#franchise_edit' ).val( 0 );
						
						$.post(
							ServerConfig.BMS_Node + 'franchise_FETCH/',
							{
								id : _this.attr( 'data-id' )
							}, function( data ){
								if( data.response ){
									$.each( data.data, function( keys, values ){
										if( $( 'input[name=' + keys + '], textarea[name=' + keys + ']', '#franchise_edit' ).length > 0 ){
											$( 'input[name=' + keys + '], textarea[name=' + keys + ']', '#franchise_edit' ).val( values );
										}
									});
								}
							}, 'json'
						);
					});
				});
			}
			
			function SEARCHFRANCHISES( search, page ){
				if( searchFranchise ){
					searchFranchise.abort();
				}
			
				searchFranchise = $.get(
					ServerConfig.BMS_Node + 'franchise_SEARCH/' + search + '/' + page,
					function( data ){
						$( '#franchise_table' ).html( data ).find( '.edit_franchise' ).button();
						_page.search = +page;
						
						$( '.franchise_search_paginate' ).each(function(){
							$( this ).click(function(){
								if( _page.search != +$( this ).attr( 'data-page' ) ){
									SEARCHFRANCHISES( search, $( this ).attr( 'data-page' ) );
								}
							});
						});
						
						EDITFRANCHISE();
					}
				);
			}
			
			FETCHFRANCHISES( _page.list );
			
			$( 'input[name=search_franchise]' ).keyup(function(){
				if( $( this ).val().length > 2 ){
					SEARCHFRANCHISES( $( this ).val(), 1 );
				} else{
					FETCHFRANCHISES( _page.list );
				}
			});
			
			$( '#save_changes_franchise' ).button().click(function(){
				if( +$( '#franchise_edit input[name=id]' ).val() > 0 ){
					var _details = {};
					
					$( 'input, textarea', '#franchise_edit' ).each(function(){
						_details[$( this ).attr( 'name' )] = $( this ).val();
					});
					$.post(
						ServerConfig.BMS_Node + 'franchise_SAVE/',
						_details,
						function( data ){
							alert( data.message );
						}, 'json'
					);
				}
			});
		});
	</script>
</head>
<body>
	<div id='dotc_header'>
		<div id='dotc_header_image'></div>
	</div>
	<?php
		include( 'menu.php' );
	?>
	<div id='dotc_container'>
		<table class='dotc_table_data center' cellpadding=0 cellspacing=0 width='30%'>
			<tbody>
				<tr>
					<th>
						SEARCH
					</th>
					<th>
						<input type='text' name='search_franchise' />
					</th>
				</tr>
			</tbody>
		</table>
		<table class='dotc_table_data center' cellpadding=0 cellspacing=0 width='70%' id='franchise_table'></table>
		<table class='dotc_table_data center' cellpadding=0 cellspacing=0 width='50%' id='franchise_edit'>
			<tbody>
				<tr>
					<th>
						COMPANY NAME
					</th>
					<td>
						<input type='hidden' name='id' value='0' id='fid' />
						<input type='text' name='name' value='' onChange="getRemarks(document.getElementById('fid').value);"/>
					</td>
				</tr>
				<tr>
					<th>
						CASE NUMBER
					</th>
					<td>
						<input type='text' name='case_no' value='' id='case_no' onChange="change(document.getElementById('ba').value);"/>
					</td>
				</tr>
				<tr>
					<th>
						BUSINESS ADDRESS
					</th>
					<td>
						<textarea name='ba' id='ba'></textarea>
					</td>
				</tr>
				<tr>
					<th>
						ROUTE
					</th>
					<td>
						<textarea name='route'></textarea>
					</td>
				</tr>
				<tr>
					<th>
						REMARKS
					</th>
					<td>
						<div id="remarks"><textarea name='remarks' id='txtremarks'></textarea></div>
					</td>
				</tr>
				<tr>
					<th>
						EMAIL
					</th>
					<td>
						<input type='text' name='email' value='' />
					</td>
				</tr>
				<tr>
					<th>
						TEL NO
					</th>
					<td>
						<input type='text' name='tel_no' value='' />
					</td>
				</tr>
				<tr>
					<th>
						CEL NO
					</th>
					<td>
						<input type='text' name='cel_no' value='' />
					</td>
				</tr>
				<tr>
					<td colspan='2'>
						<a href='javascript: void(0)' id='save_changes_franchise' onClick="saveRemarks(document.getElementById('fid').value, document.getElementById('txtremarks').value, document.getElementById('case_no').value );">SAVE CHANGES</a>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
  <script>
    function getRemarks(id)
    {           
        console.log(id);   
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
          if (xmlhttp.readyState==4 && xmlhttp.status==200)
          {
            document.getElementById("remarks").innerHTML=xmlhttp.responseText;
          }
        }
        xmlhttp.open("GET","libs/rem_loader.php?act=2&id="+id,true);
        xmlhttp.send();
    }
    function saveRemarks(id, remarks, caseNo, ba)
    {                  
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
          if (xmlhttp.readyState==4 && xmlhttp.status==200)
          {
            //document.getElementById("remarks").innerHTML=xmlhttp.responseText;
          }
        }
        xmlhttp.open("GET","libs/rem_loader.php?act=3&id="+id+"&case_no="+caseNo+"&remarks="+remarks,true);
        xmlhttp.send(); 
        
    }
    function change(v)
    {
        document.getElementById('ba').innerHTML = "" + v + " "; 
    }
  </script>
</body>
</html>