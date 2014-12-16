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
				url : ServerConfig.BMS_Node + 'excelImport',
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