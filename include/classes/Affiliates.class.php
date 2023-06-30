<?php

/**
 * Class Affiliates (for ApPHP HotelSite ONLY)
 * -------------- 
 * Description : encapsulates Affiliates operations & properties
 * Updated	    : 10.02.2016
 * Written by  : ApPHP
 * 
 * PUBLIC:					STATIC:					PRIVATE:
 * -----------				-----------				-----------
 * __construct				GetAllAffiliates
 * __destruct              
 * 
 **/

class Affiliates extends MicroGrid {
	
	protected $debug = false;
	
    //------------------------------
	private $sqlFieldDatetimeFormat = '';
	private $sqlFieldDateFormat = '';
	
	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct()
	{
		parent::__construct();

		global $objSettings;
		global $objLogin;
		
		$this->params = array();
		if(isset($_POST['first_name'])) 	 $this->params['first_name']  	= prepare_input($_POST['first_name']);
		if(isset($_POST['last_name']))		 $this->params['last_name']   	= prepare_input($_POST['last_name']);
		if(isset($_POST['company']))   		 $this->params['company']     	= prepare_input($_POST['company']);
		if(isset($_POST['email'])) 			 $this->params['email'] 		= prepare_input($_POST['email']);
		if(isset($_POST['affiliate_id']))  	 $this->params['affiliate_id']  = prepare_input($_POST['affiliate_id']);
		if(isset($_POST['is_active']))  	 $this->params['is_active']     = prepare_input($_POST['is_active']);
		if(isset($_POST['date_added']))  	 $this->params['date_added']    = prepare_input($_POST['date_added']);
		if(isset($_POST['comments'])) 		 $this->params['comments'] 		= prepare_input($_POST['comments']);

		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_AFFILIATES;
		$this->dataSet 		= array();
		$this->error 		= '';
		///$this->languageId  	= (isset($_REQUEST['language_id']) && $_REQUEST['language_id'] != '') ? $_REQUEST['language_id'] : Languages::GetDefaultLang();

		$this->actions      = array('add'=>true, 'edit'=>true, 'details'=>true, 'delete'=>true);
		$this->actionIcons  = true;
		$this->allowRefresh = true;
		$this->allowPrint   = true;
		$this->allowTopButtons = false;

		$this->allowLanguages = false;
		$this->WHERE_CLAUSE = '';		
		$this->ORDER_CLAUSE = 'ORDER BY id DESC';

		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 20;

		$this->isSortingAllowed = true;

		$total_countries = Countries::GetAllCountries();
		$arr_countries = array();
		foreach($total_countries[0] as $key => $val){
			$arr_countries[$val['abbrv']] = $val['name'];
		}

		$total_groups = CustomerGroups::GetAllGroups();
		
		// Get url_parameter for affiliate link
		$url_parameter = ModulesSettings::Get('affiliates', 'url_parameter');

		$this->arr_order_count = array();
		$sql = 'SELECT COUNT('.TABLE_BOOKINGS.'.affiliate_id) as cnt,
					'.TABLE_AFFILIATES.'.affiliate_id
				FROM '.TABLE_BOOKINGS.' 
					RIGHT JOIN '.TABLE_AFFILIATES.' ON '.TABLE_AFFILIATES.'.affiliate_id = '.TABLE_BOOKINGS.'.affiliate_id
				WHERE 1
				GROUP BY '.TABLE_AFFILIATES.'.affiliate_id';
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		if($result[1] > 0){
			foreach($result[0] as $order_info){
				if($order_info['cnt'] == 0){
					$this->arr_order_count[$order_info['affiliate_id']] = '<label class="mgrid_label">'._ORDERS.' (0)'.'</label>';
				}else{
					$this->arr_order_count[$order_info['affiliate_id']] = '<label class="mgrid_label"><a href="'.APPHP_BASE.'index.php?admin=mod_booking_bookings&mg_action=view&mg_operation=filtering&mg_search_status=active&filter_by_'.TABLE_BOOKINGS.'affiliate_id='.$order_info['affiliate_id'].'&mg_search_status=active">'._ORDERS.' </a>'.' ('.$order_info['cnt'].')</label>';
				}
			}
		}

		$this->generationString = '[ <a href="javascript:generateRandomAlphaNumeric(\'affiliate_id\', 10)">'._GENERATE.'</a> ]';
		
		$this->isFilteringAllowed = true;
		$this->arrFilteringFields = array(
			_FIRST_NAME  	=> array('table'=>$this->tableName, 'field'=>'first_name', 'type'=>'text', 'sign'=>'like%', 'width'=>'90px'),
			_LAST_NAME  	=> array('table'=>$this->tableName, 'field'=>'last_name', 'type'=>'text', 'sign'=>'like%', 'width'=>'90px'),
			_EMAIL  		=> array('table'=>$this->tableName, 'field'=>'email', 'type'=>'text', 'sign'=>'like%', 'width'=>'100px'),
		);


		$datetime_format = get_datetime_format();		
		$date_format_view = get_date_format('view');
		$date_format_edit = get_date_format('edit');
		
		$arr_is_active = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');

		if($objSettings->GetParameter('date_format') == 'mm/dd/yyyy'){
			$this->sqlFieldDatetimeFormat = '%b %d, %Y %H:%i';
			$this->sqlFieldDateFormat = '%b %d, %Y';
		}else{
			$this->sqlFieldDatetimeFormat = '%d %b, %Y %H:%i';
			$this->sqlFieldDateFormat = '%d %b, %Y';
		}
		$this->SetLocale(Application::Get('lc_time_name'));

		//---------------------------------------------------------------------- 
		// VIEW MODE
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT
		                            '.$this->tableName.'.*,
									'.$this->tableName.'.affiliate_id as order_count,
									DATE_FORMAT(date_added, "'.$this->sqlFieldDateFormat.'") as date_added,
									CONCAT(first_name, " ", last_name) as full_name
								FROM '.$this->tableName;		
		// define view mode fields
		$this->arrViewModeFields = array(			
			'full_name'    	=> array('title'=>_NAME, 'type'=>'label', 'align'=>'left', 'width'=>'210px', 'maxlength'=>'25'),
			'affiliate_id'  => array('title'=>_AFFILIATE_ID, 'type'=>'label', 'align'=>'left', 'width'=>'110px', 'maxlength'=>'25'),
			'email' 	   	=> array('title'=>_EMAIL_ADDRESS, 'type'=>'link', 'href'=>'mailto:{email}', 'align'=>'left', 'maxlength'=>'28', 'width'=>'210px'),
			'company'  		=> array('title'=>_COMPANY, 'type'=>'label', 'align'=>'left', 'width'=>'', 'maxlength'=>'25'),
			'date_added'  	=> array('title'=>_DATE_ADDED, 'type'=>'label', 'align'=>'center', 'width'=>'110px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'maxlength'=>''),
			'order_count'   => array('title'=>'', 'type'=>'enum',  'align'=>'center', 'width'=>'100px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$this->arr_order_count, 'details'=>_ORDERS.' (0)'),
			'is_active'    	=> array('title'=>_ACTIVE, 'type'=>'enum', 'align'=>'center', 'width'=>'85px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_is_active),
			'id'           	=> array('title'=>'ID', 'type'=>'label', 'align'=>'center', 'width'=>'60px'),
		);			
		
		//---------------------------------------------------------------------- 
		// ADD MODE
		//---------------------------------------------------------------------- 
		// define add mode fields
		$this->arrAddModeFields = array(
			'first_name'  	=> array('title'=>_FIRST_NAME,'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'32', 'required'=>true, 'validation_type'=>'text'),
			'last_name' 	=> array('title'=>_LAST_NAME, 'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'32', 'required'=>true, 'validation_type'=>'text'),
			'email' 		=> array('title'=>_EMAIL_ADDRESS,	 'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'100', 'required'=>true, 'validation_type'=>'email', 'unique'=>true, 'autocomplete'=>'off'),
			'company' 		=> array('title'=>_COMPANY, 'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'255', 'required'=>false, 'validation_type'=>'text'),
			'affiliate_id' 	=> array('title'=>_AFFILIATE_ID, 'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'10', 'required'=>true, 'validation_type'=>'alpha_numeric', 'unique'=>true, 'post_html'=>' '.$this->generationString),			
			'date_added'	=> array('title'=>'', 'type'=>'hidden', 'width'=>'210px', 'required'=>true, 'default'=>date('Y-m-d H:i:s')),
			'is_active'		=> array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),
			'comments'      => array('title'=>_COMMENTS, 'type'=>'textarea', 'width'=>'310px', 'required'=>false, 'height'=>'90px', 'editor_type'=>'simple', 'readonly'=>false, 'default'=>'', 'maxlength'=>'1024', 'validation_type'=>'', 'validation_maxlength'=>'1024', 'unique'=>false),
		);

		//---------------------------------------------------------------------- 
		// EDIT MODE
		// * password field must be written directly in SQL!!!
		//---------------------------------------------------------------------- 
		$this->EDIT_MODE_SQL = 'SELECT
									'.$this->tableName.'.'.$this->primaryKey.',
		                            '.$this->tableName.'.*,
									DATE_FORMAT('.$this->tableName.'.date_added, \''.$this->sqlFieldDatetimeFormat.'\') as date_added,
									CONCAT("'.APPHP_BASE.'index.php?'.$url_parameter.'=", affiliate_id) as link
								FROM '.$this->tableName.'
								WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';

		// define edit mode fields
		$this->arrEditModeFields = array(
			'first_name'  	=> array('title'=>_FIRST_NAME,'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'32', 'required'=>true, 'validation_type'=>'text'),
			'last_name' 	=> array('title'=>_LAST_NAME, 'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'32', 'required'=>true, 'validation_type'=>'text'),
			'email' 		=> array('title'=>_EMAIL_ADDRESS,	 'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'100', 'required'=>true, 'validation_type'=>'email', 'unique'=>true, 'autocomplete'=>'off'),
			'company' 		=> array('title'=>_COMPANY, 'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'255', 'required'=>false, 'validation_type'=>'text'),
			'affiliate_id' 	=> array('title'=>_AFFILIATE_ID, 'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'10', 'required'=>true, 'validation_type'=>'alpha_numeric', 'unique'=>true, 'post_html'=>' '.$this->generationString),
			'is_active'		=> array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),
			'date_added'	=> array('title'=>_DATE_ADDED, 'type'=>'label'),
			'comments'      => array('title'=>_COMMENTS, 'type'=>'textarea', 'width'=>'310px', 'required'=>false, 'height'=>'90px', 'editor_type'=>'simple', 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'maxlength'=>'1024', 'validation_maxlength'=>'1024', 'unique'=>false),
			'link'			=> array('title'=>_YOUR_AFFILIATE_LINK, 'type'=>'label'),
		);
		
		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = 'SELECT
									'.$this->tableName.'.'.$this->primaryKey.',
		                            '.$this->tableName.'.*,
									DATE_FORMAT('.$this->tableName.'.date_added, \''.$this->sqlFieldDatetimeFormat.'\') as date_added,
									CONCAT("'.APPHP_BASE.'index.php?'.$url_parameter.'=", affiliate_id) as link
								FROM '.$this->tableName.'
								WHERE '.$this->primaryKey.' = _RID_';

		// define edit mode fields
		$this->arrDetailsModeFields = array(
			'first_name'  	=> array('title'=>_FIRST_NAME,'type'=>'label'),
			'last_name' 	=> array('title'=>_LAST_NAME, 'type'=>'label'),
			'email' 		=> array('title'=>_EMAIL_ADDRESS, 'type'=>'label'),
			'company' 		=> array('title'=>_COMPANY, 'type'=>'label'),
			'affiliate_id' 	=> array('title'=>_AFFILIATE_ID, 'type'=>'label'),			
			'is_active'	        => array('title'=>_ACTIVE,	 'type'=>'label', 'type'=>'enum', 'source'=>$arr_is_active),
			'date_added'	=> array('title'=>_DATE_ADDED, 'type'=>'label'),
			'comments'      => array('title'=>_COMMENTS, 'type'=>'label'),
			'link'			=> array('title'=>_YOUR_AFFILIATE_LINK, 'type'=>'label'),

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
	 * Returns all array of all affiliates
	 * @param $where_clause
	 * @return array
	 */
	public static function GetAllAffiliates($where_clause = '')
	{
		$sql = 'SELECT
					'.TABLE_AFFILIATES.'.*
				FROM '.TABLE_AFFILIATES.'
				WHERE
					'.(!empty($where_clause) ? ' AND '.$where_clause : '1').'
				ORDER BY '.TABLE_AFFILIATES.'.first_name, '.TABLE_AFFILIATES.'.last_name ASC ';			
		return database_query($sql, DATA_AND_ROWS);
	}

}
