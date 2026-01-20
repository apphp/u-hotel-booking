<?php

/***
 *	RestApiInterface Interface
 *  ------------------ 
 *  Description : 
 *	Written by  : ApPHP
 *	Version     : 1.0.0
 *  Updated	    : 04.08.2016
 *  Usage       : uHotelBooking
 *  Differences : $PROJECT
 *
 *	PUBLIC:					STATIC:					PRIVATE:
 *  -----------				-----------				-----------
 *  __construct										_addAdditonFilter
 *  listCommand
 *  readCommand
 *  createCommand
 *  updateCommand
 *  deleteCommand
 *	headersCommand
 *
 **/


class HotelsApi extends PHP_CRUD_API {

	/* @var array */
	private $arr_hotel_ids = array();
	/* @var string */
	private $account_type = null;
	/* @var int */
	private $account_id = null;

	public function __construct(){

		parent::__construct();

		$this->account_type = Session::Get('session_api_account_type');
		$this->account_id = Session::Get('session_api_account_id');
		if($this->account_type == 'hotelowner'){
			$this->arr_hotel_ids = Session::Get('session_api_hotel_ids');

			if(empty($this->arr_hotel_ids)){
				$this->_printError('You do not have any hotels', true);
			}
		}elseif(!in_array($this->account_type, array('owner', 'mainadmin', 'admin'))){
			$this->_printError('You are not logged in', true);
		}
	}

	public function listCommand($parameters)
	{
		if($this->account_type == 'hotelowner'){
			$parameters = $this->_addAdditonFilter($parameters);
		}
		parent::listCommand($parameters);
	}
	
	public function readCommand($parameters)
	{
		if($this->account_type == 'hotelowner'){
			$parameters = $this->_addAdditonFilter($parameters);
		}
		parent::readCommand($parameters);
	}
	
	public function createCommand($parameters)
	{
		$arr_columns = array('email', 'hotel_image', 'hotel_image_thumb');

		if($this->account_type == 'hotelowner'){
			$this->_checkFieldsExists($parameters['input'], array('id'));
			$this->_checkFieldsUnique($parameters['tables'][0], $parameters['input'], $arr_columns);
			$account_id = Session::Get('session_api_account_id');
			ob_start();
			parent::createCommand($parameters);
			$result = ob_get_contents();
			ob_end_clean();
			$intResult = (int)$result;
			if($intResult){
				$this->arr_hotel_ids[] = $intResult;
				$companies = implode(',', $this->arr_hotel_ids);
				$sql = 'UPDATE '.TABLE_ACCOUNTS.' SET companies = \''.$companies.'\'
				WHERE id = '.(int)$account_id;
				database_void_query($sql);
			}
			echo $result;
		}else{
			$this->_checkFieldsUnique($parameters['tables'][0], $parameters['input'], $arr_columns);
			parent::createCommand($parameters);
		}
	}
	
	public function updateCommand($parameters)
	{
		if($this->account_type == 'hotelowner'){
			$this->_checkFieldsExists($parameters['input'], array('id'));
			$parameters = $this->_addAdditonFilter($parameters);
		}
		$arr_columns = array('email', 'hotel_image', 'hotel_image_thumb');
		$this->_checkFieldsUnique($parameters['tables'][0], $parameters['input'], $arr_columns, $parameters['key'][0]);
		parent::updateCommand($parameters);
	}
	
	public function deleteCommand($parameters)
	{
		$hotel_id = $parameters['key'][0];
		if($this->account_type == 'hotelowner'){
			$parameters = $this->_addAdditonFilter($parameters);
		}
		ob_start();
		parent::deleteCommand($parameters);
		$content = ob_get_contents();
		ob_end_clean();
		$intResult = (int)$content;
		if($intResult){
			$company = serialize($hotel_id);
			$sql = 'SELECT id, companies
				FROM '.TABLE_ACCOUNTS.'
				WHERE companies LIKE \'%'.$company.'%\' AND account_type = \'hotelowner\'';
			$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
			if($result[1] > 0){
				$hotels = @explode(',', $result[0]['companies']);
				$account_id = $result[0]['id'];
				if(!empty($hotels) && in_array($hotel_id, $hotels)){
					$hotels = array_diff($hotels, array($hotel_id));
					$companies = implode(',', $hotels);

					$sql = 'UPDATE '.TABLE_ACCOUNTS.' SET companies = \''.$companies.'\'
					WHERE id = '.(int)$account_id;
					database_void_query($sql);
				}
			}
		}

		echo $content;
	}
	
	public function headersCommand($parameters)
	{
		parent::headersCommand($parameters);
	}

	/**
	 * @param array $parameters
	 * @return array
	 * */
	private function _addAdditonFilter($parameters){
		$table = $parameters['tables'][0];
		$filters = $parameters['filters'];
		if(!empty($filters)){
			$filters[$table]['and'][] = array('id', 'IN', $this->arr_hotel_ids);
		}else{
			$filters = array(
				$table => array(
					'and' => array(array('id', 'IN', $this->arr_hotel_ids))
				)
			);
		}
		$parameters['filters'] = $filters;

		return $parameters;
	}
}
