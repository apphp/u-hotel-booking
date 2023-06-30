<?php

/**
 * Class AccountLocations
 * -------------- 
 * Description : encapsulates account locations properties
 * Written by  : ApPHP
 * Version     : 1.0.0
 * Updated	    : 23.08.2016
 * Differences : no
 *
 * PUBLIC:				  	STATIC:				 	PRIVATE:
 * 	------------------	  	---------------     	---------------
 * __construct				GetAllTravelAgencies
 * __destruct				GetCountries
 * BeforeUpdateRecord      	GetMeCountries
 * BeforeInsertRecord      	GetHotelLocations
 * BeforeEditRecord        	GetHotels
 *	                        GetTravelAgencies
 *
 *
 *
 **/


class AccountLocations extends MicroGrid {
	
	protected $debug = false;

    //---------------------------	
	private $objCustomers = null;
	private $arrCompanies = array(); /* used to show companies where admin is owner */
	private $arrCompaniesSelected = array(); /* used to show selected companies (add mode) where admin is owner */
	private $arrTranslations = '';		
	
	//==========================================================================
    // Class Constructor
	// 	@param $hotel_id
	//==========================================================================
	function __construct($account_id = 0)
	{		
		parent::__construct();
		
		global $objLogin;

		$this->params = array();		
		if(isset($_POST['account_id'])) $this->params['account_id'] = prepare_input($_POST['account_id']);
		if(isset($_POST['country_id'])) $this->params['country_id'] = prepare_input($_POST['country_id'], true);
		if(isset($_POST['companies'])) $this->params['companies'] = prepare_input($_POST['companies']);

		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_ACCOUNT_LOCATIONS;
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=regional_manager_locations&aid='.(int)$account_id;
		$this->actions      = array('add'=>true, 'edit'=>true, 'details'=>true, 'delete'=>true);
		$this->actionIcons  = true;
		$this->allowRefresh = true;
		$this->allowButtons = true;

		$this->allowLanguages = false;
		$this->languageId  	=  '';//$objLogin->GetPreferredLang();
		$this->WHERE_CLAUSE = 'WHERE account_id = \''.$account_id.'\'';
		$this->ORDER_CLAUSE = 'ORDER BY id DESC'; // ORDER BY date_created DESC

		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 20;

		$this->isSortingAllowed = true;

		// prepare countries array		
		$total_countries = Countries::GetAllCountries();
		$arr_countries = array();
		foreach($total_countries[0] as $key => $val){
			$arr_countries[$val['abbrv']] = $val['name'];
		}

		$this->objCustomers = new Customers();
		$companies = $this->objCustomers->GetAllCustomers(' AND customer_type = 1'.(!empty($country_id) ? ' AND b_country = \''.$country_id.'\'' : ''));
		$total_companies = isset($companies[0]) ? $companies[0] : array();
	
		if(isset($this->params['companies'])){
			foreach($total_companies as $key => $val){
				if(in_array($val['id'], $this->params['companies'])){
					$this->arrCompaniesSelected[$val['id']] = $val['first_name'].' '.$val['last_name'];
				}
			}
		}

		foreach($total_companies as $key => $val){
			$this->arrCompanies[$val['id']] = $val['first_name'].' '.$val['last_name'];
		}

		$this->isFilteringAllowed = true;
		// define filtering fields
		$this->arrFilteringFields = array(
			_COUNTRIES  => array('table'=>$this->tableName, 'field'=>'country_id', 'type'=>'dropdownlist', 'source'=>$arr_countries, 'sign'=>'=', 'width'=>'130px', 'visible'=>true),
		);

		$autocomplete_companies = '<script>
		jQuery(document).ready(function(){
			var companiesPointAdd = null;
			jQuery("#autocomplete_companies").autocomplete({
				source: function(request, response){
					var token = "'.htmlentities(Application::Get('token')).'";
					jQuery.ajax({
						url: "ajax/travel_agencies.ajax.php",
						global: false,
						type: "POST",
						data: ({
							token: token,
							act: "send",
							country_id: jQuery("#country_id").val(),
							lang: "'.htmlentities(Application::Get('lang')).'",
							search : jQuery("#autocomplete_companies").val(),
						}),
						dataType: "json",
						async: true,
						error: function(html){
							console.log("AJAX: cannot connect to the server or server response error! Please try again later.");
						},
						success: function(data){
							if(data.length == 0){
								response({ label: "'.htmlentities(_NO_MATCHES_FOUND).'" });
							}else{
								response(jQuery.map(data, function(item){
									return{ full_name: item.full_name, travel_agency_id: item.travel_agency_id, label: item.label }
								}));
							}
						}
					});
				},
				minLength: 2,
				select: function(event, ui) {
					if(typeof(ui.item.travel_agency_id) != "undefined" && jQuery("#group_companies" + ui.item.travel_agency_id).length == 0){
						var idInput = "";
						var newInput = "";
						if(companiesPointAdd == null){
							idInput = "companies1";
							var tmpInput = jQuery("input[name=\'companies[]\']").last();
							if(jQuery("input[name=\'companies[]\']").length == 1){
								newInput = jQuery("<div class=checkbox><input id=\'" + idInput + "\' type=\'checkbox\' name=\'companies[]\' value=\'" + ui.item.travel_agency_id + "\' checked=\'checked\'/></input></div>");
							}else{
								jQuery("input[name=\'companies[]\']").each(function(a,b){var id = jQuery(b).val(); jQuery(b).parent().attr("id", "group_companies" + id);});
								if(jQuery("#group_companies" + ui.item.travel_agency_id).length != 0){
									jQuery("#autocomplete_companies").val("");
									return false;
								}
								idInput = "companies" + (jQuery("input[name=\'companies[]\']").length + 1);
								newInput = jQuery("<div class=checkbox><input id=\'" + idInput + "\' type=\'checkbox\' name=\'companies[]\' value=\'" + ui.item.travel_agency_id + "\' checked=\'checked\'/></input></div>");
								tmpInput.parent().after(newInput);
							}
							tmpInput.after(newInput);
							tmpInput.remove();
						}else{
							idInput = "companies" + (companiesPointAdd.parent().children("div").length + 1);
							newInput = jQuery("<input id=\'" + idInput + "\' type=\'checkbox\' name=\'companies[]\' value=\'" + ui.item.travel_agency_id + "\' checked=\'checked\'></input>");
							companiesPointAdd.after(newInput);
						}
						// Find the item in the DOM
						newInput = jQuery("#" + idInput);
						newInput.wrap("<div id=\'group_companies" + ui.item.travel_agency_id + "\' style=\'float:left;width:220px;\'></div>");
						newInput.after("<label for=\'" + idInput + "\'>" + ui.item.full_name + "</label>")
						companiesPointAdd = jQuery("#group_companies" + ui.item.travel_agency_id);
					}

					jQuery("#autocomplete_companies").val("");
					return false;
				}
			});
			jQuery("#country_id").change(function(){
				jQuery("#mg_row_companies div.checkbox").parent().html("<input type=\"hidden\" name=\"companies[]\">");
				companiesPointAdd = null;
			});
		});
		</script>';

		//---------------------------------------------------------------------- 
		// VIEW MODE
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT
									'.$this->tableName.'.'.$this->primaryKey.',
									cd.name as country_name,
									IF(
										SUBSTRING(SUBSTRING_INDEX('.$this->tableName.'.companies, ":", 2), 3) > 0,
										SUBSTRING(SUBSTRING_INDEX('.$this->tableName.'.companies, ":", 2), 3),
										"0"
									) as count_campanies,
									CONCAT(a.first_name, " ", a.last_name) as full_name
								FROM '.$this->tableName.'
									LEFT OUTER JOIN '.TABLE_ACCOUNTS.' a ON '.$this->tableName.'.account_id = a.id
									LEFT OUTER JOIN '.TABLE_COUNTRIES.' c ON '.$this->tableName.'.country_id = c.abbrv AND c.is_active = 1
									LEFT OUTER JOIN '.TABLE_COUNTRIES_DESCRIPTION.' cd ON c.id = cd.country_id AND cd.language_id = \''.Application::Get('lang').'\'
								';
								
		// define view mode fields
		$this->arrViewModeFields['full_name'] 	= array('title'=>_REGIONAL_MANAGER, 'type'=>'label', 'align'=>'left', 'width'=>'', 'maxlength'=>'70', 'movable'=>false);
		$this->arrViewModeFields['country_name'] = array('title'=>_COUNTRY, 'type'=>'label', 'align'=>'center', 'width'=>'200px');
		$this->arrViewModeFields['count_campanies'] = array('title'=>_NUMBER_AGENCIES, 'type'=>'label', 'align'=>'center', 'width'=>'150px');

		//---------------------------------------------------------------------- 
		// ADD MODE
		//---------------------------------------------------------------------- 
		// define add mode fields
		$this->arrAddModeFields = array(
			'country_id' => array('title'=>_COUNTRY, 'type'=>'enum',  'width'=>'210px', 'source'=>$arr_countries, 'required'=>true),
			'account_id' => array('title'=>'', 'type'=>'hidden',   'required'=>true, 'readonly'=>false, 'default'=>$account_id),
			'autocomplete_companies' => array('title'=>_EX_TRAVEL_AGENCY , 'type'=>'textbox', 'width'=>'210px', 'required'=>false, 'readonly'=>false, 'default'=>'', 'javascript_event'=>'', 'post_html'=>$autocomplete_companies),
			'companies' => array('title'=>_TRAVEL_AGENCIES, 'type'=>'enum', 'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'', 'source'=>$this->arrCompaniesSelected, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'checkboxes', 'multi_select'=>true),
		);

		//---------------------------------------------------------------------- 
		// EDIT MODE
		//---------------------------------------------------------------------- 
		$this->EDIT_MODE_SQL = 'SELECT
								'.$this->primaryKey.',
								country_id,
								companies,
								account_id
							FROM '.$this->tableName.'
							WHERE '.$this->primaryKey.' = _RID_';		
		// define edit mode fields
		$this->arrEditModeFields = array(
			'country_id' => array('title'=>_COUNTRY, 'type'=>'enum',  'width'=>'210px', 'source'=>$arr_countries, 'required'=>true),
			'account_id' => array('title'=>'', 'type'=>'hidden',   'required'=>true, 'readonly'=>false, 'default'=>$account_id),
			'autocomplete_companies' => array('title'=>_EX_TRAVEL_AGENCY , 'type'=>'textbox', 'width'=>'210px', 'required'=>false, 'readonly'=>false, 'default'=>'', 'javascript_event'=>'', 'post_html'=>$autocomplete_companies),
			'companies' => array('title'=>_TRAVEL_AGENCIES, 'type'=>'enum', 'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'', 'source'=>$this->arrCompanies, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'checkboxes', 'multi_select'=>true),
		);

		
		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			'country_id' => array('title'=>_COUNTRY, 'type'=>'enum', 'source'=>$arr_countries),
			'companies' => array('title'=>_TRAVEL_AGENCIES, 'type'=>'enum', 'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'', 'source'=>$this->arrCompanies, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'checkboxes', 'multi_select'=>true)
		);

		
	}
	
	//==========================================================================
    // Class Destructor
	//==========================================================================
    function __destruct()
	{
		// echo 'this object has been destroyed';
    }

	////////////////////////////////////////////////////////////////////
	// BEFORE/AFTER METHODS
	///////////////////////////////////////////////////////////////////

	/**
	 * Before-Insert operation
	 */
	public function BeforeInsertRecord()
	{
		$sql = 'SELECT country_id FROM '.$this->tableName.' WHERE account_id = '.(int)$this->params['account_id'].' AND country_id = \''.$this->params['country_id'].'\'';
		$result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
		if(!empty($result)){
			$this->error = str_replace('_FIELD_', '<b>'._COUNTRY.'</b>', _FILED_UNIQUE_VALUE_ALERT);
			return false;
		}
		return true;
	}

	/**
	 * Before-Update operation
	 */
	public function BeforeUpdateRecord()
	{
		// Remove company items that have not been sent
		if(!empty($this->params['companies'])){
			reset($this->params['companies']);
			for($i = 0, $maxCount = count($this->params['companies']); $i < $maxCount; $i++){
				$curVal = current($this->params['companies']);
				$curKey = key($this->params['companies']);
				next($this->params['companies']);
				if($curVal === '' || $curVal === null){
					unset($this->params['companies'][$curKey]);
				}
			}
		}
		$sql = 'SELECT country_id FROM '.$this->tableName.' WHERE '.$this->primaryKey.' != '.(int)$this->curRecordId.' AND account_id = '.(int)$this->params['account_id'].' AND country_id = \''.$this->params['country_id'].'\'';
		$result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
		if(!empty($result)){
			$this->error = str_replace('_FIELD_', '<b>'._COUNTRY.'</b>', _FILED_UNIQUE_VALUE_ALERT);
			return false;
		}
		return true;
	}

	public function BeforeEditRecord()
	{
		$sql = 'SELECT country_id FROM '.$this->tableName.' WHERE '.$this->primaryKey.' = '.(int)$this->curRecordId;
		$result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
		$country_id = isset($result['country_id']) ? $result['country_id'] : '';

		$companies = $this->objCustomers->GetAllCustomers(' AND customer_type = 1'.(!empty($country_id) ? ' AND b_country = \''.$country_id.'\'' : ''));
		$total_companies = isset($companies[0]) ? $companies[0] : array();

		$arr_companies = array();
		foreach($total_companies as $key => $val){
			$arr_companies[$val['id']] = $val['first_name'].' '.$val['last_name'];
		}
	
		$this->arrEditModeFields['companies'] = array('title'=>_TRAVEL_AGENCIES, 'type'=>'enum', 'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'', 'source'=>$arr_companies, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'checkboxes', 'multi_select'=>true);

		return true;
	}


	/*
	 * @param int $regional_manager_id
	 * @return array
	 * */
	public static function GetAllTravelAgencies($regional_manager_id)
	{
		$arr_output = array();
		$sql = 'SELECT companies, country_id FROM '.TABLE_ACCOUNT_LOCATIONS.' WHERE account_id = '.(int)$regional_manager_id;
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		if($result[1] > 0){
			for($i = 0; $i < $result[1]; $i++){
				$companies = @explode(',', $result[0][$i]['companies']);
				if(!empty($companies) && is_array($companies)){
					$arr_output = array_merge($arr_output, $companies);
				}
			}
			$arr_output = array_unique($arr_output, SORT_STRING);
		}

		return $arr_output;
	}

	/*
	 * @param int $regional_manager_id
	 * @return array
	 * */
	public static function GetCountries($regional_manager_id)
	{
		$arr_output = array();
		$sql = 'SELECT 
				c.abbrv,
				cd.name as country_name
			FROM '.TABLE_ACCOUNT_LOCATIONS.' as al
				LEFT OUTER JOIN '.TABLE_COUNTRIES.' as c ON c.abbrv = al.country_id
				LEFT OUTER JOIN '.TABLE_COUNTRIES_DESCRIPTION.' as cd ON c.id = cd.country_id AND cd.language_id = \''.Application::Get('lang').'\'
			WHERE account_id = '.(int)$regional_manager_id;
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		if($result[1] > 0){
			for($i = 0; $i < $result[1]; $i++){
				$arr_output[$result[0][$i]['abbrv']] = $result[0][$i]['country_name'];
			}
		}

		return $arr_output;
	}

	/**
	 * Returns all locations for regional manager
	*/
	public static function GetMeCountries()
	{
		global $objLogin;

		$output = array();

		if($objLogin->IsLoggedInAs('regionalmanager')){
			$account_id = $objLogin->GetLoggedID();
			$lang_id = Application::Get('lang');
			$sql = 'SELECT 
				al.country_id,
				cd.name as country_name
				FROM '.TABLE_ACCOUNT_LOCATIONS.' as al
					LEFT OUTER JOIN '.TABLE_COUNTRIES.' c ON al.country_id = c.abbrv
					LEFT OUTER JOIN '.TABLE_COUNTRIES_DESCRIPTION." as cd ON c.id = cd.country_id AND cd.language_id = '".$lang_id."'
				WHERE al.account_id = ".(int)$account_id;
			$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
			if($result[1] > 0){
				for($i = 0; $i < $result[1]; $i++){
					$output[$result[0][$i]['country_id']] = $result[0][$i]['country_name'];
				}
			}
		}
		return $output;
	}

	/*
	 * @param int $regional_manager_id
	 * @return array
	 * */
	public static function GetHotelLocations($regional_manager_id)
	{
		$arr_output = array();
		$sql = 'SELECT 
				hl.id as hotel_location_id
			FROM '.TABLE_ACCOUNT_LOCATIONS.' as al
				INNER JOIN '.TABLE_HOTELS_LOCATIONS.' as hl ON al.country_id = hl.country_id
			WHERE account_id = '.(int)$regional_manager_id;
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		if($result[1] > 0){
			for($i = 0; $i < $result[1]; $i++){
				$arr_output[] = $result[0][$i]['hotel_location_id'];
			}
		}

		return $arr_output;
	}

	/*
	 * @param int $regional_manager_id
	 * @return array
	 * */
	public static function GetHotels($regional_manager_id)
	{
		$arr_output = array();
		$sql = 'SELECT 
				h.id as hotel_id
			FROM '.TABLE_ACCOUNT_LOCATIONS.' as al
				INNER JOIN '.TABLE_HOTELS_LOCATIONS.' as hl ON al.country_id = hl.country_id
				INNER JOIN '.TABLE_HOTELS.' as h ON h.hotel_location_id = hl.id
			WHERE account_id = '.(int)$regional_manager_id;
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		if($result[1] > 0){
			for($i = 0; $i < $result[1]; $i++){
				$arr_output[] = $result[0][$i]['hotel_id'];
			}
		}

		return $arr_output;
	}

	/*
	 * @param int $regional_manager_id
	 * @return array
	 * */
	public static function GetTravelAgencies($regional_manager_id)
	{
		$arr_output = array();
		$sql = 'SELECT 
				al.companies
			FROM '.TABLE_ACCOUNT_LOCATIONS.' as al
			WHERE account_id = '.(int)$regional_manager_id;
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		if($result[1] > 0){
			for($i = 0; $i < $result[1]; $i++){
				$agencies = @explode(',', $result[0][$i]['companies']);
				if(!empty($agencies)){
					$arr_output = array_merge($arr_output, $agencies);
				}
			}
			$arr_output = array_unique($arr_output);
		}

		return $arr_output;
	}
}
