$(function(){
	$( '#generate_report' ).button().click(function(){
		$.post(
			ServerConfig.BMS_Node + 'viewReports',
			{
				type : $( 'select[name=select_report]' ).val(),
				start_date : $( "input[name=start_date]" ).val(),
				end_date : $( "input[name=end_date]" ).val()
			}, function( data ){
				$( '#dotc_reports_container' ).html( data ).dialog({
					draggable : true,
					resizable : false,
					width : '50%',
					height : 450,
					modal : true,
					buttons : 
						( $( '#dotc_reports_container .no_record_found' ).length == 0 ) ? {
						'GENERATE PDF' : function(){
							window.location = 'actions.php?action=generate_registration_report&type=' + $( 'select[name=select_report]' ).val() + '&start_date=' + $( "input[name=start_date]" ).val() + '&end_date=' + $( "input[name=end_date]" ).val();
						}
					} : {}
				}).find( '.record_found' ).each(function(){
					var _this = $( this );
					
					_this.click(function(){
						window.location = 'actions.php?action=download_revise_verification&code_id=' + _this.attr( 'data-id' )
					});
				});
			}
		);
	});
	
	$( 'select[name=select_report]' ).change(function(){
		$( '.dotc_table_data tr[data-id]' ).hide();
		
		if( $( '.dotc_table_data tr[data-id=' + $( this ).val() + ']' ).length > 0 ){
			$( '.dotc_table_data tr[data-id=' + $( this ).val() + ']' ).show();
		}
	});
	
	$( 'select[name=select_report]' ).change();
	
	$( "input[name=start_date]" ).datepicker({
		maxDate : 0,
		changeMonth: true,
		numberOfMonths: 3,
		onClose: function( selectedDate ) {
			$( "input[name=end_date]" ).datepicker( "option", "minDate", selectedDate );
		}
	});
	$( "input[name=end_date]" ).datepicker({
		maxDate : 0,
		changeMonth: true,
		numberOfMonths: 3,
		onClose: function( selectedDate ) {
			$( "input[name=start_date]" ).datepicker( "option", "maxDate", selectedDate );
		}
	});
});