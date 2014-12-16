$(function(){
	var _operator_json = false;
	var _http_host = ServerConfig.BMS_PHP;
	var _http_node = ServerConfig.BMS_Node;
	var generate_ajax = false;
	var _loader = $( '#ajax_loader' ).css({
		'z-index' : 999
	});
	var _gps_intervals = [];
	var _sms_intervals = [];
	var paginate = {
		page : 1,
		start_row : 0,
		limit : 10
	};
	
	$( '#dotc_search_result ul' ).slimScroll({
		position: 'right',
		height: '270px',
		railVisible: true,
		alwaysVisible: true
	});
	
	$( '#dotc_data' ).submit(function( e ){
		e.preventDefault();
	});
	
	try{
		$( '#dotc_webcam' ).photobooth().on( 'image', function( event, dataURL ){
			$( "#dotc_webcam_captured" ).show().find( 'img' ).attr( 'src', dataURL );
			$( '#dotc_webcam' ).hide()
		}).find( 'li:not(.trigger)' ).remove();
		
		$( "#dotc_webcam_captured a" ).click(function(){
			$( '#dotc_webcam' ).show();
			$( "#dotc_webcam_captured" ).hide();
		});
		
		var timeout = setTimeout(function(){
			$( '#dotc_webcam_container' ).hide();
		}, 150 );
	} catch( err ){
	
	}
	
	var emailRegex = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	
	var forSelection = [];
	var forSelectionCompanyName = '';
	
	function SELECTVEHICLE(){
		if( $( '#dotc_search_result ul li' ).length > 0 ){
			$( '#dotc_search_result ul li' ).each(function(){
				var $this = $( this );
				
				$this.click(function(){
					if(
						forSelection.length == 0
						|| forSelectionCompanyName == $this.attr( 'data-companyname' )
					){
						if( $.inArray( $this.attr( 'data-vehicle' ), forSelection ) > -1 ){
							forSelection.splice( forSelection.indexOf( $this.attr( 'data-vehicle' ) ), 1 );
							$this.removeClass( 'ui-selected' );
						} else{
							forSelection.push( $this.attr( 'data-vehicle' ) );
							$this.addClass( 'ui-selected' );
						}
						
						if( forSelectionCompanyName.length == 0 ){
							if( forSelection.length > 0 ){
								forSelectionCompanyName = $this.attr( 'data-companyname' );
							}
						} else{
							if( forSelection.length == 0 ){
								forSelectionCompanyName = '';
							}
						}
					} else{
						$.jGrowl( 'Selected Transit Company should be the same as the first selected', { life : 3000 });
					}
				});
			});
		}
	}
	
	function HISTORYACTIONS(){
		$( '.dotc_view_changes' ).each(function(){
			var m_this = $( this );
			m_this.click(function(){
				$.get(
					_http_node + 'fetchFranchiseRevisions/' + m_this.attr( 'data-codeid' ),
					function( data ){
						$( '#dotc_history_revisions_container' ).html( data ).dialog({
							width : '70%',
							height : 250,
							modal : true,
							draggable : true,
							resizable : false
						});
					}
				)
			});
		});
		
		$( '.dotc_revise_qr_codes_latest' ).button().each(function(){
			var m_this = $( this );
			
			m_this.click(function(){
				window.location = 'actions.php?action=download_revise_qr_codes_latest&case_no=' + m_this.attr( 'data-caseno' );
			});
		});
		
		$( '.dotc_revise_verification_latest' ).button().each(function(){
			var m_this = $( this );
			
			m_this.click(function(){
				window.location = 'actions.php?action=download_revise_verification_latest&case_no=' + m_this.attr( 'data-caseno' );
			});
		});
		
		$( '.dotc_revise_qr_codes' ).button().each(function(){
			var m_this = $( this );
			
			m_this.click(function(){
				window.location = 'actions.php?action=download_revise_qr_codest&code_id=' + m_this.attr( 'data-codeid' );
			});
		});
		
		$( '.dotc_revise_verification' ).button().each(function(){
			var m_this = $( this );
			
			m_this.click(function(){
				window.location = 'actions.php?action=download_revise_verification&code_id=' + m_this.attr( 'data-codeid' );
			});
		});
	}
	
	$( '#vehicle_edit_selected' ).button().click(function(){
		if( forSelection.length > 0 ){
			_loader.show();
		
			$.post(
				_http_node + 'fetchVehicleInfo_V2',
				{
					ids : forSelection.join( ',' ),
					companyname : forSelectionCompanyName
				}, function( data ){
					$( '#dotc_search' ).fadeOut( 500, function(){
						$( '#dotc_webcam_container' ).show();
						$( '#dotc_operator_details' ).html( data ).show();
						_loader.hide();
						
						if( $( '#dotc_rep_image_latest' ).length > 0 ){
							$( '#dotc_webcam_captured img' ).attr( 'src', $( '#dotc_rep_image_latest' ).attr( 'src' ) );
							$( '#dotc_webcam' ).hide();
							$( "#dotc_webcam_captured" ).show();
						} else{
							$( '#dotc_webcam' ).show();
							$( "#dotc_webcam_captured" ).hide();
						}
						
						HISTORYACTIONS();
						
						$( '#vehicle_zip_archive' ).unbind( 'click' ).click(function(){
							$.post(
								_http_host + 'actions.php?action=check_vehicle_zip_archive',
								{
									ids : forSelection.join( ',' )
								}, function( data ){
									if( data.response ){
										window.location = _http_host + 'actions.php?action=create_vehicle_zip_archive&ids=' + forSelection.join( ',' );
									} else{
										$.jGrowl( 'No ZIP Archive for this batch. Update at least one vehicle',{ life : 30000 });
									}
								}
							);
						});
						
						$( '#dotc_form .dotc_cp_no' ).each(function(){
							var $this = $( this );
							$this.blur(function(){
								var cp_no = $this.val().replace( /[^0-9]/g, '' );
								
								if( cp_no.length == 9 && $( '#dotc_form .dotc_gps_id[data-id=' + $this.attr( 'data-id' ) + ']' ).val().length == 8 ){
									$( '#dotc_form .dotc_test_cp_no[data-id=' + $this.attr( 'data-id' ) + ']' ).show();
								} else{
									$( '#dotc_form .dotc_test_cp_no[data-id=' + $this.attr( 'data-id' ) + ']' ).hide();
								}
							});
						});
						
						$( '#dotc_form .dotc_gps_id' ).each(function(){
							var $this = $( this );
							$this.change(function(){
								var cp_no = $( '#dotc_form .dotc_cp_no[data-id=' + $this.attr( 'data-id' ) + ']' ).val().replace( /[^0-9]/g, '' );
							
								if( cp_no.length == 9 && $this.val().length == 8 ){
									$( '#dotc_form .dotc_test_cp_no[data-id=' + $this.attr( 'data-id' ) + ']' ).show();
								} else{
									$( '#dotc_form .dotc_test_cp_no[data-id=' + $this.attr( 'data-id' ) + ']' ).hide();
								}
							});
						});
						
						$( '#dotc_form .dotc_test_cp_no' ).each(function(){
							var $this = $( this );
							
							$this.click(function(){
								$this.parent( 'td' ).parent( 'tr' ).find( '.livestamp' ).hide().siblings( 'img' ).show();
							
								var _details = new FormData();
							
								_details.append( 'image', $( "#dotc_webcam_captured img" ).attr( 'src' ) );
								_details.append( 'case_no', $this.attr( 'data-case' ) );
								_details.append( 'encoder_id', $( 'input[name=user_details_id]' ).val() );
								_details.append( 'unit_id', $this.attr( 'data-id' ) );
								_details.append( 'gps_id', $( '.dotc_gps_id[data-id=' + $this.attr( 'data-id' ) + ']' ).val() );
								_details.append( 'cp_no', $( '.dotc_cp_no[data-id=' + $this.attr( 'data-id' ) + ']' ).val() );
								_details.append( 'fname', $( '#dotc_data input[name=fname]' ).val() );
								_details.append( 'lname',$( '#dotc_data input[name=lname]' ).val() );
								_details.append( 'title', $( '#dotc_data input[name=title]' ).val() );
								_details.append( 'id_no', $( '#dotc_data input[name=id_no]' ).val() );
								_details.append( 'contact_no', $( '#dotc_data input[name=contact_no]' ).val() );
								_details.append( 'email', $( '#dotc_data input[name=email]' ).val() );
								_details.append( 'gps_password', $( '.dotc_gps_password[data-id=' + $this.attr( 'data-id' ) + ']' ).val() );
								_details.append( 'sending_time', $( '.dotc_sending_time[data-id=' + $this.attr( 'data-id' ) + ']' ).val() );
								_details.append( 'franchise_email', $( '#dotc_data input[name=franchise_email]' ).val() );
								_details.append( 'device_id', $( '.dotc_device_id[data-id=' + $this.attr( 'data-id' ) + ']' ).val() );
								
								$.ajax({
									url : _http_node + 'DOTC_AssignDEVICEID',
									type : 'POST',
									data : _details,
									async : true,
									cache : false,
									contentType : false,
									processData : false,
									success : function( data ){
										// alert( data.message );
										$.jGrowl( data.message, { life: 30000 });
									}
								});
							});
						});
						
						$( '.dotc_view_changes_vehicle' ).each(function(){
							var m_this = $( this );
							m_this.click(function(){
								$.get(
									_http_node + 'fetchUnitRevisions/' + m_this.attr( 'data-id' ) + '/' + m_this.attr( 'data-chassisno' ),
									function( data ){
										$( '#dotc_history_revisions_container' ).html( data ).dialog({
											width : 680,
											height : 200,
											modal : true,
											draggable : true,
											resizable : false
										});
									}
								)
							});
						});
						//kkk
						$( '.dotc_view_vehicle_remarks' ).each(function(){
							var m_this = $( this );
							m_this.click(function(){
								$.get(
									_http_host + 'libs/rem.php?id=' + m_this.attr( 'data' ),
									function( data ){
										$( '#dotc_history_remarks_container' ).html( data ).dialog({
											width : 500,
											height : 230,
											modal : true,
											draggable : true,
											resizable : false
										});
									}
								)
							});
						});
						
						$( '#dotc_form .dotc_generate_gps_id' ).each(function(){
							var $this = $( this );
							$this.click(function(){
								if( !generate_ajax ){
									generate_ajax = $.get(
										_http_node + 'generateGPS_ID/' + $this.attr( 'data-id' ),
										function( data ){
											$( '#dotc_form input[name="id[' + $this.attr( 'data-id' ) + ']"]' ).val( data.l );
											generate_ajax = false;
										}
									);
								}
							});
						});
						
						$( '#dotc_close' ).click(function(){
							if( _gps_intervals.length > 0 ){
								for( var x in _gps_intervals ){
									clearInterval( _gps_intervals[x] );
									delete _gps_intervals[x];
								}
							}
							
							if( _sms_intervals.length > 0 ){
								for( var x in _sms_intervals ){
									clearInterval( _sms_intervals[x] );
									delete _sms_intervals[x];
								}
							}
						
							$( '#dotc_webcam_container' ).hide();
							$( '#dotc_operator_details' ).fadeOut( 500, function(){
								$( '#dotc_search' ).show();
							});
						});
						
						$( '#dotc_print_qrcodes' ).button().click(function(){
							$.post(
								'actions.php?action=check_pdf_qr',
								{
									case_no : _this.attr( 'data-case' )
								}, function( data ){
									if( data.response ){
										// alert( data.message );
										$.jGrowl( data.message, { life: 30000 });
									} else{
										window.location = 'actions.php?action=download_pdf_qr&case_no=' + _this.attr( 'data-case' )
									}
								}
							);
						});
					});
				}
			);
		}
	});

	$( 'input[name=operator_name],input[name=case_no],input[name=plate_no],input[name=chassis_no]', '#dotc_search' ).each(function(){
		var _this = $( this );
		
		_this.keyup(function(){
			var _total = 0;
			
			$( 'input[name=operator_name],input[name=case_no],input[name=plate_no],input[name=chassis_no]', '#dotc_search' ).each(function(){
				_total += ( $( this ).val().length > 2 ) ? 1 : 0;
			});
			
			if(
				_total > 0
			){
				$( '#dotc_search_result ul' ).html( '' );
				forSelection = [];
				
				if( _operator_json ){
					_operator_json.abort();
				}
				
				_operator_json = $.post(
					_http_node + 'searchFranchise_V2',
					{
						name : $( '#dotc_search input[name=operator_name]' ).val(),
						case_no : $( '#dotc_search input[name=case_no]' ).val(),
						plate_no : $( '#dotc_search input[name=plate_no]' ).val(),
						chassis_no : $( '#dotc_search input[name=chassis_no]' ).val()
					}, function( data ){
						$( '#dotc_search_result ul' ).html( data );
						forSelection = [];
						forSelectionCompanyName = '';
						SELECTVEHICLE();
					}
				);
			} else{
				$( '#dotc_search_result ul' ).html( '' );
			}
		});
	});
});