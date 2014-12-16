$(function(){
	$( '.action-button' ).button();
	
	$( 'input[name=username], input[name=password]', '#dotc_login_container' ).each(function(){
		$( this ).keyup(function( e ){
			if( e.keyCode == 13 ){
				$( '#dotc_login_button' ).click();
			}
		});
	});
	
	$( '#dotc_login_container input[name=username]' ).focus();
	
	$( '#dotc_login_button' ).click(function(){
		$.post(
			'actions.php?action=login',
			{
				username : $( '#dotc_login_container input[name=username]' ).val(),
				password : $( '#dotc_login_container input[name=password]' ).val()
			}, function( data ){
				if( data.response ){
					window.location = data.redirect;
				} else{
					alert( data.message );
				}
			}
		);
	});
});