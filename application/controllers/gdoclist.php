<?php
/**
 * @see Zend_Loader
 */

class Gdoclist extends CI_Controller
{
	private $http;
	private $docs;
	private $excels;
	private $ss_ws_arr = array();
	private $data = array();
	
	
	public function __construct()
	{
		parent::__construct();
		$this->data['RPath'] = base_url() . 'resource/';
		require_once dirname(BASEPATH) . '/Zend/Loader.php';
		Zend_Loader::loadClass('Zend_Gdata');
		Zend_Loader::loadClass('Zend_Http_Client');
		Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
		Zend_Loader::loadClass('Zend_Gdata_Spreadsheets');
		Zend_Loader::loadClass('Zend_Gdata_Docs');
		$this->load->model('gdocs_model');
	}
	
	
	public function index()
	{
		$this->isLogIn() ?	$this->showDasboard() : $this->showLogin();
	}
	
	
	public function isLogIn()
	{
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
	
	
	public function showDasboard()
	{
		$this->fetchGObj();
		$this->data['ss_ws_arr'] = $this->ss_ws_arr;
		$this->showPage('dashboard');
	}
	
	
	public function showLogin()
	{
		$usrid = $this->input->post('usrid'); 
		$usrpswd = $this->input->post('usrpswd'); 
		if( ! empty($usrid ) && ! empty($usrpswd) )
		{
			if( $this->login( $usrid, $usrpswd ) )
			{
				unset($_POST);
				//$this->showDasboard();
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
	
	
	private function login( $user, $pass )
	{
		try 
		{
			$service = Zend_Gdata_Spreadsheets::AUTH_SERVICE_NAME;
			if( $this->http = Zend_Gdata_ClientLogin::getHttpClient($user, $pass, $service) )
			{
				$cur_session_id = $this->session->userdata('session_id');
				$this->session->set_userdata('cur_session_id' , $cur_session_id);
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
	
	
	private function getClientAllData()
	{
		$this->excels = new Zend_Gdata_Spreadsheets( $this->http );
		$this->docs = new Zend_Gdata_Docs( $this->http );
	}
	
	
	public function showDocument( $type )
	{
		switch ( $type ) 
		{
			case 'excel' :	 //  spreadsheets
							$feed = $this->excels->getSpreadsheetFeed();
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
								
								 //   spreadsheet -> worksheets
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
	
	
	public function fetchGObj()
	{
		$cur_session_id = $this->session->userdata('cur_session_id');
		$data_arr = $this->gdocs_model->getData($cur_session_id);
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
			$this->logout();
		}
	}
	
	
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
	
	
	public function showPage( $page )
	{
		$this->load->view( 'header', $this->data );
		$this->load->view( $page, $this->data );
		$this->load->view( 'footer', $this->data );
	}
	
	
	public function logout()
	{
		$cur_session = $this->session->unset_userdata('cur_session_id');
		$this->session->sess_destroy();
		$this->gdocs_model->delSession($cur_session);
		redirect(site_url('gdoclist/index'));
	}	
	
}
?>