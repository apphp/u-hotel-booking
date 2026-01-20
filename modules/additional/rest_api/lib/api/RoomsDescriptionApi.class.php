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
 *  listCommand										_checkRoomId
 *  readCommand
 *  createCommand
 *  updateCommand
 *  deleteCommand
 *	headersCommand
 *
 **/


class RoomsDescriptionApi extends PHP_CRUD_API {

	/* @var array */
	private $arr_room_ids = array();
	/* @var string */
	private $account_type = null;
	/* @var int */
	private $account_id = null;

	public function __construct(){

		parent::__construct();

		$this->account_type = Session::Get('session_api_account_type');
		$this->account_id = Session::Get('session_api_account_id');
		if($this->account_type == 'hotelowner'){
			$hotels = Session::Get('session_api_hotel_ids');
			if(empty($hotels)){
				$this->_printError('You do not have any hotels', true);
			}
			$hotels_list = implode(',', $hotels);

			$sql = 'SELECT 
					'.TABLE_ROOMS.'.id
				FROM '.TABLE_ROOMS.'
				WHERE '.TABLE_ROOMS.'.hotel_id IN ('.$hotels_list.')';
			$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
			if($result[1] > 0){
				foreach($result[0] as $room_info){
					$this->arr_room_ids[] = $room_info['id'];
				}
			}

			if(empty($this->arr_room_ids)){
				$this->_printError('There is no room for the selected hotel owner', true);
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
		if($this->account_type == 'hotelowner'){
			$room_id = isset($parameters['input']->room_id) ? $parameters['input']->room_id : 0;
			$this->_checkFieldsExists($parameters['input'], array('id'));
			if(!in_array($room_id, $this->arr_room_ids)){
				$this->_printError('You are trying to add a record with an invalid room_id', true);
			}
		}
		parent::createCommand($parameters);
	}
	
	public function updateCommand($parameters)
	{
		if($this->account_type == 'hotelowner'){
			$room_id = isset($parameters['input']->room_id) ? $parameters['input']->room_id : 0;
			$this->_checkFieldsExists($parameters['input'], array('id'));
			if(!in_array($room_id, $this->arr_room_ids)){
				$this->_printError('Parameter room_id contains an invalid value', true);
			}
			$parameters = $this->_addAdditonFilter($parameters);
		}
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
	private function _addAdditonFilter($parameters)
	{
		$table = $parameters['tables'][0];
		$filters = $parameters['filters'];
		if(!empty($filters)){
			$filters[$table]['and'][] = array('room_id', 'IN', $this->arr_room_ids);
		}else{
			$filters = array(
				$table => array(
					'and' => array(array('room_id', 'IN', $this->arr_room_ids))
				)
			);
		}
		$parameters['filters'] = $filters;

		return $parameters;
	}

	/**
	 * @param array $parameters
	 * @return array
	 * */
	private function _checkRoomId($room_id = 0)
	{
		if($room_id != 0){
			$sql = 'SELECT 
					id,
					hotel_id
				FROM '.TABLE_ROOMS.'
				WHERE id = '.(int)$room_id.' AND hotel_id IN ('.implode(',',$this->arr_hotel_ids).')';
			$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
			if($result[1] > 0){
				return true;
			}
		}

		return false;
	}
}
