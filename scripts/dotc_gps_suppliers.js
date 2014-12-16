$(function(){
	var added = {
		personnel : 0,
		device : 0
	};
	var errors = 0;
	var _loader = $( '#ajax_loader' ).css({
		'z-index' : 999
	});
	var ajaxEdit = false;
	var ajaxList = false;

	function PERSONNELACTIONS(){
		$( '#dotc_gps_supplier_form #add_personnel' ).unbind( 'click' ).button().click(function(){
			var template = Handlebars.compile( $( '#gps_personnel' ).html() );
			$( '#personnels' ).before( template({
				ctr : added.personnel
			}) );
			added.personnel++;
			
			$( '.remove_personnel' ).each(function(){
				var $this = $( this );
				$this.unbind( 'click' ).button().click(function(){
					$( 'tr[data-personnel=' + $this.attr( 'data-personnel' ) + ']' ).fadeOut(500, function(){
						$this.unbind( 'click' );
						$( 'tr[data-personnel=' + $this.attr( 'data-personnel' ) + ']' ).remove();
					});
				});
			});
		});
		
		$( '.remove_personnel' ).each(function(){
			var $this = $( this );
			$this.unbind( 'click' ).button().click(function(){
				$( 'tr[data-personnel=' + $this.attr( 'data-personnel' ) + ']' ).fadeOut(500, function(){
					$this.unbind( 'click' );
					$( 'tr[data-personnel=' + $this.attr( 'data-personnel' ) + ']' ).remove();
				});
			});
		});
		
		$( '.remove_personnel' ).each(function(){
			var $this = $( this );
			
			$this.button().click(function(){
				$.post(
					ServerConfig.BMS_Node + 'gps_SupplierDELETEPERSONNEL',
					{
						id : $this.attr( 'data-personnel-id' )
					},
					function( data ){
						if( data.response ){
							$( 'tr[data-personnel-id=' + $this.attr( 'data-personnel-id' ) + ']' ).fadeOut( 500, function(){
								$( 'tr[data-personnel-id=' + $this.attr( 'data-personnel-id' ) + ']' ).remove();
							});
						}
					}
				);
			});
		});
	}
	
	function DEVICEACTIONS(){
		$( '#dotc_gps_supplier_form #add_device' ).unbind( 'click' ).button().click(function(){
			var template = Handlebars.compile( $( '#gps_device' ).html() );
			$( '#devices' ).before( template({
				ctr : added.device
			}) );
			added.device++;
			
			$( '.remove_device' ).each(function(){
				var $this = $( this );
				$this.unbind( 'click' ).button().click(function(){
					$( 'tr[data-device=' + $this.attr( 'data-device' ) + ']' ).fadeOut(500, function(){
						$this.unbind( 'click' );
						$( 'tr[data-device=' + $this.attr( 'data-device' ) + ']' ).remove();
					});
				});
			});
		});
		
		$( '.remove_device' ).each(function(){
			var $this = $( this );
			$this.unbind( 'click' ).button().click(function(){
				$( 'tr[data-device=' + $this.attr( 'data-device' ) + ']' ).fadeOut(500, function(){
					$this.unbind( 'click' );
					$( 'tr[data-device=' + $this.attr( 'data-device' ) + ']' ).remove();
				});
			});
		});
		
		$( '.remove_device' ).each(function(){
			var $this = $( this );
			
			$this.button().click(function(){
				$.post(
					ServerConfig.BMS_Node + 'gps_SupplierDELETEDEVICE',
					{
						id : $this.attr( 'data-device-id' )
					},
					function( data ){
						if( data.response ){
							$( 'tr[data-device-id=' + $this.attr( 'data-device-id' ) + ']' ).fadeOut( 500, function(){
								$( 'tr[data-device-id=' + $this.attr( 'data-device-id' ) + ']' ).remove();
							});
						} else{
							// alert( data.message );
							$.jGrowl( data.message, { life: 30000 });
						}
					}
				);
			});
		});
	}
	
	function GPS_SAVE_CHANGES(){
		$( '#dotc_gps_supplier_form #gps_save_changes' ).unbind( 'click' ).button().click(function(){
			errors = 0;
			
			async.series([
				function( callback ){
					$( '#dotc_gps_supplier_form .validate' ).each(function(){
						if( !VALIDATE_ITEM( $( this ) ) ){
							errors++;
						}
					});
					
					callback();
				}
			], function( err, results ){
				if( errors > 0 ){
					// alert( ( errors > 1 ? 'There are ' + errors + ' errors in this form' : 'Theres an error in this form' ) );
					$.jGrowl( ( errors > 1 ? 'There are ' + errors + ' errors in this form' : 'Theres an error in this form' ), { life: 30000 });
				} else{
					var _FormData = new FormData();
					
					_FormData.append( 'id', $( '#dotc_gps_supplier_form input[name=id]' ).val() );
					_FormData.append( 'companyname', $( '#dotc_gps_supplier_form input[name=company_name]' ).val() );
					_FormData.append( 'address', $( '#dotc_gps_supplier_form textarea[name=address]' ).val() );
					_FormData.append( 'contact_info', $( '#dotc_gps_supplier_form textarea[name=contact_info]' ).val() );
					_FormData.append( 'user_id', $( 'input[name=user_id]' ).val() );
					
					var personnels = []
					$( '#dotc_gps_supplier_form .personnel_id' ).each(function( keys, values ){
						personnels.push({
							personnel_id : $( this ).val(),
							personnel_fullname : $( '#dotc_gps_supplier_form .personnel_fullname:eq(' + keys + ')' ).val(),
							personnel_contact_info : $( '#dotc_gps_supplier_form .personnel_contact_info:eq(' + keys + ')' ).val()
						});
					});
					_FormData.append( 'personnel', JSON.stringify( personnels ) );
					var device_to_upload = [];
					var devices = [];
					$( '#dotc_gps_supplier_form .device_id' ).each(function( keys, values ){
						devices.push({
							device_id : $( this ).val(),
							device_name_model : $( '#dotc_gps_supplier_form .device_name_model:eq(' + keys + ')' ).val(),
							device_price : $( '#dotc_gps_supplier_form .device_price:eq(' + keys + ')' ).val(),
							device_description : $( '#dotc_gps_supplier_form .device_description:eq(' + keys + ')' ).val(),
							device_picture : $( '#dotc_gps_supplier_form .device_picture:eq(' + keys + ')' ).get(0).files[0]
						});
						
						// device_to_upload.push( $( '#dotc_gps_supplier_form .device_picture:eq(' + keys + ')' ).get(0).files[0] );
						_FormData.append( 'file' + keys, $( '#dotc_gps_supplier_form .device_picture:eq(' + keys + ')' ).get(0).files[0] );
					});
					_FormData.append( 'device', JSON.stringify( devices ) );
					
					_loader.show();
					
					$.ajax(
						{
							url : ServerConfig.BMS_Node + 'gps_SuppliersSAVE',
							type : 'POST',
							data : _FormData,
							async : true,
							cache : false,
							contentType : false,
							processData : false,
							success : function( data ){
								if( data.response ){
									setTimeout(function(){
										_loader.hide();
										GETDETAILS( data.return_id );
										SUPPLIERLIST();
									}, 3000 );
								} else{
									_loader.hide();
								}
							}
						}
					);
				}
			});
		});
	}
	
	function VALIDATE_ITEM( _item ){
		var _validate = _item.attr( 'data-validate' );
		var _is_valid = false;
		
		switch( _validate.toLowerCase() ){
			case 'length':
				if( _item.val().length > 0 ){
					_is_valid = true;
				}
			break;
			case 'number':
				if( /^\$?[0-9]*[0-9]\.?[0-9]{0,2}$/i.test( _item.val() ) ){
					_is_valid = true;
				}
			break;
		}
		
		if( _is_valid > 0 ){
			_item.css({
				backgroundColor : 'green',
				color : '#fff'
			});
		} else{
			_item.css({
				backgroundColor : 'red',
				color : '#fff'
			});
		}
		
		return _is_valid;
	}
	
	function VALIDATE(){
		$( '#dotc_gps_supplier_form .validate' ).each(function(){
			var $this = $( this );
			
			$this.unbind( 'blur' ).blur(function(){
				VALIDATE_ITEM( $this );
			});
		});
	}
	
	var page = 1;
	
	function GETDETAILS( id ){
		if( ajaxEdit ){
			ajaxEdit.abort();
		}
	
		ajaxEdit = $.get(
			ServerConfig.BMS_Node + 'gps_SuppliersFETCH/' + id,
			function( data ){
				$( '#gps_supplier_form, #dotc_gps_supplier_form' ).html( data );
				PERSONNELACTIONS();
				DEVICEACTIONS();
				GPS_SAVE_CHANGES();
			}
		);
	}
	
	function SUPPLIERLIST(){
		if( ajaxList ){
			ajaxList.abort();
		}
	
		ajaxList = $.get(
			ServerConfig.BMS_Node + 'gps_Suppliers/' + page,
			function( data ){
				$( '#dotc_gps_suppliers_list' ).html( data );
				
				$( '.edit_supplier' ).each(function(){
					$( this ).unbind( 'click' ).button().click(function(){
						GETDETAILS( $( this ).attr( 'data-id' ) );
					});
				});
			}
		);
	}
	
	function NEWPAGE(){
		$.get(
			ServerConfig.BMS_Node + 'gps_SuppliersForm',
			function( data ){
				$( '#gps_supplier_form, #dotc_gps_supplier_form' ).html( data );
				PERSONNELACTIONS();
				DEVICEACTIONS();
				// VALIDATE();
				SUPPLIERLIST();
				GPS_SAVE_CHANGES();
			}
		);
	}
	
	NEWPAGE();
});