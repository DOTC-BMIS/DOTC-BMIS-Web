$(function(){
	var _http_node = ServerConfig.BMS_Node;
	var current_page = 1;
	
	function usersList( page ){
		$.get(
			_http_node + 'usersList/' + page,
			function( data ){
				$( '#dotc_users_container' ).html( data ).find( '.dotc_edit_user' ).button().each(function(){
					var _this = $( this );
					
					_this.click(function(){
						$.get(
							_http_node + 'userDetails/' + _this.attr( 'data-id' ),
							function( data ){
								if( data.data.length > 0 ){
									var _form = $( '#dotc_users_form' );
									
									$( 'input[name=id]', _form ).val( data.data[0].id );
									$( 'input[name=username]', _form ).val( data.data[0].username );
									$( 'input[name=fname]', _form ).val( data.data[0].fname );
									$( 'input[name=lname]', _form ).val( data.data[0].lname );
									$( 'input[name=position]', _form ).val( data.data[0].position );
									$( 'input[name=id_number]', _form ).val( data.data[0].id_number );
									$( 'input[name=contact_number]', _form ).val( data.data[0].contact_number );
									$( 'input[name=email]', _form ).val( data.data[0].email );
									$( 'select[name=user_type]', _form ).val( data.data[0].user_type );
								}
							}
						);
					});
				}).end().find( '.dotc_delete_user' ).button().each(function(){
					var _this = $( this );
					
					_this.click(function(){
						if( confirm( 'Do you really want to delete this user?' ) ){
							$.get(
								_http_node + 'deleteUser/' + _this.attr( 'data-id' ),
								function( data ){
									alert( data.message );
									
									if( data.response ){
										$( '#dotc_users_new' ).click();
										usersList( current_page );
									}
								}
							);
						}
					});
				});
				
				$( '.dotc_paginate' ).each(function(){
					$( this ).click(function(){
						current_page = $( this ).attr( 'data-page' );
						usersList( current_page );
					});
				});
			}
		);
	}
	
	usersList( current_page );
	
	$( '#dotc_users_new' ).button().click(function(){
		var _form = $( '#dotc_users_form' );
		$( 'input[name=id]', _form ).val( 0 );
		$( 'input[name=username]', _form ).val( '' );
		$( 'input[name=fname]', _form ).val( '' );
		$( 'input[name=lname]', _form ).val( '' );
		$( 'input[name=position]', _form ).val( '' );
		$( 'input[name=id_number]', _form ).val( '' );
		$( 'input[name=contact_number]', _form ).val( '' );
		$( 'input[name=email]', _form ).val( '' );
		$( 'select[name=user_type]', _form ).val( 1 );
		$( 'input[name=password]', _form ).val( '' );
	});
	
	$( '#dotc_users_save_changes' ).button().click(function(){
		var _form = $( '#dotc_users_form' );
		$.post(
			_http_node + 'saveChangesUser',
			{
				id : $( 'input[name=id]', _form ).val(),
				username : $( 'input[name=username]', _form ).val(),
				fname : $( 'input[name=fname]', _form ).val(),
				lname : $( 'input[name=lname]', _form ).val(),
				position : $( 'input[name=position]', _form ).val(),
				id_number : $( 'input[name=id_number]', _form ).val(),
				contact_number : $( 'input[name=contact_number]', _form ).val(),
				email : $( 'input[name=email]', _form ).val(),
				user_type : $( 'select[name=user_type]', _form ).val(),
				password : $( 'input[name=password]', _form ).val()
			},
			function( data ){
				alert( data.message );
				
				if( data.response ){
					if( data.mode.toLowerCase() == 'add' ){
						$( '#dotc_users_new' ).click();
						
						usersList( current_page );
					}
				}
			}, 'json'
		);
	});
});