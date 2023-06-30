<?php

/**
 *	Class Extras (for Hotel site ONLY)
 *  --------------
 *  Description : encapsulates Extras class properties
 *  Updated	    : 07.06.2012
 *	Written by  : ApPHP
 *
 *	PUBLIC				  	STATIC				 	PRIVATE
 * 	------------------	  	---------------     	---------------
 *	__construct             GetAllExtras            ValidateTranslationFields
 *	__destruct              GetExtrasInfo
 *	BeforeInsertRecord      GetExtrasSum
 *	AfterInsertRecord       GetExtrasList
 *	BeforeUpdateRecord
 *	AfterUpdateRecord
 *	AfterDeleteRecord
 *	
 **/


class Extras extends MicroGrid {
	
	protected $debug = false;
	
	private $arrTranslations = '';
	protected $assigned_to_hotels = '';

    private $hotelOwner = '';
	
	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct()
	{		
		parent::__construct();

        global $objLogin;

        $this->hotelOwner = $objLogin->IsLoggedInAs('hotelowner') ? true : false;
		
		$this->params = array();		
		if(isset($_POST['hotel_id']))   	$this->params['hotel_id'] = prepare_input($_POST['hotel_id']);
		if(isset($_POST['price']))   		$this->params['price'] = prepare_input($_POST['price']);
		if(isset($_POST['maximum_count']))  $this->params['maximum_count'] = prepare_input($_POST['maximum_count']);
		if(isset($_POST['priority_order'])) $this->params['priority_order'] = prepare_input($_POST['priority_order']);
		if(isset($_POST['is_active']))  	$this->params['is_active']  = (int)$_POST['is_active']; else $this->params['is_active'] = '0';
		
		## for checkboxes 
		//$this->params['field4'] = isset($_POST['field4']) ? prepare_input($_POST['field4']) : '0';

		## for images (not necessary)
		//if(isset($_POST['icon'])){
		//	$this->params['icon'] = prepare_input($_POST['icon']);
		//}elseif(isset($_FILES['icon']['name']) && $_FILES['icon']['name'] != ''){
		//	// nothing 			
		//}elseif (self::GetParameter('action') == 'create'){
		//	$this->params['icon'] = '';
		//}

		## for files:
		// define nothing

		$this->params['language_id'] = MicroGrid::GetParameter('language_id');
	
		//$this->uPrefix 		= 'prefix_';
		
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_EXTRAS; // TABLE_NAME
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=mod_booking_extras';
		$this->actions      = array('add'=>true, 'edit'=>true, 'details'=>true, 'delete'=>true);
		$this->actionIcons  = true;
		$this->allowRefresh = true;

		$this->allowLanguages = false;
		$this->languageId  	=  $objLogin->GetPreferredLang();

		$this->WHERE_CLAUSE = 'WHERE 1 = 1';
		$hotels_list = '';
		if($this->hotelOwner){
			$hotels = $objLogin->AssignedToHotels();
			$hotels_list = implode(',', $hotels);
			$this->assigned_to_hotels = ' AND '.(!empty($this->hotelOwner) ? $this->tableName.'.hotel_id IN ('.$hotels_list.') ' : '1 = 0');
		}
		$this->WHERE_CLAUSE .= $this->assigned_to_hotels;

        // prepare hotels array
        $where_clause = '';
        if($this->hotelOwner){
            $where_clause = !empty($hotels_list) ? TABLE_HOTELS.'.id IN ('.$hotels_list.')' : '1 = 0';
        }
		$total_hotels = Hotels::GetAllHotels($where_clause);
		$arr_hotels = $objLogin->IsLoggedInAs('hotelowner') ? array() : array('0'=>'-- '._ALL.' --');
		foreach($total_hotels[0] as $key => $val){
			$arr_hotels[$val['id']] = $val['name'].($val['is_active'] == 0 ? ' ('._NOT_ACTIVE.')' : '');
		}		
		
		$this->ORDER_CLAUSE = 'ORDER BY '.$this->tableName.'.hotel_id ASC, '.$this->tableName.'.priority_order ASC';
		
		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 20;

		$this->isSortingAllowed = true;

		$this->isFilteringAllowed = true;

        $hotel_name = FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL;
		// define filtering fields
		$this->arrFilteringFields = array(
			$hotel_name  => array('table'=>$this->tableName, 'field'=>'hotel_id', 'type'=>'dropdownlist', 'source'=>$arr_hotels, 'sign'=>'=', 'width'=>'130px', 'visible'=>true),
		);

		$arr_is_active = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');

		$default_currency = Currencies::GetDefaultCurrency();
		$currency_format = get_currency_format();

		///////////////////////////////////////////////////////////////////////////////
		// #002. prepare translation fields array
		$this->arrTranslations = $this->PrepareTranslateFields(
			array('name', 'description')
		);
		///////////////////////////////////////////////////////////////////////////////			

		///////////////////////////////////////////////////////////////////////////////			
		// #003. prepare translations array for add/edit/detail modes
		// REMEMBER! to add '.$sql_translation_description.' in EDIT_MODE_SQL
		$sql_translation_description = $this->PrepareTranslateSql(
			TABLE_EXTRAS_DESCRIPTION,
			'extra_id',
			array('name', 'description')
		);
		///////////////////////////////////////////////////////////////////////////////			

	
		//---------------------------------------------------------------------- 
		// VIEW MODE
		// format: strip_tags
		// format: nl2br
		// format: 'format'=>'date', 'format_parameter'=>'M d, Y, g:i A' + IF(date_created IS NULL, '', date_created) as date_created,
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT
									'.$this->tableName.'.'.$this->primaryKey.',
									'.$this->tableName.'.hotel_id,
									'.$this->tableName.'.is_active,
									'.$this->tableName.'.maximum_count,
									'.$this->tableName.'.priority_order,
									ed.name,
									ed.description,
									CONCAT(\''.$default_currency.'\', '.$this->tableName.'.price) as mod_price
								FROM ('.$this->tableName.' 
									LEFT OUTER JOIN '.TABLE_EXTRAS_DESCRIPTION.' ed ON '.$this->tableName.'.id = ed.extra_id AND ed.language_id = \''.$this->languageId.'\')';		
		// define view mode fields
		$this->arrViewModeFields = array(
			'name'  	     => array('title'=>_SERVICE, 'type'=>'label', 'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'40', 'format'=>'', 'format_parameter'=>''),
			'description'    => array('title'=>_DESCRIPTION, 'type'=>'label', 'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'50', 'format'=>'', 'format_parameter'=>''),
			'hotel_id'       => array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL, 'type'=>'enum',  'align'=>'center', 'width'=>'140px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_hotels),
			'mod_price'      => array('title'=>_PRICE,  'type'=>'label', 'align'=>'right', 'width'=>'90px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'', 'format'=>'currency', 'format_parameter'=>$currency_format.'|2'),
			'maximum_count'  => array('title'=>_COUNT,  'type'=>'label', 'align'=>'center', 'width'=>'100px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
			'is_active'      => array('title'=>_ACTIVE, 'type'=>'enum',  'align'=>'center', 'width'=>'60px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_is_active),
			'priority_order' => array('title'=>_ORDER,  'type'=>'label', 'align'=>'center', 'width'=>'110px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>'', 'movable'=>true),			
		);
		
		//---------------------------------------------------------------------- 
		// ADD MODE
		// - Validation Type: alpha|numeric|float|alpha_numeric|text|email|ip_address|password|date
		// 	 Validation Sub-Type: positive (for numeric and float)
		//   Ex.: 'validation_type'=>'numeric', 'validation_type'=>'numeric|positive'
		// - Validation Max Length: 12, 255... Ex.: 'validation_maxlength'=>'255'
		// - Validation Min Length: 4, 6... Ex.: 'validation_minlength'=>'4'
		// - Validation Max Value: 12, 255... Ex.: 'validation_maximum'=>'99.99'
		//---------------------------------------------------------------------- 
		// define add mode fields
		$this->arrAddModeFields = array(		    
			'hotel_id'       => array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL,  'type'=>'enum',     'width'=>'', 'required'=>($objLogin->IsLoggedInAs('hotelowner') ? true : false), 'validation_type'=>'numeric|positive', 'align'=>'left', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_hotels),
			'price'  	     => array('title'=>_PRICE,  'type'=>'textbox',  'width'=>'100px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'10', 'default'=>'0', 'validation_type'=>'float|positive', 'validation_maximum'=>'10000000', 'unique'=>false, 'visible'=>true, 'pre_html'=>$default_currency.' '),
			'maximum_count'  => array('title'=>_COUNT,  'type'=>'textbox',  'width'=>'45px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'2', 'default'=>'1', 'validation_type'=>'numeric|positive', 'validation_maximum'=>'20'),
			'priority_order' => array('title'=>_ORDER,  'type'=>'textbox',  'width'=>'45px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'3', 'default'=>'0', 'validation_type'=>'numeric|positive'),
			'is_active'		 => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),			
		);

		//---------------------------------------------------------------------- 
		// EDIT MODE
		// - Validation Type: alpha|numeric|float|alpha_numeric|text|email|ip_address|password|date
		//   Validation Sub-Type: positive (for numeric and float)
		//   Ex.: 'validation_type'=>'numeric', 'validation_type'=>'numeric|positive'
		// - Validation Max Length: 12, 255... Ex.: 'validation_maxlength'=>'255'
		// - Validation Min Length: 4, 6... Ex.: 'validation_minlength'=>'4'
		// - Validation Max Value: 12, 255... Ex.: 'validation_maximum'=>'99.99'
		//---------------------------------------------------------------------- 
		$this->EDIT_MODE_SQL = 'SELECT
								'.$this->tableName.'.'.$this->primaryKey.',
								'.$this->tableName.'.price,
								'.$this->tableName.'.maximum_count,
								'.$this->tableName.'.priority_order,
								'.$this->tableName.'.hotel_id,
								'.$sql_translation_description.'
								'.$this->tableName.'.is_active
							FROM '.$this->tableName.'
							WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';		
		// define edit mode fields
		$this->arrEditModeFields = array(		
			'hotel_id'       => array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL,  'type'=>'enum',     'width'=>'', 'required'=>($objLogin->IsLoggedInAs('hotelowner') ? true : false), 'validation_type'=>'numeric|positive', 'align'=>'left', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_hotels),
			'price'  	     => array('title'=>_PRICE,  'type'=>'textbox',  'width'=>'90px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'10', 'default'=>'0', 'validation_type'=>'float|positive', 'validation_maximum'=>'10000000', 'unique'=>false, 'visible'=>true, 'pre_html'=>$default_currency.' '),
			'maximum_count'  => array('title'=>_COUNT,  'type'=>'textbox',  'width'=>'45px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'2', 'default'=>'1', 'validation_type'=>'numeric|positive', 'validation_maximum'=>'20'),
			'priority_order' => array('title'=>_ORDER,  'type'=>'textbox',  'width'=>'45px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'3', 'default'=>'0', 'validation_type'=>'numeric|positive'),
			'is_active'		 => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			'hotel_id'       => array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL, 'type'=>'enum', 'source'=>$arr_hotels),
			'price'  	     => array('title'=>_PRICE, 'type'=>'label', 'format'=>'currency', 'format_parameter'=>$currency_format.'|2', 'pre_html'=>$default_currency),
			'priority_order' => array('title'=>_ORDER, 'type'=>'label'),
			'is_active'  => array('title'=>_ACTIVE, 'type'=>'enum', 'source'=>$arr_is_active),
		);

		///////////////////////////////////////////////////////////////////////////////
		// #004. add translation fields to all modes
		$this->AddTranslateToModes(
			$this->arrTranslations,
				array('name'        => array('title'=>_SERVICE, 'type'=>'textbox', 'width'=>'410px', 'required'=>true, 'maxlength'=>'125', 'readonly'=>false),
					  'description' => array('title'=>_DESCRIPTION, 'type'=>'textarea', 'width'=>'410px', 'height'=>'90px', 'required'=>false, 'validation_maxlength'=>'512', 'readonly'=>false)
			)
		);
		///////////////////////////////////////////////////////////////////////////////			

	}
	
	//==========================================================================
    // Class Destructor
	//==========================================================================
    function __destruct()
	{
		// echo 'this object has been destroyed';
    }	
	
	/**
	 * Returns all extras 
	 * @param array $hotel_ids
	*/
	public static function GetAllExtras($hotel_ids = array())
	{
		$where = '';
		if(!empty($hotel_ids) && is_array($hotel_ids)){
			if(count($hotel_ids) > 1){
				$where = ' AND e.hotel_id IN ('.implode(',', $hotel_ids).')';
			}else{
				$hotel_id = array_shift($hotel_ids);
				$where = ' AND e.hotel_id = '.(int)$hotel_id;
			}
		}else{
			$where = ' AND e.hotel_id = 0';
		}

		$lang_id = Application::Get('lang');
		$sql = 'SELECT
					e.id, e.hotel_id, e.price, e.maximum_count, e.priority_order, e.is_active,
					ed.name, ed.description
				FROM '.TABLE_EXTRAS.' e
					LEFT OUTER JOIN '.TABLE_EXTRAS_DESCRIPTION.' ed ON e.id = ed.extra_id AND ed.language_id = \''.$lang_id.'\'
				WHERE e.is_active = 1 '.$where.'
				ORDER BY e.priority_order ASC'; 
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		return $result;
	}

	/**
	 * Get extras price
	 * @param $id
	 * @param $param
	 */
	public static function GetExtrasInfo($id, $param = '')
	{
		$lang_id = Application::Get('lang');
		$sql = 'SELECT					
					e.id, e.price, e.maximum_count, e.priority_order, e.is_active,
					ed.name, ed.description
				FROM ('.TABLE_EXTRAS.' e
					LEFT OUTER JOIN '.TABLE_EXTRAS_DESCRIPTION.' ed ON e.id = ed.extra_id AND ed.language_id = \''.$lang_id.'\')
				WHERE e.is_active = 1 AND e.id = '.(int)$id;		
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			if($param != ''){
				return isset($result[0][$param]) ? $result[0][$param] : '';
			}else{
				return $result[0];
			}
		}
		return '';
	}

	/**
	 * Returns sum of extras for booking
	 */
	public static function GetExtrasSum($arr_extras = array(), $currency = '')	
	{
		$info_extras = self::GetAllExtras();
		$currency_info = Currencies::GetCurrencyInfo($currency);
		$currency_rate = isset($currency_info['rate']) ? $currency_info['rate'] : '1';
		$extras_total = 0;

		if(is_array($arr_extras) && count($arr_extras) > 0){			
			$extras_count = 0;
			$extras_total = 0;
			foreach($arr_extras as $key => $val){
				for($i=0; $i<$info_extras[1]; $i++){
					if($info_extras[0][$i]['id'] == $key){							
						$extras_total += ($info_extras[0][$i]['price'] * $currency_rate) * $val;
						break;
					}						
				}
			}
		}
		
		return $extras_total;
	}

	/**
	 * Returns list of extras for booking
	 */
	public static function GetExtrasList($arr_extras = array(), $currency = '', $type = '', $hotel_ids = array(), $mode = 'details', $oid = '', $action = 'index.php?admin=mod_booking_bookings')	
	{
		$info_extras = self::GetAllExtras($hotel_ids);
		$currency_info = Currencies::GetCurrencyInfo($currency);
		$currency_rate = isset($currency_info['rate']) ? $currency_info['rate'] : '1';
		$currency_symbol = isset($currency_info['symbol']) ? $currency_info['symbol'] : '$';
		$currency_symbol_place = isset($currency_info['symbol_placement']) ? $currency_info['symbol_placement'] : 'before';
		$currency_format = get_currency_format();
		$output = '';
		
		if((is_array($arr_extras) && count($arr_extras) > 0) || ($mode == 'edit')){			
			if($type == 'email'){
				$output .= '<b>'._EXTRAS.':</b>';
			}else{
				$output .= '<h4>'._EXTRAS.'</h4>';
			}
		}

		if($mode == 'edit'){
			$output .= '<form name="frmBookingDescription" action="'.htmlentities($action).'" method="post">';
			$output .= draw_hidden_field('mg_action', 'add_extras', false);
			$output .= draw_hidden_field('mg_rid', $oid, false);
			$output .= draw_token_field(false);
			$output .= '<table>';
			$output .= '<tr><td>';
			$output .= '<select name="sel_extras" class="mgrid_select">';
			for($i=0; $i<$info_extras[1]; $i++){
				$output .= '<option value="'.$info_extras[0][$i]['id'].'">'.$info_extras[0][$i]['name'].' - '.Currencies::PriceFormat($info_extras[0][$i]['price']).'</option>';
			}
			$output .= '</select>&nbsp;';
			$output .= draw_numbers_select_field('extras_amount', '', '1', '10', 1, 'extras_ddl mgrid_select', '', false).'&nbsp;';
			$output .= '</td><td>';
			$output .= '<input type="submit" class="mgrid_button" value="'._ADD.'">';
			$output .= '</td></tr>';
			$output .= '</table>';
			$output .= '</form>';
		}
		
		if(is_array($arr_extras) && count($arr_extras) > 0){			
			if($type == 'email'){
				$output .= '<br />-----------------------------<br />';
				$output .= '<table>';
			}else{
				$output .= '<table '.((Application::Get('lang_dir') == 'rtl') ? 'dir="rtl"' : '').' width="100%" border="0" cellspacing="0" cellpadding="3" class="tblExtrasDetails" style="border:1px solid #d1d2d3">';
			}
			$output .= '<thead>'.($type == 'email' ? '<tr>' : '<tr style=background-color:#e1e2e3;font-weight:bold;font-size:13px;">');
			$output .= '<th align="center"> # </th>';
			$output .= '<th align="left">'._NAME.'</th>';
			$output .= '<th align="right" '.(($type == 'email') ? '' : 'width="12%"').'>'._UNIT_PRICE.'</th>';
			$output .= '<th align="center" '.(($type == 'email') ? '' : 'width="13%"').'>'._UNITS.'</th>';
			$output .= '<th align="right" '.(($type == 'email') ? '' : 'width="7%"').'>'._PRICE.'</th>';
			$output .= '<th width="5px" nowrap="nowrap">&nbsp;</th>';
			$output .= '</tr></thead>';
			$extras_count = 0;
			$extras_total = 0;
			foreach($arr_extras as $key => $val){
				for($i=0; $i<$info_extras[1]; $i++){
					if($info_extras[0][$i]['id'] == $key){							
						$output .= '<tr>';
						$output .= '<td align="center" width="40px">'.(++$extras_count).'.</td>';
						$output .= '<td>'.$info_extras[0][$i]['name'].' </td>';
						$output .= '<td align="right">'.Currencies::PriceFormat($info_extras[0][$i]['price'] * $currency_rate, $currency_symbol, $currency_symbol_place, $currency_format).'</td>';
						$output .= '<td align="center">'.$val.'</td>';
						$output .= '<td align="right">'.Currencies::PriceFormat(($info_extras[0][$i]['price'] * $currency_rate) * $val, $currency_symbol, $currency_symbol_place, $currency_format).'&nbsp;</td>';
						if($mode == 'edit'){
							$output .= '<td width="20px"><img style="cursor:pointer;" src="images/delete.gif" onclick="javascript:__RemoveExtras(\''.$oid.'\',\''.$info_extras[0][$i]['id'].'\')" alt="" /></td>';
						}else{
							$output .= '<td></td>';	
						}						
						$output .= '</tr>';
						$extras_total += ($info_extras[0][$i]['price'] * $currency_rate) * $val;
						break;
					}						
				}
			}
			if($type == '' && $extras_total > 0){
				$output .= '<tr>';
				$output .= '<td colspan="3"></td>';
				$output .= '<td colspan="2" align="right"><span>&nbsp;<b>'._TOTAL.': &nbsp;&nbsp;&nbsp;'.Currencies::PriceFormat($extras_total, $currency_symbol, $currency_symbol_place, $currency_format).'</b>&nbsp;</span></td>';
				$output .= '<td></td>';
				$output .= '</tr>';		
			}
			$output .= '</table><br>';
		}
	
		return $output;
	}
	
	/**
	 * Validate translation fields
	 */
	private function ValidateTranslationFields()	
	{
		foreach($this->arrTranslations as $key => $val){
			if(trim($val['name']) == ''){
				$this->error = str_replace('_FIELD_', '<b>'._SERVICE.'</b>', _FIELD_CANNOT_BE_EMPTY);
				$this->errorField = 'name_'.$key;
				return false;				
			}elseif(strlen($val['name']) > 125){
				$this->error = str_replace('_FIELD_', '<b>'._SERVICE.'</b>', _FIELD_LENGTH_EXCEEDED);
				$this->error = str_replace('_LENGTH_', 125, $this->error);
				$this->errorField = 'name_'.$key;
				return false;
			}elseif(strlen($val['description']) > 255){
				$this->error = str_replace('_FIELD_', '<b>'._DESCRIPTION.'</b>', _FIELD_LENGTH_EXCEEDED);
				$this->error = str_replace('_LENGTH_', 512, $this->error);
				$this->errorField = 'description_'.$key;
				return false;
			}			
		}		
		return true;		
	}
	
	/**
	 * Before-Insertion
	 */
	public function BeforeInsertRecord()
	{
		return $this->ValidateTranslationFields();
	}

	/**
	 * After-Insertion - add banner descriptions to description table
	 */
	public function AfterInsertRecord()
	{
		$sql = 'INSERT INTO '.TABLE_EXTRAS_DESCRIPTION.'(id, extra_id, language_id, name, description) VALUES ';
		$count = 0;
		foreach($this->arrTranslations as $key => $val){
			if($count > 0) $sql .= ',';
			$sql .= '(NULL, '.$this->lastInsertId.', \''.$key.'\', \''.encode_text(prepare_input($val['name'])).'\', \''.encode_text(prepare_input($val['description'])).'\')';
			$count++;
		}
		if(database_void_query($sql)){
			return true;
		}else{
			return false;
		}
	}	

	/**
	 * Before-Updating operations
	 */
	public function BeforeUpdateRecord()
	{
		return $this->ValidateTranslationFields();
	}

	/**
	 * After-Updating - update extras item descriptions to description table
	 */
	public function AfterUpdateRecord()
	{
		foreach($this->arrTranslations as $key => $val){
			$sql = 'UPDATE '.TABLE_EXTRAS_DESCRIPTION.'
					SET name = \''.encode_text(prepare_input($val['name'])).'\',
						description = \''.encode_text(prepare_input($val['description'])).'\'
					WHERE extra_id = '.$this->curRecordId.' AND language_id = \''.$key.'\'';
			database_void_query($sql);
		}
	}	

	/**
	 * After-Deleting - delete extras descriptions from description table
	 */
	public function AfterDeleteRecord()
	{
		$sql = 'DELETE FROM '.TABLE_EXTRAS_DESCRIPTION.' WHERE extra_id = '.$this->curRecordId;
		if(database_void_query($sql)){
			return true;
		}else{
			return false;
		}
	}

}
