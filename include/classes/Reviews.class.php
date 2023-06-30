<?php

/**
 *	Reviews Class
 *  --------------
 *	Description : encapsulates methods and properties
 *	Written by  : ApPHP
 *	Version     : 1.0.2
 *  Updated	    : 11.10.2017
 *	Usage       : HotelSite
 *	Differences : no
 *
 *	PUBLIC				  	STATIC				 	PRIVATE
 * 	------------------	  	---------------     	---------------
 *	__construct             DrawReviews				CheckRecordAssigned
 *	__destruct				GetReviews
 *	BeforeAddRecord			GetRandomReview
 *	AfterInsertRecord		GetEvaluation
 *	BeforeEditRecord		GetEvaluations
 *	BeforeDetailsRecord		CheckNewReviewsAfterStayed
 *	BeforeDeleteRecord
 *	AfterDeleteRecord
 *  UpdateReviewsCount
 *
 *  
 *  1.0.2
 *  	-
 *  	-
 *  	-
 *  	-
 *  	-
 *  1.0.1
 *  	- author_email made not required
 *  	- changes is_active to enum type
 *  	- added maxlength to textareas
 *  	- syntax eror in function name
 *  	- added "admin_answer" field
 *	
 **/

class Reviews extends MicroGrid {
	
	protected $debug = false;
	protected $assigned_to_hotels = '';
	
	private static $arr_rates = array('0'=>_NONE);
	private static $arr_evaluation = array('5'=>_WONDERFUL, '4'=>_VERY_GOOD, '3'=>_GOOD, '2'=>_NEUTRAL, '1'=>_NOT_GOOD, '0'=>_NOT_RECOMMENDED);
	private static $arr_evaluation_external = array('0'=>_NOT_RECOMMENDED, '1'=>_NOT_GOOD, '2'=>_NEUTRAL, '3'=>_GOOD, '4'=>_VERY_GOOD, '5'=>_RECOMMENDED_TO_EVERYONE);
	private static $arr_passenger_type = array('0'=>_NOT_DEFINED, '1'=>_AN_INDIVIDUAL, '2'=>_A_YOUNG_COUPLE, '3'=>_AN_OLD_COUPLE, '4'=>_FAMILY_YOUNG_CHILDREN, '5'=>_FAMILY_OLD_CHILDREN, '6'=>_GROUP_OF_FRIENDS);
	private static $arr_travel_type = array('0'=>_NOT_DEFINED, '1'=>_WORK, '2'=>_TOURISM, '3'=>_OTHER);
	
	//------------------------------
	private $page;
	private $user_id;
	private $sqlFieldDatetimeFormat = '';
	private $sqlFieldDateFormat = '';	
	private $errorMessage = '';
	private $deletedCustomerId = 0;

	// #001 private $arrTranslations = '';		
	
	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct($user_id = '')
	{		
		parent::__construct();
		
		global $objSettings, $objLogin;
		
		$this->params = array();
		$this->hotel_owner = $objLogin->IsLoggedInAs('hotelowner');
		
		## for standard fields
		if(isset($_POST['hotel_id']))       		$this->params['hotel_id'] = prepare_input($_POST['hotel_id']);
		if(isset($_POST['customer_id']))    		$this->params['customer_id'] = prepare_input($_POST['customer_id']);
		if(isset($_POST['rating_cleanliness']))		$this->params['rating_cleanliness'] = prepare_input($_POST['rating_cleanliness']);
		if(isset($_POST['rating_room_comfort']))	$this->params['rating_room_comfort'] = prepare_input($_POST['rating_room_comfort']);
		if(isset($_POST['rating_location']))		$this->params['rating_location'] = prepare_input($_POST['rating_location']);
		if(isset($_POST['rating_service']))			$this->params['rating_service'] = prepare_input($_POST['rating_service']);
		if(isset($_POST['rating_sleep_quality']))	$this->params['rating_sleep_quality'] = prepare_input($_POST['rating_sleep_quality']);
		if(isset($_POST['rating_price']))			$this->params['rating_price'] = prepare_input($_POST['rating_price']);
		if(isset($_POST['evaluation']))				$this->params['evaluation'] = prepare_input($_POST['evaluation']);
		if(isset($_POST['title']))					$this->params['title'] = prepare_input($_POST['title']);
		if(isset($_POST['positive_comments'])) 		$this->params['positive_comments'] = prepare_input($_POST['positive_comments']);
		if(isset($_POST['negative_comments'])) 		$this->params['negative_comments'] = prepare_input($_POST['negative_comments']);
		if(isset($_POST['admin_answer'])) 			$this->params['admin_answer'] = prepare_input($_POST['admin_answer']);
		if(isset($_POST['travel_type']))			$this->params['travel_type'] = prepare_input($_POST['travel_type']);
		if(isset($_POST['passenger_type'])) 		$this->params['passenger_type'] = prepare_input($_POST['passenger_type']);
		if(isset($_POST['date_created']))  			$this->params['date_created']   = prepare_input($_POST['date_created']);
		if(isset($_POST['is_active']))      		$this->params['is_active'] = (int)$_POST['is_active']; else $this->params['is_active'] = '0';
		if(isset($_POST['priority_order'])) 		$this->params['priority_order'] = prepare_input($_POST['priority_order']); 

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

		$this->params['language_id'] = MicroGrid::GetParameter('language_id');
		if($user_id != ''){
			$this->params['customer_id'] = $user_id;
		}else{
			$sql = 'SELECT * FROM '.TABLE_CUSTOMERS.' WHERE is_active = 1 ';
			$result = database_query($sql, DATA_AND_ROWS);
			$arr_customers = array();
			if($result[1] > 0){
				foreach($result[0] as $customer){
					$arr_customers[$customer['id']] = $customer['first_name'].' '.$customer['last_name'];
				}
			}
		}
	
		//$this->uPrefix 		= 'prefix_';
		
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_REVIEWS;
		$this->dataSet 		= array();
		$this->error 		= '';

		if($user_id != ''){
			$this->user_id = $user_id;
			$this->page = 'customer=my_reviews';
			$this->actions  = array(
				'add'=>true,
				'edit'=>true,
				'details'=>true,
				'delete'=>true
			);	
		}else{
			$this->user_id = '';
			$this->page = 'admin=mod_reviews_management';
			if($this->hotel_owner){
				$this->actions  = array('add'=>false, 'edit'=>false, 'details'=>true, 'delete'=>true);	
			}else{
				$this->actions  = array('add'=>true, 'edit'=>true, 'details'=>true, 'delete'=>true);	
			}		
		}

		$this->formActionURL = 'index.php?'.$this->page;

		$this->actionIcons  = true;
		$this->allowRefresh = true;
		$this->allowTopButtons = true;
		$this->alertOnDelete = ''; // leave empty to use default alerts

		$this->allowLanguages = false;
		$this->languageId  	= ($this->params['language_id'] != '') ? $this->params['language_id'] : Languages::GetDefaultLang();

		$this->WHERE_CLAUSE = 'WHERE 1 = 1';
		if($this->user_id != ''){
			$this->WHERE_CLAUSE .= ' AND r.customer_id = '.(int)$this->user_id;
		}elseif($objLogin->IsLoggedInAs('hotelowner')){
			$hotels_list = '';
			if($objLogin->IsLoggedInAs('hotelowner')){
				$hotels = $objLogin->AssignedToHotels();
				$hotels_list = implode(',', $hotels);
				if(!empty($hotels_list)) $this->assigned_to_hotels = ' AND r.hotel_id IN ('.$hotels_list.') ';
			}
			$this->WHERE_CLAUSE .= $this->assigned_to_hotels;
		}
		
		$this->ORDER_CLAUSE = 'ORDER BY priority_order ASC'; // ORDER BY date_created DESC
		
		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 20;

		$this->isSortingAllowed = true;
		
		// Prepare Hotel list
		if($user_id != ''){
			$sql = 'SELECT
						br.hotel_id as id,
						hd.name
						FROM '.TABLE_BOOKINGS_ROOMS.' br
						INNER JOIN '.TABLE_BOOKINGS.' b ON b.booking_number = br.booking_number
						INNER JOIN '.TABLE_HOTELS_DESCRIPTION.' hd ON br.hotel_id = hd.hotel_id AND hd.language_id = \''.$this->languageId.'\'
					WHERE b.customer_id = '.(int)$user_id.' AND b.status = 3 AND br.checkin <= \''.date('Y-m-d').'\'
					GROUP BY br.hotel_id
					ORDER BY br.checkin ASC';
			$total_hotels = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		}else{
			$total_hotels = Hotels::GetAllActive();	
		}

        $arr_hotels = array();
		foreach($total_hotels[0] as $key => $val){
			$arr_hotels[$val['id']] = $val['name'];
		}

		$hid = 0;
		if($user_id != ''){
			// We find hotel on those who had not left a review
			$hotel_no_reviews = $arr_hotels;
			$sql = 'SELECT
						r.hotel_id
						FROM '.TABLE_REVIEWS.' r
					WHERE r.customer_id = '.(int)$user_id;
			$hotel_reviews = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
			if($hotel_reviews[1] > 0){
				foreach($hotel_reviews[0] as $hotel){
					if(isset($hotel_no_reviews[$hotel['hotel_id']])){
						unset($hotel_no_reviews[$hotel['hotel_id']]);
					}
				}
			}
			$this->hotel_no_reviews = $hotel_no_reviews;
			if(empty($hotel_no_reviews)){
				$this->actions['add'] = false;
			}

			if(!empty($_GET['hid'])){
				$hid = prepare_input($_GET['hid']);
			}
			// Priority of data transmitted via POST
			if(!empty($_POST['hotel_id'])){
				$hid = prepare_input($_POST['hotel_id']);
			}

			// removed as blocks check of required field
			//$this->params['hotel_id'] = $hid;

			// If the client, it can add a review just for the hotel in which he visited
			if(!isset($hotel_no_reviews[$hid]) && (!empty($_GET['hid']) || !empty($_POST['hotel_id']))){
				// then error (if add mode)
				$this->error = FLATS_INSTEAD_OF_HOTELS ? _YOU_NOT_REVIEW_THIS_FLAT : _YOU_NOT_REVIEW_THIS_HOTEL;
			}
		}else{
			if(!empty($_POST['hotel_id']) && isset($arr_hotels[$_POST['hotel_id']])){
				$this->params['hotel_id'] = prepare_input($_POST['hotel_id']);
			}
		}

        $total_countries = Countries::GetAllCountries();
		$arr_countries = array();
		foreach($total_countries[0] as $key => $val){
			$arr_countries[$val['abbrv']] = $val['name'];
		}
		
		$arr_is_active = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');
        
		$this->isFilteringAllowed = ($this->user_id != '') ? false : true;

        $hotel_name = FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL;
		// define filtering fields 
		$this->arrFilteringFields = array(
			_CUSTOMER   => array('table'=>'cr', 'field'=>'last_name', 'type'=>'text', 'sign'=>'%like%', 'width'=>'80px', 'visible'=>true),
			_EMAIL   	=> array('table'=>'cr', 'field'=>'email', 'type'=>'text', 'sign'=>'%like%', 'width'=>'80px', 'visible'=>true),
			$hotel_name => array('table'=>'r', 'field'=>'hotel_id', 'type'=>'dropdownlist', 'source'=>$arr_hotels, 'sign'=>'=', 'width'=>'130px', 'visible'=>($objLogin->IsLoggedInAs('hotelowner') ? false: true)),
			_EVALUATION => array('table'=>'r', 'field'=>'evaluation', 'type'=>'dropdownlist', 'source'=>self::$arr_evaluation, 'sign'=>'=', 'width'=>'130px', 'visible'=>true),
		);

		$this->isAggregateAllowed = true;
		// define aggregate fields for View Mode
		$this->arrAggregateFields = array(
			'average_rating' => array('function'=>'AVG', 'align'=>'center', 'aggregate_by'=>'average_rating', 'decimal_place'=>2, 'sign'=>' '),
		);

		// prepare languages array		
		/// $total_languages = Languages::GetAllActive();
		/// $arr_languages      = array();
		/// foreach($total_languages[0] as $key => $val){
		/// 	$arr_languages[$val['abbreviation']] = $val['lang_name'];
		/// }

		if($objSettings->GetParameter('date_format') == 'mm/dd/yyyy'){
			$this->sqlFieldDateFormat = '%b %d, %Y';
			$this->sqlFieldDatetimeFormat = '%b %d, %Y %H:%i';
		}else{
			$this->sqlFieldDateFormat = '%d %b, %Y';
			$this->sqlFieldDatetimeFormat = '%d %b, %Y %H:%i';
		}
		$this->SetLocale(Application::Get('lc_time_name'));

		//---------------------------------------------------------------------- 
		// VIEW MODE
		// format: strip_tags
		// format: nl2br
		// format: 'format'=>'date', 'format_parameter'=>'M d, Y, g:i A'
		// format: 'format'=>'currency', 'format_parameter'=>'european|2' or 'format_parameter'=>'american|4'
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT r.'.$this->primaryKey.',
                                    r.hotel_id,
									CONCAT(cr.first_name, \' \', cr.last_name) as author_name,
									cr.b_city as author_city,
									cr.email as author_email,
									r.priority_order,
									((r.rating_cleanliness + r.rating_room_comfort + r.rating_location + r.rating_service + r.rating_sleep_quality + r.rating_price) / 6) as average_rating,
									r.evaluation,
									r.is_active,
									DATE_FORMAT(r.date_created, "'.$this->sqlFieldDateFormat.'") as created_date_formated,
									cr.b_country,
									hd.name as hotel_name
								FROM '.$this->tableName.' r
									INNER JOIN '.TABLE_CUSTOMERS.' cr ON r.customer_id = cr.id
									INNER JOIN '.TABLE_HOTELS_DESCRIPTION.' hd ON r.hotel_id = hd.hotel_id AND hd.language_id = \''.Application::Get('lang').'\'';

		// define view mode fields
		if($this->user_id != ''){
			$this->arrViewModeFields = array(
				'created_date_formated'  => array('title'=>_DATE_ADDED, 'type'=>'label', 'align'=>'left', 'width'=>'120px', 'height'=>'', 'maxlength'=>''),
				'hotel_name'     => array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL, 'type'=>'link',  'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>'', 'href'=>'index.php?page='.(FLATS_INSTEAD_OF_HOTELS ? 'flats&fid=' : 'hotels&hid=').'{hotel_id}', 'target'=>'_new'),
				'average_rating' => array('title'=>_RATE,          'type'=>'label', 'align'=>'center', 'width'=>'110px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
				'evaluation'     => array('title'=>_EVALUATION,    'type'=>'enum',  'align'=>'center', 'width'=>'110px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>self::$arr_evaluation),
				'is_active'      => array('title'=>_ACTIVE,        'type'=>'enum',  'align'=>'center', 'width'=>'90px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_is_active),
			);			
		}else{
			$this->arrViewModeFields = array(
				'author_name'    => array('title'=>_CUSTOMER,      'type'=>'label', 'align'=>'left', 'width'=>'180px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'20', 'format'=>'', 'format_parameter'=>''),
				'author_email'   => array('title'=>_EMAIL_ADDRESS, 'type'=>'link',  'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'30', 'format'=>'', 'format_parameter'=>'', 'href'=>'mailto://{author_email}', 'target'=>''),
				'b_country'  	 => array('title'=>_COUNTRY, 		'type'=>'enum',  'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_countries),
				'author_city'    => array('title'=>_CITY,          'type'=>'label', 'align'=>'left', 'width'=>'140px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
				'hotel_id'       => array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL, 'type'=>'enum',  'align'=>'left', 'width'=>'110px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_hotels),
				'average_rating' => array('title'=>_RATE,          'type'=>'label', 'align'=>'center', 'width'=>'90px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
				'evaluation'     => array('title'=>_EVALUATION,    'type'=>'enum',  'align'=>'center', 'width'=>'90px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>self::$arr_evaluation),
				'is_active'      => array('title'=>_ACTIVE,        'type'=>'enum',  'align'=>'center', 'width'=>'70px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_is_active),
				'priority_order' => array('title'=>_ORDER,         'type'=>'label', 'align'=>'center', 'width'=>'70px', 'movable'=>true),
			);
		}

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
		$this->arrAddModeFields = array();
		if($user_id != ''){
			$this->arrAddModeFields['separator_1'] = array(
				'separator_info' 	=> array('legend'=>_GENERAL, 'columns'=>'0'),
				'hotel_id'       	=> array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL, 'type'=>'enum', 'required'=>true, 'align'=>'left', 'width'=>'', 'default'=>(!empty($hid) ? $hid : ''), 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$hotel_no_reviews),
				'date_created'	 	=> array('title'=>_DATE_ADDED,	'type'=>'hidden', 'width'=>'210px', 'required'=>true, 'default'=>date('Y-m-d H:i:s'), 'visible'=>false),
				'customer_id'    	=> array('title'=>_CUSTOMER, 'type'=>'hidden', 'default'=>$user_id, 'visible'=>true),
				'is_active'      	=> array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),
			);
		}else{
			$this->arrAddModeFields['separator_1'] = array(
				'separator_info' 	=> array('legend'=>_GENERAL, 'columns'=>'0'),
				//'customer_id'       => array('title'=>_CUSTOMER, 'type'=>'enum', 'required'=>true, 'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_customers),
				'customer_id'       => array('title'=>_CUSTOMER, 'type'=>'textbox',  'width'=>'210px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'', 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'visible'=>true, 'username_generator'=>false, 'placeholder'=>''),
				'hotel_id'          => array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL, 'type'=>'enum', 'required'=>true, 'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_hotels),
				'date_created'		=> array('title'=>_DATE_ADDED,	'type'=>'hidden', 'width'=>'210px', 'required'=>true, 'default'=>date('Y-m-d H:i:s')),
				'priority_order'   	=> array('title'=>_ORDER, 'type'=>'textbox',  'width'=>'60px', 'default'=>'0', 'maxlength'=>'3', 'required'=>true, 'readonly'=>false, 'validation_type'=>'numeric|positive'),
				'is_active'        	=> array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),
			);
		}
		
		$this->arrAddModeFields['separator_2'] = array(
			'separator_info' 		=> array('legend'=>_RATINGS, 'columns'=>'0'),
			'rating_cleanliness'	=> array('title'=>_CLEANLINESS, 'type'=>'slider', 'width'=>'410px', 'default'=>'4', 'slider_settings'=>array('from'=>0, 'to'=>5, 'step'=>'0.1'), 'validation_type'=>'float'),
			'rating_room_comfort'	=> array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT_COMFORT : _ROOM_COMFORT, 'type'=>'slider', 'width'=>'410px',  'default'=>'4','slider_settings'=>array('from'=>0, 'to'=>5, 'step'=>'0.1'), 'validation_type'=>'float'),
			'rating_location'		=> array('title'=>_LOCATION, 'type'=>'slider', 'width'=>'410px', 'default'=>'4', 'slider_settings'=>array('from'=>0, 'to'=>5, 'step'=>'0.1'), 'validation_type'=>'float'),
			'rating_service'		=> array('title'=>_SERVICE_AND_STAFF, 'type'=>'slider', 'width'=>'410px', 'default'=>'4', 'slider_settings'=>array('from'=>0, 'to'=>5, 'step'=>'0.1'), 'validation_type'=>'float'), 
			'rating_sleep_quality'	=> array('title'=>_SLEEP_QUALITY, 'type'=>'slider', 'width'=>'410px', 'default'=>'4', 'slider_settings'=>array('from'=>0, 'to'=>5, 'step'=>'0.1'), 'validation_type'=>'float'),
			'rating_price'			=> array('title'=>_VALUE_FOR_PRICE, 'type'=>'slider', 'width'=>'410px', 'default'=>'4', 'slider_settings'=>array('from'=>0, 'to'=>5, 'step'=>'0.1'), 'validation_type'=>'float'),
			'evaluation'			=> array('title'=>_EVALUATION, 'type'=>'enum', 'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'2', 'source'=>self::$arr_evaluation, 'default_option'=>false, 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
			'travel_type'			=> array('title'=>_TRAVEL_TYPE, 'type'=>'enum', 'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'0', 'source'=>self::$arr_travel_type, 'default_option'=>false, 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
			'passenger_type'		=> array('title'=>_PASSENGER_TYPE, 'type'=>'enum', 'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'0', 'source'=>self::$arr_passenger_type, 'default_option'=>false, 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
		);
		$this->arrAddModeFields['separator_3'] = array(
			'separator_info' 		=> array('legend'=>_COMMENTS, 'columns'=>'0'),
			'title' 				=> array('title'=>_TITLE, 'type'=>'textarea', 'required'=>true, 'width'=>'410px', 'height'=>'60px', 'editor_type'=>'simple', 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'maxlength'=>255, 'validation_maxlength'=>255),
			'positive_comments' 	=> array('title'=>_POSITIVE_COMMENTS, 'type'=>'textarea', 'required'=>false, 'width'=>'410px', 'height'=>'120px', 'editor_type'=>'simple', 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'maxlength'=>1024, 'validation_maxlength'=>1024),
			'negative_comments' 	=> array('title'=>_NEGATIVE_COMMENTS, 'type'=>'textarea', 'required'=>false, 'width'=>'410px', 'height'=>'120px', 'editor_type'=>'simple', 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'maxlength'=>1024, 'validation_maxlength'=>1024),
			'admin_answer' 			=> array('title'=>_ADMIN_ANSWER, 'type'=>'textarea', 'required'=>false, 'width'=>'410px', 'height'=>'120px', 'editor_type'=>'simple', 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'maxlength'=>1024, 'validation_maxlength'=>1024, 'visible'=>($user_id != '' ? false : true)),
		);
		$this->arrAddModeFields['separator_4'] = array(
			'separator_info' 	=> array('legend'=>_IMAGES, 'columns'=>'0'),
			'image_file_1'   	=> array('title'=>_IMAGE.' #1', 'type'=>'image', 'width'=>'210px', 'required'=>false, 'readonly'=>false, 'target'=>'images/reviews/', 'no_image'=>'no_image.png', 'random_name'=>true, 'overwrite_image'=>true, 'unique'=>true, 'image_name_pefix'=>'testi_img_'.(int)self::GetParameter('rid').'_', 'thumbnail_create'=>true, 'thumbnail_field'=>'image_file_1_thumb', 'thumbnail_width'=>'120px', 'thumbnail_height'=>'', 'file_maxsize'=>'500k', 'watermark'=>'', 'watermark_text'=>''),
			'image_file_2'   	=> array('title'=>_IMAGE.' #2', 'type'=>'image', 'width'=>'210px', 'required'=>false, 'readonly'=>false, 'target'=>'images/reviews/', 'no_image'=>'no_image.png', 'random_name'=>true, 'overwrite_image'=>true, 'unique'=>true, 'image_name_pefix'=>'testi_img_'.(int)self::GetParameter('rid').'_', 'thumbnail_create'=>true, 'thumbnail_field'=>'image_file_2_thumb', 'thumbnail_width'=>'120px', 'thumbnail_height'=>'', 'file_maxsize'=>'500k', 'watermark'=>'', 'watermark_text'=>''),
			'image_file_3'   	=> array('title'=>_IMAGE.' #3', 'type'=>'image', 'width'=>'210px', 'required'=>false, 'readonly'=>false, 'target'=>'images/reviews/', 'no_image'=>'no_image.png', 'random_name'=>true, 'overwrite_image'=>true, 'unique'=>true, 'image_name_pefix'=>'testi_img_'.(int)self::GetParameter('rid').'_', 'thumbnail_create'=>true, 'thumbnail_field'=>'image_file_3_thumb', 'thumbnail_width'=>'120px', 'thumbnail_height'=>'', 'file_maxsize'=>'500k', 'watermark'=>'', 'watermark_text'=>''),
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
								rv.'.$this->primaryKey.',
                                rv.hotel_id,
                                hd.name as hotel_name,
								CONCAT(cr.first_name, \' \', cr.last_name) as author_name,
								cr.b_country as author_country,
								cr.b_city as author_city,
								cr.email as author_email,
								rv.title,
								rv.positive_comments,
								rv.negative_comments,
								rv.admin_answer,
								rv.rating_cleanliness,
								rv.rating_room_comfort,
								rv.rating_location,
								rv.rating_service,
								rv.rating_sleep_quality,
								rv.rating_price,
								rv.evaluation,
								rv.travel_type,
								rv.passenger_type,
								rv.image_file_1,
								rv.image_file_2,
								rv.image_file_3,
								DATE_FORMAT(rv.date_created, \''.$this->sqlFieldDatetimeFormat.'\') as date_created,
								rv.is_active,
								rv.priority_order
							FROM '.$this->tableName.' rv
							INNER JOIN '.TABLE_HOTELS_DESCRIPTION.' hd ON rv.hotel_id = hd.hotel_id AND hd.language_id = \''.Application::Get('lang').'\'
							LEFT OUTER JOIN '.TABLE_CUSTOMERS.' cr ON rv.customer_id = cr.id
							WHERE rv.'.$this->primaryKey.' = _RID_' . ($this->user_id != '' ? ' AND rv.customer_id = '.$this->user_id : '');
		// define edit mode fields
		$this->arrEditModeFields = array();
		if($user_id != ''){
			$this->arrEditModeFields['separator_1'] = array(
				'separator_info' => array('legend'=>_GENERAL, 'columns'=>'0'),
				'hotel_name'       => array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL, 'type'=>'label'),
				'author_name'      => array('title'=>_CUSTOMER, 'type'=>'label'),
				'author_country'   => array('title'=>_COUNTRY,	 'type'=>'enum',  'source'=>$arr_countries, 'view_type'=>'label'),
				'author_city'      => array('title'=>_CITY, 'type'=>'label'),
				'author_email'     => array('title'=>_EMAIL_ADDRESS, 'type'=>'label'),
				'date_created'	   => array('title'=>_DATE_ADDED, 'type'=>'label'),
				'priority_order'   => array('title'=>_ORDER, 'type'=>'textbox', 'width'=>'60px', 'maxlength'=>'3', 'required'=>true, 'readonly'=>false, 'validation_type'=>'numeric|positive', 'visible'=>($this->user_id ? false : true)),
				'is_active'        => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),
			);
		}else{
			$this->arrEditModeFields['separator_1'] = array(
				'separator_info' => array('legend'=>_GENERAL, 'columns'=>'0'),
				///'hotel_id'         => array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$arr_hotels, 'default_option'=>'0', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
				'hotel_name'       => array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL, 'type'=>'label'),
				'author_name'      => array('title'=>_CUSTOMER, 'type'=>'label'),
				'author_country'   => array('title'=>_COUNTRY, 'type'=>'enum',  'source'=>$arr_countries, 'view_type'=>'label'),
				'author_city'      => array('title'=>_CITY, 'type'=>'label'),
				'author_email'     => array('title'=>_EMAIL_ADDRESS, 'type'=>'label'),
				'date_created'	   => array('title'=>_DATE_ADDED, 'type'=>'label'),
				'priority_order'   => array('title'=>_ORDER, 'type'=>'textbox', 'width'=>'60px', 'maxlength'=>'3', 'required'=>true, 'readonly'=>false, 'validation_type'=>'numeric|positive', 'visible'=>($this->user_id ? false : true)),
				'is_active'        => array('title'=>_ACTIVE, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),
			);
		}

		$this->arrEditModeFields['separator_2'] = array(
			'separator_info' 		=> array('legend'=>_RATINGS, 'columns'=>'0'),
			'rating_cleanliness'	=> array('title'=>_CLEANLINESS, 'type'=>'slider', 'readonly'=>false, 'width'=>'410px', 'default'=>'4', 'slider_settings'=>array('from'=>0, 'to'=>5, 'step'=>'0.1'), 'validation_type'=>'float|positive', 'validation_maximum'=>5, 'validation_minimum'=>0),
			'rating_room_comfort'	=> array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT_COMFORT : _ROOM_COMFORT, 'type'=>'slider', 'readonly'=>false, 'width'=>'410px',  'default'=>'4','slider_settings'=>array('from'=>0, 'to'=>5, 'step'=>'0.1'), 'validation_type'=>'float|positive', 'validation_maximum'=>5, 'validation_minimum'=>0),
			'rating_location'		=> array('title'=>_LOCATION, 'type'=>'slider', 'readonly'=>false, 'width'=>'410px', 'default'=>'4', 'slider_settings'=>array('from'=>0, 'to'=>5, 'step'=>'0.1'), 'validation_type'=>'float|positive', 'validation_maximum'=>5, 'validation_minimum'=>0),
			'rating_service'		=> array('title'=>_SERVICE_AND_STAFF, 'type'=>'slider', 'readonly'=>false, 'width'=>'410px', 'default'=>'4', 'slider_settings'=>array('from'=>0, 'to'=>5, 'step'=>'0.1'), 'validation_type'=>'float|positive', 'validation_maximum'=>5, 'validation_minimum'=>0), 
			'rating_sleep_quality'	=> array('title'=>_SLEEP_QUALITY, 'type'=>'slider', 'readonly'=>false, 'width'=>'410px', 'default'=>'4', 'slider_settings'=>array('from'=>0, 'to'=>5, 'step'=>'0.1'), 'validation_type'=>'float|positive', 'validation_maximum'=>5, 'validation_minimum'=>0),
			'rating_price'			=> array('title'=>_VALUE_FOR_PRICE, 'type'=>'slider', 'readonly'=>false, 'width'=>'410px', 'default'=>'4', 'slider_settings'=>array('from'=>0, 'to'=>5, 'step'=>'0.1'), 'validation_type'=>'float|positive', 'validation_maximum'=>5, 'validation_minimum'=>0),
			'evaluation'			=> array('title'=>_EVALUATION, 'type'=>'enum', 'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'2', 'source'=>self::$arr_evaluation, 'default_option'=>false, 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
			'travel_type'			=> array('title'=>_TRAVEL_TYPE, 'type'=>'enum', 'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'0', 'source'=>self::$arr_travel_type, 'default_option'=>false, 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
			'passenger_type'		=> array('title'=>_PASSENGER_TYPE, 'type'=>'enum', 'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'0', 'source'=>self::$arr_passenger_type, 'default_option'=>false, 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist', 'multi_select'=>false),
		);
		$this->arrEditModeFields['separator_3'] = array(
			'separator_info' 		=> array('legend'=>_COMMENTS, 'columns'=>'0'),
			'title' 				=> array('title'=>_TITLE, 'type'=>'textarea', 'required'=>true, 'width'=>'410px', 'height'=>'60px', 'editor_type'=>'simple', 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'maxlength'=>255, 'validation_maxlength'=>255),
			'positive_comments' 	=> array('title'=>_POSITIVE_COMMENTS, 'type'=>'textarea', 'required'=>false, 'width'=>'410px', 'height'=>'120px', 'editor_type'=>'simple', 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'maxlength'=>1024, 'validation_maxlength'=>1024),
			'negative_comments' 	=> array('title'=>_NEGATIVE_COMMENTS, 'type'=>'textarea', 'required'=>false, 'width'=>'410px', 'height'=>'120px', 'editor_type'=>'simple', 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'maxlength'=>1024, 'validation_maxlength'=>1024),
			
		);
		if($user_id != ''){
			$this->arrEditModeFields['separator_3']['admin_answer']	= array('title'=>_ADMIN_ANSWER, 'type'=>'label');
		}else{
			$this->arrEditModeFields['separator_3']['admin_answer']	= array('title'=>_ADMIN_ANSWER, 'type'=>'textarea', 'required'=>false, 'width'=>'410px', 'height'=>'120px', 'editor_type'=>'simple', 'readonly'=>false, 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'maxlength'=>1024, 'validation_maxlength'=>1024);			
		}
		
		$this->arrEditModeFields['separator_4'] = array(
			'separator_info' 	=> array('legend'=>_IMAGES, 'columns'=>'0'),
			'image_file_1'   	=> array('title'=>_IMAGE.' #1', 'type'=>'image', 'width'=>'210px', 'required'=>false, 'readonly'=>false, 'target'=>'images/reviews/', 'no_image'=>'no_image.png', 'random_name'=>true, 'overwrite_image'=>true, 'unique'=>true, 'image_name_pefix'=>'r_'.(int)self::GetParameter('rid').'_', 'thumbnail_create'=>true, 'thumbnail_field'=>'image_file_1_thumb', 'thumbnail_width'=>'120px', 'thumbnail_height'=>'', 'file_maxsize'=>'500k', 'watermark'=>'', 'watermark_text'=>''),
			'image_file_2'   	=> array('title'=>_IMAGE.' #2', 'type'=>'image', 'width'=>'210px', 'required'=>false, 'readonly'=>false, 'target'=>'images/reviews/', 'no_image'=>'no_image.png', 'random_name'=>true, 'overwrite_image'=>true, 'unique'=>true, 'image_name_pefix'=>'r_'.(int)self::GetParameter('rid').'_', 'thumbnail_create'=>true, 'thumbnail_field'=>'image_file_2_thumb', 'thumbnail_width'=>'120px', 'thumbnail_height'=>'', 'file_maxsize'=>'500k', 'watermark'=>'', 'watermark_text'=>''),
			'image_file_3'   	=> array('title'=>_IMAGE.' #3', 'type'=>'image', 'width'=>'210px', 'required'=>false, 'readonly'=>false, 'target'=>'images/reviews/', 'no_image'=>'no_image.png', 'random_name'=>true, 'overwrite_image'=>true, 'unique'=>true, 'image_name_pefix'=>'r_'.(int)self::GetParameter('rid').'_', 'thumbnail_create'=>true, 'thumbnail_field'=>'image_file_3_thumb', 'thumbnail_width'=>'120px', 'thumbnail_height'=>'', 'file_maxsize'=>'500k', 'watermark'=>'', 'watermark_text'=>''),
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(

			'separator_1'   =>array(
				'separator_info' => array('legend'=>_GENERAL, 'columns'=>'0'),
				'hotel_name'        => array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL, 'type'=>'label'),
				'author_name'      	=> array('title'=>_CUSTOMER, 'type'=>'label'),
				'author_country'    => array('title'=>_COUNTRY,	 'type'=>'enum',  'source'=>$arr_countries, 'view_type'=>'label'),
				'author_city'      	=> array('title'=>_CITY, 'type'=>'label'),
				'author_email'     	=> array('title'=>_EMAIL_ADDRESS, 'type'=>'label'),
				'date_created'		=> array('title'=>_DATE_ADDED, 'type'=>'label'),
				'priority_order'   	=> array('title'=>_ORDER, 'type'=>'label', 'visible'=>($this->user_id || $this->hotel_owner ? false : true)),
				'is_active'        	=> array('title'=>_ACTIVE, 'type'=>'enum', 'source'=>$arr_is_active),
			),
			'separator_2'   =>array(
				'separator_info' 	=> array('legend'=>_RATINGS, 'columns'=>'0'),
				'rating_cleanliness'	=> array('title'=>_CLEANLINESS, 'type'=>'label'),
				'rating_room_comfort'	=> array('title'=>FLATS_INSTEAD_OF_HOTELS ? _FLAT_COMFORT : _ROOM_COMFORT, 'type'=>'label'),
				'rating_location'		=> array('title'=>_LOCATION, 'type'=>'label'),
				'rating_service'		=> array('title'=>_SERVICE_AND_STAFF, 'type'=>'label'),
				'rating_sleep_quality'	=> array('title'=>_SLEEP_QUALITY, 'type'=>'label'),
				'rating_price'			=> array('title'=>_VALUE_FOR_PRICE, 'type'=>'label'),
				'evaluation'			=> array('title'=>_EVALUATION, 'type'=>'enum', 'source'=>self::$arr_evaluation),
				'travel_type'			=> array('title'=>_TRAVEL_TYPE, 'type'=>'enum', 'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'2', 'source'=>self::$arr_travel_type),
				'passenger_type'		=> array('title'=>_PASSENGER_TYPE, 'type'=>'enum', 'width'=>'', 'required'=>false, 'readonly'=>false, 'default'=>'0', 'source'=>self::$arr_passenger_type),
			),
			'separator_3'   =>array(
				'separator_info' 	=> array('legend'=>_COMMENTS, 'columns'=>'0'),
				'title' 			=> array('title'=>_TITLE, 'type'=>'label'),
				'positive_comments' => array('title'=>_POSITIVE_COMMENTS, 'type'=>'label'),
				'negative_comments' => array('title'=>_NEGATIVE_COMMENTS, 'type'=>'label'),
				'admin_answer' 		=> array('title'=>_ADMIN_ANSWER, 'type'=>'label'), 
			),
			'separator_4'   =>array(
				'separator_info' 	=> array('legend'=>_IMAGES, 'columns'=>'0'),
				'image_file_1'   	=> array('title'=>_IMAGE.' #1', 'type'=>'image', 'target'=>'images/reviews/', 'no_image'=>'no_image.png', 'image_width'=>'120px', 'image_height'=>'90px', 'visible'=>true),
				'image_file_2'   	=> array('title'=>_IMAGE.' #2', 'type'=>'image', 'target'=>'images/reviews/', 'no_image'=>'no_image.png', 'image_width'=>'120px', 'image_height'=>'90px', 'visible'=>true),
				'image_file_3'   	=> array('title'=>_IMAGE.' #3', 'type'=>'image', 'target'=>'images/reviews/', 'no_image'=>'no_image.png', 'image_width'=>'120px', 'image_height'=>'90px', 'visible'=>true),
			)
		);

		///////////////////////////////////////////////////////////////////////////////
		// #004. add translation fields to all modes
		/// $this->AddTranslateToModes(
		/// $this->arrTranslations,
		/// array('name'        => array('title'=>_NAME, 'type'=>'textbox', 'width'=>'410px', 'required'=>true, 'readonly'=>false),
		/// 	  'description' => array('title'=>_DESCRIPTION, 'type'=>'textarea', 'width'=>'410px', 'height'=>'90px', 'required'=>false, 'readonly'=>false)
		/// )
		/// );
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
	 *	Before-adding record
	 */
	public function BeforeAddRecord()
	{ 
		if($this->hotel_owner){
			redirect_to($this->formActionURL);
		}
		
		return true;
	}

	/**
	 *	After-adding record
	 */
	public function AfterInsertRecord()
	{
		$customer_id = isset($_POST['customer_id']) ? (int)$_POST['customer_id'] : 0;
		$result = $this->UpdateReviewsCount($customer_id);
	}

	/**
	 *	Before-editing record
	 */
	public function BeforeEditRecord()
	{ 
		if(!$this->CheckRecordAssigned($this->curRecordId) || $this->hotel_owner){
			redirect_to($this->formActionURL);
		}
		
		return true;
	}

	/**
	 *	Before-details record
	 */
	public function BeforeDetailsRecord()
	{
		if(!$this->CheckRecordAssigned($this->curRecordId)){
			redirect_to($this->formActionURL);
		}
		
		return true;
	}

	/**
	 *	Before-deleting record
	 */
	public function BeforeDeleteRecord()
	{
		$res = $this->CheckRecordAssigned($this->curRecordId);

		if($res){
			$sql = 'SELECT * FROM '.TABLE_REVIEWS.' WHERE id = '.(int)$this->curRecordId;
			$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
			if($result[1] > 0){
				$this->deletedCustomerId = isset($result[0]['customer_id']) ? (int)$result[0]['customer_id'] : 0;
			}
			
			return true;			
		}
		
		return false;
	}

	/**
	 *	After-deleting record
	 */
	public function AfterDeleteRecord()
	{
		// Update reviews count
		if(!empty($this->deletedCustomerId)){
			$result = $this->UpdateReviewsCount($this->deletedCustomerId);
		}
	}

	/**
	 * Updates reviews counter for customer
	 * @param int $customer_id
	 * @return bool
	 */
	public function UpdateReviewsCount($customer_id = 0)
	{
		if(empty($customer_id)){
			return false;
		}
		
		$sql = 'UPDATE '.TABLE_CUSTOMERS.'
				SET reviews_count = (SELECT COUNT(*) FROM '.TABLE_REVIEWS.' WHERE customer_id = '.(int)$customer_id.')
				WHERE id = '.(int)$customer_id;

		if(database_void_query($sql)){
			return true;
		}else{
			return false;
		}								
	}

	/**
	 * ADD NEW RECORD
	 */
	public function AddRecord()
	{
		global $objSettings; 

		if($this->user_id != '' && count($this->hotel_no_reviews) <= 1){
			$this->actions['add'] = false;
		}
		$add_record = parent::AddRecord();

		if($add_record){
			$hotel_id = (int)$this->params['hotel_id'];
			$customer_id = (int)$this->params['customer_id'];
			$sql = 'SELECT
				'.TABLE_CUSTOMERS.'.first_name,
				'.TABLE_CUSTOMERS.'.last_name,
				'.TABLE_CUSTOMERS.'.preferred_language,
				'.TABLE_CUSTOMERS.'.email,
				'.TABLE_CUSTOMERS.'.email_notifications
			FROM '.TABLE_CUSTOMERS.'
			WHERE '.TABLE_CUSTOMERS.'.id = '.$customer_id;

			$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
			if($result[1] > 0 && $result[0]['email_notifications'] == 1){
				$first_name = $result[0]['first_name'];
				$last_name  = $result[0]['last_name'];
				$email      = $result[0]['email'];
				$preferred_language = $result[0]['preferred_language'];
				$sql = 'SELECT
					'.TABLE_HOTELS_DESCRIPTION.'.name
				FROM '.TABLE_HOTELS_DESCRIPTION.'
				WHERE '.TABLE_HOTELS_DESCRIPTION.'.hotel_id = '.$hotel_id.' AND '.TABLE_HOTELS_DESCRIPTION.'.language_id = \''.$preferred_language.'\'';
				$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
				if($result[1] > 0){
					$hotel_name = $result[0]['name'];
				}else{
					$hotel_name = '';
				}
				$admin_email = $objSettings->GetParameter('admin_email');

				$replace = array(
					'{FIRST NAME}'  => $first_name,
					'{LAST NAME}'   => $last_name,
					'{HOTEL}'       => $hotel_name != '' ? '<a href="'.APPHP_BASE.'index.php?page='.(FLATS_INSTEAD_OF_HOTELS ? 'flats&fid=' : 'hotels&hid=').$hotel_id.'">'.$hotel_name.'</a>' : '',
					'{REVIEW LINK}' => '<a href="'.APPHP_BASE.'index.php?page='.(FLATS_INSTEAD_OF_HOTELS ? 'flats&fid=' : 'hotels&hid=').$hotel_id.'&r_page=1">'.APPHP_BASE.'index.php?page='.(FLATS_INSTEAD_OF_HOTELS ? 'flats&fid=' : 'hotels&hid=').$hotel_id.'&r_page=1</a>'
				);

				send_email($email, $admin_email, 'new_review', $replace, $preferred_language);
			}
		}	
		return $add_record;
	}

	/**
	 * DELETE RECORD
	 */
	public function DeleteRecord($rid = '')
	{
		if($this->user_id != ''){
			$this->actions['add'] = true;
		}
		return parent::DeleteRecord($rid);
	}

	/**
	 * Count Reviews
	 * @param $hotel_id
	 * @param $page
	 * @param $num_reviews
	 */
	public static function CountReviews($hotel_id = 0)
	{
		$output = '';
		$lang = Application::Get('lang');

		$sql = 'SELECT
					COUNT(*) as cnt
				FROM '.TABLE_REVIEWS.' r
					INNER JOIN '.TABLE_CUSTOMERS.' ON r.customer_id = '.TABLE_CUSTOMERS.'.id AND '.TABLE_CUSTOMERS.'.is_active = 1
                    LEFT OUTER JOIN '.TABLE_HOTELS_DESCRIPTION.' hd ON r.hotel_id = hd.hotel_id AND hd.language_id = \''.$lang.'\'
					LEFT OUTER JOIN '.TABLE_COUNTRIES.' c ON '.TABLE_CUSTOMERS.'.b_country = c.abbrv AND c.is_active = 1
					LEFT OUTER JOIN '.TABLE_COUNTRIES_DESCRIPTION.' cd ON c.id = cd.country_id AND cd.language_id = \''.$lang.'\'
				WHERE r.is_active = 1
                    '.($hotel_id != 0 ? ' AND r.hotel_id = '.(int)$hotel_id : '');
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			return $result[0]['cnt'];
		}else{
			return 0;
		}
	}

	/**
	 * Get Reviews
	 * @param $hotel_id
	 * @param $page
	 * @param $num_reviews
	 */
	public static function GetReviews($hotel_id = 0, $page = 1, $num_reviews = 10)
	{
		$output = '';
		$lang = Application::Get('lang');

		$page = (int)$page;
		$num_reviews = (int)$num_reviews;

		if($page < 1){
			$page = 1;
		}

		if($num_reviews < 1){
			$num_reviews = 10;
		}

		$start_review = ($page - 1) * $num_reviews;

		$sql = 'SELECT
					r.*,
					CONCAT('.TABLE_CUSTOMERS.'.first_name, " ", SUBSTRING('.TABLE_CUSTOMERS.'.last_name, 1, 1), ".") as author_name,
					'.TABLE_CUSTOMERS.'.b_city author_city,
					'.TABLE_CUSTOMERS.'.profile_photo_thumb,
					cd.name as country_name,
                    hd.name as hotel_name
				FROM '.TABLE_REVIEWS.' r
					INNER JOIN '.TABLE_CUSTOMERS.' ON r.customer_id = '.TABLE_CUSTOMERS.'.id AND '.TABLE_CUSTOMERS.'.is_active = 1
                    LEFT OUTER JOIN '.TABLE_HOTELS_DESCRIPTION.' hd ON r.hotel_id = hd.hotel_id AND hd.language_id = \''.$lang.'\'
					LEFT OUTER JOIN '.TABLE_COUNTRIES.' c ON '.TABLE_CUSTOMERS.'.b_country = c.abbrv AND c.is_active = 1
					LEFT OUTER JOIN '.TABLE_COUNTRIES_DESCRIPTION.' cd ON c.id = cd.country_id AND cd.language_id = \''.$lang.'\'
				WHERE r.is_active = 1
                    '.($hotel_id != 0 ? ' AND r.hotel_id = '.(int)$hotel_id : '').'
				ORDER BY r.priority_order ASC
				LIMIT '.$start_review.','.$num_reviews;
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		
		return $result;
	}

	/**
	 * check leave a customer review
	 */
	public static function CheckCustomerReview($hotel_id, $customer_id)
	{
		$lang = Application::Get('lang');
		
		$result = array();
		if(!empty($hotel_id) && !empty($customer_id)){
			$sql = 'SELECT
						r.*,
						cd.name as country_name,
						hd.name as hotel_name
					FROM '.TABLE_REVIEWS.' r
						INNER JOIN '.TABLE_CUSTOMERS.' ON r.customer_id = '.TABLE_CUSTOMERS.'.id AND '.TABLE_CUSTOMERS.'.is_active = 1
						LEFT OUTER JOIN '.TABLE_HOTELS_DESCRIPTION.' hd ON r.hotel_id = hd.hotel_id AND hd.language_id = \''.$lang.'\'
						LEFT OUTER JOIN '.TABLE_COUNTRIES.' c ON '.TABLE_CUSTOMERS.'.b_country = c.abbrv AND c.is_active = 1
						LEFT OUTER JOIN '.TABLE_COUNTRIES_DESCRIPTION.' cd ON c.id = cd.country_id AND cd.language_id = \''.$lang.'\'
					WHERE r.is_active = 1
						AND r.hotel_id = '.(int)$hotel_id.' AND r.customer_id = '.(int)$customer_id.'
					ORDER BY r.priority_order ASC';
			$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		}
		return !empty($result[1]) ? true : false;
	}

	/**
	 * Draw Reviews
	 * @param $hotel_id
	 * @param $draw
	 */
	public static function DrawReviews($hotel_id = 0, $draw = true)
	{
		$output = '';
		$result = self::GetReviews();
		for($i=0; $i<$result[1]; $i++){
			$address = ($result[0][$i]['author_city'] != '') ? $result[0][$i]['author_city'].' ('.$result[0][$i]['country_name'].')' : $result[0][$i]['country_name'];
			$hotel = ($result[0][$i]['hotel_name'] != '') ? ' <strong>'.$result[0][$i]['hotel_name'].'</strong>' : '';
            $output .= '<strong><u>'.$result[0][$i]['author_name'].'</u></strong>, '.$address.$hotel.'<br>';
			$output .= (!empty($result[0][$i]['positive_comments']) ? $result[0][$i]['positive_comments'] : $result[0][$i]['negative_comments']).'<br><br>';
		}
	
		if($draw) echo $output;		
		else return $output;
	}

	/**
	 * Returns random reviews
	 * @param $hotel_id
	 */
	public static function GetRandomReview($hotel_id = 0)
	{
		$lang = Application::Get('lang');
		
		$sql = 'SELECT
					r.*,
					cd.name as country_name
				FROM '.TABLE_REVIEWS.' r
					INNER JOIN '.TABLE_CUSTOMERS.' ON r.customer_id = '.TABLE_CUSTOMERS.'.id AND '.TABLE_CUSTOMERS.'.is_active = 1
					LEFT OUTER JOIN '.TABLE_COUNTRIES.' c ON '.TABLE_CUSTOMERS.'.b_country = c.abbrv AND c.is_active = 1
					LEFT OUTER JOIN '.TABLE_COUNTRIES_DESCRIPTION.' cd ON c.id = cd.country_id AND cd.language_id = \''.$lang.'\'
				WHERE r.is_active = 1
                    '.($hotel_id != 0 ? ' AND r.hotel_id = '.(int)$hotel_id : '').'
				ORDER BY RAND() ASC';
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		
		return $result;
	}

	/**
	 *	Check it is possible to leave a new review after they stayed at the hotel
	 *	@return bool
	 */
	public static function CheckNewReviewsAfterStayed($customer_id = 0)
	{
		$sql = 'SELECT 
				'.TABLE_BOOKINGS.'.customer_id,
				'.TABLE_CUSTOMERS.'.first_name,
				'.TABLE_CUSTOMERS.'.last_name,
				'.TABLE_CUSTOMERS.'.email,
				'.TABLE_CUSTOMERS.'.preferred_language,
				'.TABLE_BOOKINGS.'.booking_number,
				'.TABLE_BOOKINGS_ROOMS.'.id as booking_room_id,
				'.TABLE_BOOKINGS_ROOMS.'.hotel_id
			FROM '.TABLE_BOOKINGS.'
				INNER JOIN '.TABLE_CUSTOMERS.' ON '.TABLE_CUSTOMERS.'.id = '.TABLE_BOOKINGS.'.customer_id AND '.TABLE_CUSTOMERS.'.is_active = 1 AND '.TABLE_CUSTOMERS.'.is_removed = 0
				LEFT OUTER JOIN '.TABLE_BOOKINGS_ROOMS.' ON '.TABLE_BOOKINGS_ROOMS.'.booking_number = '.TABLE_BOOKINGS.'.booking_number  
			WHERE 
				'.TABLE_CUSTOMERS.'.id = '.(int)$customer_id.'
				AND '.TABLE_BOOKINGS.'.status = 3
				AND '.TABLE_BOOKINGS_ROOMS.'.checkout <= \''.date('Y-m-d').'\' 
				AND '.TABLE_BOOKINGS_ROOMS.'.checkout >= \''.date('Y-m-d', strtotime('-1 months')).'\'';

		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);

		if($result[1] > 0){
			$hotel_no_answer = array();
			for($i = 0; $i < $result[1]; $i++){
				$hotel_id = $result[0][$i]['hotel_id'];
				$b_room_id = $result[0][$i]['booking_room_id'];
				$hotel_no_answer[$hotel_id] = $b_room_id;
			}

			// We are looking customers for all comments
			$sql = 'SELECT
					'.TABLE_REVIEWS.'.hotel_id,
					'.TABLE_REVIEWS.'.customer_id
				FROM '.TABLE_REVIEWS.'
				WHERE '.TABLE_REVIEWS.'.customer_id = '.(int)$customer_id;
			$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);

			if($result[1] > 0){
				// Remove a hotel from the list for which customer replied
				for($i = 0; $i < $result[1]; $i++){
					$hotel_id = $result[0][$i]['hotel_id'];
					if(isset($hotel_no_answer[$hotel_id])){
						unset($hotel_no_answer[$hotel_id]);
						if(empty($hotel_no_answer)){
							unset($hotel_no_answer);
						}
					}
				}
			}

			return !empty($hotel_no_answer) ? true : false;
		}
		return false;
	}
    
	/**
	 * Returns humanized evaluation s
	 */
	public static function GetEvaluations()
	{
		return self::$arr_evaluation_external;
	}

	/**
	 * Returns humanized evaluation 
	 * @param $evaluation
	 */
	public static function GetEvaluation($evaluation = 0)
	{
		return isset(self::$arr_evaluation_external[$evaluation]) ? self::$arr_evaluation_external[$evaluation] : '';
	}

	/**
	 * Check if specific record is assigned to given owner
	 * @param int $curRecordId
	 */
	private function CheckRecordAssigned($curRecordId = 0)
	{
		global $objSession;
		
		$sql = 'SELECT * 
				FROM '.$this->tableName.' r
				WHERE '.$this->primaryKey.' = '.(int)$curRecordId . $this->assigned_to_hotels;
				
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] <= 0){
			$objSession->SetMessage('notice', draw_important_message(_WRONG_PARAMETER_PASSED, false));
			return false;
		}
		
		return true;		
	}

}
