<?php
require_once( 'class.Connection.php' );
require_once( 'phpqrcode/qrlib.php' );
require_once( 'MPDF57/mpdf.php' );
require_once( 'export-xls.class/export-xls.class.php' );

class DOTC_BMS extends PDO_Connection{
	protected $qrcode_dir = 'images/qrcodes/';
	protected $pdf_dir = 'files/pdf/';
	protected $zip_dir = 'files/zip/';
	protected $pdf_c;

	public function __construct(){
		$this->DOTC_BMS();
	}
	
	public function DOTC_BMS(){
		parent::__construct();
	}
	
	public function userSession( $details = array() ){
		$_SESSION['user_details'] = $details;
	}
	
	public function redirect( $url = '' ){
		header( 'location: ' . $url );
		exit;
	}
	
	public function checkSession(){
		return isset( $_SESSION['user_details'] ) && is_array( $_SESSION['user_details'] ) && isset( $_SESSION['user_details']['id'] );
	}
	
	public function currentURL(){
		$url  = 'http' . ( isset( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ? 's' : '' );
		$url .= '://' . $_SERVER["SERVER_NAME"];
		$url .= in_array( $_SERVER['SERVER_PORT'], array('80', '443') ) ? '' : ':' . $_SERVER['SERVER_PORT'];
		$uri = $_SERVER['REQUEST_URI'];
		return array(
			'domain' => $url,
			'uri' => $uri,
			'full' => $url . $uri
		);
	}
	
	public function checkIfUserIsAllowedOnThisPage(){
		$current_page = $this->currentURL();
		
		$allowed = false;
		
		$pages = $this->fetchUserMenu();
		
		foreach( $pages AS $keys => $values ):
			if( strpos( $current_page['full'], $values['link'] ) > -1 ){
				$allowed = true;
				break;
			}
		endforeach;
		
		return $allowed;
	}
	
	public function firstUrl(){
		$pages = $this->fetchUserMenu();
		
		return is_array( $pages ) && count( $pages ) > 0 ? $pages[0]['link'] : 'index.php';
	}
	
	public function fetchUserTypes(){
		$sql = $this->conn->prepare(
			"
				SELECT
					*
				FROM
					dotc_user_types
			"
		);
		$sql->execute();
		
		$rows = $sql->fetchAll( PDO::FETCH_ASSOC );
		
		return $rows;
	}
	
	public function fetchUserMenu(){
		$sql = $this->conn->prepare(
			"
				SELECT
					*
				FROM
					dotc_user_type_menu
				WHERE
					user_type = :user_type
			"
		);
		
		$sql->bindValue( ':user_type', $_SESSION['user_details']['user_type'], PDO::PARAM_INT );
		$sql->execute();
		
		$rows = $sql->fetchAll( PDO::FETCH_ASSOC );
		
		return $rows;
	}
	
	public function login( $username = '', $password = '' ){
		$sql = $this->conn->prepare(
			"
				SELECT
					`dotc_users`.`id`, `dotc_users`.`fname`, `dotc_users`.`lname`, `dotc_users`.`mname`, CONCAT( `dotc_users`.`lname`, ', ', `dotc_users`.`fname`, ' ', `dotc_users`.`mname` ) AS fullname, `dotc_users`.`username`, `dotc_users`.`position`, `dotc_users`.`id_number`, `dotc_users`.`contact_number`, `dotc_users`.`email`, `dotc_users`.`date_added`, `dotc_users`.`date_modified`, `dotc_users`.`user_type`, `dotc_user_types`.`name` AS type_name
				FROM
					{$this->server_details['prefix']}users
					INNER JOIN
					{$this->server_details['prefix']}user_types
					ON
					(
						`dotc_users`.`user_type` = `dotc_user_types`.`id`
					)
				WHERE
					`dotc_users`.`username` LIKE :username
					AND
					`dotc_users`.`password` LIKE :password
			"
		);
		
		$sql->bindValue( ':username', $username, PDO::PARAM_STR );
		$sql->bindValue( ':password', $password, PDO::PARAM_STR );
		$sql->execute();
		
		$row = $sql->fetchAll( PDO::FETCH_ASSOC );
		
		if( count( $row ) == 0 ){
			throw new Exception( 'Wrong Login Credentials' );
		}
		
		return $row;
	}
	
	public function createQRCodeByCaseNo( $case_no = '' ){
		$sql = $this->conn->prepare(
			"
				SELECT
					vehicle_id, case_no, plate_no, name, gps_id
				FROM
					dotc_unit_frnc
				WHERE
					case_no LIKE :case_no
			"
		);
		
		$sql->bindValue( ':case_no', $case_no, PDO::PARAM_STR );
		$sql->execute();
		
		$rows = $sql->fetchAll( PDO::FETCH_ASSOC );
		
		foreach( $rows AS $keys => $values ):
			$this->createQRCode( $values['vehicle_id'] );
		endforeach;
		
		$this->savePDF_QRCODES( $case_no );
	}
	
	public function createQRVerificationByVehicle( $vehicle_id = 0 ){
		$this->createQRCode( $vehicle_id );
		
		$this->saveVechiclePDF_QRCODE( $vehicle_id );
	}
	
	public function saveVechiclePDF_QRCODE( $vehicle_id = 0 ){
		$sql = $this->conn->prepare(
			"
				SELECT
					vehicle_id, case_no, plate_no, name, gps_id
				FROM
					dotc_unit_frnc
				WHERE
					vehicle_id LIKE :vehicle_id
			"
		);
		
		$sql->bindValue( ':vehicle_id', $vehicle_id, PDO::PARAM_STR );
		$sql->execute();
		
		$rows = $sql->fetchAll( PDO::FETCH_ASSOC );
		
		$html = '';
		
		foreach( $rows AS $keys => $values ):
			$html .= "<div class='left dotc_pdf_qrcode'>
				<div class='left dotc_pdf_qrcode-img'>
					<img src='{$this->qrcode_dir}vehicle-{$values['vehicle_id']}.png' width='123px' height='123px' />
				</div>
				<div class='left dotc_pdf_qrcode-data'>
					<table cellpadding='0' cellspacing='0' width='100%'>
						<tr>
							<td>
								CASE #
							</td>
							<td>
								{$values['case_no']}
							</td>
						</tr>
						<tr>
							<td>
								PLATE #
							</td>
							<td>
								{$values['plate_no']}
							</td>
						</tr>
						<tr>
							<td>
								COMPANY
							</td>
							<td>
								{$values['name']}
							</td>
						</tr>
						<tr>
							<td>
								GPS ID
							</td>
							<td>
								" . implode( '-', str_split( $values['gps_id'], 7 ) ) . "
							</td>
						</tr>
					</table>
				</div><div class='clearb'></div>
			</div>";
			
			if( $keys > 0 && ( ( $keys + 1 ) % 2 ) == 0 ){
				$html .= "<div class='clearb'></div>";
			}
		endforeach;
		
		$frnc_details = $this->latestFranchiseCode( $rows[0]['case_no'] );
		
		$this->createVehicleVerficationPDF( $vehicle_id, $frnc_details, $html );
	
		# $this->PDF_File( $html, $this->pdf_dir . 'VEHICLE-QRCODE' . $vehicle_id, 'F' );
	}
	
	public function createVehicleVerficationPDF( $vehicle_id = 0, $frnc_details = array(), $to_array_html = '' ){
		$sql = $this->conn->prepare(
			"
				SELECT
					*
				FROM
					`dotc_unit_frnc`
				WHERE
					vehicle_id LIKE :vehicle_id
			"
		);
		
		$sql->bindValue( ':vehicle_id', $vehicle_id, PDO::PARAM_STR );
		$sql->execute();
		
		$rows = $sql->fetchAll( PDO::FETCH_ASSOC );
		
		if(
			is_array( $rows )
			&& count( $rows ) > 0
		){
			$html = "<h4 class='dotc_verification_header'>
				Republic of the Philippines<br />Department of Transportation & Communication<br />LAND TRANSPORTATION FRANCHISING & REGULATORY BOARD<br />East Avenue, Quezon City<br />Tel. Nos. 426-2505, 426-2491 & 426-2513
			</h4><br /><br /><br /><h4 style='text-align: center;'><span style='text-decoration: underline;'>FRANCHISE VERIFICATION</span></h4><br /><br /><br />";
			
			$html .= "<table class='dotc_table' cellpadding=0 cellspacing=0 width='100%'>";
			$html .= "<tr>
				<td colspan='5'>Operator : {$rows[0]['name']}</td><td colspan='4'>Case No. : {$rows[0]['case_no']}</td>
			</tr>";
			$html .= "<tr>
				<td colspan='5'>Bus. Address : {$rows[0]['name']}</td><td colspan='4'>Deno. :</td>
			</tr>";
			$html .= "<tr>
				<td colspan='5'>Comm. Name : {$rows[0]['com_name']}</td><td colspan='4'>No. of Autho. Units : {$rows[0]['nau']}</td>
			</tr>";
			$html .= "<tr>
				<td colspan='5'>Date Granted : " . date( 'F j, Y', strtotime( $rows[0]['dg'] ) ) . "</td><td colspan='4'>Birth Date : </td>
			</tr>";
			$html .= "<tr>
				<td colspan='5'>Expiry Date : " . date( 'F j, Y', strtotime( $rows[0]['de'] ) ) . "</td><td colspan='4'>T I N : {$rows[0]['tin']}</td>
			</tr>";
			$html .= "<tr>
				<td colspan='9'>Route Name : {$rows[0]['route']}</td>
			</tr>";
			$html .= "<tr>
				<td colspan='9'>Remarks : {$rows[0]['remarks']}</td>
			</tr>";
			$html .= "<tr>
				<td colspan='9'>Status : {$rows[0]['status']}</td>
			</tr>";
			$html .= "<tr>
				<td colspan='9'>Tel Number : {$rows[0]['tel_no']}</td>
			</tr>";
			$html .= "<tr>
				<td colspan='9'>Cel Number : {$rows[0]['cel_no']}</td>
			</tr>";
			$html .= "<tr>
				<td colspan='9'>Email : {$rows[0]['email']}</td>
			</tr>";
			$html .= "
				<tr>
					<td colspan='9'>&nbsp;</td>
				</tr>
				<tr>
					<td colspan='9'>&nbsp;</td>
				</tr>
				<tr>
					<td colspan='9' align='center'><h3 style='text-align: center;'>I D E N T I T Y&nbsp;&nbsp;O F&nbsp;&nbsp;U N I T S</h3></td>
			</tr>";
			$html .= "
			<tr>
				<td>
					M A K E
				</td>
				<td>
					MOTOR NUMBER
				</td>
				<td>
					CHASSIS NUMBER
				</td>
				<td>
					PLATE#
				</td>
				<td>
					YM
				</td>
				<td>
					YC
				</td>
				<td>
					REMARKS
				</td>
				<td>
					GPS ID
				</td>
				<td>
					PHONE NO
				</td>
			</tr>";
			
			foreach( $rows AS $keys => $values ):
				$html .= "<tr>
					<td>
						{$values['make']}
					</td>
					<td>
						{$values['motor_no']}
					</td>
					<td>
						{$values['chassis_no']}
					</td>
					<td>
						{$values['plate_no']}
					</td>
					<td>
						{$values['year_model']}
					</td>
					<td>
						
					</td>
					<td>
						
					</td>
					<td>
						" . implode( '-', str_split( $values['gps_id'], 7 ) ) . "
					</td>
					<td>
						{$values['cp_no']}
					</td>
				</tr>";
			endforeach;
			
			$_f_details = $this->fetchReviseHistoryView( $frnc_details['id'] );
			
			$html .= "
				<tr>
					<td colspan='9'>&nbsp;</td>
				</tr>
				<tr>
					<td colspan='9'>&nbsp;</td>
				</tr>
				<tr>
					<td colspan='2'>
						A u t h o r i z e d&nbsp;&nbsp;b y : 
					</td>
					<td colspan='4' style='border-bottom: 1px solid #000;'>
						{$_f_details[0]['fname']} {$_f_details[0]['lname']}
					</td>
					<td>
						Date : 
					</td>
					<td colspan='2' style='border-bottom: 1px solid #000;'>
						
					</td>
				</tr>
				<tr>
					<td colspan='2'>
						V e r i f i e d&nbsp;&nbsp;b y : 
					</td>
					<td colspan='4' style='border-bottom: 1px solid #000;'>
						{$_f_details[0]['encoder_fname']} {$_f_details[0]['encoder_lname']}
					</td>
					<td>
						Date : 
					</td>
					<td colspan='2' style='border-bottom: 1px solid #000;'>
						
					</td>
				</tr>
				<tr>
					<td colspan='2'>
						A p p r o v e d&nbsp;&nbsp;b y : 
					</td>
					<td colspan='4' style='border-bottom: 1px solid #000;'>
						
					</td>
					<td>
						Date : 
					</td>
					<td colspan='2' style='border-bottom: 1px solid #000;'>
						
					</td>
				</tr>
				<tr>
					<td colspan='2'></td><td colspan='4' align='center'>NIDA P. QUIBIC<br />Chief, MID</td>
				</tr>
			";
			
			$html .= "</table>";
			
			# $this->PDF_File( $html, $this->pdf_dir . 'VEHICLE-VERIFICATION-' . $vehicle_id, 'F', false );
			
			# get latest filename
			$sql = $this->conn->prepare(
				"
					SELECT
						pdf_filename
					FROM
						`dotc_pub_unit_revise_history`
					WHERE
						pub_unit_id = :vehicle_id
					ORDER BY
						id DESC
					LIMIT
						0, 1
				"
			);
			
			$sql->bindValue( ':vehicle_id', $vehicle_id, PDO::PARAM_INT );
			$sql->execute();
			
			$rows = $sql->fetchAll( PDO::FETCH_ASSOC );
			
			$this->PDF_File_V2(
				array( $html, $to_array_html ),
				$this->pdf_dir . rtrim( $rows[0]['pdf_filename'], '.pdf' ),
				'F',
				false
			);
			# create latest pdf
			$this->PDF_File_V2(
				array( $html, $to_array_html ),
				$this->pdf_dir . 'VEHICLE-QRCODE-VERIFICATION-' . $vehicle_id,
				'F',
				false
			);
		}
	}
	
	public function createQRCode( $unit_id = 0 ){
		$sql = $this->conn->prepare(
			"
				SELECT
					case_no, plate_no, name, gps_id
				FROM
					dotc_unit_frnc
				WHERE
					vehicle_id = :unit_id
			"
		);
		
		$sql->bindValue( ':unit_id', $unit_id, PDO::PARAM_INT );
		$sql->execute();
		
		$row = $sql->fetchAll( PDO::FETCH_ASSOC );
		
		if(
			is_array( $row )
			&& count( $row ) > 0
		){
			# lets create the qrcode
			$tempDir = $this->qrcode_dir;
			
			# we are building raw data
			
			$codeContents .= "CASENO:{$row[0]['case_no']}\n";
			$codeContents .= "PLATENO:{$row[0]['plate_no']}\n";
			$codeContents .= "COMPANY:{$row[0]['name']}\n";
			$codeContents .= "GPSID:" . implode( '-', str_split( $row[0]['gps_id'], 7 ) );
			
			# generate
			QRcode::png( $codeContents, $tempDir . 'vehicle-' . $unit_id . '.png', QR_ECLEVEL_L, 3 ); 
			
			echo 'vehicle-' . $unit_id . '.png' . " has been created\n";
		}
	}
	
	public function check_PDF_QRCODES( $case_no = '' ){
		$sql = $this->conn->prepare(
			"
				SELECT
					vehicle_id, case_no, plate_no, name, gps_id
				FROM
					dotc_unit_frnc
				WHERE
					case_no LIKE :case_no
			"
		);
		
		$sql->bindValue( ':case_no', $case_no, PDO::PARAM_STR );
		$sql->execute();
		
		$rows = $sql->fetchAll( PDO::FETCH_ASSOC );
		
		# check if someone generated some qrcodes
		$ctr = 0;
		foreach( $rows AS $keys => $values ):
			if( file_exists( $this->qrcode_dir . 'vehicle-' . $values['vehicle_id'] . '.png' ) ){
				$ctr++;
			}
		endforeach;
		
		if( $ctr == 0 ){
			throw new Exception( 'You have to generate to qrcodes before downloading the pdf file' );
		}
		
		if( $ctr != count( $rows ) ){
			throw new Exception( 'Some qrcodes are not generated, just click the Generate button to create a random gps id and save the changes' );
		}
	}
	
	public function latestFranchiseCode( $case_no = '' ){
		$sql = $this->conn->prepare(
			"
				SELECT
					id, case_no, qr_code_filename, date_registered
				FROM
					dotc_frnc_codes
				WHERE
					case_no LIKE :case_no
				ORDER BY
					date_registered DESC
				LIMIT
					0, 1
			"
		);
		
		$sql->bindValue( ':case_no', $case_no, PDO::PARAM_STR );
		$sql->execute();
		
		$rows = $sql->fetchAll( PDO::FETCH_ASSOC );
		
		return isset( $rows[0] ) ? $rows[0] : array();
	}
	
	public function savePDF_QRCODES( $case_no = '' ){
		$this->check_PDF_QRCODES( $case_no );
	
		$sql = $this->conn->prepare(
			"
				SELECT
					vehicle_id, case_no, plate_no, name, gps_id
				FROM
					dotc_unit_frnc
				WHERE
					case_no LIKE :case_no
			"
		);
		
		$sql->bindValue( ':case_no', $case_no, PDO::PARAM_STR );
		$sql->execute();
		
		$rows = $sql->fetchAll( PDO::FETCH_ASSOC );
		
		$html = '';
		
		foreach( $rows AS $keys => $values ):
			$html .= "<div class='left dotc_pdf_qrcode'>
				<div class='left dotc_pdf_qrcode-img'>
					<img src='{$this->qrcode_dir}vehicle-{$values['vehicle_id']}.png' width='123px' height='123px' />
				</div>
				<div class='left dotc_pdf_qrcode-data'>
					<table cellpadding='0' cellspacing='0' width='100%'>
						<tr>
							<td>
								CASE #
							</td>
							<td>
								{$values['case_no']}
							</td>
						</tr>
						<tr>
							<td>
								PLATE #
							</td>
							<td>
								{$values['plate_no']}
							</td>
						</tr>
						<tr>
							<td>
								COMPANY
							</td>
							<td>
								{$values['name']}
							</td>
						</tr>
						<tr>
							<td>
								GPS ID
							</td>
							<td>
								" . implode( '-', str_split( $values['gps_id'], 7 ) ) . "
							</td>
						</tr>
					</table>
				</div><div class='clearb'></div>
			</div>";
			
			if( $keys > 0 && ( ( $keys + 1 ) % 2 ) == 0 ){
				$html .= "<div class='clearb'></div>";
			}
		endforeach;
		
		$frnc_details = $this->latestFranchiseCode( $case_no );
		
		if(
			is_array( $frnc_details )
			&& count( $frnc_details ) > 0
		){
			$this->createFranchiseVerficationPDF( $case_no, $frnc_details );
		
			$this->PDF_File( $html, $this->pdf_dir . rtrim( $frnc_details['qr_code_filename'], '.pdf' ), 'F' );
		}
	}
	
	public function fetchReviseHistoryView( $code_id = 0 ){
		$sql = $this->conn->prepare(
			"
				SELECT
					*
				FROM
					`revise_history_view`
				WHERE
					`code_id` = :code_id
			"
		);
		$sql->bindValue( ':code_id', $code_id, PDO::PARAM_INT );
		$sql->execute();
		
		$rows = $sql->fetchAll( PDO::FETCH_ASSOC );
		
		return $rows;
	}
	
	public function createFranchiseVerficationPDF( $case_no = '', $frnc_details = array() ){
		$sql = $this->conn->prepare(
			"
				SELECT
					*
				FROM
					`dotc_unit_frnc`
				WHERE
					case_no LIKE :case_no
			"
		);
		
		$sql->bindValue( ':case_no', $case_no, PDO::PARAM_STR );
		$sql->execute();
		
		$rows = $sql->fetchAll( PDO::FETCH_ASSOC );
		
		if(
			is_array( $rows )
			&& count( $rows ) > 0
		){
			$html = "<h4 class='dotc_verification_header'>
				Republic of the Philippines<br />Department of Transportation & Communication<br />LAND TRANSPORTATION FRANCHISING & REGULATORY BOARD<br />East Avenue, Quezon City<br />Tel. Nos. 426-2505, 426-2491 & 426-2513
			</h4><br /><br /><br /><h4 style='text-align: center;'><span style='text-decoration: underline;'>FRANCHISE VERIFICATION</span></h4><br /><br /><br />";
			
			$html .= "<table class='dotc_table' cellpadding=0 cellspacing=0 width='100%'>";
			$html .= "<tr>
				<td colspan='5'>Operator : {$rows[0]['name']}</td><td colspan='4'>Case No. : {$rows[0]['case_no']}</td>
			</tr>";
			$html .= "<tr>
				<td colspan='5'>Bus. Address : {$rows[0]['name']}</td><td colspan='4'>Deno. :</td>
			</tr>";
			$html .= "<tr>
				<td colspan='5'>Comm. Name : {$rows[0]['com_name']}</td><td colspan='4'>No. of Autho. Units : {$rows[0]['nau']}</td>
			</tr>";
			$html .= "<tr>
				<td colspan='5'>Date Granted : " . date( 'F j, Y', strtotime( $rows[0]['dg'] ) ) . "</td><td colspan='4'>Birth Date : </td>
			</tr>";
			$html .= "<tr>
				<td colspan='5'>Expiry Date : " . date( 'F j, Y', strtotime( $rows[0]['de'] ) ) . "</td><td colspan='4'>T I N : {$rows[0]['tin']}</td>
			</tr>";
			$html .= "<tr>
				<td colspan='9'>Route Name : {$rows[0]['route']}</td>
			</tr>";
			$html .= "<tr>
				<td colspan='9'>Remarks : {$rows[0]['remarks']}</td>
			</tr>";
			$html .= "<tr>
				<td colspan='9'>Status : {$rows[0]['status']}</td>
			</tr>";
			$html .= "<tr>
				<td colspan='9'>Tel Number : {$rows[0]['tel_no']}</td>
			</tr>";
			$html .= "<tr>
				<td colspan='9'>Cel Number : {$rows[0]['cel_no']}</td>
			</tr>";
			$html .= "<tr>
				<td colspan='9'>Email : {$rows[0]['email']}</td>
			</tr>";
			$html .= "
				<tr>
					<td colspan='9'>&nbsp;</td>
				</tr>
				<tr>
					<td colspan='9'>&nbsp;</td>
				</tr>
				<tr>
					<td colspan='9' align='center'><h3 style='text-align: center;'>I D E N T I T Y&nbsp;&nbsp;O F&nbsp;&nbsp;U N I T S</h3></td>
			</tr>";
			$html .= "
			<tr>
				<td>
					M A K E
				</td>
				<td>
					MOTOR NUMBER
				</td>
				<td>
					CHASSIS NUMBER
				</td>
				<td>
					PLATE#
				</td>
				<td>
					YM
				</td>
				<td>
					YC
				</td>
				<td>
					REMARKS
				</td>
				<td>
					GPS ID
				</td>
				<td>
					PHONE NO
				</td>
			</tr>";
			
			foreach( $rows AS $keys => $values ):
				$html .= "<tr>
					<td>
						{$values['make']}
					</td>
					<td>
						{$values['motor_no']}
					</td>
					<td>
						{$values['chassis_no']}
					</td>
					<td>
						{$values['plate_no']}
					</td>
					<td>
						{$values['year_model']}
					</td>
					<td>
						
					</td>
					<td>
						
					</td>
					<td>
						" . implode( '-', str_split( $values['gps_id'], 7 ) ) . "
					</td>
					<td>
						{$values['cp_no']}
					</td>
				</tr>";
			endforeach;
			
			$_f_details = $this->fetchReviseHistoryView( $frnc_details['id'] );
			
			$html .= "
				<tr>
					<td colspan='9'>&nbsp;</td>
				</tr>
				<tr>
					<td colspan='9'>&nbsp;</td>
				</tr>
				<tr>
					<td colspan='2'>
						A u t h o r i z e d&nbsp;&nbsp;b y : 
					</td>
					<td colspan='4' style='border-bottom: 1px solid #000;'>
						{$_f_details[0]['fname']} {$_f_details[0]['lname']}
					</td>
					<td>
						Date : 
					</td>
					<td colspan='2' style='border-bottom: 1px solid #000;'>
						
					</td>
				</tr>
				<tr>
					<td colspan='2'>
						V e r i f i e d&nbsp;&nbsp;b y : 
					</td>
					<td colspan='4' style='border-bottom: 1px solid #000;'>
						{$_f_details[0]['encoder_fname']} {$_f_details[0]['encoder_lname']}
					</td>
					<td>
						Date : 
					</td>
					<td colspan='2' style='border-bottom: 1px solid #000;'>
						
					</td>
				</tr>
				<tr>
					<td colspan='2'>
						A p p r o v e d&nbsp;&nbsp;b y : 
					</td>
					<td colspan='4' style='border-bottom: 1px solid #000;'>
						
					</td>
					<td>
						Date : 
					</td>
					<td colspan='2' style='border-bottom: 1px solid #000;'>
						
					</td>
				</tr>
				<tr>
					<td colspan='2'></td><td colspan='4' align='center'>NIDA P. QUIBIC<br />Chief, MID</td>
				</tr>
			";
			
			$html .= "</table>";
			
			$this->PDF_File( $html, $this->pdf_dir . 'VERIFICATION-' . rtrim( $frnc_details['qr_code_filename'], '.pdf' ), 'F', false );
		}
	}
	
	public function downloadPDF_QRCODES( $case_no = '' ){
		$this->check_PDF_QRCODES( $case_no );
		
		$sql = $this->conn->prepare(
			"
				SELECT
					qr_code_filename
				FROM
					dotc_frnc_codes
				WHERE
					case_no LIKE :case_no
				ORDER BY
					date_registered DESC
				LIMIT
					0, 1
			"
		);
		
		$sql->bindValue( ':case_no', $case_no, PDO::PARAM_STR );
		$sql->execute();
		
		$rows = $sql->fetchAll( PDO::FETCH_ASSOC );
		
		if(
			!is_array( $rows )
			|| count( $rows ) == 0
		){
			throw new Exception( "You need to generate qr codes before downloading the latest pdf." );
		}
		
		$this->force_download( $this->pdf_dir . $rows[0]['qr_code_filename'] );
	
		/*$sql = $this->conn->prepare(
			"
				SELECT
					vehicle_id, case_no, plate_no, name, gps_id
				FROM
					dotc_unit_frnc
				WHERE
					case_no LIKE :case_no
			"
		);
		
		$sql->bindValue( ':case_no', $case_no, PDO::PARAM_STR );
		$sql->execute();
		
		$rows = $sql->fetchAll( PDO::FETCH_ASSOC );
		
		$html = '';
		
		foreach( $rows AS $keys => $values ):
			$html .= "<div class='left dotc_pdf_qrcode'>
				<div class='left dotc_pdf_qrcode-img'>
					<img src='{$this->qrcode_dir}vehicle-{$values['vehicle_id']}.png' width='123px' height='123px' />
				</div>
				<div class='left dotc_pdf_qrcode-data'>
					<table cellpadding='0' cellspacing='0' width='100%'>
						<tr>
							<td>
								CASE #
							</td>
							<td>
								{$values['case_no']}
							</td>
						</tr>
						<tr>
							<td>
								PLATE #
							</td>
							<td>
								{$values['plate_no']}
							</td>
						</tr>
						<tr>
							<td>
								COMPANY
							</td>
							<td>
								{$values['name']}
							</td>
						</tr>
						<tr>
							<td>
								GPS ID
							</td>
							<td>
								{$values['gps_id']}
							</td>
						</tr>
					</table>
				</div><div class='clearb'></div>
			</div>";
			
			if( $keys > 0 && ( ( $keys + 1 ) % 2 ) == 0 ){
				$html .= "<div class='clearb'></div>";
			}
		endforeach;
		
		$this->PDF_File( $html, 'CASE-' . $case_no . '-' . time() );*/
	}
	
	public function generateRegistrationReport( $details = array() ){
		$for_query = array(
			'where' => '',
			'conditions' => ''
		);
		
		switch( strtolower( $details['type'] ) ){
			case 1:
				$sql = $this->conn->prepare(
					"
						SELECT
							DISTINCT code_id, lname, fname, DATE_FORMAT( date_registered, '%M %e, %Y %l:%i %p' ) AS date_registered, encoder_lname, encoder_fname, title, id_no, contact_no, email, case_no, franchise_name
						FROM 
							`revise_history_view`
						WHERE
							DATE_FORMAT( date_registered, '%Y-%m-%d' ) = :date_registered
						ORDER BY
							date_registered DESC, history_date_registered DESC
					"
				);
				$sql->bindValue( ':date_registered', date( 'Y-m-d' ), PDO::PARAM_STR );
			break;
			case 2:
				$sql = $this->conn->prepare(
					"
						SELECT
							DISTINCT code_id, lname, fname, DATE_FORMAT( date_registered, '%M %e, %Y %l:%i %p' ) AS date_registered, encoder_lname, encoder_fname, title, id_no, contact_no, email, case_no, franchise_name
						FROM 
							`revise_history_view`
						WHERE
							DATE_FORMAT( date_registered, '%Y-%m-%d' ) = :date_registered
						ORDER BY
							date_registered DESC, history_date_registered DESC
					"
				);
				$sql->bindValue( ':date_registered', date( 'Y-m-d', strtotime( 'yesterday' ) ), PDO::PARAM_STR );
			break;
			case 3:
				$sql = $this->conn->prepare(
					"
						SELECT
							DISTINCT code_id, lname, fname, DATE_FORMAT( date_registered, '%M %e, %Y %l:%i %p' ) AS date_registered, encoder_lname, encoder_fname, title, id_no, contact_no, email, case_no, franchise_name
						FROM 
							`revise_history_view`
						WHERE
							DATE_FORMAT( date_registered, '%Y-%m-%d' ) >= :date_registered1
							AND
							DATE_FORMAT( date_registered, '%Y-%m-%d' ) <= :date_registered2
						ORDER BY
							date_registered DESC, history_date_registered DESC
					"
				);
				$sql->bindValue( ':date_registered1', date( 'Y-m-d', strtotime( 'Last Week Monday' ) ), PDO::PARAM_STR );
				$sql->bindValue( ':date_registered2', date( 'Y-m-d', strtotime( 'Last Week Monday +6 days' ) ), PDO::PARAM_STR );
			break;
			case 4:
				$sql = $this->conn->prepare(
					"
						SELECT
							DISTINCT code_id, lname, fname, DATE_FORMAT( date_registered, '%M %e, %Y %l:%i %p' ) AS date_registered, encoder_lname, encoder_fname, title, id_no, contact_no, email, case_no, franchise_name
						FROM 
							`revise_history_view`
						WHERE
							DATE_FORMAT( date_registered, '%Y-%m-%d' ) >= :date_registered1
							AND
							DATE_FORMAT( date_registered, '%Y-%m-%d' ) <= :date_registered2
						ORDER BY
							date_registered DESC, history_date_registered DESC
					"
				);
				$sql->bindValue( ':date_registered1', date( 'Y-m-d', strtotime( 'This Week Monday' ) ), PDO::PARAM_STR );
				$sql->bindValue( ':date_registered2', date( 'Y-m-d', strtotime( 'This Week Monday +6 days' ) ), PDO::PARAM_STR );
			break;
			case 5:
				$sql = $this->conn->prepare(
					"
						SELECT
							DISTINCT code_id, lname, fname, DATE_FORMAT( date_registered, '%M %e, %Y %l:%i %p' ) AS date_registered, encoder_lname, encoder_fname, title, id_no, contact_no, email, case_no, franchise_name
						FROM 
							`revise_history_view`
						WHERE
							DATE_FORMAT( date_registered, '%Y-%m' ) = :date_registered
						ORDER BY
							date_registered DESC, history_date_registered DESC
					"
				);
				$sql->bindValue( ':date_registered', date( 'Y-m' ), PDO::PARAM_STR );
			break;
			case 6:
				$sql = $this->conn->prepare(
					"
						SELECT
							DISTINCT code_id, lname, fname, DATE_FORMAT( date_registered, '%M %e, %Y %l:%i %p' ) AS date_registered, encoder_lname, encoder_fname, title, id_no, contact_no, email, case_no, franchise_name
						FROM 
							`revise_history_view`
						WHERE
							DATE_FORMAT( date_registered, '%Y-%m' ) = :date_registered
						ORDER BY
							date_registered DESC, history_date_registered DESC
					"
				);
				$sql->bindValue( ':date_registered', date( 'Y-m', strtotime( 'Last Month' ) ), PDO::PARAM_STR );
			break;
			case 4:
				$sql = $this->conn->prepare(
					"
						SELECT
							DISTINCT code_id, lname, fname, DATE_FORMAT( date_registered, '%M %e, %Y %l:%i %p' ) AS date_registered, encoder_lname, encoder_fname, title, id_no, contact_no, email, case_no, franchise_name
						FROM 
							`revise_history_view`
						WHERE
							DATE_FORMAT( date_registered, '%Y-%m-%d' ) >= :date_registered1
							AND
							DATE_FORMAT( date_registered, '%Y-%m-%d' ) <= :date_registered2
						ORDER BY
							date_registered DESC, history_date_registered DESC
					"
				);
				$sql->bindValue( ':date_registered1', date( 'Y-m-d', strtotime( $details['start_date'] ) ), PDO::PARAM_STR );
				$sql->bindValue( ':date_registered2', date( 'Y-m-d', strtotime( $details['end_date'] ) ), PDO::PARAM_STR );
			break;
		}
		$sql->execute();
		
		$rows = $sql->fetchAll( PDO::FETCH_ASSOC );
		
		if(
			is_array( $rows )
			&& count( $rows ) > 0
		){
			$html = '';
			
			$html .= "<table class='dotc_table_report' cellpadding=0 cellspacing=0 width='100%'>";
			$html .= "
				<tr>
					<th>
						DATE
					</th>
					<th>
						ENCODER
					</th>
					<th>
						COMPANY NAME
					</th>
					<th>
						CASE NO
					</th>
				</tr>
			";
			
			foreach( $rows AS $keys => $values ):
				$html .= "<tr>
					<td>
						{$values['date_registered']}
					</td>
					<td>
						{$values['encoder_lname']}, {$values['encoder_fname']}
					</td>
					<td>
						{$values['franchise_name']}
					</td>
					<td>
						{$values['case_no']}
					</td>
				</tr>";
			endforeach;
			
			$html .= "</table>";
			
			$this->PDF_File( $html, 'REGISTRATION-REPORT-' . time() );
		}
	}
	
	public function fetchFranchiseRevision( $code_id = 0 ){
		$sql = $this->conn->prepare(
			"
				SELECT
					*
				FROM
					dotc_frnc_codes
				WHERE
					id = :code_id
			"
		);
		
		$sql->bindValue( ':code_id', $code_id, PDO::PARAM_INT );
		$sql->execute();
		
		$rows = $sql->fetchAll( PDO::FETCH_ASSOC );
		
		return isset( $rows[0] ) ? $rows[0] : array();
	}
	
	public function downloadReviseQrCodesLatest( $case_no = '' ){
		$sql = $this->conn->prepare(
			"
				SELECT
					code_id
				FROM
					`revise_history_view`
				WHERE
					case_no LIKE :case_no
				ORDER BY
					history_date_registered DESC
				LIMIT
					0, 1
			"
		);
		
		$sql->bindValue( ':case_no', $case_no, PDO::PARAM_STR );
		$sql->execute();
		$rows = $sql->fetchAll( PDO::FETCH_ASSOC );
		
		if(
			is_array( $rows )
			&& count( $rows ) > 0
		){
			$r = $this->fetchFranchiseRevision( $rows[0]['code_id'] );
			
			$this->force_download( $this->pdf_dir . $r['qr_code_filename'] );
		}
	}
	
	public function downloadReviseVerificationLatest( $case_no = 0 ){
		$sql = $this->conn->prepare(
			"
				SELECT
					code_id
				FROM
					`revise_history_view`
				WHERE
					case_no LIKE :case_no
				ORDER BY
					history_date_registered DESC
				LIMIT
					0, 1
			"
		);
		
		$sql->bindValue( ':case_no', $case_no, PDO::PARAM_STR );
		$sql->execute();
		$rows = $sql->fetchAll( PDO::FETCH_ASSOC );
		
		if(
			is_array( $rows )
			&& count( $rows ) > 0
		){
			$r = $this->fetchFranchiseRevision( $rows[0]['code_id'] );
		
			$this->force_download( $this->pdf_dir . 'VERIFICATION-' . $r['qr_code_filename'] );
		}
	}
	
	public function downloadReviseQrCodes( $code_id = 0 ){
		$r = $this->fetchFranchiseRevision( $code_id );
		
		$this->force_download( $this->pdf_dir . $r['qr_code_filename'] );
	}
	
	public function downloadReviseVerification( $code_id = 0 ){
		$r = $this->fetchFranchiseRevision( $code_id );
		
		$this->force_download( $this->pdf_dir . 'VERIFICATION-' . $r['qr_code_filename'] );
	}
	
	protected function force_download( $file, $delete_file = false ){
        if(
            isset( $file )
            && file_exists( $file )
        ) {
           header("Content-type: application/force-download");
           header('Content-Disposition: inline; filename="' . $file . '"');
           header("Content-Transfer-Encoding: Binary");
           header("Content-length: ".filesize( $file ) );
           header('Content-Type: application/octet-stream');
           header('Content-Disposition: attachment; filename="' . basename( $file ) . '"');
           readfile( $file );
		   if( $delete_file ){
				unlink( $file );
		   }
		   exit;
        } else {
           throw new Exception( "No file selected" );
        }
    }
	
	/* import/export */
	public function cleanData( &$str ){
		$str = preg_replace( "/\t/", "\\t", $str );
		$str = preg_replace( "/\r?\n/", "\\n", $str );
		if( $str == 't' ) $str = 'TRUE';
		if( $str == 'f' ) $str = 'FALSE';
		// force add quote
		// if( preg_match( "/^0/", $str ) || preg_match( "/^\+?\d{8,}$/", $str ) || preg_match( "/^\d{4}.\d{1,2}.\d{1,2}/", $str ) ) { $str = "'$str"; }
		if( strstr( $str, '"' ) ) $str = '"' . str_replace( '"', '""', $str ) . '"';
		// $str = mb_convert_encoding( $str, 'UTF-16LE', 'UTF-8' );
	}
	
	public function CleanDataXLS( &$str ){
		$str = preg_replace( "/\r\n|\n\r|\n|\r/", ' ', $str );
		return $str . "\t";
	}
	
	public function exportTable_V2( $id = 0 ){
		$details = $this->fetchTableDetails( $id );
		
		if(
			is_array( $details )
			&& count( $details ) > 0
		){
			$filename = preg_replace( '/[^0-9A-Za-z]/', '', $details['table_alias'] ) . "_" . date( 'YmdGis' ) . ".xls";

			$xls = new ExportXLS( $filename );
			
			$columns = $this->showColumnsOfTable( $details['tablename'] );
			$xls->addHeader( $columns );
			
			$sql = $this->conn->prepare( "SELECT * FROM `{$details['tablename']}` ORDER BY id" );
			$sql->execute();
			
			$rows = $sql->fetchAll( PDO::FETCH_ASSOC );
			
			foreach( $rows AS $keys => $values ):
				$xls->addRow( array_values( $values ) );
			endforeach;
			
			$xls->sendFile();
		}
	}
	
	public function exportTable( $id = 0 ){
		$details = $this->fetchTableDetails( $id );
		
		if(
			is_array( $details )
			&& count( $details ) > 0
		){
			$filename = preg_replace( '/[^0-9A-Za-z]/', '', $details['table_alias'] ) . "_" . date( 'YmdGis' ) . ".xls";
			
			header( "Content-Disposition: attachment; filename=\"$filename\"" );
			header( "Content-Type: application/xls" );
			header( "Pragma: no-cache" ); 
			header( "Expires: 0" );
			$data = '';
			#$out = fopen( "php://output", 'w' );
			
			$columns = $this->showColumnsOfTable( $details['tablename'] );
			#fputcsv( $out, $columns, ',', '"' );
			array_walk( $columns, array( $this, 'CleanDataXLS' ) );
			$data .= trim( implode( '', $columns ) ) . "\n";
			
			$sql = $this->conn->prepare( "SELECT * FROM `{$details['tablename']}` ORDER BY id" );
			$sql->execute();
			
			$rows = $sql->fetchAll( PDO::FETCH_ASSOC );
			
			foreach( $rows AS $keys => $values ):
				array_walk( $values, array( $this, 'CleanDataXLS' ) );
				#fputcsv( $out, array_values( $values ), ',', '"' );
				$data .= trim( implode( '', array_values( $values ) ) ) . "\n";
			endforeach;
			
			#fclose( $out );
			echo $data;
			exit;
		}
	}
	
	public function fetchTableDetails( $id = 0 ){
		$sql = $this->conn->prepare(
			"
				SELECT
					*
				FROM
					`dotc_tables_for_export`
				WHERE
					id = :id
			"
		);
		
		$sql->bindValue( ':id', $id, PDO::PARAM_INT );
		$sql->execute();
		
		$row = $sql->fetchAll( PDO::FETCH_ASSOC );
		
		return is_array( $row ) && count( $row ) > 0 ? $row[0] : array();
	}
	
	public function importExportTables(){
		$sql = $this->conn->prepare( "SELECT * FROM `dotc_tables_for_export`" );
		$sql->execute();
		
		return $sql->fetchAll( PDO::FETCH_ASSOC );
	}
	
	public function showColumnsOfTable( $tablename = '' ){
		$sql = $this->conn->prepare( "DESCRIBE `{$tablename}`" );
		$sql->execute();
		
		$row = $sql->fetchAll( PDO::FETCH_COLUMN );
		
		return $row;
	}
	/* end */
	
	public function CheckVehicleZipArchive( $ids = array() ){
		$files_to_add = 0;
		
		foreach( $ids AS $keys => $values ):
			if( file_exists( $this->pdf_dir . 'VEHICLE-QRCODE-VERIFICATION-' . $values . '.pdf' ) ){
				$files_to_add++;
			}
		endforeach;
		
		return $files_to_add > 0;
	}
	
	public function CreateVehicleZipArchive( $ids = array() ){
		$files_to_add = array();
		
		foreach( $ids AS $keys => $values ):
			if( file_exists( $this->pdf_dir . 'VEHICLE-QRCODE-VERIFICATION-' . $values . '.pdf' ) ){
				array_push( $files_to_add, $this->pdf_dir . 'VEHICLE-QRCODE-VERIFICATION-' . $values . '.pdf' );
			}
		endforeach;
		
		$this->createZipArchive( $files_to_add );
	}
	
	protected function createZipArchive( $files = array() ){
		if(
			is_array( $files )
			&& count( $files ) > 0
		){
			$zip = new ZipArchive();
			
			$filename = $this->zip_dir . 'ZIP-ARCHIVE-' . time() . '.zip';
			
			$zip->open( $filename, ZipArchive::CREATE );
			
			foreach( $files AS $keys => $values ):
				$zip->addFile( $values, basename( $values ) );
			endforeach;

			$zip->close();
			
			$this->force_download( $filename, true );
		}
	}
	
	protected function PDF_File_V2( $array_html = array(), $filename = '', $type = 'D', $do_exit = true ){
		$pdf_c = new mPDF( 'utf-8', 'A4', 0, 'Arial', 6, 6, 5, 5 );
		$stylesheet = file_get_contents( 'styles/download.css' );
		
		if(
			is_array( $array_html )
			&& count( $array_html ) > 0
		){
			foreach( $array_html AS $keys => $values ):
				$pdf_c->AddPage( 'P' );
				$pdf_c->WriteHTML( $stylesheet, 1 );
				$pdf_c->WriteHTML( $values, 2 );
			endforeach;
		}
		
		$pdf_c->Output( $filename . '.pdf', $type );
		
		if( $do_exit ){
			exit;
		}
	}
	
	protected function PDF_File( $html = '', $filename = '', $type = 'D', $do_exit = true ){
		$pdf_c = new mPDF( 'utf-8', 'A4', 0, 'Arial', 6, 6, 5, 5 );
		$stylesheet = file_get_contents( 'styles/download.css' );
		$pdf_c->AddPage( 'P' );
		$pdf_c->WriteHTML( $stylesheet, 1 );
		$pdf_c->WriteHTML( $html, 2 );
		$pdf_c->Output( $filename . '.pdf', $type );
		
		if( $do_exit ){
			exit;
		}
	}
}
?>