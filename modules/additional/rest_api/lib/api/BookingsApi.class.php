<?php

/***
 *	RestApiInterface Interface
 *  ------------------ 
 *  Description : 
 *	Written by  : ApPHP
 *	Version     : 1.0.0
 *  Updated	    : 04.08.2016
 *  Usage       : uHotelBooking
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


class BookingsApi extends PHP_CRUD_API {

	/* @var string */
	private $account_type = null;
	/* @var int */
	private $account_id = null;

	public function __construct(){

		parent::__construct();

		$this->account_type = Session::Get('session_api_account_type');
		$this->account_id = Session::Get('session_api_account_id');
		// Comment [20.08.2016]
		//if($account_type == 'hotelowner'){
		//	$hotels = Session::Get('session_api_hotel_ids');
		//	$hotel_ids = implode(',', $hotels);

		//	$sql = 'SELECT 
		//			'.TABLE_BOOKINGS_ROOMS.'.booking_number
		//		FROM '.TABLE_BOOKINGS_ROOMS.'
		//		WHERE '.TABLE_BOOKINGS_ROOMS.'.hotel_id IN ('.$hotel_ids.') 
		//		GROUP BY '.TABLE_BOOKINGS_ROOMS.'.booking_number';
		//	$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		//	if($result[1] > 0){
		//		foreach($result[0] as $booking_number){
		//			$this->arr_booking_numbers[] = $booking_number['booking_number'];
		//		}
		//	}

		//	if(empty($this->arr_booking_numbers)){
		//		$this->_printError('For your hotel was not a single order', true);
		//	}
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
			$arr_fields = array('id', 'is_admin_reservation', 'created_by_admin_id');
			$this->_checkFieldsExists($parameters['input'], $arr_fields);
		}
		$arr_columns = array('booking_number');
		$this->_checkFieldsUnique($parameters['tables'][0], $parameters['input'], $arr_columns);
		$parameters['input']->created_by_admin_id = $this->account_id;
		$parameters['input']->is_admin_reservation = 1;
		parent::createCommand($parameters);
	}
	
	public function updateCommand($parameters)
	{
		if($this->account_type == 'hotelowner'){
			$arr_fields = array('id', 'is_admin_reservation', 'created_by_admin_id');
			$this->_checkFieldsExists($parameters['input'], $arr_fields);
			$parameters = $this->_addAdditonFilter($parameters);
		}
		$arr_columns = array('booking_number');
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
