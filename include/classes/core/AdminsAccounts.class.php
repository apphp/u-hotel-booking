<?php

/**
 *	Class AdminsAccounts
 *  -------------- 
 *  Description : encapsulates Admins Accounts operations & properties
 *	Written by  : ApPHP
 *	Version     : 1.0.5
 *  Updated	    : 05.09.2016
 *	Usage       : Core
 *	Differences : $PROJECT
 *	
 *	PUBLIC:				  	STATIC:				 	PRIVATE:
 * 	------------------	  	---------------     	---------------
 *	__construct                                     SetCompaniesViewState
 *	__destruct										PrepareOaCredentials
 *	AfterInsertRecord
 *	AfterUpdateRecord
 *	AfterAddRecord
 *	AfterEditRecord
 *	AfterDetailsMode
 *	BeforeInsertRecord
 *	BeforeEditRecord
 *	BeforeDetailsRecord
 *	BeforeUpdateRecord
 *	RecreateApi
 *
 *  1.0.6
 *  	- added authorization fields
 *  	- added regional_admins
 *  	-
 *  	-
 *  	-
 *  1.0.5
 *      - changes in $login_type
 *      - added $page
 *      - ALTER TABLE  `<DB_PREFIX>accounts` CHANGE  `hotels`  `companies` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  '';
 *      - added DEFAULT_LOGIN_LINK
 *      - added profile photo
 *  1.0.4
 *      - added SetLocale()
 *      - added default value for preferred lang
 *      - added username and passwrod generators
 *      - added '{ACCOUNT TYPE}' => 'admin'
 *      - added blank values for some datetime fields
 *  1.0.3
 *      - SQL _YES/_NO replaced with "enum"
 *      - last_login redone with date_lastlogin
 *      - added $PROJECT + changes for HotelSite
 *      - added $accountTypeOnChange
 *      - added AfterAddRecord/AfterEditRecord/AfterDetailsMode for HotelSite
 *  1.0.2
 *      - replaced " with '
 *      - improved working with #arr_languages
 *      - added sending emails in preferred language
 *      - removed clients for HotelSite
 *	
 **/

class AdminsAccounts extends MicroGrid {
	
	protected $debug = false;
	
	private $arrCompanies = array(); /* used to show companies where admin is owner */
	private $arrCompaniesSelected = array(); /* used to show selected companies (add mode) where admin is owner */
	private $additionalFields = '';
	private $accountTypeOnChange = '';
	private $sqlFieldDatetimeFormat = '';
	private $page = '';
	private $multiHotelsForManagers = '';

	//------------------------------
	private static $PROJECT = PROJECT_NAME;

	//==========================================================================
    // Class Constructor
	//	@param $login_type
	//	@param $page
	//==========================================================================
	function __construct($login_type = '', $page = '')	
	{
		parent::__construct();
		
		global $objSettings;

        global $objLogin;

		$this->params = array();		
		if(isset($_POST['first_name'])) 	$this->params['first_name'] = prepare_input($_POST['first_name']);
		if(isset($_POST['last_name']))		$this->params['last_name']  = prepare_input($_POST['last_name']);
		if(isset($_POST['user_name']))  	$this->params['user_name']  = prepare_input($_POST['user_name']);
		if(isset($_POST['password']))		$this->params['password']   = prepare_input($_POST['password']);
		if(isset($_POST['email']))   		$this->params['email']      = prepare_input($_POST['email']);
		if(isset($_POST['preferred_language']))  $this->params['preferred_language'] = prepare_input($_POST['preferred_language']);
		if(isset($_POST['account_type']))   $this->params['account_type'] = prepare_input($_POST['account_type']);
		if(isset($_POST['oa_consumer_domain']))	$this->params['oa_consumer_domain'] = $_POST['oa_consumer_domain'];
		if(isset($_POST['oa_consumer_key']))   	$this->params['oa_consumer_key']    = prepare_input($_POST['oa_consumer_key']);
		if(isset($_POST['oa_consumer_secret']))	$this->params['oa_consumer_secret']	= prepare_input($_POST['oa_consumer_secret']);

		if(isset($_POST['date_created']))       $this->params['date_created'] = prepare_input($_POST['date_created']);
		if(isset($_POST['is_active']))          $this->params['is_active']    = (int)$_POST['is_active']; else $this->params['is_active'] = '0';
		if(isset($_POST['room_relocation']))    $this->params['room_relocation']  = $_POST['room_relocation']; else $this->params['room_relocation'] = '0';
		if(isset($_POST['hotel_relocation']))   $this->params['hotel_relocation'] = $_POST['hotel_relocation']; else $this->params['hotel_relocation'] = '0';
		if(isset($_POST['companies']))      $this->params['companies'] = prepare_input($_POST['companies']);
		
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_ACCOUNTS;
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->page			= $page;

		$this->multiHotelsForManagers = ModulesSettings::Get('property_management', 'multi_hotels_for_managers');

		$this->formActionURL = 'index.php?admin=admins_management';
		if($page == 'hotel_admins'){
			$this->formActionURL = 'index.php?admin=hotel_owners_management';
		}elseif($page == 'agency_admins'){
			$this->formActionURL = 'index.php?admin=mod_car_rental_owners_management';
		}elseif($page == 'regional_admins'){
			$this->formActionURL = 'index.php?admin=regional_managers_management';
		}elseif($page == 'hotel_managers' && Modules::IsModuleInstalled('property_management')){
			$this->formActionURL = 'index.php?admin=mod_property_management_managers';
		}
		
		$this->actions      = array('add'=>true, 'edit'=>true, 'details'=>true, 'delete'=>true);
		$this->actionIcons  = true;
		$this->allowRefresh = true;

		$this->allowLanguages = false;

		$this->WHERE_CLAUSE = '';
		if($login_type == 'owner' || $login_type == 'mainadmin'){
			if($page == 'hotel_admins'){
				$this->WHERE_CLAUSE = "WHERE ".TABLE_ACCOUNTS.".account_type = 'hotelowner'";	
			}elseif($page == 'agency_admins'){
				$this->WHERE_CLAUSE = "WHERE ".TABLE_ACCOUNTS.".account_type = 'agencyowner'";
			}elseif($page == 'hotel_managers' && Modules::IsModuleInstalled('property_management')){
                $this->WHERE_CLAUSE = "WHERE (".TABLE_ACCOUNTS.".account_type = 'hotelmanager')";
            }elseif($login_type == 'owner'){
				$this->WHERE_CLAUSE = "WHERE (".TABLE_ACCOUNTS.".account_type IN('mainadmin', 'admin'))";
			}elseif($login_type == 'mainadmin'){
				$this->WHERE_CLAUSE = "WHERE (".TABLE_ACCOUNTS.".account_type IN('admin'))";
			}
		}elseif($login_type == 'admin'){
			$this->WHERE_CLAUSE = "WHERE ".TABLE_ACCOUNTS.".account_type = 'admin'";
		}elseif($login_type == 'hotelowner'){
		    if($page == 'hotel_managers' && Modules::IsModuleInstalled('property_management')){
                 $assignedToHotels = $objLogin->AssignedToHotels();
                 $companiesFindInText = array();
                 if(!empty($assignedToHotels) && is_array($assignedToHotels)){
                     foreach($assignedToHotels as $assignedToHotel){
                         $companiesFindInText[] = "FIND_IN_SET('".$assignedToHotel."', ".TABLE_ACCOUNTS.".companies)";
                     }
                 }
                $this->WHERE_CLAUSE = "WHERE (".TABLE_ACCOUNTS.".account_type = 'hotelmanager')".(!empty($companiesFindInText) ? ' AND ('.implode(' OR ', $companiesFindInText).')' : ' AND 1 = 0');
            }else{
                $this->WHERE_CLAUSE = "WHERE ".TABLE_ACCOUNTS.".account_type = 'hotelowner'";
            }
		}elseif($login_type == 'agencyowner'){
			$this->WHERE_CLAUSE = "WHERE ".TABLE_ACCOUNTS.".account_type = 'agencyowner'";
		}elseif($login_type == 'regionalmanager'){
			$this->WHERE_CLAUSE = "WHERE ".TABLE_ACCOUNTS.".account_type = 'regionalmanager'";
		}

		$this->ORDER_CLAUSE = 'ORDER BY id ASC';
		$this->isAlterColorsAllowed = true;
		$this->isPagingAllowed = true;
		$this->pageSize = 20;

		$this->isSortingAllowed = true;
		
		$this->isFilteringAllowed = true;
		// define filtering fields
		$this->arrFilteringFields = array(
			_FIRST_NAME => array('table'=>$this->tableName, 'field'=>'first_name', 'type'=>'text', 'sign'=>'like%', 'width'=>'80px'),
			_LAST_NAME  => array('table'=>$this->tableName, 'field'=>'last_name', 'type'=>'text', 'sign'=>'like%', 'width'=>'80px'),
			_EMAIL      => array('table'=>$this->tableName, 'field'=>'email', 'type'=>'text', 'sign'=>'like%', 'width'=>'100px'),
			_ACTIVE     => array('table'=>$this->tableName, 'field'=>'is_active', 'type'=>'dropdownlist', 'source'=>array('0'=>_NO, '1'=>_YES), 'sign'=>'=', 'width'=>'85px'),
		);

		// *** Channel manager
		// -----------------------------------------------------------------------------
		$rest_api_enabled = false;
		if(Modules::IsModuleInstalled('rest_api')){
			$rest_api_enabled = true;
		}

		// Prepare languages array		
		$total_languages = Languages::GetAllActive();
		$arr_languages   = array();
		foreach($total_languages[0] as $key => $val){
			$arr_languages[$val['abbreviation']] = $val['lang_name'];
		}
		
		if($page == 'hotel_admins'){
			$arr_account_types['hotelowner'] = FLATS_INSTEAD_OF_HOTELS ? _FLAT_OWNER : _HOTEL_OWNER;
		}elseif($page == 'agency_admins'){
			$arr_account_types['agencyowner'] = _CAR_AGENCY_OWNER;
		}elseif($page == 'regional_admins'){
			$arr_account_types['regionalmanager'] = _REGIONAL_MANAGER;
		}elseif($page == 'hotel_managers' && Modules::IsModuleInstalled('property_management')){
			$arr_account_types['hotelmanager'] = FLATS_INSTEAD_OF_HOTELS ? _FLAT_MANAGERS : _HOTEL_MANAGERS;
		}else{
			$arr_account_types = array('admin'=>_ADMIN, 'mainadmin'=>_MAIN_ADMIN);
		}

		$arr_is_active = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');
		$datetime_format = get_datetime_format();		
		
		if($page == 'hotel_admins'){
			$companies = Hotels::GetAllHotels();
			$total_companies = isset($companies[0]) ? $companies[0] : array();
		}elseif($page == 'hotel_managers' && $this->multiHotelsForManagers == 'no'){
            $where_clause = '';
            $hotelOwner = $objLogin->IsLoggedInAs('hotelowner');
            if($hotelOwner){
                $hotelsList = implode(',', $objLogin->AssignedToHotels());
                $where_clause = (!empty($hotelsList) ? 'hotel_id IN ('.$hotelsList.')' : '1 = 0');
            }
            $companies = Hotels::GetAllHotels($where_clause);
            $total_companies = isset($companies[0]) ? $companies[0] : array();
		}elseif($page == 'agency_admins'){
			$companies = CarAgencies::GetAllActive();
			$total_companies = isset($companies[0]) ? $companies[0] : array();
		}else{
			$total_companies = array();
		}

		if($page == 'hotel_admins'){
			if(isset($this->params['companies'])){
				foreach($total_companies as $key => $val){
					if(in_array($val['id'], $this->params['companies'])){
						$this->arrCompaniesSelected[$val['id']] = $val['name'];
					}
				}
			}
		}

		foreach($total_companies as $key => $val){
			$this->arrCompanies[$val['id']] = $val['name'];
		}

		$this->additionalFields = ', companies';
		$this->accountTypeOnChange = 'onchange="javascript:AccountType_OnChange(this.value)"';

		if($objSettings->GetParameter('date_format') == 'mm/dd/yyyy'){
			$this->sqlFieldDatetimeFormat = '%b %d, %Y %H:%i';
		}else{
			$this->sqlFieldDatetimeFormat = '%d %b, %Y %H:%i';
		}
		$this->SetLocale(Application::Get('lc_time_name'));

		$autocomplete_companies = '<script>
		jQuery(document).ready(function(){
			var companiesPointAdd = null;
			jQuery("#autocomplete_companies").autocomplete({
				source: function(request, response){
					var token = "'.htmlentities(Application::Get('token')).'";
					jQuery.ajax({
						url: "ajax/hotel.ajax.php",
						global: false,
						type: "POST",
						data: ({
							token: token,
							act: "send",
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
									return{ hotel_name: item.hotel_name, hotel_id: item.hotel_id, label: item.label }
								}));
							}
						}
					});
				},
				minLength: 2,
				select: function(event, ui) {
					if(typeof(ui.item.hotel_id) != "undefined" && jQuery("#group_companies" + ui.item.hotel_id).length == 0){
						var idInput = "";
						
                        // Create ID for new input
						if(companiesPointAdd == null){
							idInput = "companies1";
							var tmpInput = jQuery("input[name=\'companies[]\']").last();
							if(jQuery("input[name=\'companies[]\']").length > 1){
                                idInput = "companies" + (jQuery("input[name=\'companies[]\']").length + 1);
                            }
						}else{
							idInput = "companies" + (companiesPointAdd.parent().children("div").length + 1);
						}

                        // <div class="checkbox"></div>
                        var tmpCheckbox = jQuery("<div/>",{
                            class: "checkbox",
                        });

                        // <div class="checkbox"><input ...></div>
                        var tmpInput = jQuery("<input/>", {
                            id: idInput,
                            type: "checkbox",
                            checked: "checked",
                            name: "companies[]",
                            val: ui.item.hotel_id
                        }).appendTo(tmpCheckbox);

                        // <div class="checkbox"><input ...><label for="companies..">...</label></div>
                        var tmpLabel = jQuery("<label/>", {
                            for: idInput,
                            text: ui.item.hotel_name
                        }).appendTo(tmpCheckbox);

                        // <div style="..."><div class="checkbox"><input ...><label for="companies..">...</label></div></div>
                        var tmpBlock = jQuery("<div/>", {
                            css: {
                                float: "left",
                                width: "32.3%"
                            },
                        }).append(tmpCheckbox);

                        var parentTd = jQuery("#mg_row_companies td")[1];
						var alreadyExists = false;
						
						jQuery("input[name=\'companies[]\']").each(function(){
							if(ui.item.hotel_id == jQuery(this).val()){
								alert("'.htmlentities(_ITEM_ALREADY_SELECTED).' - " + ui.item.hotel_name);
								alreadyExists = true;
							}
						});

						if(!alreadyExists){
							jQuery(parentTd).append(tmpBlock);	
						}
					}

					jQuery("#autocomplete_companies").val("");
					return false;
				}
			});
		});
		</script>';
		
		//---------------------------------------------------------------------- 
		// VIEW MODE
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT '.$this->primaryKey.',
									"[ '._LOCATIONS.' ]" as locations,		
									first_name,
		                            last_name,
									CONCAT(first_name, \' \', last_name) as full_name,
									user_name,
									email,
									profile_photo,
									profile_photo_thumb,
									preferred_language,
									account_type,
									companies,
									IF(date_lastlogin IS NULL, "<span class=gray>- never -</span>", DATE_FORMAT(date_lastlogin, \''.$this->sqlFieldDatetimeFormat.'\')) as date_lastlogin,
									CONCAT("<a href=index.php?admin=hotel_owners_payment_gateways&hotel_owner_id=", '.$this->tableName.'.'.$this->primaryKey.', ">[ '.(FLATS_INSTEAD_OF_HOTELS ? _FLAT_PAYMENT_GATEWAYS : _PAYMENT_GATEWAYS).' ]</a>") as link_payment,
									is_active,
									room_relocation,
                                    hotel_relocation
									'.$this->additionalFields.'
								FROM '.$this->tableName;		
		// define view mode fields
		if($page == 'regional_admins'){
			$this->arrViewModeFields = array(
				'profile_photo_thumb'  => array('title'=>_PHOTO, 'type'=>'image', 'align'=>'left', 'width'=>'50px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'image_width'=>'32px', 'image_height'=>'28px', 'target'=>'images/admins/', 'no_image'=>'no_image.png'),
				'full_name'   => array('title'=>_NAME, 'type'=>'label', 'align'=>'left', 'width'=>''),
				'email' 	  => array('title'=>_EMAIL_ADDRESS, 'type'=>'link', 'maxlength'=>'35', 'href'=>'mailto:{email}', 'align'=>'left', 'width'=>'250px'),
				'user_name'   => array('title'=>_USER_NAME,  'type'=>'label', 'align'=>'left', 'width'=>'150px'),
				'is_active'   => array('title'=>_ACTIVE, 'type'=>'enum',  'align'=>'center', 'width'=>'80px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_is_active),
				'date_lastlogin'  => array('title'=>_LAST_LOGIN, 'type'=>'label', 'align'=>'center', 'width'=>'130px', 'format'=>'date', 'format_parameter'=>$datetime_format),
				'id'          => array('title'=>'ID', 'type'=>'label', 'align'=>'center', 'width'=>'40px'),
				'locations'   => array('title'=>'', 'type'=>'link', 'href'=>'index.php?admin=regional_manager_locations&aid={id}', 'align'=>'left', 'width'=>'100px'),
			);
		}else{
			$this->arrViewModeFields = array(
				'profile_photo_thumb'   => array('title'=>_PHOTO, 'type'=>'image', 'align'=>'left', 'width'=>'50px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'image_width'=>'32px', 'image_height'=>'28px', 'target'=>'images/admins/', 'no_image'=>'no_image.png'),
				'full_name'   	        => array('title'=>_NAME, 'type'=>'label', 'align'=>'left', 'width'=>''),
				'email' 	  	        => array('title'=>_EMAIL_ADDRESS, 'type'=>'link', 'maxlength'=>'35', 'href'=>'mailto:{email}', 'align'=>'left', 'width'=>'210px'),
				'user_name'   	        => array('title'=>_USER_NAME,  'type'=>'label', 'align'=>'left', 'width'=>'150px'),
				'account_type' 	        => array('title'=>_ACCOUNT_TYPE, 'type'=>'enum', 'align'=>'center', 'width'=>'100px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>($login_type == 'owner' || $login_type == 'mainadmin' || $login_type == 'admin' ? true : false), 'source'=>$arr_account_types),
				'companies' 	        => array('title'=>(Modules::IsModuleInstalled('car_rental') && $page == 'agency_admins' ? _CAR_AGENCIES : (FLATS_INSTEAD_OF_HOTELS ? _FLATS : _HOTELS)), 'type'=>'enum', 'align'=>'center', 'width'=>'120px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>($page == 'hotel_admins' || $page == 'agency_admins' ? true : false), 'source'=>$this->arrCompanies, 'data_view'=>'counter', 'data_type'=>'separated', 'tooltip_divider'=>''),
				'is_active'   	        => array('title'=>_ACTIVE, 'type'=>'enum', 'align'=>'center', 'width'=>'80px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_is_active),
				'date_lastlogin'        => array('title'=>_LAST_LOGIN, 'type'=>'label', 'align'=>'center', 'width'=>'130px', 'format'=>'date', 'format_parameter'=>$datetime_format),
				'id'          	        => array('title'=>'ID', 'type'=>'label', 'align'=>'center', 'width'=>'40px'),
                'link_payment'          => array('title'=>'', 'type'=>'link', 'align'=>'center', 'width'=>'170px', 'visible'=>(($page == 'hotel_admins' && ModulesSettings::Get('booking', 'allow_separate_gateways') == 'yes') ? true : false)),
			);
		}
		
		//---------------------------------------------------------------------- 
		// ADD MODE
		//---------------------------------------------------------------------- 
		// define add mode fields
		$this->arrAddModeFields = array(
		    'separator_1'   =>array(
				'separator_info' => array('legend'=>_PERSONAL_DETAILS),
				'first_name'  	 => array('title'=>_FIRST_NAME,	'type'=>'textbox', 'width'=>'210px', 'required'=>true, 'maxlength'=>'32', 'validation_type'=>'text'),
				'last_name'    	 => array('title'=>_LAST_NAME, 	'type'=>'textbox', 'width'=>'210px', 'required'=>true, 'maxlength'=>'32', 'validation_type'=>'text'),
				'email' 		 => array('title'=>_EMAIL_ADDRESS,'type'=>'textbox', 'width'=>'210px', 'required'=>true, 'maxlength'=>'100', 'validation_type'=>'email', 'unique'=>true),
				'profile_photo'  => array('title'=>_PHOTO, 'type'=>'image', 'width'=>'210px', 'required'=>false, 'readonly'=>false, 'target'=>'images/admins/', 'no_image'=>'no_image.png', 'random_name'=>true, 'overwrite_image'=>false, 'unique'=>false, 'thumbnail_create'=>true, 'thumbnail_field'=>'profile_photo_thumb', 'thumbnail_width'=>'90px', 'thumbnail_height'=>'90px', 'file_maxsize'=>'900k', 'watermark'=>false, 'watermark_text'=>''),
			),
		    'separator_2'   =>array(
				'separator_info' => array('legend'=>_ACCOUNT_DETAILS),
				'user_name'  	 => array('title'=>_USER_NAME,	'type'=>'textbox', 'width'=>'210px', 'required'=>true, 'maxlength'=>'32', 'validation_type'=>'alpha_numeric', 'unique'=>true, 'username_generator'=>true),
				'password'  	 => array('title'=>_PASSWORD, 	'type'=>'password', 'width'=>'210px', 'required'=>true, 'maxlength'=>'32', 'validation_type'=>'password', 'cryptography'=>PASSWORDS_ENCRYPTION, 'cryptography_type'=>PASSWORDS_ENCRYPTION_TYPE, 'aes_password'=>PASSWORDS_ENCRYPT_KEY, 'password_generator'=>true),
				'account_type'   => array('title'=>_ACCOUNT_TYPE, 'type'=>'enum', 'required'=>true, 'readonly'=>false, 'width'=>'120px', 'source'=>$arr_account_types, 'javascript_event'=>$this->accountTypeOnChange),
				'preferred_language' => array('title'=>_PREFERRED_LANGUAGE, 'type'=>'enum', 'required'=>true, 'readonly'=>false, 'width'=>'120px', 'default'=>Application::Get('lang'), 'source'=>$arr_languages),
			),
		);
		
		// REST API fields
		if($rest_api_enabled && in_array($page, array('site_admins', 'hotel_admins'))){
			$this->arrAddModeFields['separator_3']['separator_info'] = array('legend'=>_API_DETAILS);
			$this->arrAddModeFields['separator_3']['oa_consumer_domain'] = array('title'=>_API_DOMAIN, 'type'=>'textbox', 'width'=>'310px', 'maxlength'=>'150', 'required'=>false, 'readonly'=>false, 'validation_type'=>'url', 'unique'=>false, 'placeholder'=>'http://');
			$this->arrAddModeFields['separator_3']['oa_consumer_key'] = array('title'=>_API_KEY, 'type'=>'label');
			$this->arrAddModeFields['separator_3']['oa_consumer_secret'] = array('title'=>_API_SECRET, 'type'=>'label');
		}
		
		$this->arrAddModeFields['separator_4'] = array(
			'separator_info' => array('legend'=>_OTHER),
			'date_lastlogin' => array('title'=>'',      'type'=>'hidden',  'required'=>false, 'default'=>''),
			'date_created' 	 => array('title'=>'',      'type'=>'hidden',  'required'=>false, 'default'=>date('Y-m-d H:i:s')),
			'is_active'  	 => array('title'=>_ACTIVE,	'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),
		);
        if($page == 'hotel_admins'){
            $this->arrAddModeFields['separator_4']['room_relocation'] = array('title'=>_MS_ROOM_RELOCATION,	'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false);
            $this->arrAddModeFields['separator_4']['hotel_relocation'] = array('title'=>_MS_HOTEL_RELOCATION,	'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false);
        }

		if($page == 'hotel_admins' || ($page == 'hotel_managers' && Modules::IsModuleInstalled('property_management') && $this->multiHotelsForManagers == 'yes')){
			$this->arrAddModeFields['separator_4']['autocomplete_companies'] = array('title'=>FLATS_INSTEAD_OF_HOTELS ? _EX_FLAT_OR_LOCATION : _EX_HOTEL_OR_LOCATION, 'type'=>'textbox', 'width'=>'210px', 'required'=>false, 'readonly'=>false, 'default'=>'', 'javascript_event'=>'', 'post_html'=>$autocomplete_companies);
		}
		if($page == 'hotel_managers' && Modules::IsModuleInstalled('property_management') && $this->multiHotelsForManagers == 'no'){
            $this->arrAddModeFields['separator_4']['companies'] = array('title'=>(FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL), 'type'=>'enum',  'width'=>'', 'required'=>true, 'readonly'=>false, 'visible'=>true, 'default'=>'', 'source'=>$this->arrCompanies, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false);
        }else{
            $this->arrAddModeFields['separator_4']['companies'] = array('title'=>(Modules::IsModuleInstalled('car_rental') && $page == 'agency_admins') ? _CAR_AGENCIES : (FLATS_INSTEAD_OF_HOTELS ? _FLATS : _HOTELS), 'type'=>'enum',  'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'', 'source'=>($page == 'hotel_admins' ? $this->arrCompaniesSelected : $this->arrCompanies), 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'checkboxes', 'multi_select_columns'=>'3', 'multi_select'=>true, 'data_type'=>'separated');
        }



        //----------------------------------------------------------------------
		// EDIT MODE
		//---------------------------------------------------------------------- 
		$this->EDIT_MODE_SQL = 'SELECT
								'.$this->tableName.'.'.$this->primaryKey.',
								'.$this->tableName.'.first_name,
								'.$this->tableName.'.last_name,
								'.$this->tableName.'.user_name,
								'.$this->tableName.'.password,
								'.$this->tableName.'.email,
								'.$this->tableName.'.profile_photo,
								'.$this->tableName.'.account_type,
								'.$this->tableName.'.preferred_language,
								'.$this->tableName.'.oa_consumer_domain,
								'.$this->tableName.'.oa_consumer_key,
								'.$this->tableName.'.oa_consumer_secret,
								IF('.$this->tableName.'.oa_consumer_key = \'\',
									"",
									CONCAT("<button class=form_button style=color:#555!important;background-color:rgba(215,227,234,0.3)!important;border-color:rgba(198,207,212,0.5)!important; type=submit name=btnRecreate onclick=javascript:if(confirm_recreate()){document.getElementById(\"frmMicroGrid_'.$this->tableName.'\").getElementsByTagName(\"input\")[1].value=\"recreate_api\"}else{return&nbsp;false;}>", "'._RECREATE.'", "</button>")
								) as button_recreate,
								'.(in_array($page, array('site_admins', 'hotel_admins')) ? 'IF(account_type != "admin", MD5(CONCAT('.$this->tableName.'.user_name,'.$this->tableName.'.password,'.$this->tableName.'.'.$this->primaryKey.')), "'._UNKNOWN.'") as auth_token,' : '').'
								IF('.$this->tableName.'.date_created IS NULL, "<span class=gray>- unknown -</span>", DATE_FORMAT('.$this->tableName.'.date_created, \''.$this->sqlFieldDatetimeFormat.'\')) as date_created,
								IF('.$this->tableName.'.date_lastlogin IS NULL, "<span class=gray>- never -</span>", DATE_FORMAT('.$this->tableName.'.date_lastlogin, \''.$this->sqlFieldDatetimeFormat.'\')) as date_lastlogin,
								'.$this->tableName.'.is_active,
								'.$this->tableName.'.room_relocation,
                                '.$this->tableName.'.hotel_relocation
								'.$this->additionalFields.'
							FROM '.$this->tableName.'
							WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';		
		// define edit mode fields
		$this->arrEditModeFields = array(
		    'separator_1'   =>array(
				'separator_info' => array('legend'=>_PERSONAL_DETAILS),
				'first_name'  	 => array('title'=>_FIRST_NAME,	'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'32', 'required'=>true, 'validation_type'=>'text'),
				'last_name'    	 => array('title'=>_LAST_NAME, 	'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'32', 'required'=>true, 'validation_type'=>'text'),
				'email' 		 => array('title'=>_EMAIL_ADDRESS,'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'100', 'required'=>true, 'validation_type'=>'email', 'unique'=>true),
				'profile_photo'  => array('title'=>_PHOTO, 'type'=>'image', 'width'=>'210px', 'required'=>false, 'readonly'=>false, 'target'=>'images/admins/', 'no_image'=>'no_image.png', 'random_name'=>true, 'overwrite_image'=>false, 'unique'=>false, 'thumbnail_create'=>true, 'thumbnail_field'=>'profile_photo_thumb', 'thumbnail_width'=>'90px', 'thumbnail_height'=>'90px', 'file_maxsize'=>'900k', 'watermark'=>false, 'watermark_text'=>''),
			),
		    'separator_2'   =>array(
				'separator_info' => array('legend'=>_ACCOUNT_DETAILS),
				'user_name'  	 => array('title'=>_USER_NAME,	'type'=>'textbox', 'width'=>'210px', 'maxlength'=>'32', 'required'=>true, 'readonly'=>true, 'validation_type'=>'alpha_numeric', 'unique'=>true),
				'account_type'   => array('title'=>_ACCOUNT_TYPE, 'type'=>'enum', 'width'=>'120px', 'required'=>true, 'maxlength'=>'32', 'readonly'=>(($login_type == 'owner')?false:true), 'source'=>$arr_account_types, 'javascript_event'=>$this->accountTypeOnChange),
				'preferred_language' => array('title'=>_PREFERRED_LANGUAGE, 'type'=>'enum', 'width'=>'120px', 'required'=>true, 'readonly'=>false, 'source'=>$arr_languages),
			),
		);
		
		// REST API fields
		if($rest_api_enabled && in_array($page, array('site_admins', 'hotel_admins'))){
			$this->arrEditModeFields['separator_3']['separator_info'] = array('legend'=>_API_DETAILS);
			$this->arrEditModeFields['separator_3']['oa_consumer_domain'] = array('title'=>_API_DOMAIN, 'type'=>'textbox', 'width'=>'310px', 'maxlength'=>'150', 'validation_type'=>'url', 'required'=>false, 'readonly'=>false, 'unique'=>false, 'placeholder'=>'http://');
			$this->arrEditModeFields['separator_3']['oa_consumer_key'] = array('title'=>_API_KEY, 'type'=>'label');
			$this->arrEditModeFields['separator_3']['oa_consumer_secret'] = array('title'=>_API_SECRET, 'type'=>'label');
			$this->arrEditModeFields['separator_3']['button_recreate'] = array('title'=>'', 'type'=>'label');
		}
		$this->arrEditModeFields['separator_4'] = array(
			'separator_info'   	=> array('legend'=>_OTHER),
			'date_created' 		=> array('title'=>_DATE_CREATED, 'type'=>'label'),
			'date_lastlogin'  	=> array('title'=>_LAST_LOGIN, 'type'=>'label'),
			'is_active'  	   	=> array('title'=>_ACTIVE, 'type'=>'checkbox', 'true_value'=>'1', 'false_value'=>'0'),
		);
        if($page == 'hotel_admins'){
            $this->arrEditModeFields['separator_4']['room_relocation'] = array('title'=>_MS_ROOM_RELOCATION, 'type'=>'checkbox', 'true_value'=>'1', 'false_value'=>'0');
            $this->arrEditModeFields['separator_4']['hotel_relocation'] = array('title'=>_MS_HOTEL_RELOCATION, 'type'=>'checkbox', 'true_value'=>'1', 'false_value'=>'0');
        }
		if($page == 'hotel_admins' || ($this->page == 'hotel_managers' && $this->multiHotelsForManagers == 'yes')){
			$this->arrEditModeFields['separator_4']['autocomplete_companies'] = array('title'=>FLATS_INSTEAD_OF_HOTELS ? _EX_FLAT_OR_LOCATION : _EX_HOTEL_OR_LOCATION, 'type'=>'textbox', 'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'', 'javascript_event'=>'', 'post_html'=>$autocomplete_companies);
			$this->arrEditModeFields['separator_4']['companies'] = array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLATS : _HOTELS, 'type'=>'enum', 'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'', 'source'=>$this->arrCompaniesSelected, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'checkboxes', 'multi_select_columns'=>'3', 'multi_select'=>true, 'data_type'=>'separated');
		}elseif(Modules::IsModuleInstalled('car_rental') && $page == 'agency_admins'){
			$this->arrEditModeFields['separator_4']['companies'] = array('title'=>_CAR_AGENCIES, 'type'=>'enum',  'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'', 'source'=>$this->arrCompanies, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'checkboxes', 'multi_select_columns'=>'3', 'multi_select'=>true, 'data_type'=>'separated');
		}elseif($page == 'hotel_managers' && Modules::IsModuleInstalled('property_management') && $this->multiHotelsForManagers == 'no'){
            $this->arrEditModeFields['separator_4']['companies'] = array('title'=>(FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL), 'type'=>'enum',  'width'=>'', 'required'=>true, 'readonly'=>false, 'visible'=>true, 'default'=>'', 'source'=>$this->arrCompanies, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false);
        }

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = 'SELECT
								'.$this->tableName.'.'.$this->primaryKey.',
								'.$this->tableName.'.first_name,
								'.$this->tableName.'.last_name,
								'.$this->tableName.'.user_name,
								'.$this->tableName.'.password,
								'.$this->tableName.'.email,
								'.$this->tableName.'.profile_photo,
								'.$this->tableName.'.preferred_language,
								'.$this->tableName.'.account_type,
								'.$this->tableName.'.oa_consumer_domain,
								'.$this->tableName.'.oa_consumer_key,
								'.$this->tableName.'.oa_consumer_secret,
								'.(in_array($page, array('site_admins', 'hotel_admins')) ? 'IF(account_type != "admin", MD5(CONCAT('.$this->tableName.'.user_name,'.$this->tableName.'.password,'.$this->tableName.'.'.$this->primaryKey.')), "'._UNKNOWN.'") as auth_token,' : '').'
								IF('.$this->tableName.'.date_created IS NULL, "<span class=gray>- unknown -</span>", DATE_FORMAT('.$this->tableName.'.date_created, \''.$this->sqlFieldDatetimeFormat.'\')) as date_created,
								IF('.$this->tableName.'.date_lastlogin IS NULL, "<span class=gray>- never -</span>", DATE_FORMAT('.$this->tableName.'.date_lastlogin, \''.$this->sqlFieldDatetimeFormat.'\')) as date_lastlogin,
								'.$this->tableName.'.is_active,
								'.$this->tableName.'.room_relocation,
								'.$this->tableName.'.hotel_relocation
								'.$this->additionalFields.'
							FROM '.$this->tableName.'
							WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';		
		$this->arrDetailsModeFields = array(
		    'separator_1'   =>array(
				'separator_info' => array('legend'=>_PERSONAL_DETAILS),
				'first_name'  	=> array('title'=>_FIRST_NAME,	'type'=>'label'),
				'last_name'    	=> array('title'=>_LAST_NAME, 'type'=>'label'),
				'email'     	=> array('title'=>_EMAIL_ADDRESS, 	 'type'=>'label'),
				'profile_photo' => array('title'=>_PHOTO, 'type'=>'image', 'target'=>'images/admins/', 'no_image'=>'no_image.png', 'image_width'=>'90px', 'image_height'=>'90px'),
			),
		    'separator_2'   =>array(
				'separator_info' => array('legend'=>_ACCOUNT_DETAILS),
				'user_name'   	=> array('title'=>_USER_NAME, 'type'=>'label'),
				'account_type'  => array('title'=>_ACCOUNT_TYPE, 'type'=>'enum', 'source'=>$arr_account_types),
				'preferred_language' => array('title'=>_PREFERRED_LANGUAGE, 'type'=>'enum', 'source'=>$arr_languages),
			),
		);
		
		// REST API fields
		if($rest_api_enabled  && in_array($page, array('site_admins', 'hotel_admins'))){
			$this->arrDetailsModeFields['separator_3']['separator_info'] = array('legend'=>_API_DETAILS);
			$this->arrDetailsModeFields['separator_3']['oa_consumer_domain'] = array('title'=>_API_DOMAIN, 'type'=>'label');
			$this->arrDetailsModeFields['separator_3']['oa_consumer_key'] = array('title'=>_API_KEY, 'type'=>'label');
			$this->arrDetailsModeFields['separator_3']['oa_consumer_secret'] = array('title'=>_API_SECRET, 'type'=>'label');
		}
		$this->arrDetailsModeFields['separator_4'] = array(
			'separator_info' => array('legend'=>_OTHER),
			'date_created'   => array('title'=>_DATE_CREATED, 'type'=>'label'),
			'date_lastlogin' => array('title'=>_LAST_LOGIN, 'type'=>'label'),
			'is_active'  	 => array('title'=>_ACTIVE, 'type'=>'enum', 'source'=>$arr_is_active),
		);
        if($page == 'hotel_admins'){
            $this->arrDetailsModeFields['separator_4']['room_relocation'] = array('title'=>_MS_ROOM_RELOCATION, 'type'=>'enum', 'source'=>$arr_is_active);
            $this->arrDetailsModeFields['separator_4']['hotel_relocation'] = array('title'=>_MS_HOTEL_RELOCATION, 'type'=>'enum', 'source'=>$arr_is_active);
        }
		$this->arrDetailsModeFields['separator_4']['companies'] = array('title'=>(Modules::IsModuleInstalled('car_rental') && $page == 'agency_admins' ? _CAR_AGENCIES : (FLATS_INSTEAD_OF_HOTELS ? _FLATS : _HOTELS)), 'type'=>'enum', 'align'=>'center', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>($page == 'hotel_admins' || $page == 'agency_admins' || $page == 'hotel_managers' ? true : false), 'source'=>($page == 'hotel_admins' ? $this->arrCompaniesSelected : $this->arrCompanies), 'data_type'=>'separated');
	}

	//==========================================================================
    // Class Destructor
	//==========================================================================
    function __destruct()
	{
		// echo 'this object has been destroyed';
    }

	/**
	 * After-Addition operation
	 */
	public function AfterInsertRecord()
	{
		global $objSettings, $objSiteDescription;
		
		////////////////////////////////////////////////////////////
		send_email(
			$this->params['email'],
			$objSettings->GetParameter('admin_email'),
			'new_account_created_by_admin',
			array(
				'{FIRST NAME}'   => $this->params['first_name'],
				'{LAST NAME}'    => $this->params['last_name'],
				'{USER NAME}'    => $this->params['user_name'],
				'{USER PASSWORD}' => $this->params['password'],
				'{WEB SITE}'     => $_SERVER['SERVER_NAME'],
				'{BASE URL}'     => APPHP_BASE,
				'{YEAR}'         => date('Y'),
				'customer='.DEFAULT_CUSTOMER_LINK => 'admin='.DEFAULT_LOGIN_LINK,
				'user=login'     => 'admin='.DEFAULT_LOGIN_LINK,
				'{ACCOUNT TYPE}' => 'admin'
			),
			$this->params['preferred_language']
		);
		////////////////////////////////////////////////////////////
	}

	/**
	 * After-Updating operation
	 */
	public function AfterUpdateRecord()
	{
		global $objLogin;		
		$objLogin->UpdateLoggedEmail($this->params['email']);
	}
	
	/**
	 * After drawing Add Mode
	 */
	public function AfterAddRecord()
	{
		$this->SetCompaniesViewState();
	}
	
	/**
	 * After drawing Edit Mode
	 */
	public function AfterEditRecord()
	{
		$this->SetCompaniesViewState();
	}
	
	/**
	 * After drawing Details Mode
	 */
	public function AfterDetailsMode()
	{
		if(isset($this->result[0][0]['account_type']) && !in_array($this->result[0][0]['account_type'], array('hotelowner', 'agencyowner', 'hotelmanager'))) $this->SetCompaniesViewState(true);
	}
	
	/**
	 * Before drawing Edit Mode
	 */
	public function BeforeEditRecord()
	{
        global $objLogin;

		$sql = 'SELECT
					'.$this->tableName.'.id,
					'.$this->tableName.'.companies
				FROM '.$this->tableName.'
				WHERE '.$this->tableName.'.id = '.(int)$this->curRecordId;

		$arr_companies = array();
		$account = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($account[1] > 0){
			$companies = @explode(',', $account[0]['companies']);
			if(!empty($companies)){
				if($this->page == 'hotel_admins' || ($this->page == 'hotel_managers' && $this->multiHotelsForManagers == 'yes')){
					$where = TABLE_HOTELS.'.id IN ('.implode(',',$companies).')';
                    $arr_companies = Hotels::GetAllHotels($where);
				}elseif(Modules::IsModuleInstalled('car_rental') && $this->page == 'agency_admins'){
					$where = TABLE_CAR_AGENCIES.'.id IN ('.implode(',',$companies).')';
					$arr_companies = CarAgencies::GetAllActive($where);
				}elseif($this->page == 'hotel_managers' && $this->multiHotelsForManagers == 'no'){
                    $where = '';
                    if($objLogin->IsLoggedInAs('hotelowner')){
                        $where = TABLE_HOTELS.'.id IN ('.implode(',',$companies).')';
                    }
                    $arr_companies = Hotels::GetAllHotels($where);
                }

                $companies_source = array();
				if($arr_companies[1] > 0){
					foreach($arr_companies[0] as $key => $val){
						$companies_source[$val['id']] = $val['name'];
					}
				}
                if($this->page == 'hotel_managers' && $this->multiHotelsForManagers == 'no'){
                    $this->arrEditModeFields['separator_4']['companies'] = array('title'=>(FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL), 'type'=>'enum',  'width'=>'', 'required'=>true, 'readonly'=>false, 'visible'=>true, 'default'=>'', 'source'=>$companies_source, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false);
                }else{
                    $this->arrEditModeFields['separator_4']['companies'] = array('title'=>(Modules::IsModuleInstalled('car_rental') && $this->page == 'agency_admins') ? _CAR_AGENCIES : (FLATS_INSTEAD_OF_HOTELS ? _FLATS : _HOTELS), 'type'=>'enum',  'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'', 'source'=>$companies_source, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'checkboxes', 'multi_select_columns'=>'3', 'multi_select'=>true, 'data_type'=>'separated');
                }
			}
		}

		return true;
	}
	
	/**
	 * Before drawing Details Mode
	 */
	public function BeforeDetailsRecord()
	{
        global $objLogin;

		$sql = 'SELECT
					'.$this->tableName.'.id,
					'.$this->tableName.'.companies
				FROM '.$this->tableName.'
				WHERE '.$this->tableName.'.id = '.(int)$this->curRecordId;
		$account = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($account[1] > 0){
			$companies = @explode(',', $account[0]['companies']);
			if(!empty($companies)){
				if($this->page == 'hotel_admins' || ($this->page == 'hotel_managers' && $this->multiHotelsForManagers == 'yes')){
					$where = TABLE_HOTELS.'.id IN ('.implode(',',$companies).')';
					$arr_companies = Hotels::GetAllHotels($where);
				}elseif(Modules::IsModuleInstalled('car_rental') && $this->page == 'agency_admins'){
					$where = TABLE_CAR_AGENCIES.'.id IN ('.implode(',',$companies).')';
					$arr_companies = CarAgencies::GetAllActive($where);
				}elseif($this->page == 'hotel_managers' && $this->multiHotelsForManagers == 'no'){
                    $where = '';
                    if($objLogin->IsLoggedInAs('hotelowner')){
                        $where = TABLE_HOTELS.'.id IN ('.implode(',',$companies).')';
                    }
                    $arr_companies = Hotels::GetAllHotels($where);
                }
				
				$companies_source = array();
				if($arr_companies[1] > 0){
					foreach($arr_companies[0] as $key => $val){
						$companies_source[$val['id']] = $val['name'];
					}
				}
				$this->arrDetailsModeFields['separator_4']['companies'] = array('title'=>(Modules::IsModuleInstalled('car_rental') && $this->page == 'agency_admins') ? _CAR_AGENCIES : (FLATS_INSTEAD_OF_HOTELS ? _FLATS : _HOTELS), 'type'=>'enum',  'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'', 'source'=>$companies_source, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'checkboxes', 'multi_select_columns'=>'3', 'multi_select'=>true, 'data_type'=>'separated');
			}
		}

		return true;
	}
	
	/**
	 * Before drawing Insert Mode
	 */
	public function BeforeInsertRecord()
	{
		$this->PrepareOaCredentials('add');
		return true;
	}
		
	/**
	 * Before drawing Update Mode
	 */
	public function BeforeUpdateRecord()
	{
		$this->PrepareOaCredentials('edit');
		return true;
	}
	
	/**
	 *  Recreate API Credentials
	 */
	public function RecreateApi($rid)
	{
		global $objLogin;

		$consumer_domain = isset($_POST['oa_consumer_domain'])	? $_POST['oa_consumer_domain'] : '';
		
		// deny all operations in demo version
		if(strtolower(SITE_MODE) == 'demo'){
			return draw_important_message(_OPERATION_BLOCKED, false);
		}
		
		$oa_consumer_key = '';
		$oa_consumer_secret = '';
		
		if(!empty($consumer_domain) && substr($consumer_domain, -1) != '/'){
			$consumer_domain .= '/';
		}

		if(!empty($consumer_domain)){
			if(!is_url($consumer_domain)){
				$error_field = 'oa_consumer_domain';
				return draw_important_message(str_replace('_FIELD_', '<b>'._API_DOMAIN.'</b>', _FIELD_MUST_BE_URL), false);
			}else{
				$sql = 'SELECT * FROM '.TABLE_ACCOUNTS.' WHERE id = '.(int)$rid;
				$account = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
				if($account[1] > 0){
					$oa_consumer_key = get_random_string(20);
					$oa_consumer_secret = md5($consumer_domain.$oa_consumer_key);		
				}else{
					$error_field = 'oa_consumer_domain';
					return draw_success_message(_CHANGES_SAVED, false);
				}
			}
		}
		
		$sql = 'UPDATE '.TABLE_ACCOUNTS.'
				SET
					oa_consumer_domain = '.quote_text($consumer_domain).',
					oa_consumer_key = '.quote_text($oa_consumer_key).',
					oa_consumer_secret = '.quote_text($oa_consumer_secret).',
					oa_date_created = "'.date('Y-m-d H:i:s').'"
				WHERE id = '.(int)$rid;
		if(database_void_query($sql)){
			return draw_success_message(_CHANGES_SAVED, false);
		}else{
			return draw_important_message(_TRY_LATER, false);
		}
	}

	/**
	 * Prepare OA credentials
	 * @param string $mode
	 */
	private function PrepareOaCredentials($mode = 'edit')
	{
		if(!Modules::IsModuleInstalled('rest_api')){
			return false;
		}
		
		if(!empty($this->params['oa_consumer_domain'])){
			$generate = false;
			if($mode == 'add'){
				$generate = true;
			}else{
				$sql = 'SELECT * FROM '.$this->tableName.' WHERE id = '.(int)$this->curRecordId;
				$account = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
				if($account[1] > 0 && $this->params['oa_consumer_domain'] != $account[0]['oa_consumer_domain']){
					$generate = true;
				}
			}
			
			if($generate){				
				$this->params['oa_consumer_key'] = get_random_string(20);
				$this->params['oa_consumer_secret']	= md5($this->params['oa_consumer_domain'].$this->params['oa_consumer_key']);
			}
		}else{
			$this->params['oa_consumer_key'] = '';
			$this->params['oa_consumer_secret']	= '';
		}
	}	

	/**
	 * Set companies view state
	 * @param $force_hidding
	 */
	private function SetCompaniesViewState($force_hidding = false)
	{
		if($force_hidding){
			echo '<script type="text/javascript">jQuery("#mg_row_autocomplete_companies").hide();jQuery("#mg_row_companies").hide();</script>';
		}else{
			echo '<script type="text/javascript">if(jQuery("#account_type").val() != "hotelowner" && jQuery("#account_type").val() != "agencyowner" && jQuery("#account_type").val() != "hotelmanager"){ jQuery("#mg_row_autocomplete_companies").hide();jQuery("#mg_row_companies").hide(); }</script>';
		}
	}

	/**
	 * Set companies view state
	 * @param int $hotel_owner_id
     * @return array $hotel_ids
	 */
	public static function getHotelListForHotelOwner($hotel_owner_id = 0)
	{
        $hotel_ids = array();
        $sql = "SELECT ".TABLE_ACCOUNTS.".companies FROM ".TABLE_ACCOUNTS." WHERE ".TABLE_ACCOUNTS.".id = ".$hotel_owner_id." AND ".TABLE_ACCOUNTS.".account_type = 'hotelowner'";
        $hotel_owner = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);

        if(!empty($hotel_owner)){
            $hotel_ids = @explode(',', $hotel_owner['companies']);
        }

        return $hotel_ids;
	}
}
