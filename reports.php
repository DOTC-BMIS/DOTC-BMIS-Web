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
	<script type='text/javascript' src='scripts/dotc_reports.js'></script>
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
					<th colspan=2>
						VIEW REPORTS
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<th>
						SELECT REPORT
					</th>
					<td>
						<select name='select_report'>
							<?php
								$report_types = array( 'Today', 'Yesterday', 'Last Week', 'This Week', 'Last Month', 'This Month', 'Date Range' );
								
								foreach( $report_types AS $keys => $values ):
									echo "<option value='" . ( $keys + 1 ) . "'>{$values}</option>";
								endforeach;
							?>
						</select>
					</td>
				</tr>
				<tr data-id='7'>
					<th>
						SELECT START DATE
					</th>
					<td>
						<input type='text' name='start_date' placeholder='START DATE' value='<?php echo date( 'm/d/Y' ); ?>' />
					</td>
				</tr>
				<tr data-id='7'>
					<th>
						SELECT END DATE
					</th>
					<td>
						<input type='text' name='end_date' placeholder='END DATE' value='<?php echo date( 'm/d/Y' ); ?>' />
					</td>
				</tr>
				<tr>
					<td colspan='2'>
						<a href='javascript: void(0)' id='generate_report'>SEARCH</a>
					</td>
				</tr>
			</tbody>
		</table>
		<div id='dotc_reports_container' title='VIEW REPORT' class='hidden'></div>
	</div>
</body>
</html>