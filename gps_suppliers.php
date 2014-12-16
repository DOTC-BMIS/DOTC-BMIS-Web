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
	<link rel='stylesheet' type='text/css' href='scripts/jGrowl/jGrowl-dark.css' />
	<script type='text/javascript' src='scripts/jquery-1.11.1.js'></script>
	<script type='text/javascript' src='scripts/jquery-ui-1.10.3.custom/js/jquery-ui-1.10.3.custom.min.js'></script>
	<script type='text/javascript' src='scripts/jGrowl/jquery.jgrowl.min.js'></script>
	<script type='text/javascript' src='scripts/jquery-upload.js'></script>
	<script type='text/javascript' src='scripts/mustache.js/mustache.js'></script>
	<script type='text/javascript' src='scripts/handlebars.js'></script>
	<script type='text/javascript' src='scripts/handlebars.swag.js'></script>
	<script type='text/javascript' src='scripts/jQuery-slimScroll-1.1.0/jquery.slimscroll.min.js'></script>
	<script type='text/javascript' src='scripts/async.js'></script>
	<script type='text/javascript' src='scripts/config.js'></script>
	<script type='text/javascript' src='scripts/dotc_gps_suppliers.js'></script>
</head>
<body>
	<div id='dotc_header'>
		<div id='dotc_header_image'></div>
	</div>
	<?php
		include( 'menu.php' );
	?>
	<div id='dotc_container'>
		<div id='dotc_gps_suppliers_list'></div>
		<div id='dotc_gps_supplier_form'></div>
		<input type='hidden' name='user_id' value='<?php echo $_SESSION['user_details']['id']; ?>' />
	</div>
	<script type='text/x-handlebars-template' id='gps_supplier_form'></script>
	<script type='text/x-handlebars-template' id='gps_personnel'>
		<tr data-personnel='{{ctr}}'>
			<th>
				PERSONNEL FULLNAME
			</th>
			<td>
				<input type='hidden' name='personnel_id' value='0' class='personnel_id' />
				<input type='text' name='personnel_fullname' value='' class='personnel_fullname validate' data-validate='length' />
			</td>
		</tr>
		<tr data-personnel='{{ctr}}'>
			<th>
				CONTACT INFO
			</th>
			<td>
				<textarea name='personnel_contact_info' class='personnel_contact_info validate' data-validate='length'></textarea>
			</td>
		</tr>
		<tr data-personnel='{{ctr}}'>
			<td colspan='2' align='left'>
				<a href='javascript: void(0)' class='remove_personnel' data-personnel='{{ctr}}'>REMOVE</a>
			</td>
		</tr>
	</script>
	<script type='text/x-handlebars-template' id='gps_device'>
		<tr data-device='{{ctr}}'>
			<th>
				NAME / MODEL
			</th>
			<td>
				<input type='hidden' name='device_id' value='0' class='device_id' />
				<input type='text' name='device_name_model' value='' class='device_name_model validate' data-validate='length' />
			</td>
		</tr>
		<tr data-device='{{ctr}}'>
			<th>
				PRICE
			</th>
			<td>
				<input type='text' name='device_price' value='0.00' class='device_price validate' data-validate='number' />
			</td>
		</tr>
		<tr data-device='{{ctr}}'>
			<th>
				DESCRIPTION
			</th>
			<td>
				<textarea name='device_description' class='device_description validate' data-validate='length'></textarea>
			</td>
		</tr>
		<tr data-device='{{ctr}}'>
			<th valign='top'>
				PHOTO
			</th>
			<td valign='top'>
				<input type='file' name='device_picture' class='device_picture' />
			</td>
		</tr>
		<tr data-device='{{ctr}}'>
			<td colspan='2' align='left'>
				<a href='javascript: void(0)' class='remove_device' data-device='{{ctr}}'>REMOVE</a>
			</td>
		</tr>
	</script>
</body>
</html>