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


class CustomersApi extends PHP_CRUD_API 
{
	/* @var string */
	private $account_type = null;
	/* @var int */
	private $account_id = null;

	public function __construct(){

		parent::__construct();

		$this->account_type = Session::Get('session_api_account_type');
		$this->account_id = Session::Get('session_api_account_id');
		// Comment [20.08.2016]
		//if($this->account_type == 'hotelowner'){
			//$hotels = Session::Get('session_api_hotel_ids');
			//$hotels_list = implode(',', $hotels);
			//$sql = 'SELECT 
			//		'.TABLE_BOOKINGS.'.customer_id
			//	FROM '.TABLE_BOOKINGS.'
			//		INNER JOIN '.TABLE_BOOKINGS_ROOMS.' ON '.TABLE_BOOKINGS.'.booking_number = '.TABLE_BOOKINGS_ROOMS.'.booking_number
			//	WHERE '.TABLE_BOOKINGS_ROOMS.'.hotel_id IN ('.$hotels_list.') 
			//	GROUP BY '.TABLE_BOOKINGS.'.customer_id';
			//$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
			//if($result[1] > 0){
			//	foreach($result[0] as $customer_info){
			//		$this->arr_customer_ids[] = $customer_info['customer_id'];
			//	}
			//}

			//if(empty($this->arr_customer_ids)){
			//	$this->_printError('You have no customers yet who made any order.', true);
			//}
		//}elseif(!in_array($account_type, array('owner', 'mainadmin', 'admin'))){
		// End [20.08.2016]
		if(!in_array($this->account_type, array('owner', 'mainadmin', 'admin', 'hotelowner'))){
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
		if($this->account_type == 'hotelowner'){
			$arr_fields = array('id', 'created_by_admin_id');
			$this->_checkFieldsExists($parameters['input'], $arr_fields);
		}
		$arr_columns = array('email', 'user_name');
		$this->_checkFieldsUnique($parameters['tables'][0], $parameters['input'], $arr_columns);
		$parameters['input']->created_by_admin_id = $this->account_id;
		parent::createCommand($parameters);
	}
	
	public function updateCommand($parameters)
	{
		if($this->account_type == 'hotelowner'){
			$arr_fields = array('id', 'created_by_admin_id');
			$this->_checkFieldsExists($parameters['input'], $arr_fields);
			$parameters = $this->_addAdditonFilter($parameters);
		}
		$arr_columns = array('email', 'user_name');
		$this->_checkFieldsUnique($parameters['tables'][0], $parameters['input'], $arr_columns, $parameters['key'][0]);
		parent::updateCommand($parameters);
	}
	
	public function deleteCommand($parameters)
	{
		if($this->account_type == 'hotelowner'){
			$parameters = $this->_addAdditonFilter($parameters);
		}
		parent::deleteCommand($parameters);
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
			$filters[$table]['and'][] = array('created_by_admin_id', '=', $this->account_id);
		}else{
			$filters = array(
				$table => array(
					'and' => array(array('created_by_admin_id', '=', $this->account_id))
				)
			);
		}
		$parameters['filters'] = $filters;

		return $parameters;
	}
}
