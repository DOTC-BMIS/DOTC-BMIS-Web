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

# $dotc->debug( $dotc->showColumnsOfTable( 'dotc_pub_frnc' ) );
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
		var _loader = $( '#ajax_loader' );

		$( '#dotc_export' ).button().click(function(){
			window.location = 'actions.php?action=export_data&id=' + $( 'select[name=dotc_tablename]' ).val();
		});
		
		$( '#dotc_data' ).submit(function( e ){
			e.preventDefault();
		});
		
		$( '#dotc_import' ).button().click(function(){
			if( $( 'input:file[name=dotc_file]' ).val().length > 0 ){
				_loader.show();
				var formData = new FormData( $( '#dotc_data' )[0] );
				
				formData.append( 'tid', $( 'select[name=dotc_tablename]' ).val() );
				
				$.ajax({
					url : ServerConfig.BMS_Node + 'excelImportUpdateByTable',
					type : 'POST',
					data : formData,
					async : true,
					cache : false,
					contentType : false,
					processData : false,
					success: function ( data ){
						_loader.hide();
						
						alert( data.message );
					}
				});
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
		<table class='dotc_table_data center' cellpadding=0 cellspacing=0 width='40%'>
			<thead>
				<tr>
					<th colspan='3'>
						IMPORT/EXPORT
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<th>
						SELECT TABLE
					</th>
					<td>
						<select name='dotc_tablename'>
							<?php
								$import_export = $dotc->importExportTables();
								
								foreach( $import_export AS $keys => $values ):
									echo "<option value='{$values['id']}'>{$values['table_alias']}</option>";
								endforeach;
							?>
						</select>
					</td>
				</tr>
				<tr>
					<th>
						FILE (XLS)
					</th>
					<td>
						<form id='dotc_data'>
							<input type='file' name='dotc_file' />
						</form>
					</td>
				</tr>
				<tr>
					<td colspan='2'>
						<a href='javascript: void(0)' id='dotc_import'>IMPORT</a>&nbsp;<a href='javascript: void(0)' id='dotc_export'>EXPORT</a>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</body>
</html>