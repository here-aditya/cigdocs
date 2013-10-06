<?php
/**
 * Default controller of the application
 */

class Gdoclist extends CI_Controller
{
	private $http;
	private $docs;
	private $excels;
	private $ss_ws_arr = array();
	private $data = array();
	
	// call the constructor as page is called
	public function __construct()
	{
		// call CI controller constructor
		parent::__construct();
		// set path to resource folder
		$this->data['RPath'] = base_url() . 'resource/';
		// set path to Zend folder for Google Doc's Page handling classes
		require_once dirname(BASEPATH) . '/Zend/Loader.php';
		// load required classes for spread sheet & other docs handling
		Zend_Loader::loadClass('Zend_Gdata');
		Zend_Loader::loadClass('Zend_Http_Client');
		Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
		Zend_Loader::loadClass('Zend_Gdata_Spreadsheets');
		Zend_Loader::loadClass('Zend_Gdata_Docs');
		// load the model for storing 
		// current session along with authenticated user's Google docs login token
		// this part could be discarded but used to show database handling part too
		$this->load->model('gdocs_model');
	}
	
	// main method which called during controller access 
	public function index()
	{
		// check if user is logged in or not
		// if logged in redirect to dashboard else show login screen
		$this->isLogIn() ?	$this->showDasboard() : $this->showLogin();
	}
	
	
	// check for authenticated session
	public function isLogIn()
	{
		// get current CI session ID & authenticated session id if any
		$cur_session_id = $this->session->userdata('session_id');
		$prev_sess_id = $this->session->userdata('cur_session_id');
		if( ! empty($prev_sess_id) && $prev_sess_id == $cur_session_id )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	// show dashboard to authenticated user
	// fetch all spreadsheet data for the user
	public function showDasboard()
	{
		$this->fetchGObj();
		$this->data['ss_ws_arr'] = $this->ss_ws_arr;
		$this->showPage('dashboard');
	}
	
	// show login screen 
	// if data send via form authenticate the credntials too
	public function showLogin()
	{
		$usrid = $this->input->post('usrid'); 
		$usrpswd = $this->input->post('usrpswd'); 
		if( ! empty($usrid ) && ! empty($usrpswd) )
		{	// call login method with user provided credentials
			if( $this->login( $usrid, $usrpswd ) )
			{
				unset($_POST);
				redirect('gdoclist/showDasboard');
				return;
			}
			else
			{
				$this->data['Err'] = true;
				$this->data['ErrMsg'] = 'Error: Unable to authenticate. Please check your credentials.';
			}
		}
		$this->showPage('login');
	}
	
	// check login for Google docs via Zend data object
	private function login( $user, $pass )
	{
		try 
		{
			$service = Zend_Gdata_Spreadsheets::AUTH_SERVICE_NAME;
			
			if( $this->http = Zend_Gdata_ClientLogin::getHttpClient($user, $pass, $service) )
			{	// login success
				$cur_session_id = $this->session->userdata('session_id');
				// store current sesion id
				$this->session->set_userdata('cur_session_id' , $cur_session_id);
				// check if user's current session is already into database
				// if no entry found new record is enterd
				// if current session is registered then update the google login token
				$prevdata = $this->gdocs_model->getData($cur_session_id);
				if( empty( $prevdata ) )
				{
					$this->gdocs_model->setData($cur_session_id, $this->http->getClientLoginToken());
				}
				else
				{
					$this->gdocs_model->updateData($cur_session_id, $this->http->getClientLoginToken());
				}
				return true;
			}
			else
			{
				return false;
			}
		} 
		catch (Zend_Gdata_App_AuthException $e) 
		{
			return false;
		}
		
	}
	
	
	// get authenticated user's spreadsheet & docs information
	// I've taken only spreadsheet data for the app
	private function getClientAllData()
	{
		$this->excels = new Zend_Gdata_Spreadsheets( $this->http );
		$this->docs = new Zend_Gdata_Docs( $this->http );
	}
	
	
	// return document array containing Spreadsheet along with their worksheets
	// word document can also be retrived by changing type parameter which has discarded here
	public function showDocument( $type )
	{
		switch ( $type ) 
		{
			case 'excel' :	 //  slecetion is for spreadsheets
							$feed = $this->excels->getSpreadsheetFeed();
							// get all spreadhets list
							foreach ($feed->entries as $key => $entry) 
							{
								$spreadsheetId = basename($entry->id);
								$query = new Zend_Gdata_Spreadsheets_DocumentQuery();
								$query->setSpreadsheetKey( $spreadsheetId );
								foreach ( $entry->link as $link ) 
								{
									if ($link->getRel() === 'alternate') 
									{
										$alternateLink = $link->getHref();
									}
								}
								
								 //   spreadsheet has worksheets
								$feed2 = $this->excels->getWorksheetFeed($query);
								$ws = array();
								foreach ($feed2->entries as $entry2)
								{
									$worksheetId = basename($entry2->id);
									$ws[] = array( 
																	'wsid' => $worksheetId,
																	'title' => $entry2->title->text
																);
								}
								
								$this->ss_ws_arr[$key] = array( 
															'ssid' => $spreadsheetId,
															'title' => $entry->title->text, 
															'link' => $alternateLink, 
															'wslist' => $ws
														   );
								
							}
							return $this->ss_ws_arr;
							
			default: break;
		}
	}
	
	
	// fetch for all quries related to google docs
	public function fetchGObj()
	{
		$cur_session_id = $this->session->userdata('cur_session_id');
		$data_arr = $this->gdocs_model->getData($cur_session_id);
		// initialize memebers of the class with current cleint data
		if($this->http = new Zend_Gdata_HttpClient() )
		{
			try
			{
				$this->http->setClientLoginToken($data_arr->gdoc_obj);
				$this->getClientAllData();
				$this->showDocument( 'excel' );
			}
			catch (Zend_Gdata_App_AuthException $e) 
			{
				$this->logout();
			}
		}
		else
		{
			// if no authenticated session found logout the user
			$this->logout();
		}
	}
	
	
	// fetch spreadsheet related data by their stylesheet id & worksheet id
	// function is called via AJAX in frontend
	// a JSON string is returned
	public function showTable()
	{
		$spreadsheetId = $this->input->post('ssid'); 
		$worksheetId = $this->input->post('wsid');
		
		$this->fetchGObj();
		$query = new Zend_Gdata_Spreadsheets_CellQuery();
		$query->setSpreadsheetKey( $spreadsheetId );
		$query->setWorksheetId( $worksheetId );
		
		$cellFeed = $this->excels->getCellFeed($query);
		$row_col_val = array();
		
		foreach($cellFeed as $cellEntry) 
		{
			$row = $cellEntry->cell->getRow();
			$col = $cellEntry->cell->getColumn();
			$val = $cellEntry->cell->getText();
			$row_col_val[] = array( 'row' => $row, 'col' => $col, 'val' => $val);
		}
		echo json_encode($row_col_val);
	}
	
	// show a page contains its header , fooeter & the page section
	// some required data is also sent while loading the page initially
	public function showPage( $page )
	{
		$this->load->view( 'header', $this->data );
		$this->load->view( $page, $this->data );
		$this->load->view( 'footer', $this->data );
	}
	
	// logout the authenticated user
	// delete record from table by current session id
	// redirect user to login screen
	public function logout()
	{
		$cur_session = $this->session->unset_userdata('cur_session_id');
		$this->session->sess_destroy();
		$this->gdocs_model->delSession($cur_session);
		redirect(site_url('gdoclist/index'));
	}	
	
}
?>