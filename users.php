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
	<script type='text/javascript' src='scripts/dotc_users.js'></script>
</head>
<body>
	<div id='dotc_header'>
		<div id='dotc_header_image'></div>
	</div>
	<?php
		include( 'menu.php' );
	?>
	<div id='dotc_container'>
		<div id='dotc_users_container'></div>
		<table class='dotc_table_data center' cellpadding=0 cellspacing=0 width='50%' id='dotc_users_form'>
			<tbody>
				<tr>
					<th>
						USERNAME
					</th>
					<td>
						<input type='hidden' name='id' value='0' />
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
					<th>
						FIRSTNAME
					</th>
					<td>
						<input type='text' name='fname' />
					</td>
				</tr>
				<tr>
					<th>
						LASTNAME
					</th>
					<td>
						<input type='text' name='lname' />
					</td>
				</tr>
				<tr>
					<th>
						TITLE
					</th>
					<td>
						<input type='text' name='position' />
					</td>
				</tr>
				<tr>
					<th>
						ID #
					</th>
					<td>
						<input type='text' name='id_number' />
					</td>
				</tr>
				<tr>
					<th>
						CONTACT #
					</th>
					<td>
						<input type='text' name='contact_number' />
					</td>
				</tr>
				<tr>
					<th>
						EMAIL
					</th>
					<td>
						<input type='text' name='email' />
					</td>
				</tr>
				<tr>
					<th>
						USERTYPE
					</th>
					<td>
						<select name='user_type'>
							<?php
								$user_types = $dotc->fetchUserTypes();
								
								foreach( $user_types AS $keys => $values ):
									echo "<option value='{$values['id']}'>{$values['name']}</option>";
								endforeach;
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td colspan=2>
						<a href='javascript: void(0)' id='dotc_users_save_changes'>SAVE CHANGES</a>&nbsp;<a href='javascript: void(0)' id='dotc_users_new'>NEW</a>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</body>
</html>