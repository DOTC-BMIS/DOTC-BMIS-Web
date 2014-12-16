<?php
$menu = $dotc->fetchUserMenu();

$menu_html = '';

if(
	is_array( $menu )
	&& count( $menu ) > 0
){
	foreach( $menu AS $keys => $values ):
		$menu_html .= "<li><a href='{$values['link']}'>" . strtoupper( $values['label'] ) . "</a></li>";
	endforeach;
}
?>
<script type='text/javascript'>
$(function(){
	$( '#logout_link' ).click(function( e ){
		e.preventDefault();
	
		if( $( '#ajax_loader' ).length > 0 ){
			$( '#ajax_loader' ).show();
		}
		
		setTimeout(function(){
			window.location = 'actions.php?action=logout';
		}, 1000 );
	});
});
</script>
<div id='dotc_menu_container'>
	<div id='dotc_menu_inner'>
		<ul>
			<?php
				echo $menu_html;
			?>
			<li><a href='javascript: void(0)' id='logout_link'>LOGOUT</a></li>
		</ul>
		<div class='clearb'></div>
	</div>
</div>
<div style='display: none;' id='ajax_loader'><img src='images/ajax-loader.gif' /></div>