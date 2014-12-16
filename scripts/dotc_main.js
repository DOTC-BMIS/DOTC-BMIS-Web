$(function(){
	var _operator_json = false;
	var _http_host = 'http://localhost:8596/dotc_bms_mysql/';
	var _http_node = 'http://localhost:3000/';
	var generate_ajax = false;
	var _loader = $( '#ajax_loader' );
	var _gps_intervals = [];
	var _sms_intervals = [];
	var paginate = {
		page : 1,
		start_row : 0,
		limit : 10
	};
	
	$( '#dotc_search_result ul' ).slimScroll({
		position: 'right',
		height: '300px',
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
	
	function CHECKVALIDATEDFORMS(){
		$( '#dotc_operator_details .verifyElement' ).each(function(){
			var $this = $( this );
		
			$this.unbind( 'blur' ).blur(function(){
				var verify = $this.attr( 'data-verify' );
			
				switch( verify.toLowerCase() ){
					case 'email':
						if( emailRegex.test( $this.val() ) ){
							$this.removeClass( 'notvalidated' ).addClass( 'validated' );
						} else{
							$this.removeClass( 'validated' ).addClass( 'notvalidated' );
						}
					break;
					default:
						if( $this.val().length > 0 ){
							$this.removeClass( 'notvalidated' ).addClass( 'validated' );
						} else{
							$this.removeClass( 'validated' ).addClass( 'notvalidated' );
						}
					break;
				}
			});
			
			if( $( '#dotc_revise_history table tbody tr' ).length > 0 ){
				$this.blur();
			}
		});
	}
	
	function encodeURL( str ){
		return str.replace(/\+/g, '-').replace(/\//g, '_').replace(/\=+$/, '');
	}

	function decodeUrl( str ){
		str = (str + '===').slice(0, str.length + (str.length % 4));
		return str.replace(/-/g, '+').replace(/_/g, '/');
	}
	
	function _LIVESTAMP( case_no ){
		$.post(
			_http_node + 'DOTC_LIVESTAMP',
			{
				case_no : case_no
			},
			function( data ){
				$.each( data.data, function( keys, values ){
					var is_after = moment( values.datetime ).add( 1, 'h' ).isAfter( moment().format( 'YYYY-MM-DD' ) );
				
					$( '.livestamp[data-id=' + values.id + ']' ).css({
						'text-transform' : 'uppercase'
					}).livestamp( values.datetime );
					
					if( is_after ){
						$( '.dotc_gps_id[data-id=' + values.id + ']' ).parent( 'td' ).parent( 'tr' ).find( 'td' ).css({
							'background-color' : 'green',
							'color' : '#fff'
						});
					}
				});
			}, 'json'
		);
	}
	
	function HISTORYACTIONS(){
		$( '.dotc_view_changes' ).button().each(function(){
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
	
	function SMSMESSAGES(){
		if( _sms_intervals.length > 0 ){
			for( var x in _sms_intervals ){
				clearInterval( _sms_intervals[x] );
			}
			_sms_intervals = [];
		}
	
		$( '.status_message' ).each(function(){
			var $this = $( this );
			if( typeof _sms_intervals[$this.attr( 'data-id' )] == 'undefined' ){
				// set interval that checks a json url
				// var current_date = date( 'Y-m-d G:i:s', strtotime( date( 'Y-m-d G:i:s' ) + ' -5 minutes' ) );
				
				$this.find( '.livestamp' ).hide().siblings( 'img' ).show();
				
				_sms_intervals[$this.attr( 'data-id' )] = setInterval(function(){
					var cp_no = $this.attr( 'data-cpno' );
					cp_no = +cp_no.substring( 0, 2 ) == 63 ? '0' + cp_no.substring( 2 ) : cp_no;
					
					$.post(
						_http_node + 'DOTC_SMSMESSAGE',
						{
							cp_no : cp_no,
							gps_id : $this.attr( 'data-gpsid' ),
							datetime : $this.attr( 'data-datetime' )
						}, function( data ){
							if( data.response ){
								$this.find( 'td' ).css({
									'background-color' : 'green',
									'color' : '#fff'
								}).end().find( '.livestamp' ).show().siblings( 'img' ).hide();
								clearInterval( _sms_intervals[$this.attr( 'data-id' )] );
								delete _sms_intervals[$this.attr( 'data-id' )];
								$( '#refresh_last_seen' ).click();
							}
						}, 'json'
					);
				}, 5000 );
			}
		});
	}
	
	function franchiseSelect(){
		if( $( '#dotc_search_result ul li' ).length > 0 ){
			$( '#dotc_search_result ul li' ).each(function(){
				var _this = $( this );
				
				_this.click(function(){
					_loader.show();
					$.get(
						_http_node + 'fetchFranchise/' + _this.attr( 'data-case' ),
						function( data ){
							paginate = {
								page : 1,
								start_row : 0,
								limit : 10
							};
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
								
								$( '#refresh_last_seen' ).click(function(){
									_LIVESTAMP( _this.attr( 'data-case' ) );
								});
								
								$( '#revise_history_prev' ).css({
									'text-decoration' : 'none',
									'color' : 'blue',
									'font-size' : '12px',
									'font-weight' : 'bold'
								}).click(function(){
									$.get(
										_http_node + 'fetchFranchiseRevisionsPaginate/' + _this.attr( 'data-case' ) + '/' + ( paginate.page - 1 ) + '/' + paginate.start_row + '/' + paginate.limit,
										function( data ){
											if( data != 'false' ){
												$( '#dotc_revise_history table tbody' ).html( data );
												HISTORYACTIONS();
												paginate.page--;
												paginate.start_row = ( paginate.page - 1 ) * paginate.limit;
											}
										}
									);
								});
								
								$( '#revise_history_next' ).css({
									'text-decoration' : 'none',
									'color' : 'blue',
									'font-size' : '12px',
									'font-weight' : 'bold'
								}).click(function(){
									$.get(
										_http_node + 'fetchFranchiseRevisionsPaginate/' + _this.attr( 'data-case' ) + '/' + ( paginate.page + 1 ) + '/' + paginate.start_row + '/' + paginate.limit,
										function( data ){
											if( data != 'false' ){
												$( '#dotc_revise_history table tbody' ).html( data );
												HISTORYACTIONS();
												paginate.page++;
												paginate.start_row = ( paginate.page - 1 ) * paginate.limit;
											}
										}
									);
								});
								
								$( '#refresh_last_seen' ).click();
								
								$( '#dotc_form .dotc_generate_gps_id' ).button().each(function(){
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
								
								$( '.dotc_view_changes_vehicle' ).button().each(function(){
									var m_this = $( this );
									m_this.click(function(){
										$.get(
											_http_node + 'fetchUnitRevisions/' + m_this.attr( 'data-id' ) + '/' + m_this.attr( 'data-chassisno' ),
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
								
								CHECKVALIDATEDFORMS();
								
								SMSMESSAGES();
								
								HISTORYACTIONS();
								
								$( '#dotc_form .dotc_cp_no' ).each(function(){
									var $this = $( this );
									$this.blur(function(){
										var cp_no = $this.val().replace( /[^0-9]/g, '' );
										cp_no = +cp_no.substring( 0, 2 ) == 63 ? '0' + cp_no.substring( 2 ) : cp_no;
										
										if( cp_no.length == 11 && $( '#dotc_form .dotc_gps_id[data-id=' + $this.attr( 'data-id' ) + ']' ).val().length == 8 ){
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
										cp_no = +cp_no.substring( 0, 2 ) == 63 ? '0' + cp_no.substring( 2 ) : cp_no;
									
										if( cp_no.length == 11 && $this.val().length == 8 ){
											$( '#dotc_form .dotc_test_cp_no[data-id=' + $this.attr( 'data-id' ) + ']' ).show();
										} else{
											$( '#dotc_form .dotc_test_cp_no[data-id=' + $this.attr( 'data-id' ) + ']' ).hide();
										}
									});
								});
								
								$( '#dotc_form .dotc_test_cp_no' ).button().each(function(){
									var $this = $( this );
									
									$this.click(function(){
										$this.parent( 'td' ).parent( 'tr' ).find( '.livestamp' ).hide().siblings( 'img' ).show();
									
										var _details = new FormData();
									
										_details.append( 'image', $( "#dotc_webcam_captured img" ).attr( 'src' ) );
										_details.append( 'case_no', _this.attr( 'data-case' ) );
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
										
										$.ajax({
											url : _http_node + 'DOTC_AssignDEVICEID',
											type : 'POST',
											data : _details,
											async : true,
											cache : false,
											contentType : false,
											processData : false,
											success : function( data ){
												alert( data.message );
												
												$( '#refresh_last_seen' ).click();
												
												if( typeof _sms_intervals[$this.attr( 'data-id' )] == 'undefined' ){
													clearInterval( _sms_intervals[$this.attr( 'data-id' )] );
													delete _sms_intervals[$this.attr( 'data-id' )];
												}
												
												if( data.response ){
													$.get(
														_http_node + 'fetchFranchiseRevisionsPaginate/' + _this.attr( 'data-case' ) + '/1/' + paginate.start_row + '/' + paginate.limit,
														function( data ){
															if( data != 'false' ){
																$( '#dotc_revise_history table tbody' ).html( data );
																HISTORYACTIONS();
																paginate.page = 1;
																paginate.start_row = 0;
															}
														}
													);
												
													if( data.reply == 'success' ){
														$this.parent( 'td' ).parent( 'tr' ).find( 'td' ).css({
															'background-color' : 'green',
															'color' : '#fff'
														});
													} else if( data.reply == 'no-change' ){
													
													} else if( data.reply == 'proceeding' ){
														$this.parent( 'td' ).parent( 'tr' ).find( 'td' ).css({
															'background-color' : 'orange',
															'color' : '#ababab'
														});
													
														if( typeof _gps_intervals[$this.attr( 'data-id' )] == 'undefined' ){
															// set interval that checks a json url
															// var current_date = date( 'Y-m-d G:i:s', strtotime( date( 'Y-m-d G:i:s' ) + ' -5 minutes' ) );
															var current_date = moment().subtract( 5, 'm' ).format( 'YYYY-MM-DD HH:mm:ss' );
															
															_gps_intervals[$this.attr( 'data-id' )] = setInterval(function(){
																var cp_no = $( '#dotc_form .dotc_cp_no[data-id=' + $this.attr( 'data-id' ) + ']' ).val().replace( /[^0-9]/g, '' );
																cp_no = +cp_no.substring( 0, 2 ) == 63 ? '0' + cp_no.substring( 2 ) : cp_no;
																
																$.post(
																	_http_node + 'DOTC_SMSMESSAGE',
																	{
																		cp_no : cp_no,
																		gps_id : $( '#dotc_form .dotc_gps_id[data-id=' + $this.attr( 'data-id' ) + ']' ).val(),
																		datetime : current_date
																	}, function( data ){
																		if( data.response ){
																			$this.parent( 'td' ).parent( 'tr' ).find( 'td' ).css({
																				'background-color' : 'green',
																				'color' : '#fff'
																			}).end().find( '.livestamp' ).show().siblings( 'img' ).hide();
																			clearInterval( _gps_intervals[$this.attr( 'data-id' )] );
																			delete _gps_intervals[$this.attr( 'data-id' )];
																			$( '#refresh_last_seen' ).click();
																		}
																	}, 'json'
																);
															}, 2000 );
														}
													} else{
														$this.parent( 'td' ).parent( 'tr' ).find( 'td' ).removeAttr( 'style' );
													}
												}
											}
										});
									
										/*$.post(
											_http_node + 'DOTC_AssignDEVICEID',
											{
												unit_id : $this.attr( 'data-id' ),
												gps_id : $( '.dotc_gps_id[data-id=' + $this.attr( 'data-id' ) + ']' ).val(),
												cp_no : $( '.dotc_cp_no[data-id=' + $this.attr( 'data-id' ) + ']' ).val(),
												image : $( "#dotc_webcam_captured img" ).attr( 'src' ),
												case_no : _this.attr( 'data-case' ),
												encoder_id : $( 'input[name=user_details_id]' ).val(),
												fname : $( '#dotc_data input[name=fname]' ).val(),
												lname : $( '#dotc_data input[name=lname]' ).val(),
												title : $( '#dotc_data input[name=title]' ).val(),
												id_no : $( '#dotc_data input[name=id_no]' ).val(),
												contact_no : $( '#dotc_data input[name=contact_no]' ).val(),
												email : $( '#dotc_data input[name=email]' ).val()
											}, function( data ){
												alert( data.message );
												_LIVESTAMP( _this.attr( 'data-case' ) );
												if( data.response ){
													if( data.reply == 'success' ){
														$this.parent( 'td' ).parent( 'tr' ).find( 'td' ).css({
															'background-color' : 'green',
															'color' : '#fff'
														});
													} else if( data.reply == 'proceeding' ){
														$this.parent( 'td' ).parent( 'tr' ).find( 'td' ).css({
															'background-color' : 'orange',
															'color' : '#ababab'
														});
													
														if( typeof _gps_intervals[$this.attr( 'data-id' )] == 'undefined' ){
															// set interval that checks a json url
															// var current_date = date( 'Y-m-d G:i:s', strtotime( date( 'Y-m-d G:i:s' ) + ' -5 minutes' ) );
															var current_date = moment().subtract( 5, 'm' ).format( 'YYYY-MM-DD HH:mm:ss' );
															
															_gps_intervals[$this.attr( 'data-id' )] = setInterval(function(){
																var cp_no = $( '#dotc_form .dotc_cp_no[data-id=' + $this.attr( 'data-id' ) + ']' ).val().replace( /[^0-9]/g, '' );
																cp_no = +cp_no.substring( 0, 2 ) == 63 ? '0' + cp_no.substring( 2 ) : cp_no;
																
																$.post(
																	_http_node + 'DOTC_SMSMESSAGE',
																	{
																		cp_no : cp_no,
																		gps_id : $( '#dotc_form .dotc_gps_id[data-id=' + $this.attr( 'data-id' ) + ']' ).val(),
																		datetime : current_date
																	}, function( data ){
																		if( data.response ){
																			$this.parent( 'td' ).parent( 'tr' ).find( 'td' ).css({
																				'background-color' : 'green',
																				'color' : '#fff'
																			});
																			clearInterval( _gps_intervals[$this.attr( 'data-id' )] );
																			delete _gps_intervals[$this.attr( 'data-id' )];
																		}
																	}, 'json'
																);
															}, 2000 );
														}
													} else{
														$this.parent( 'td' ).parent( 'tr' ).find( 'td' ).removeAttr( 'style' );
													}
												}
											}, 'json'
										);*/
									});
								});
								
								$( '#dotc_form .dotc_test_device' ).button().each(function(){
									var $this = $( this );
									
									$this.click(function(){
										
									});
								});
								
								$( '#dotc_close' ).button().click(function(){
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
												alert( data.message );
											} else{
												window.location = 'actions.php?action=download_pdf_qr&case_no=' + _this.attr( 'data-case' )
											}
										}
									);
								});
								
								$( '#dotc_save_changes' ).button().click(function(){
									_loader.css({
										top : $( document ).scrollTop() + 'px'
									}).show();
									$( 'body' ).css({
										overflow : 'hidden'
									});
									$( '.dotc_gps_id' ).attr( 'disabled', false );
									/*var _details = {
										id : {},
										rep_details : {
											fname : $( '#dotc_rep_details input[name=rep_fname]' ).val(),
											lname : $( '#dotc_rep_details input[name=rep_lname]' ).val(),
											title : $( '#dotc_rep_details input[name=rep_title]' ).val(),
											contact_no : $( '#dotc_rep_details input[name=rep_contact_no]' ).val(),
											id_no : $( '#dotc_rep_details input[name=rep_id_no]' ).val(),
											email : $( '#dotc_rep_details input[name=rep_email]' ).val(),
											case_no : _this.attr( 'data-case' ),
											encoder_id : $( 'input[name=user_details_id]' ).val()
										},
										cp_nos : {},
										case_no : _this.attr( 'data-case' ),
										image : $( "#dotc_webcam_captured img" ).attr( 'src' )
									};
									$( '#dotc_form .dotc_gps_id' ).each(function( i ){
										_details.id['id' + $( this ).attr( 'data-id' )] = $( this ).val();
										_details.cp_nos['id' + $( this ).attr( 'data-id' )] = $( '#dotc_form input[name="cp_no[' + $( this ).attr( 'data-id' ) + ']"]' ).val();
									});*/
									
									var _details = new FormData( $( '#dotc_data' )[0] );
									
									_details.append( 'image', $( "#dotc_webcam_captured img" ).attr( 'src' ) );
									_details.append( 'case_no', _this.attr( 'data-case' ) );
									_details.append( 'encoder_id', $( 'input[name=user_details_id]' ).val() );
									_details.append( 'franchise_email', $( '#dotc_data input[name=franchise_email]' ).val() );
									
									$.ajax({
										url : _http_node + 'processGPS_IDS',
										type : 'POST',
										data : _details,
										async : true,
										cache : false,
										contentType : false,
										processData : false,
										success : function( data ){
											_loader.hide();
											$( 'body' ).css({
												overflow : 'visible'
											});
											$( '.dotc_gps_id' ).attr( 'disabled', true );
										
											if( !data.response ){
												alert( data.message );
											} else{
												if( data.errors == 0 ){
													alert( 'Changes has been saved' );
													_this.click();
												} else{
													alert( 'There are ' + data.errors + ' error(s) during the save process' );
												}
											}
										}
									});
								});
							});
								/*$( '#dotc_operator_details' ).html( data ).dialog({
									width : '98%',
									height : ( $( document ).height() - 10 ),
									draggable : false,
									resizable : false,
									modal : true,
									buttons : {
										'Print PDF' : function(){
											// check the server first
											$.post(
												'actions.php?action=check_pdf_qr',
												{
													case_no : _this.attr( 'data-case' )
												}, function( data ){
													if( data.response ){
														alert( data.message );
													} else{
														window.location = 'actions.php?action=download_pdf_qr&case_no=' + _this.attr( 'data-case' )
													}
												}
											);
										},
										'Save Changes' : function(){
											var _details = {
												id : {},
												rep_details : {
													fname : $( '#dotc_rep_details input[name=rep_fname]' ).val(),
													lname : $( '#dotc_rep_details input[name=rep_lname]' ).val(),
													title : $( '#dotc_rep_details input[name=rep_title]' ).val(),
													contact_no : $( '#dotc_rep_details input[name=rep_contact_no]' ).val(),
													id_no : $( '#dotc_rep_details input[name=rep_id_no]' ).val(),
													email : $( '#dotc_rep_details input[name=rep_email]' ).val(),
													case_no : _this.attr( 'data-case' ),
													encoder_id : $( 'input[name=user_details_id]' ).val()
												},
												cp_nos : {}
											};
											$( '#dotc_optr_details .dotc_gps_id' ).each(function( i ){
												_details.id['id' + $( this ).attr( 'data-id' )] = $( this ).val();
												_details.cp_nos['id' + $( this ).attr( 'data-id' )] = $( '#dotc_optr_details input[name="cp_no[' + $( this ).attr( 'data-id' ) + ']"]' ).val();
											});
											
											$.post(
												'http://localhost:3000/processGPS_IDS',
												_details,
												function( data ){
													if( !data.response ){
														alert( data.message );
													} else{
														if( data.errors == 0 ){
															alert( 'Changes has been saved' );
															_this.click();
														} else{
															alert( 'There are ' + data.errors + ' error(s) during the save process' );
														}
													}
												},
												'json'
											);
										}
									}
								});*/
						}
					);
				});
			});
		}
	}
	
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
			
				if( _operator_json ){
					_operator_json.abort();
				}
				
				_operator_json = $.post(
					_http_node + 'searchFranchise',
					{
						name : $( '#dotc_search input[name=operator_name]' ).val(),
						case_no : $( '#dotc_search input[name=case_no]' ).val(),
						plate_no : $( '#dotc_search input[name=plate_no]' ).val(),
						chassis_no : $( '#dotc_search input[name=chassis_no]' ).val()
					}, function( data ){
						$( '#dotc_search_result ul' ).html( data );
						
						franchiseSelect();
					}
				);
			} else{
				$( '#dotc_search_result ul' ).html( '' );
			}
		});
	});

	/*$( '#dotc_search input[name=operator_name]' ).keyup(function(){
		var _this = $( this );
		
		if(
			_this.val().length > 2
			|| $( '#dotc_search input[name=case_no]' ).val().length > 2
		){
			$( '#dotc_search_result ul' ).html( '' );
		
			if( _operator_json ){
				_operator_json.abort();
			}
			
			_operator_json = $.post(
				_http_node + 'searchFranchise',
				{
					query : _this.val(),
					case_no : $( '#dotc_search input[name=case_no]' ).val()
				}, function( data ){
					$( '#dotc_search_result ul' ).html( data );
					
					franchiseSelect();
				}
			);
		}
	});
	
	$( '#dotc_search input[name=case_no]' ).keyup(function(){
		var _this = $( this );
		
		if(
			_this.val().length > 2
			|| $( '#dotc_search input[name=operator_name]' ).val().length > 2
		){
			$( '#dotc_search_result ul' ).html( '' );
		
			if( _operator_json ){
				_operator_json.abort();
			}
			
			_operator_json = $.post(
				_http_node + 'searchFranchise',
				{
					query : $( '#dotc_search input[name=operator_name]' ).val(),
					case_no : _this.val()
				}, function( data ){
					$( '#dotc_search_result ul' ).html( data );
					
					franchiseSelect();
				}
			);
		}
	});*/
});