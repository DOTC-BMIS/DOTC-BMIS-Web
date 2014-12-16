<?php
require_once( 'libs/class.DOTC_BMS.php' );
$dotc = new DOTC_BMS;

if( $dotc->checkSession() ){
	$dotc->redirect( 'index.php' );
}
?>
<html>
<head>
	<title>DOTC BMS - Login</title>
	<link href='styles/main.css' rel='StyleSheet' />
	<link rel='stylesheet' type='text/css' href='scripts/jquery-ui-1.10.3.custom/css/flick/jquery-ui-1.10.3.custom.min.css' />
	<script type='text/javascript' src='scripts/jquery-1.11.1.js'></script>
	<script type='text/javascript' src='scripts/jquery-ui-1.10.3.custom/js/jquery-ui-1.10.3.custom.min.js'></script>
	<script type='text/javascript' src='scripts/jquery-upload.js'></script>
	<script type='text/javascript' src='scripts/mustache.js/mustache.js'></script>
	<script type='text/javascript' src='scripts/handlebars.js'></script>
	<script type='text/javascript' src='scripts/handlebars.swag.js'></script>
	<script type='text/javascript' src='scripts/config.js'></script>
	<script type='text/javascript' src='scripts/dotc_login.js'></script>
</head>
<body>
	<div id='dotc_header'>
		<div id='dotc_header_image'></div>
	</div>
	<div id='dotc_login_container'>
		<table class='dotc_table_data' cellpadding=0 cellspacing=0>
			<tbody>
				<tr>
					<th>
						USERNAME
					</th>
					<td>
						<input type='text' name='username' />
					</td>
				</tr>
				<tr>
					<th>
						PASSWORD
					</th>
					<td>
						<input type='password' name='password' />
					</td>
				</tr>
				<tr>
					<td colspan='2'>
						<a href='javascript: void(0)' class='action-button' id='dotc_login_button'>Login</a>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</body>
</html>