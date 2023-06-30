<?php

/***
 * CustomerAwardPoints Class (for ApPHP HotelSite ONLY)
 * ------------------ 
 * Description : encapsulates customer balances properties
 * Written by  : ApPHP
 * Version     : 1.0.1
 * Updated	   : 10.02.2018
 *
 * PUBLIC:				  	STATIC:				 	PRIVATE:
 * 	------------------	  	---------------     	---------------
 * __construct             GetTotalFunds
 * __destruct
 * AfterInsertRecord
 * BeforeDeleteRecord
 * RemoveRecord
 * 
 * ChangeLog:
 * ---------
 * 1.0.1
 * 	- 
 * 	- 
 * 	-
 * 	-
 * 	-
 * 
 **/


class CustomerAwardPoints extends MicroGrid {
	
	protected $debug = false;
	
	private $sqlFieldDatetimeFormat = '';
	

	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct($customer_id = 0, $page = '')
	{		
		parent::__construct();
		
		global $objLogin;
		global $objSettings;

		$this->params = array();
		
		## for standard fields
		if(isset($_POST['customer_id']))   	$this->params['customer_id'] = prepare_input($_POST['customer_id']);
		if(isset($_POST['admin_id']))   	$this->params['admin_id'] = prepare_input($_POST['admin_id']);
		if(isset($_POST['funds']))   	 	$this->params['funds'] = prepare_input($_POST['funds']);
		if(isset($_POST['voucher']))   	 	$this->params['voucher'] = prepare_input($_POST['voucher']);
		if(isset($_POST['comments'])) 		$this->params['comments'] = prepare_input($_POST['comments']);
		if(isset($_POST['date_added']))  	$this->params['date_added'] = prepare_input($_POST['date_added']);
		
		## for checkboxes 
		//$this->params['field4'] = isset($_POST['field4']) ? prepare_input($_POST['field4']) : '0';

		## for images (not necessary)
		//if(isset($_POST['icon'])){
		//	$this->params['icon'] = prepare_input($_POST['icon']);
		//}elseif(isset($_FILES['icon']['name']) && $_FILES['icon']['name'] != ''){
		//	// nothing 			
		//}elseif(self::GetParameter('action') == 'create'){
		//	$this->params['icon'] = '';
		//}

		## for files:
		// define nothing

		//$this->params['language_id'] = MicroGrid::GetParameter('language_id');
	
		//$this->uPrefix 		= 'prefix_';
		
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_CUSTOMER_AWARD_POINTS;
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = ($page == 'my_funds')
								? 'index.php?customer=my_funds'
								: 'index.php?admin=mod_customers_agency_funds'.($customer_id ? '&aid='.$customer_id : '');

		$allow_adding = true;
		$allow_editing = true;
		$allow_deleting = false;
		$allow_details = true;
		// Block actions for Customer > My Funds page
		if($page == 'my_funds'){
			$allow_adding = false;
			$allow_deleting = false;		
			$allow_editing = false;
			$allow_details = false;
		}
		$this->actions = array('add'=>$allow_adding, 'edit'=>$allow_editing, 'details'=>$allow_details, 'delete'=>$allow_deleting);

		$this->actionIcons  = true;
		$this->allowRefresh = true;
		$this->allowPrint   = true;

		$this->allowLanguages = false;
		$this->languageId  	= ''; //($this->params['language_id'] != '') ? $this->params['language_id'] : Languages::GetDefaultLang();
		$this->WHERE_CLAUSE = ($customer_id ? 'WHERE customer_id='.(int)$customer_id : ''); // WHERE .... / 'WHERE language_id = \''.$this->languageId.'\'';				
		$this->ORDER_CLAUSE = 'ORDER BY '.$this->tableName.'.date_added DESC';
		
		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 20;

		$this->isSortingAllowed = true;

		// Currency
		$currency_format = get_currency_format();		
		$default_currency = Currencies::GetDefaultCurrency();

		// Date formats
		if($objSettings->GetParameter('date_format') == 'mm/dd/yyyy'){
			$this->sqlFieldDatetimeFormat = '%b %d, %Y %H:%i';
			$this->sqlFieldDateFormat = '%b %d, %Y';
		}else{
			$this->sqlFieldDatetimeFormat = '%d %b, %Y %H:%i';
			$this->sqlFieldDateFormat = '%d %b, %Y';
		}
		$this->SetLocale(Application::Get('lc_time_name'));

		$this->isFilteringAllowed = false;
		// define filtering fields
		$this->arrFilteringFields = array(
			// 'Caption_1'  => array('table'=>'', 'field'=>'', 'type'=>'text', 'sign'=>'=|like%|%like|%like%', 'width'=>'80px'),
			// 'Caption_2'  => array('table'=>'', 'field'=>'', 'type'=>'dropdownlist', 'source'=>array(), 'sign'=>'=|like%|%like|%like%', 'width'=>'130px'),
		);

		// define aggregate fields for View Mode
		$this->isAggregateAllowed = true;
		$this->arrAggregateFields = array(
			'funds' => array('function'=>'SUM', 'align'=>'right', 'aggregate_by'=>'real_finds', 'decimal_place'=>2),
		);

		// prepare languages array		
		/// $total_languages = Languages::GetAllActive();
		/// $arr_languages      = array();
		/// foreach($total_languages[0] as $key => $val){
		/// 	$arr_languages[$val['abbreviation']] = $val['lang_name'];
		/// }
		

		//---------------------------------------------------------------------- 
		// VIEW MODE
		// format: strip_tags
		// format: nl2br
		// format: 'format'=>'date', 'format_parameter'=>'M d, Y, g:i A' + IF(date_created IS NULL, '', date_created) as date_created,
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT '.$this->tableName.'.'.$this->primaryKey.',
									'.$this->tableName.'.customer_id,
									'.$this->tableName.'.admin_id,
									'.$this->tableName.'.funds,
									'.$this->tableName.'.comments,
									'.$this->tableName.'.voucher,
									'.$this->tableName.'.removed_by,
									IF(
										'.$this->tableName.'.is_deleted = 0,
										'.$this->tableName.'.funds,
										0
									) as real_finds,
									IF(
										'.$this->tableName.'.is_deleted = 0,
										'.($page == 'my_funds'
											? '"",'
											: 'CONCAT("<nobr><a href=\"javascript:void(0);\" title=\"'._REMOVE.'\" onclick=\"javascript:customerFoundRemove(\''.TABLE_CUSTOMER_FUNDS.'\', \'", '.$this->tableName.'.'.$this->primaryKey.', "\');\">[ '._REMOVE.' ]</a></nobr>"),').'
										"<span class=lightgray> '._REMOVED.'</span>"
									) as fund_remove,
									DATE_FORMAT('.$this->tableName.'.date_added, \''.$this->sqlFieldDateFormat.'\') as mod_date_added,
									'.TABLE_CUSTOMERS.'.company as agency,
									CONCAT('.TABLE_ACCOUNTS.'.first_name, " ", '.TABLE_ACCOUNTS.'.last_name) as added_by,
									DATE_FORMAT('.$this->tableName.'.removal_date, \''.$this->sqlFieldDateFormat.'\') as mod_removal_date
								FROM '.$this->tableName.'
									INNER JOIN '.TABLE_CUSTOMERS.' ON '.$this->tableName.'.customer_id = '.TABLE_CUSTOMERS.'.id
									INNER JOIN '.TABLE_ACCOUNTS.' ON '.$this->tableName.'.admin_id = '.TABLE_ACCOUNTS.'.id';
		// define view mode fields
		$this->arrViewModeFields = array(
//			'agency'           => array('title'=>_AGENCY, 'type'=>'label', 'align'=>'left', 'width'=>'120px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>true, 'maxlength'=>'70', 'format'=>'', 'format_parameter'=>''),
			'mod_date_added'   => array('title'=>_DATE_ADDED, 'type'=>'label', 'align'=>'center', 'width'=>'120px', 'format'=>'', 'format_parameter'=>''),
			'funds'            => array('title'=>_FUNDS, 'type'=>'label', 'align'=>'center', 'width'=>'100px', 'pre_html'=>$default_currency, 'format'=>'currency', 'format_parameter'=>$currency_format.'|2'),
			'voucher'          => array('title'=>_RECEIPT_VOUCHER, 'type'=>'label', 'align'=>'left', 'width'=>'200px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>true, 'maxlength'=>'70', 'format'=>'', 'format_parameter'=>''),
			'comments'         => array('title'=>_COMMENTS, 'type'=>'label', 'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>true, 'maxlength'=>'70', 'format'=>'', 'format_parameter'=>''),
			'added_by'         => array('title'=>_ADDED_BY, 'type'=>'label', 'align'=>'left', 'width'=>'170px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>true, 'maxlength'=>'70', 'format'=>'', 'format_parameter'=>''),
			'removed_by'       => array('title'=>_REMOVED_BY, 'type'=>'label', 'align'=>'left', 'width'=>'110px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>true, 'maxlength'=>'70', 'format'=>'', 'format_parameter'=>''),
			'mod_removal_date' => array('title'=>_REMOVAL_DATE, 'type'=>'label', 'align'=>'center', 'width'=>'120px', 'format'=>'', 'format_parameter'=>''),
			'fund_remove'      => array('title'=>'', 'type'=>'label', 'align'=>'center', 'width'=>'90px'),
		);
		
		//---------------------------------------------------------------------- 
		// ADD MODE
		// - Validation Type: alpha|numeric|float|alpha_numeric|text|email|ip_address|password
		// 	 Validation Sub-Type: positive (for numeric and float)
		//   Ex.: 'validation_type'=>'numeric', 'validation_type'=>'numeric|positive'
		// - Validation Max Length: 12, 255... Ex.: 'validation_maxlength'=>'255'
		// - Validation Max Value: 12, 255... Ex.: 'validation_maximum'=>'99.99'
		//---------------------------------------------------------------------- 
		// define add mode fields
		$this->arrAddModeFields = array(
			'customer_id'  	=> array('title'=>'', 'type'=>'hidden', 'required'=>true, 'readonly'=>false, 'default'=>$customer_id),
			'admin_id'  	=> array('title'=>'', 'type'=>'hidden', 'required'=>true, 'readonly'=>false, 'default'=>$objLogin->GetLoggedID()),
			'date_added'  	=> array('title'=>'', 'type'=>'hidden', 'required'=>true, 'readonly'=>false, 'default'=>date('Y-m-d H:i:s')),
			'funds'  		=> array('title'=>_FUNDS, 'type'=>'textbox',  'width'=>'100px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'8', 'default'=>'', 'validation_type'=>'float|positive', 'validation_minimum'=>'1', 'pre_html'=>$default_currency.' '),
			'comments' 		=> array('title'=>_COMMENTS, 'type'=>'textarea', 'width'=>'410px', 'height'=>'90px', 'editor_type'=>'simple', 'readonly'=>false, 'default'=>'', 'required'=>false, 'validation_type'=>'', 'maxlength'=>'255', 'validation_maxlength'=>'255', 'unique'=>false),
			'voucher' 		=> array('title'=>_RECEIPT_VOUCHER, 'type'=>'textbox', 'width'=>'410px', 'editor_type'=>'simple', 'readonly'=>false, 'default'=>'', 'required'=>false, 'validation_type'=>'', 'maxlength'=>'100', 'validation_maxlength'=>'100', 'unique'=>false),
		);

		//---------------------------------------------------------------------- 
		// EDIT MODE
		// - Validation Type: alpha|numeric|float|alpha_numeric|text|email|ip_address|password
		//   Validation Sub-Type: positive (for numeric and float)
		//   Ex.: 'validation_type'=>'numeric', 'validation_type'=>'numeric|positive'
		// - Validation Max Length: 12, 255... Ex.: 'validation_maxlength'=>'255'
		// - Validation Max Value: 12, 255... Ex.: 'validation_maximum'=>'99.99'
		//---------------------------------------------------------------------- 
		$this->EDIT_MODE_SQL = 'SELECT
								'.$this->tableName.'.'.$this->primaryKey.',
								'.$this->tableName.'.customer_id,
								'.$this->tableName.'.admin_id,
								'.$this->tableName.'.funds,
								'.$this->tableName.'.comments,
								'.$this->tableName.'.voucher,
								'.$this->tableName.'.date_added,
								'.$this->tableName.'.removal_date,
								'.$this->tableName.'.removed_by,
								'.$this->tableName.'.removed_comments,
								DATE_FORMAT('.$this->tableName.'.date_added, \''.$this->sqlFieldDatetimeFormat.'\') as mod_date_added,
								'.TABLE_CUSTOMERS.'.company as agency
							FROM '.$this->tableName.'
								INNER JOIN '.TABLE_CUSTOMERS.' ON '.$this->tableName.'.customer_id = '.TABLE_CUSTOMERS.'.id
							WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';		
		// define edit mode fields
		$this->arrEditModeFields = array(
			'agency'  		=> array('title'=>_AGENCY, 'type'=>'label',    'format'=>'', 'format_parameter'=>'', 'visible'=>true),
			'funds'  		=> array('title'=>_FUNDS, 'type'=>'textbox',  'width'=>'100px', 'required'=>true, 'readonly'=>true, 'maxlength'=>'8', 'default'=>'0', 'validation_type'=>'float|positive', 'validation_minimum'=>'1', 'pre_html'=>$default_currency.' '),
			'comments' 		=> array('title'=>_COMMENTS, 'type'=>'textarea', 'width'=>'410px', 'height'=>'90px', 'editor_type'=>'simple', 'readonly'=>false, 'default'=>'', 'required'=>false, 'validation_type'=>'', 'maxlength'=>'255', 'validation_maxlength'=>'255', 'unique'=>false),
			'voucher' 		=> array('title'=>_RECEIPT_VOUCHER, 'type'=>'textbox', 'width'=>'410px', 'editor_type'=>'simple', 'readonly'=>false, 'default'=>'', 'required'=>false, 'validation_type'=>'', 'maxlength'=>'100', 'validation_maxlength'=>'100', 'unique'=>false),
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			'agency'  		=> array('title'=>_AGENCY, 'type'=>'label', 'format'=>'', 'format_parameter'=>'', 'visible'=>true),
			'funds'  		=> array('title'=>_FUNDS, 'type'=>'label', 'pre_html'=>$default_currency.' '),
			'comments' 		=> array('title'=>_COMMENTS, 'type'=>'label', 'format'=>'', 'format_parameter'=>'', 'visible'=>true),
			'voucher' 		=> array('title'=>_RECEIPT_VOUCHER, 'type'=>'label', 'format'=>'', 'format_parameter'=>'', 'visible'=>true),
		);
	}
	
	//==========================================================================
    // Class Destructor
	//==========================================================================
    function __destruct()
	{
		// echo 'this object has been destroyed';
    }

	/**
	 *	Get value of total funds of customers 
	 */
	public static function GetTotalFunds()
	{
		//$sql = 'SELECT customer_id, SUM(funds) as total_funds
		//		FROM '.TABLE_CUSTOMER_FUNDS.'
		//		WHERE is_deleted = 0
		//		GROUP BY customer_id';
		//$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		//return $result;
	}		

	/**
	 * After Insert handler
	 */
	public function AfterInsertRecord(){
	//    // $this->lastInsertId - currently inserted record
	//    // $this->params - current record insert info
	//	
	//	$sql = 'UPDATE '.TABLE_CUSTOMERS.'
	//			SET balance = balance + '.(float)$this->params['funds'].'
	//			WHERE id = '.(int)$this->params['customer_id'];
	//			
	//	database_void_query($sql);	
	}

	/**
	 * Before Delete handler
	 */
	public function BeforeDeleteRecord(){
	//   	// $this->curRecordId - current record
	//
	//	// Get current funds value
	//	$sql = 'SELECT * FROM '.TABLE_CUSTOMER_FUNDS.' WHERE id = '.(int)$this->curRecordId; 
	//	$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);		
	//	if($result[1] > 0){
	//		$current_funds = $result[0]['funds'];
	//		$customer_id = $result[0]['customer_id'];
	//		$customer_balance = 0;
	//		
	//		// Get total customer funds
	//		$sql = 'SELECT * FROM '.TABLE_CUSTOMERS.' WHERE id = '.(int)$customer_id;
	//		$customer_result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
	//		if($customer_result[1] > 0){
	//			$customer_balance = $customer_result[0]['balance'];
	//		}
	//		
	//		if($customer_balance > 0 && ($customer_balance - $current_funds) > 0){
	//			return true;
	//		}else{
	//			$this->error = _CANNOT_REMOVE_FUNDS_ALERT;
	//			return false;	
	//		}
	//	}
	//   	
	//	return true;
	}

	/**
	 *	Remove record
	 */
	public function RemoveRecord($rid, $comments = '')
	{
		//global $objLogin;
		//
		//if(strtolower(SITE_MODE) == 'demo'){
		//	$this->error = _OPERATION_BLOCKED;
		//	return false;
		//}
		//
		//if(!$objLogin->IsLoggedInAs('owner','mainadmin')){
		//	return false;
		//}
		//
		//$sql = 'SELECT 
		//			'.$this->tableName.'.customer_id,
		//			'.$this->tableName.'.funds
		//		FROM '.$this->tableName.'
		//		WHERE  '.$this->tableName.'.'.$this->primaryKey.' = '.(int)$rid.'
		//		LIMIT 1';
		//		
		//$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		//if($result[1] < 1){
		//	return false;
		//}
		//
		//$full_name = $objLogin->GetLoggedFirstName().' '.$objLogin->GetLoggedLastName();
		//$removal_date = date('Y-m-d H:i:s');
		//
		//$sql = 'UPDATE '.$this->tableName.'
		//		SET
		//			is_deleted = 1,
		//			removal_date = \''.$removal_date.'\',
		//			removed_by = \''.$full_name.'\',
		//			removed_comments = \''.$comments.'\'
		//		WHERE '.$this->primaryKey.' = '.(int)$rid;
		//if(!database_void_query($sql)) return false;
		//
		//$sql = 'UPDATE '.TABLE_CUSTOMERS.'
		//		SET balance = balance - '.$result[0]['funds'].'
		//		WHERE id = '.$result[0]['customer_id'];
		//		
		//database_void_query($sql);	
		//
		//return true; 
	}	
	
	/**
	 * Before drawing Edit Mode
	 */
	public function BeforeEditRecord()
	{
		//$sql = 'SELECT
		//			'.$this->tableName.'.id,
		//			'.$this->tableName.'.removal_date,
		//			'.$this->tableName.'.removed_by,
		//			'.$this->tableName.'.removed_comments
		//		FROM '.$this->tableName.'
		//		WHERE '.$this->tableName.'.id = '.(int)$this->curRecordId.' AND '.$this->tableName.'.is_deleted = 1';
		//$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		//if($result[1] > 0){
		//	$this->arrEditModeFields['removed_comments'] = array('title'=>_COMMENTS_REMOVAL, 'type'=>'textarea', 'width'=>'410px', 'height'=>'90px', 'editor_type'=>'simple', 'readonly'=>false, 'default'=>'', 'required'=>false, 'validation_type'=>'', 'maxlength'=>'255', 'validation_maxlength'=>'255', 'unique'=>false);
		//	$this->arrEditModeFields['removal_date'] = array('title'=>_REMOVAL_DATE, 'type'=>'label');
		//	$this->arrEditModeFields['removed_by'] = array('title'=>_REMOVED_BY, 'type'=>'label');
		//}
		//
		//return true;
	}

	/**
	 * Before drawing Preview Mode
	 */
	public function BeforeDetailsRecord()
	{
		//$sql = 'SELECT
		//			'.$this->tableName.'.id,
		//			'.$this->tableName.'.removal_date,
		//			'.$this->tableName.'.removed_by,
		//			'.$this->tableName.'.removed_comments
		//		FROM '.$this->tableName.'
		//		WHERE '.$this->tableName.'.id = '.(int)$this->curRecordId.' AND '.$this->tableName.'.is_deleted = 1';
		//$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		//if($result[1] > 0){
		//	$this->arrDetailsModeFields['removed_comments'] = array('title'=>_COMMENTS_REMOVAL, 'type'=>'label');
		//	$this->arrDetailsModeFields['removal_date'] = array('title'=>_REMOVAL_DATE, 'type'=>'label');
		//	$this->arrDetailsModeFields['removed_by'] = array('title'=>_REMOVED_BY, 'type'=>'label');
		//}
		//
		//return true;
	}
}
