<?php

/***
 *	Currencies Class 
 *  --------------
 *  Description : encapsulates currencies properties
 *	Written by  : ApPHP
 *	Version     : 1.0.6
 *  Updated	    : 08.10.2017
 *	Usage       : Core
 *
 *	PUBLIC:					STATIC:					PRIVATE:
 * 	------------------	  	---------------     	---------------
 *  __construct				GetDefaultCurrency
 *  __destruct              PriceFormat  
 *  AfterInsertRecord       CurrencyExists 
 *  AfterUpdateRecord       GetDefaultCurrencyInfo
 *  BeforeDeleteRecord      GetCurrencyInfo
 *  AfterDeleteRecord       GetCurrenciesDDL
 *  UpdateCurrencyRates     GetAllActive
 *                          
 *                          
 *  1.0.6
 *  	-
 *  	-
 *  	-
 *  	-
 *  	-
 *  1.0.5
 *      - added select_class in GetCurrenciesDDL
 *      - commented $url = str_replace(array('page=check_availability'), '', $url);									
 *      - added unique for currency code
 *      - added UpdateCurrencyRates
 *      - added new field "date_lastupdate"
 *  1.0.4
 *      - fixed bug - unexpected characters in symbol of currency
 *      - mysq l_real_escape_string() replaced with encode_text()
 *      - removed page=index for HotelSite
 *      - added $url = trim($url, '?&');
 *      - improved some syntax in GetCurrenciesDDL
 *  1.0.3
 *      - added allow_seo_links to GetCurrenciesDDL
 *      - changes SELECT CASEs with 'enum' type
 *      - added decimal_points to PriceFormat()
 *      - added gray color to not default
 *      - changed left/right placement with before/after
 *  1.0.2
 *      - improved PriceFormat() method - to display european format
 *      - fixed bug on drawing currency DDL 
 *      - fixed issues with maxlength of some fields
 *      - added AfterInsertRecord()
 *      - added BusinessDirectory
 *  1.0.1
 *      - blocked possibility to make all currencies un-active
 *      - currencies dropdown box now shown only if there is more that 1 active currency 
 *      - removed arguments from GetCurrenciesDDL()
 *      - added (float)$price in PriceFormat() method
 *      - added number format for rate in view mode
 **/


class Currencies extends MicroGrid {
	
	protected $debug = false;
	
	//------------------------------
	private $sqlFieldDatetimeFormat = '';

	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct()
	{		
		parent::__construct();
		
		global $objSettings;
		
		$this->params = array();		
			if(isset($_POST['name']))   $this->params['name'] = prepare_input($_POST['name']);
		if(isset($_POST['symbol'])) $this->params['symbol'] = prepare_input(str_replace(array('"', "'"), '', $_POST['symbol']));
		if(isset($_POST['symbol_placement'])) $this->params['symbol_placement'] = prepare_input($_POST['symbol_placement']);
		if(isset($_POST['code']))   $this->params['code'] = prepare_input($_POST['code']);
		if(isset($_POST['rate']))   $this->params['rate'] = prepare_input($_POST['rate']);
		if(isset($_POST['decimals']))      		$this->params['decimals'] = prepare_input($_POST['decimals']);
		if(isset($_POST['primary_order'])) 		$this->params['primary_order'] = (int)$_POST['primary_order'];
		if(isset($_POST['date_lastupdate']))	$this->params['date_lastupdate'] = prepare_input($_POST['date_lastupdate']);
		// for checkboxes 
		if(isset($_POST['is_default'])) $this->params['is_default'] = (int)$_POST['is_default']; else $this->params['is_default'] = '0';
		if(isset($_POST['is_active']))  $this->params['is_active'] = (int)$_POST['is_active']; else $this->params['is_active'] = '0';
		
		$this->params['language_id'] 	  = MicroGrid::GetParameter('language_id');
	
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_CURRENCIES;
		$this->dataSet 		= array();
		$this->alert 		= '';
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=mod_booking_currencies';
		$this->actions      = array('add'=>true, 'edit'=>true, 'details'=>true, 'delete'=>true);
		$this->actionIcons  = true;
		$this->allowRefresh = true;

		$this->allowLanguages = false;
		$this->languageId  	= ''; // ($this->params['language_id'] != '') ? $this->params['language_id'] : Languages::GetDefaultLang();
		$this->WHERE_CLAUSE = ''; // WHERE .... / 'WHERE language_id = \''.$this->languageId.'\'';				
		$this->ORDER_CLAUSE = 'ORDER BY '.$this->tableName.'.primary_order ASC'; // ORDER BY date_created DESC
		
		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 20;

		$this->isSortingAllowed = true;

		$this->isFilteringAllowed = false;
		// define filtering fields
		///$this->arrFilteringFields = array(
		///	'parameter1' => array('title'=>'',  'type'=>'text', 'sign'=>'=|like%|%like|%like%', 'width'=>'80px'),
		///	'parameter2'  => array('title'=>'',  'type'=>'text', 'sign'=>'=|like%|%like|%like%', 'width'=>'80px'),
		///);

		$currency_format = get_currency_format();
		
		$arr_is_active = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');
		$arr_is_default = array('0'=>'<span class=gray>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');
		$arr_decimals = array('0'=>'0', '1'=>'1', '2'=>'2');
		$arr_symbol_placement = array('before'=>_BEFORE, 'after'=>_AFTER);

		// prepare languages array		
		/// $total_languages = Languages::GetAllActive();
		/// $arr_languages      = array();
		/// foreach($total_languages[0] as $key => $val){
		/// 	$arr_languages[$val['abbreviation']] = $val['lang_name'];
		/// }

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
		$this->VIEW_MODE_SQL = 'SELECT '.$this->primaryKey.',
									name,
									symbol,
									symbol_placement,
									code,
									rate,
									decimals,
									primary_order,
									is_default,
									is_active,
									IF('.$this->tableName.'.date_lastupdate IS NULL, "--", DATE_FORMAT('.$this->tableName.'.date_lastupdate, \''.$this->sqlFieldDatetimeFormat.'\')) as date_lastupdate
								FROM '.$this->tableName;		
		// define view mode fields
		$this->arrViewModeFields = array(
			'name'   	=> array('title'=>_NAME, 'type'=>'label', 'align'=>'left', 'width'=>'', 'height'=>'', 'maxlength'=>''),
			'symbol' 	=> array('title'=>_SYMBOL, 'type'=>'label', 'align'=>'center', 'width'=>'90px', 'height'=>'', 'maxlength'=>''),
			'code' 		=> array('title'=>_CODE, 'type'=>'label', 'align'=>'center', 'width'=>'90px', 'height'=>'', 'maxlength'=>''),
			'rate' 		=> array('title'=>_RATE, 'type'=>'label', 'align'=>'center', 'width'=>'90px', 'height'=>'', 'maxlength'=>'', 'format'=>'currency', 'format_parameter'=>$currency_format.'|4'),
			'date_lastupdate' => array('title'=>_LAST_UPDATE, 'type'=>'label', 'align'=>'center', 'width'=>'140px'),
			'decimals'  => array('title'=>_DECIMALS, 'type'=>'label', 'align'=>'center', 'width'=>'80px', 'height'=>'', 'maxlength'=>''),
			'primary_order' => array('title'=>_ORDER, 'type'=>'label', 'align'=>'center', 'width'=>'80px', 'height'=>'', 'maxlength'=>'', 'movable'=>true),
			'is_default' => array('title'=>_DEFAULT, 'type'=>'enum',  'align'=>'center', 'width'=>'80px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_is_default),
			'is_active'  => array('title'=>_ACTIVE, 'type'=>'enum',  'align'=>'center', 'width'=>'80px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_is_active),
		);
		
		//---------------------------------------------------------------------- 
		// ADD MODE
		//---------------------------------------------------------------------- 
		// define add mode fields
		$this->arrAddModeFields = array(
			'name'       => array('title'=>_NAME, 'type'=>'textbox',  'width'=>'210px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'50', 'validation_type'=>'text'),
			'symbol'     => array('title'=>_SYMBOL, 'type'=>'textbox',  'width'=>'50px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'5', 'validation_type'=>'text'),
			'symbol_placement' => array('title'=>_SYMBOL_PLACEMENT, 'type'=>'enum',     'required'=>true, 'readonly'=>false, 'width'=>'100px', 'source'=>$arr_symbol_placement),
			'code'       => array('title'=>_CODE, 'type'=>'textbox',  'width'=>'50px', 'required'=>true, 'readonly'=>false, 'unique'=>true, 'maxlength'=>'3', 'validation_type'=>'alpha'),
			'rate' 	     => array('title'=>_RATE, 'type'=>'textbox',  'width'=>'80px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'10', 'validation_type'=>'float', 'validation_maximum'=>'999999'),
			'decimals'   => array('title'=>_DECIMALS, 'type'=>'enum',  'required'=>true, 'readonly'=>false, 'width'=>'80px', 'source'=>$arr_decimals, 'default'=>'2'),
			'primary_order'  => array('title'=>_ORDER, 'type'=>'textbox',  'width'=>'40px', 'required'=>true, 'readonly'=>false, 'default'=>'0', 'maxlength'=>'2', 'validation_type'=>'numeric'),
			'is_default' => array('title'=>_DEFAULT, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'0', 'true_value'=>'1', 'false_value'=>'0'),
			'is_active'  => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0'),
		);

		//---------------------------------------------------------------------- 
		// EDIT MODE
		//---------------------------------------------------------------------- 
		$this->EDIT_MODE_SQL = 'SELECT '.$this->primaryKey.',
									name,
									symbol,
									symbol_placement,
									code,
									rate,
									decimals,
									primary_order,
									is_default,
									is_active,
									DATE_FORMAT('.$this->tableName.'.date_lastupdate, \''.$this->sqlFieldDatetimeFormat.'\') as date_lastupdate
							FROM '.$this->tableName.'
							WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';
		
		
		$rid = MicroGrid::GetParameter('rid');
		$sql = 'SELECT is_default FROM '.TABLE_CURRENCIES.' WHERE id = '.(int)$rid;
		$readonly = false;
		if($result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY)){
			$readonly = (isset($result['is_default']) && $result['is_default'] == '1') ? true : false;
		}
		
		// define edit mode fields
		$this->arrEditModeFields = array(		
			'name'   	 => array('title'=>_NAME, 'type'=>'textbox',  'width'=>'210px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'50', 'validation_type'=>'text'),
			'symbol' 	 => array('title'=>_SYMBOL, 'type'=>'textbox',  'width'=>'50px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'5', 'validation_type'=>'text'),
			'symbol_placement' => array('title'=>_SYMBOL_PLACEMENT, 'type'=>'enum',     'required'=>true, 'readonly'=>false, 'width'=>'100px', 'source'=>$arr_symbol_placement),
			'code'   	 => array('title'=>_CODE, 'type'=>'textbox',  'width'=>'50px', 'required'=>true, 'readonly'=>false, 'unique'=>true, 'maxlength'=>'3', 'validation_type'=>'alpha'),
			'rate' 	 	 => array('title'=>_RATE, 'type'=>'textbox',  'width'=>'80px', 'required'=>true, 'readonly'=>$readonly, 'maxlength'=>'10', 'validation_type'=>'float', 'validation_maximum'=>'999999'),
			'date_lastupdate'  => array('title'=>'', 'type'=>'hidden', 'required'=>true, 'default'=>date('Y-m-d H:i:s'), 'visible'=>true),
			'decimals' 	 => array('title'=>_DECIMALS, 'type'=>'enum',  'required'=>true, 'readonly'=>false, 'width'=>'80px', 'source'=>$arr_decimals),
			'primary_order'  => array('title'=>_ORDER, 'type'=>'textbox',  'width'=>'40px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'2', 'validation_type'=>'numeric'),
			'is_default' => array('title'=>_DEFAULT, 'type'=>'checkbox', 'readonly'=>$readonly, 'default'=>'0', 'true_value'=>'1', 'false_value'=>'0'),
			'is_active'  => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>$readonly, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0'),
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			'name'   	 => array('title'=>_NAME, 'type'=>'label'),
			'symbol' 	 => array('title'=>_SYMBOL, 'type'=>'label'),
			'symbol_placement' => array('title'=>_SYMBOL_PLACEMENT, 'type'=>'label'),
			'code'   	 => array('title'=>_CODE, 'type'=>'label'),
			'rate' 	 	 => array('title'=>_RATE, 'type'=>'label', 'format'=>'currency', 'format_parameter'=>$currency_format.'|4'),
			'date_lastupdate' => array('title'=>_LAST_UPDATE, 'type'=>'label'),
			'decimals' 	 => array('title'=>_DECIMALS, 'type'=>'enum', 'source'=>$arr_decimals),
			'primary_order'  => array('title'=>_ORDER, 'type'=>'label'),
			'is_default' => array('title'=>_DEFAULT, 'type'=>'enum', 'source'=>$arr_is_default),
			'is_active'  => array('title'=>_ACTIVE, 'type'=>'enum', 'source'=>$arr_is_active),
		);
	}
	
	//==========================================================================
    // Class Destructor
	//==========================================================================
    function __destruct()
	{
		// echo 'this object has been destroyed';
    }

	//==========================================================================
    // Static Methods
	//==========================================================================	
	/**
	 *	Returns default currency
	 */
	public static function GetDefaultCurrency()
	{
		$def_currency = '$';
		$sql = 'SELECT symbol FROM '.TABLE_CURRENCIES.' WHERE is_default = 1';
		if($result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY)){
			$def_currency = $result['symbol'];					
		}
		return $def_currency;
	}
	
	/**
	 * Returns array of all active currencies
	 * @return array
	 */
	public static function GetAllActive()
	{
		$sql = 'SELECT symbol, code, name, rate, decimals, symbol_placement FROM '.TABLE_CURRENCIES.' WHERE is_active = 1 ';
		return database_query($sql, DATA_AND_ROWS);
	}

	/**
	 * Returns formatted price value
	 * @param $price
	 * @param $cur_symbol
	 * @param $cur_symbol_place
	 * @param $currency_format
	 * @param $decimal_points
	 */
	public static function PriceFormat($price, $cur_symbol = '', $cur_symbol_place = '', $currency_format = 'american', $decimal_points = '')
	{
		if($cur_symbol_place == 'left') $cur_symbol_place = 'before';
		else if($cur_symbol_place == 'right') $cur_symbol_place = 'after';
		
		if(Application::Get('currency_code') == ''){
			$default_currency = Currencies::GetDefaultCurrencyInfo();
			$currency_symbol = ($cur_symbol != '') ? $cur_symbol : $default_currency['symbol'];
			$currency_symbol_place = ($cur_symbol_place != '') ? $cur_symbol_place : $default_currency['symbol_placement'];
			$decimal_points = ($decimal_points != '') ? $decimal_points : $default_currency['decimals'];
		}else{
			$currency_symbol = ($cur_symbol != '') ? $cur_symbol : Application::Get('currency_symbol');
			$currency_symbol_place = ($cur_symbol_place != '') ? $cur_symbol_place : Application::Get('currency_symbol_place');
			$decimal_points = ($decimal_points != '') ? $decimal_points : Application::Get('currency_decimals');
		}

		if($currency_symbol_place == 'before'){
			$field_value_pre = $currency_symbol;
			$field_value_post = '';
		}else{
			$field_value_pre = '';
			$field_value_post = $currency_symbol;
		}		

		if($currency_format == 'european'){
			$price = str_replace('.', '#', $price);							
			$price = str_replace(',', '.', $price);
			$price = str_replace('#', '.', $price);
			return $field_value_pre.number_format((float)$price, (int)$decimal_points, ',', '.').$field_value_post;
		}else{
			return $field_value_pre.number_format((float)$price, (int)$decimal_points, '.', ',').$field_value_post;	
		}
	}

	/**
	 * Check if currency exists
	 * @param $currency_code
	 */
	public static function CurrencyExists($currency_code = '')
	{
		$sql = "SELECT code FROM ".TABLE_CURRENCIES." WHERE code = '".encode_text($currency_code)."' AND is_active = 1 ";
		if(database_query($sql, ROWS_ONLY) > 0){
			return true;
		}
		return false;
	}

	/**
	 * Returns default currency info
	 * @param $currency_code
	 */
	public static function GetDefaultCurrencyInfo($currency_code = '')
	{
		$sql = 'SELECT symbol, code, rate, decimals, symbol_placement FROM '.TABLE_CURRENCIES.' WHERE is_default = 1 AND is_active = 1 ';
		if($result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY)){
			return $result;
		}
		return array('symbol'=>'$', 'code'=>'USD', 'rate'=>'1', 'decimals'=>'2', 'symbol_placement'=>'before');
	}

	/**
	 * Returns currency info
	 * @param $currency_code
	 */
	public static function GetCurrencyInfo($currency_code = '')
	{
		$sql = 'SELECT symbol, code, rate, decimals, symbol_placement FROM '.TABLE_CURRENCIES.' WHERE code = \''.$currency_code.'\' AND is_active = 1 ';
		if($result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY)){
			return $result;
		}
		return array('symbol'=>'$', 'code'=>'USD', 'rate'=>'1', 'decimals'=>'2', 'symbol_placement'=>'before');
	}

    /**
	 * Returns currencies dropdown list
	 * @param $allow_seo_links
	 * @param $show_all_options
	 * @param $params
	 */
	public static function GetCurrenciesDDL($allow_seo_links = true, $show_all_options = false, $params = array())
	{
		global $objSettings;
		
		$sel_currency = Application::Get('currency_code');
		$currency = Application::Get('currency');
		$select_class = isset($params['select_class']) ? $params['select_class'] : 'currency_select';
		$output = '';
		
		$sql = 'SELECT id, name, symbol, code, rate, decimals, primary_order
				FROM '.TABLE_CURRENCIES.'
				WHERE is_active = 1';
        $result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
        $min_records = $show_all_options ? 0 : 1;
        if($result[1] > $min_records){
            $base_url = APPHP_BASE;
            $url = get_page_url();

            // prevent wrong re-loading for some problematic cases
			$url = str_replace(array('page=booking_payment'), 'page=booking_checkout', $url);
			//$url = str_replace(array('page=check_availability'), '', $url);

            // trim last character if ? or &
            $url = trim($url, '?&');

            if($objSettings->GetParameter('seo_urls') == '1' && $allow_seo_links){					
                // remove lang parameters
                $url = str_replace('/'.Application::Get('lang').'/', '/', $url);											
                if(preg_match('/\/'.Application::Get('currency_code').'\//i', $url)){
                    $url = str_replace('/'.Application::Get('currency_code').'/', '/__CUR__/', $url);						
                }else{
                    $url = str_replace($base_url, $base_url.'__CUR__/', $url); 							
                }						
            }else{
                if(preg_match('/currency='.Application::Get('currency_code').'/i', $url)){
                    $url = str_replace('currency='.Application::Get('currency_code'), 'currency=__CUR__', $url);
                }else{
                    $url = $url.(preg_match('/\?/', $url) ? '&amp;' : '?').'currency=__CUR__';						
                }
            }

            if(in_array(Application::Get('page'), array('check_cars_availability', 'check_availability'))){
                $output .= '<select onchange="javascript:appEditActionFormSubmit(\''.(Application::Get('page') == 'check_availability' ? 'reservation-form' : 'cars-reservation-form').'\', \'currency=\' + this.value)" name="currency" class="'.$select_class.'">';
            }else{
                $output .= '<select onchange="javascript:appSetNewCurrency(\''.$url.'\',this.value)" name="currency" class="'.$select_class.'">';
            }
            for($i=0; $i < $result[1]; $i++){
                $output .= '<option value="'.$result[0][$i]['code'].'" '.(($sel_currency == $result[0][$i]['code']) ? ' selected="selected"' : '').'>'.$result[0][$i]['name'].'</option>';
            }
            $output .= '</select>';
        }
	    return $output;
	}	

    /**
	 * After-Insertion Record
	 */
	public function AfterInsertRecord()
	{
		$is_default = MicroGrid::GetParameter('is_default', false);
		if($is_default == '1'){
			$sql = 'UPDATE '.TABLE_CURRENCIES.' SET is_default = \'0\' WHERE id != '.(int)$this->lastInsertId;
			database_void_query($sql);
			return true;
		}
	}
	
	/**
	 * After-Updating Record
	 */
	public function AfterUpdateRecord()
	{
		$sql = 'SELECT id, is_active FROM '.TABLE_CURRENCIES;
		if($result = database_query($sql, DATA_AND_ROWS, ALL_ROWS)){
			if((int)$result[1] == 1){
				// make last currency always be default
				$sql = 'UPDATE '.TABLE_CURRENCIES.' SET rate = \'1\', is_default = \'1\', is_active = \'1\' WHERE id = '.(int)$result[0][0]['id'];
				database_void_query($sql);
				return true;	
			}else{
				// save all other currencies to be not default
				$rid = MicroGrid::GetParameter('rid');
				$is_default = MicroGrid::GetParameter('is_default', false);
				if($is_default == '1'){
					$sql = 'UPDATE '.TABLE_CURRENCIES.' SET rate = \'1\', is_active = \'1\' WHERE id = '.(int)$rid;
					database_void_query($sql);
					
					$sql = 'UPDATE '.TABLE_CURRENCIES.' SET is_default = \'0\' WHERE id != '.(int)$rid;
					database_void_query($sql);
					return true;
				}
			}
		}
	    return true;	
	}

    /**
	 * Before-Deleting Record
	 */
	public function BeforeDeleteRecord()
	{
		$sql = 'SELECT COUNT(*) as cnt FROM '.TABLE_CURRENCIES.' WHERE is_active = 1';
		$result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
		
		$sql = 'SELECT is_active, is_default FROM '.TABLE_CURRENCIES.' WHERE id = '.(int)$this->curRecordId;
		$result_current = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);

		if((int)$result['cnt'] > 1){
			if($result_current['is_default'] == '1'){
				$this->error = _DEFAULT_CURRENCY_DELETE_ALERT;
				return false;
			}				
		}else{
			if($result_current['is_active'] == '1'){
				$this->error = _LAST_CURRENCY_ALERT;
				return false;
			}
		}
	    return true;	
	}

    /**
	 * After-Deleting Record
	 */
	public function AfterDeleteRecord()
	{
		$sql = 'SELECT id, is_active FROM '.TABLE_CURRENCIES;
		if($result = database_query($sql, DATA_AND_ROWS, ALL_ROWS)){
			if((int)$result[1] == 1){
				// Make last currency always 
				$sql = "UPDATE ".TABLE_CURRENCIES." SET rate = '1', is_default = '1', is_active = '1' WHERE id = ".(int)$result[0][0]['id'];
				database_void_query($sql);
				return true;	
			}
		}
	    return true;	
	}
	
    /**
	 * After-Deleting Record
	 */
	public function UpdateCurrencyRates()
	{
		$default_currency = Currencies::GetDefaultCurrencyInfo();
		
		$sql = 'SELECT * FROM '.TABLE_CURRENCIES.' WHERE is_default = 0';
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);		
		
		$updated = false;
		for($i=0; $i<$result[1]; $i++){			
			$url = 'https://finance.google.com/finance/converter?a=1&from='.$default_currency['code'].'&to='.$result[0][$i]['code'];
			$data = @file_get_contents($url);
			$data = explode('<span class=bld>', $data);
			
			$converted_amount = 0;
			if(isset($data[0]) && isset($data[1])){
				$data = explode('</span>', $data[1]);
				$converted_amount = $data[0];
				$converted_amount = round($converted_amount, 4);				
			}
			
			if(!empty($converted_amount)){
				// Update currency rate
				$sql = "UPDATE ".TABLE_CURRENCIES." SET rate = '".$converted_amount."', date_lastupdate = '".date('Y-m-d H:i:s')."' WHERE code = '".$result[0][$i]['code']."'";
				database_void_query($sql);
	
				$this->alert .= '<br>'.$result[0][$i]['code']." - "._CURRENCY_PREVIOUS_RATE.": ".$result[0][$i]['rate']." | ".($converted_amount != $result[0][$i]['rate'] ? _CURRENCY_NEW_RATE.': <b>'.$converted_amount.'</b>' : _NO_CHANGES);
				$updated = true;				
			}
		}

		if($updated){
			return true;
		}else{
			$this->error = _NO_RECORDS_UPDATED;
			return false;
		}		
		
	}
}
