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
 *  __construct
 *  listCommand
 *  readCommand
 *  createCommand
 *  updateCommand
 *  deleteCommand
 *	headersCommand
 *
 **/


class [TableName]Api extends PHP_CRUD_API {

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
				// Do not have any hotels
			}
		}elseif(!in_array($this->account_type, array('owner', 'mainadmin', 'admin'))){
			// Not login
		}
	}

	/*
	 * Reads all the available records in the table
	 *
	 * GET --> http://[MySite]/api.php/[tabel]?api_key=[APIKey]&api_token=[APIToken]
	 * Example:
	 * http://hotel/api.php/customers?api_key=trb6m7ddbxphsho5amxb&api_token=4848ba971bcdd560b36fdad70ab54486
	 *
	 * */
	public function listCommand($parameters)
	{
		if($this->account_type == 'hotelowner'){
			// Additional verification for hotelowner
		}
		parent::listCommand($parameters);
	}
	
	/*
	 * Reads a single record in the table
	 *
	 * GET --> http://[MySite]/api.php/[tabel]/[id]/?api_key=[APIKey]&api_token=[APIToken]
	 * Example:
	 * http://hotel/api.php/customers/6/?api_key=trb6m7ddbxphsho5amxb&api_token=4848ba971bcdd560b36fdad70ab54486
	 *
	 * */
	public function readCommand($parameters)
	{
		if($this->account_type == 'hotelowner'){
			// Additional verification for hotelowner
		}

		parent::readCommand($parameters);
	}
	
	/*
	 * It creates a single entry in the table
	 * POST --> url: http://[MySite]/api.php/[tabel]
	 *			data: [column] => [value], ... , api_key => [api_key], api_token => [api_token]
	 * Example:
	 *	url:
	 *		http://hotel/api.php/customers
	 *	date:
	 *		[first_name] => Tom
	 *		[last_name]  => Ridle
	 *		[user_name]  => test
	 *		[api_key]    => trb6m7ddbxphsho5amxb
	 *		[api_token]  => 4848ba971bcdd560b36fdad70ab54486
	 * */
	public function createCommand($parameters)
	{
		if($this->account_type == 'hotelowner'){
			// Additional verification for hotelowner
		}

		parent::createCommand($parameters);
	}
	
	/*
	 * Edit a record in the table
	 * PUT --> url: http://[MySite]/api.php/[tabel]/[id]
	 *			data: [column] => [value], ... , api_key => [api_key], api_token => [api_token]
	 * Example:
	 *	url:
	 *		http://hotel/api.php/customers/6
	 *	date:
	 *		[first_name] => Jerry
	 *		[b_address]  => New Youk, Mouse mink 14
	 *		[api_key]    => trb6m7ddbxphsho5amxb
	 *		[api_token]  => 4848ba971bcdd560b36fdad70ab54486
	 * */
	public function updateCommand($parameters)
	{
		if($this->account_type == 'hotelowner'){
			// Additional verification for hotelowner
		}
		parent::updateCommand($parameters);
	}
	
	/*
	 * Delete a record in the table
	 * DELETE --> url: http://[MySite]/api.php/[tabel]/[id]
	 *			data: api_key => [api_key], api_token => [api_token]
	 * Example:
	 *	url:
	 *		http://hotel/api.php/customers/6
	 *	date:
	 *		[api_key]    => trb6m7ddbxphsho5amxb
	 *		[api_token]  => 4848ba971bcdd560b36fdad70ab54486
	 * */
	public function deleteCommand($parameters)
	{
		if($this->account_type == 'hotelowner'){
			// Additional verification for hotelowner
		}
		parent::deleteCommand($parameters);
	}
	
	/*
	 * Returns table structure available for the account
	 *
	 * GET --> http://[MySite]/api.php?api_key=[APIKey]&api_token=[APIToken]
	 * Example:
	 * http://hotel/api.php?api_key=trb6m7ddbxphsho5amxb&api_token=4848ba971bcdd560b36fdad70ab54486
	 *
	 **/
	public function headersCommand($parameters)
	{
		parent::headersCommand($parameters);
	}
}
