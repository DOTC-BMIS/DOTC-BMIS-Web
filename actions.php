<?php
require_once( 'libs/class.DOTC_BMS.php' );
$dotc = new DOTC_BMS;

$data = array(
	'response' => false,
	'message' => '',
	'is_ajax' => false,
	'redirect_to' => isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : ''
);

header( 'Access-Control-Allow-Origin: *' );

try{
	switch( $_GET['action'] ){
		case 'login':
			$data['is_ajax'] = true;
			$data['login_details'] = $dotc->login( $_POST['username'], $_POST['password'] );
			$dotc->userSession( $data['login_details'][0] );
			$data['redirect'] = $dotc->firstUrl();
			$data['response'] = true;
		break;
		case 'generate_registration_report':
			$dotc->generateRegistrationReport( $_GET );
		break;
		case 'create_qr':
			$e = explode( ',', $_POST['ids'] );
			if(
				is_array( $e )
				&& count( $e ) > 0
			){
				foreach( $e AS $keys => $values ):
					$dotc->createQRCode( $values );
				endforeach;
			}
			exit;
		break;
		# new
		case 'create_qr_verification_vehicle':
			if(
				isset( $_POST['vehicle_id'] )
				&& is_numeric( $_POST['vehicle_id'] )
				&& $_POST['vehicle_id'] > 0
			){
				$dotc->createQRVerificationByVehicle( $_POST['vehicle_id'] );
			}
			exit;
		break;
		# end
		case 'create_qr_case_no':
			if(
				isset( $_POST['case_no'] )
				&& strlen( trim( $_POST['case_no'] ) ) > 0
			){
				$dotc->createQRCodeByCaseNo( $_POST['case_no'] );
			}
			exit;
		break;
		case 'check_pdf_qr':
			$data['is_ajax'] = true;
			$dotc->check_PDF_QRCODES( $_POST['case_no'] );
			$data['response'] = false;
		break;
		case 'download_pdf_qr':
			$dotc->downloadPDF_QRCODES( $_GET['case_no'] );
		break;
		case 'download_revise_qr_codes_latest':
			$dotc->downloadReviseQrCodesLatest( $_GET['case_no'] );
		break;
		case 'download_revise_verification_latest':
			$dotc->downloadReviseVerificationLatest( $_GET['case_no'] );
		break;
		case 'download_revise_qr_codes':
			$dotc->downloadReviseQrCodes( $_GET['code_id'] );
		break;
		case 'download_revise_verification':
			$dotc->downloadReviseVerification( $_GET['code_id'] );
		break;
		case 'logout':
			session_destroy();
			$data['redirect_to'] = 'login.php';
		break;
		case 'export_data':
			$dotc->exportTable_V2( $_GET['id'] );
		break;
		case 'check_vehicle_zip_archive':
			$data['is_ajax'] = true;
			$data['response'] = $dotc->CheckVehicleZipArchive( explode( ',', $_POST['ids'] ) );
		break;
		case 'create_vehicle_zip_archive':
			$dotc->CreateVehicleZipArchive( explode( ',', $_GET['ids'] ) );
		break;
	}
} catch( Exception $e ){
	if( $data['is_ajax'] ){
		$data['message'] = $e->getMessage();
	} else{
		$_SESSION['errmsg'] = $e->getMessage();
	}
}

if( $data['is_ajax'] ){
	header( 'Content-Type: application/x-json' );
	echo json_encode( $data );
	exit;
} else{
	if( strlen( trim( $data['redirect_to'] ) ) > 0 ){
		header( 'location: ' . $data['redirect_to'] . '' );
	}
	exit;
}
?>