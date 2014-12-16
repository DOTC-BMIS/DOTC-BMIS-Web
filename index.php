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
	<script type='text/javascript' src='scripts/jquery-ui-1.10.3.custom/js/jquery-1.9.1.js'></script>
	<script type='text/javascript' src='scripts/jquery-ui-1.10.3.custom/js/jquery-ui-1.10.3.custom.min.js'></script>
	<script type='text/javascript' src='scripts/jQuery-slimScroll-1.1.0/jquery.slimscroll.min.js'></script>
	<script type='text/javascript' src='scripts/jGrowl/jquery.jgrowl.min.js'></script>
	<script type='text/javascript' src='scripts/photobooth.js'></script>
	<script type='text/javascript' src='scripts/phpjs.js'></script>
	<script type='text/javascript' src="scripts/moment.js"></script>
	<script type='text/javascript' src="scripts/livestamp.js"></script>
	<script type='text/javascript' src="scripts/async.js"></script>
	<script type='text/javascript' src='scripts/config.js'></script>
	<script type='text/javascript' src='scripts/dotc_main_v2.js'></script> 
</head>
<body>
	<div id='dotc_header'>
		<div id='dotc_header_image'></div>
	</div>
	<?php
		include( 'menu.php' );
	?>
	<div id='dotc_container'>
		<input type='hidden' name='user_details_id' value='<?php echo $_SESSION['user_details']['id']; ?>' />
		<div id="dotc_search">
			<h3>SEARCH FRANCHISE</h3>
			<table class='dotc_table' cellpadding=0 cellspacing=0 width='40%'>
				<tbody>
					<tr>
						<th>
							OPERATOR NAME
						</th>
						<td>
							<input type='text' name='operator_name' placeholder='SEARCH BY OPERATOR NAME' />
						</td>
					</tr>
					<tr>
						<th>
							CASE NUMBER
						</th>
						<td>
							<input type='text' name='case_no' placeholder='SEARCH BY CASE #' />
						</td>
					</tr>
					<tr>
						<th>
							PLATE NUMBER
						</th>
						<td>
							<input type='text' name='plate_no' placeholder='SEARCH BY PLATE #' />
						</td>
					</tr>
					<tr>
						<th>
							CHASSIS NUMBER
						</th>
						<td>
							<input type='text' name='chassis_no' placeholder='SEARCH BY CHASSIS #' />
						</td>
					</tr>
					<tr>
						<td colspan=2>
							<a href='javascript: void(0)' id='vehicle_edit_selected'>EDIT SELECTED</a>
						</td>
					</tr>
				</tbody>
			</table>
			<div id="dotc_search_result"><ul></ul></div>
		</div>
		<form id='dotc_data'>
		<div id='dotc_webcam_container' class='left'>
			<div id='dotc_webcam'></div>
			<div id='dotc_webcam_captured' class='hidden'><img src='' /><a href='javascript: void(0)'>Capture Again?</a></div>
		</div>
		<div id='dotc_operator_details' class='hidden'></div>
		</form>
	</div>
	<div id='dotc_history_remarks_container' title='REMARKS' class='hidden'></div>
	<div id='dotc_history_revisions_container' title='CHANGES' class='hidden'></div>
</body>
</html>