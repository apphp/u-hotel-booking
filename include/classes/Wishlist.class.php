<?php

/**
 *	Wishlist Class
 *  --------------
 *	Description : encapsulates methods and properties
 *	Written by  : ApPHP
 *  Updated	    : 25.12.2013
 *  Usage       : Core Class (ALL)
 *
 *	PUBLIC:				  	STATIC:				 	PRIVATE:
 * 	------------------	  	---------------     	---------------
 *	__construct				GetHotelInfo
 *	__destruct				GetFavoriteButton
 *							AddToList
 *							RemoveFromList	
 *	
 **/


class Wishlist extends MicroGrid {
	
	protected $debug = false;
	
	private static $arr_stars_vm = array(
		'0'=>_NONE,
		'1'=>'<img src="images/smallstar-1.png" alt="1" title="1-star hotel" />',
		'2'=>'<img src="images/smallstar-2.png" alt="2" title="2-stars hotel" />',
		'3'=>'<img src="images/smallstar-3.png" alt="3" title="3-stars hotel" />',
		'4'=>'<img src="images/smallstar-4.png" alt="4" title="4-stars hotel" />',
		'5'=>'<img src="images/smallstar-5.png" alt="5" title="5-stars hotel" />');
	
	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct($user_id = 0)
	{		
		parent::__construct();
		
		global $objSettings;
		
		$this->params = array();
		
		## for standard fields
		//if(isset($_POST['field1']))   $this->params['field1'] = prepare_input($_POST['field1']);
		//if(isset($_POST['field2']))   $this->params['field2'] = prepare_input($_POST['field2']);
		//if(isset($_POST['field3']))   $this->params['field3'] = prepare_input($_POST['field3']);
		//if(isset($_POST['field4_link']))   $this->params['field4_link'] = prepare_input($_POST['field4_link'], false, 'middle');
		
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
	
		//$this->uPrefix 		= 'prefix_';
		
		$this->primaryKey 	= 'id';
		$this->tableName 	= TABLE_WISHLIST;
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?customer=my_wishlist';
		$this->actions      = array('add'=>false, 'edit'=>false, 'details'=>false, 'delete'=>true);
		$this->actionIcons  = true;
		$this->allowRefresh = true;
        $this->allowPrint   = false;
		$this->allowTopButtons = false;
		$this->alertOnDelete = ''; // leave empty to use default alerts

		$this->allowLanguages = false;
		$this->languageId  	= ($this->params['language_id'] != '') ? $this->params['language_id'] : Languages::GetDefaultLang();
		$this->WHERE_CLAUSE = !empty($user_id) ? 'WHERE w.customer_id = '.(int)$user_id : '';
		$this->GROUP_BY_CLAUSE = ''; // GROUP BY '.$this->tableName.'.order_number
		$this->ORDER_CLAUSE = ''; // ORDER BY '.$this->tableName.'.date_created DESC
		
		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 20;

		$this->isSortingAllowed = true;

		// exporting settings
		$this->isExportingAllowed = false;
		$this->arrExportingTypes = array('csv'=>false);

		// define filtering fields		
		$this->isFilteringAllowed = false;
		///$this->maxFilteringColumns = 4; /* optional, defines split columns default = 0 */
		$this->arrFilteringFields = array(
			// 'Caption_1'  => array('table'=>'', 'field'=>'', 'type'=>'text', 'sign'=>'=|>=|<=|like%|%like|%like%', 'width'=>'80px', 'visible'=>true),
			// 'Caption_2'  => array('table'=>'', 'field'=>'', 'type'=>'dropdownlist', 'source'=>array(), 'sign'=>'=|>=|<=|like%|%like|%like%', 'width'=>'130px', 'visible'=>true),
			// 'Caption_3'  => array('table'=>'', 'field'=>'', 'type'=>'calendar', 'date_format'=>'dd/mm/yyyy|mm/dd/yyyy|yyyy/mm/dd', 'sign'=>'=|>=|<=|like%|%like|%like%', 'width'=>'80px', 'visible'=>true),
		);

		///$this->isAggregateAllowed = false;
		///// define aggregate fields for View Mode
		///$this->arrAggregateFields = array(
		///	'field1' => array('function'=>'SUM', 'align'=>'center', 'aggregate_by'=>'', 'decimal_place'=>2),
		///	'field2' => array('function'=>'AVG', 'align'=>'center', 'aggregate_by'=>'', 'decimal_place'=>2),
		///);
		
        if(FLATS_INSTEAD_OF_HOTELS){
    		$arr_item_types = array('1'=>_FLATS, '3'=>_VEHICLES);
        }else{
    		$arr_item_types = array('1'=>_HOTELS, '2'=>_ROOMS, '3'=>_VEHICLES);
        }

		if($objSettings->GetParameter('date_format') == 'mm/dd/yyyy'){
			$sqlFieldDateFormat = '%b %d, %Y';
		}else{
			$sqlFieldDateFormat = '%d %b, %Y';
		}

		///$date_format = get_date_format('view');
		///$date_format_settings = get_date_format('view', true); /* to get pure settings format */
		///$date_format_edit = get_date_format('edit');
		///$datetime_format = get_datetime_format();
		///$time_format = get_time_format(); /* by default 1st param - shows seconds */
		///$currency_format = get_currency_format();

		// prepare languages array		
		/// $total_languages = Languages::GetAllActive();
		/// $arr_languages      = array();
		/// foreach($total_languages[0] as $key => $val){
		/// 	$arr_languages[$val['abbreviation']] = $val['lang_name'];
		/// }

		//---------------------------------------------------------------------- 
		// VIEW MODE
		// format: strip_tags, nl2br, readonly_text
		// format: 'format'=>'date', 'format_parameter'=>'M d, Y, g:i A'
		// format: 'format'=>'currency', 'format_parameter'=>'european|2' or 'format_parameter'=>'american|4'
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = "SELECT w.".$this->primaryKey.",
									w.customer_id,
									w.item_id,
									w.item_type,
									DATE_FORMAT(w.date_added, '".$sqlFieldDateFormat."') as date_added,
									IF(hd.name IS NOT NULL, CONCAT('index.php?page=".(FLATS_INSTEAD_OF_HOTELS ? 'flats&fid=' : 'hotels&hid=')."', w.item_id), CONCAT('index.php?page=rooms&room_id=', w.item_id)) as item_link,
									IF(hd.name IS NOT NULL, hd.name, rd.room_type) as item_name,
									IF(h.id IS NOT NULL, h.stars, '') as item_stars,
									IF(h.hotel_image_thumb IS NOT NULL, CONCAT('images/hotels/', h.hotel_image_thumb), CONCAT('images/rooms/', r.room_icon_thumb)) as item_image
								FROM ".$this->tableName." w
									LEFT OUTER JOIN ".TABLE_HOTELS." h ON w.item_id = h.id AND w.item_type = 1 
									LEFT OUTER JOIN ".TABLE_HOTELS_DESCRIPTION." hd ON w.item_id = hd.hotel_id AND w.item_type = 1 AND hd.language_id = '".Application::Get('lang')."'
									LEFT OUTER JOIN ".TABLE_ROOMS." r ON w.item_id = r.id AND w.item_type = 2
									LEFT OUTER JOIN ".TABLE_ROOMS_DESCRIPTION." rd ON w.item_id = rd.room_id AND w.item_type = 2 AND rd.language_id = '".Application::Get('lang')."'
								";
		// define view mode fields
		$this->arrViewModeFields = array(
			'item_image' => array('title'=>_IMAGE, 'type'=>'image', 'align'=>'left', 'width'=>'60px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'image_width'=>'50px', 'image_height'=>'30px', 'target'=>'', 'no_image'=>'no_image.png'),
			'item_name'  => array('title'=>_TITLE, 'type'=>'link',  'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>'', 'href'=>'{item_link}', 'target'=>'_new'),
			'item_stars' => array('title'=>'', 'type'=>'enum',  'align'=>'center', 'width'=>'110px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>self::$arr_stars_vm),
			'item_type'  => array('title'=>_TYPE, 'type'=>'enum',  'align'=>'center', 'width'=>'110px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>true, 'source'=>$arr_item_types),
			'date_added' => array('title'=>_DATE_ADDED, 'type'=>'label', 'align'=>'center', 'width'=>'120px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'maxlength'=>'')

		);
		
		//---------------------------------------------------------------------- 
		// ADD MODE
		// - Validation Type: alpha|numeric|float|alpha_numeric|text|email|ip_address|password|date
		// 	 Validation Sub-Type: positive (for numeric and float)
		//   Ex.: 'validation_type'=>'numeric', 'validation_type'=>'numeric|positive'
		// - Validation Max Length: 12, 255... Ex.: 'validation_maxlength'=>'255'
		// - Validation Min Length: 4, 6... Ex.: 'validation_minlength'=>'4'
		// - Validation Min Value: 1, 10... Ex.: 'validation_minimum'=>'1'
		// - Validation Max Value: 12, 255... Ex.: 'validation_maximum'=>'99.99'
		//---------------------------------------------------------------------- 
		// define add mode fields
		$this->arrAddModeFields = array(		    

			// 'field1'  => array('title'=>'', 'type'=>'label'),
			// 'field2'  => array('title'=>'', 'type'=>'textbox',  'width'=>'210px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'', 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'visible'=>true, 'username_generator'=>false),
			// 'field3'  => array('title'=>'', 'type'=>'textarea', 'width'=>'310px', 'required'=>false, 'readonly'=>false, 'maxlength'=>'', 'default'=>'', 'height'=>'90px', 'editor_type'=>'simple|wysiwyg', 'validation_type'=>'', 'unique'=>false),
			// 'field4'  => array('title'=>'', 'type'=>'hidden',                     'required'=>true, 'readonly'=>false, 'default'=>date('Y-m-d H:i:s')),
			// 'field5'  => array('title'=>'', 'type'=>'enum',     'width'=>'',      'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$arr_languages, 'default_option'=>'', 'unique'=>false, 'javascript_event'=>'', 'view_type'=>'dropdownlist|checkboxes', 'multi_select'=>false),
			// 'field6'  => array('title'=>'', 'type'=>'checkbox', 					 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),
			// 'field7'  => array('title'=>'', 'type'=>'image',    'width'=>'210px', 'required'=>true, 'readonly'=>false, 'target'=>'uploaded/', 'no_image'=>'', 'random_name'=>true, 'image_name_pefix'=>'', 'overwrite_image'=>false, 'unique'=>false, 'thumbnail_create'=>false, 'thumbnail_field'=>'', 'thumbnail_width'=>'', 'thumbnail_height'=>'', 'file_maxsize'=>'300k', 'watermark'=>false, 'watermark_text'=>''),
			// 'field8'  => array('title'=>'', 'type'=>'file',     'width'=>'210px', 'required'=>true, 'target'=>'uploaded/', 'random_name'=>true, 'overwrite_image'=>false, 'unique'=>false, 'file_maxsize'=>'300k'),
			// 'field9'  => array('title'=>'', 'type'=>'date',     				     'required'=>true, 'readonly'=>false, 'unique'=>false, 'visible'=>true, 'default'=>'', 'validation_type'=>'date', 'format'=>'date', 'format_parameter'=>'M d, Y, g:i A', 'min_year'=>'90', 'max_year'=>'10'),
			// 'field10' => array('title'=>'', 'type'=>'datetime', 					 'required'=>true, 'readonly'=>false, 'unique'=>false, 'visible'=>true, 'default'=>'', 'validation_type'=>'', 'format'=>'date', 'format_parameter'=>'M d, Y, g:i A', 'min_year'=>'90', 'max_year'=>'10'),
			// 'field11' => array('title'=>'', 'type'=>'time', 					     'required'=>true, 'readonly'=>false, 'unique'=>false, 'visible'=>true, 'default'=>'', 'validation_type'=>'', 'format'=>'date', 'format_parameter'=>'H:i:s', 'show_seconds'=>true, 'minutes_step'=>'1'),
			// 'field12' => array('title'=>'', 'type'=>'password', 'width'=>'310px', 'required'=>true, 'validation_type'=>'password', 'cryptography'=>PASSWORDS_ENCRYPTION, 'cryptography_type'=>PASSWORDS_ENCRYPTION_TYPE, 'aes_password'=>PASSWORDS_ENCRYPT_KEY, 'password_generator'=>false),
			// 'language_id'  => array('title'=>_LANGUAGE, 'type'=>'enum', 'source'=>$arr_languages, 'required'=>true, 'unique'=>false),
			
			//  'separator_X'   =>array(
			//		'separator_info' => array('legend'=>'Legend Text', 'columns'=>'0'),
			//		'field1'  => array('title'=>'', 'type'=>'label'),
			// 		'field2'  => array('title'=>'', 'type'=>'textbox',  'width'=>'210px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'', 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'visible'=>true),
			// 		'field3'  => array('title'=>'', 'type'=>'textarea', 'width'=>'310px', 'required'=>false, 'readonly'=>false, 'maxlength'=>'', 'height'=>'90px', 'editor_type'=>'simple|wysiwyg', 'default'=>'', 'validation_type'=>'', 'unique'=>false),
			//  )
		);

		//---------------------------------------------------------------------- 
		// EDIT MODE
		// - Validation Type: alpha|numeric|float|alpha_numeric|text|email|ip_address|password|date
		//   Validation Sub-Type: positive (for numeric and float)
		//   Ex.: 'validation_type'=>'numeric', 'validation_type'=>'numeric|positive'
		// - Validation Max Length: 12, 255... Ex.: 'validation_maxlength'=>'255'
		// - Validation Min Length: 4, 6... Ex.: 'validation_minlength'=>'4'
		// - Validation Max Value: 12, 255... Ex.: 'validation_maximum'=>'99.99'
		// - for editable passwords they must be defined directly in SQL : '.$this->tableName.'.user_password,
		//---------------------------------------------------------------------- 
		$this->EDIT_MODE_SQL = 'SELECT
								'.$this->tableName.'.'.$this->primaryKey.',
								'.$this->tableName.'.field1,
								'.$this->tableName.'.field2,
								'./* #003 $sql_translation_description */'
								'.$this->tableName.'.field3
							FROM '.$this->tableName.'
							WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';		
		// define edit mode fields
		$this->arrEditModeFields = array(
		
			// 'field1'  => array('title'=>'', 'type'=>'label',    'format'=>'', 'format_parameter'=>'', 'visible'=>true),
			// 'field2'  => array('title'=>'', 'type'=>'textbox',  'width'=>'210px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'', 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'visible'=>true),
			// 'field3'  => array('title'=>'', 'type'=>'textarea', 'width'=>'310px', 'required'=>false, 'readonly'=>false, 'maxlength'=>'', 'default'=>'', 'height'=>'90px', 'editor_type'=>'simple|wysiwyg', 'validation_type'=>'', 'unique'=>false, 'visible'=>true),
			// 'field4'  => array('title'=>'', 'type'=>'hidden',   					 'required'=>true, 'default'=>date('Y-m-d H:i:s'), 'visible'=>true),
			// 'field5'  => array('title'=>'', 'type'=>'enum',     'width'=>'',      'required'=>true, 'readonly'=>false, 'default'=>'', 'source'=>$arr_languages, 'default_option'=>'', 'unique'=>false, 'visible'=>true, 'javascript_event'=>'', 'view_type'=>'dropdownlist|checkboxes|label', 'multi_select'=>false),
			// 'field6'  => array('title'=>'', 'type'=>'checkbox', 					 'readonly'=>false, 'default'=>'1', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false, 'visible'=>true),
			// 'field7'  => array('title'=>'', 'type'=>'image',    'width'=>'210px', 'required'=>true, 'readonly'=>false, 'target'=>'uploaded/', 'no_image'=>'', 'random_name'=>true, 'image_name_pefix'=>'', 'overwrite_image'=>false, 'unique'=>false, 'visible'=>true, 'image_width'=>'120px', 'image_height'=>'90px', 'thumbnail_create'=>false, 'thumbnail_field'=>'', 'thumbnail_width'=>'', 'thumbnail_height'=>'', 'file_maxsize'=>'300k', 'watermark'=>false, 'watermark_text'=>''),
			// 'field8'  => array('title'=>'', 'type'=>'file',     'width'=>'210px', 'required'=>true, 'readonly'=>false, 'target'=>'uploaded/', 'random_name'=>true, 'overwrite_image'=>false, 'unique'=>false, 'visible'=>true, 'file_maxsize'=>'300k'),
			// 'field9'  => array('title'=>'', 'type'=>'date',     					 'required'=>true, 'readonly'=>false, 'unique'=>false, 'visible'=>true, 'default'=>'', 'validation_type'=>'date', 'format'=>'date', 'format_parameter'=>'M d, Y, g:i A', 'min_year'=>'90', 'max_year'=>'10'),
			// 'field10' => array('title'=>'', 'type'=>'datetime',                   'required'=>true, 'readonly'=>false, 'unique'=>false, 'visible'=>true, 'default'=>'', 'validation_type'=>'', 'format'=>'date', 'format_parameter'=>'M d, Y, g:i A', 'min_year'=>'90', 'max_year'=>'10'),
			// 'field11' => array('title'=>'', 'type'=>'time', 					     'required'=>true, 'readonly'=>false, 'unique'=>false, 'visible'=>true, 'default'=>'', 'validation_type'=>'', 'format'=>'date', 'format_parameter'=>'H:i:s', 'show_seconds'=>true, 'minutes_step'=>'1'),
			// 'field12' => array('title'=>'', 'type'=>'password', 'width'=>'310px', 'required'=>true, 'validation_type'=>'password', 'cryptography'=>PASSWORDS_ENCRYPTION, 'cryptography_type'=>PASSWORDS_ENCRYPTION_TYPE, 'aes_password'=>PASSWORDS_ENCRYPT_KEY, 'visible'=>true),
		
			//  'separator_X'   =>array(
			//		'separator_info' => array('legend'=>'Legend Text', 'columns'=>'0'),
			//		'field1'  => array('title'=>'', 'type'=>'label'),
			// 		'field2'  => array('title'=>'', 'type'=>'textbox',  'width'=>'210px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'', 'default'=>'', 'validation_type'=>'', 'unique'=>false, 'visible'=>true),
			// 		'field3'  => array('title'=>'', 'type'=>'textarea', 'width'=>'310px', 'required'=>false, 'readonly'=>false, 'maxlength'=>'', 'default'=>'', 'height'=>'90px', 'editor_type'=>'simple|wysiwyg', 'validation_type'=>'', 'unique'=>false, 'visible'=>true),
			//  )
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		// format: strip_tags, nl2br, readonly_text
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(

			// 'field1'  => array('title'=>'', 'type'=>'label', 'format'=>'', 'format_parameter'=>'', 'visible'=>true),
			// 'field2'  => array('title'=>'', 'type'=>'image', 'target'=>'uploaded/', 'no_image'=>'', 'image_width'=>'120px', 'image_height'=>'90px', 'visible'=>true),
			// 'field3'  => array('title'=>'', 'type'=>'date', 'format'=>'date', 'format_parameter'=>'M d, Y', 'visible'=>true),
			// 'field3'  => array('title'=>'', 'type'=>'datetime', 'format'=>'date', 'format_parameter'=>'M d, Y, g:i A', 'visible'=>true),
			// 'field4'  => array('title'=>'', 'type'=>'time', 'format'=>'date', 'format_parameter'=>'g:i A', 'visible'=>true),
			// 'field5'  => array('title'=>'', 'type'=>'enum', 'source'=>array(), 'visible'=>true, 'view_type'=>'label|checkboxes', 'multi_select'=>false),
			// 'field6'  => array('title'=>'', 'type'=>'html', 'visible'=>true),
			// 'field7'  => array('title'=>'', 'type'=>'object', 'width'=>'240px', 'height'=>'200px', 'visible'=>true),

			//  'separator_X'   =>array(
			//		'separator_info' => array('legend'=>'Legend Text', 'columns'=>'0', 'visible'=>true),
            //  )
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
    // MicroGrid Methods
	//==========================================================================	

	/**
	 * Retrieves wishlist for specified hotel
	 * @param int $item_id
	 * @param string $item_type
	 * @param int $customer_id
	 * @return array|bool
	 */
	public static function GetHotelInfo($item_id = 0, $item_type = '', $customer_id = 0, $return_type = 'bool')
	{
		$output = array();
		
		if($item_type == 'car'){
			$item_type = 3;
		}elseif($item_type == 'room'){
			$item_type = 2;
		}else{
			$item_type = 1;
		}
		
		$sql = "SELECT *
			FROM ".TABLE_WISHLIST."
			WHERE item_id = '".(int)$item_id."' AND item_type = '".$item_type."' AND customer_id = ".(int)$customer_id;
				
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			$output['id'] = $result[0]['id']; 
			$output['customer_id'] = $result[0]['customer_id'];
			$output['item_id'] = $result[0]['item_id'];
			$output['item_type'] = $result[0]['item_type'];
			$output['date_added'] = $result[0]['date_added'];
		}

		if($return_type == 'bool'){
			return !empty($output) ? true : false;
		}
		
		return $output;
	}

	/**
	 * Returns favorite button
	 * @param string $item_type
	 * @param int $item_id
	 * @param int $customer_id
	 * @param string $token
	 * @param string $action
	 * @return string
	 */
	public static function GetFavoriteButton($item_type = '', $item_id = 0, $customer_id = 0, $token = '', $action = 'add', $link_class = '', $link_text = '')
	{
		$output = '';
		
		$item_type = in_array($item_type, array('hotel', 'room', 'car')) ? $item_type : 'hotel';
		$action = in_array($action, array('add', 'remove')) ? $action : 'add';
		$link_text = !empty($link_text) ? $link_text : '<span class="fav-icon-'.$action.'"></span>';
		
		if($action == 'remove'){			
			$output = "<a class=\"".$link_class."\" href=\"javascript:void(0)\" onclick=\"appFavoriteItem(this,'".$item_type."','".(int)$item_id."','".(int)$customer_id."','".$token."');\" data-action=\"".$action."\" title=\"".htmlentities(_REMOVE_FROM_WISHLIST)."\">".$link_text."</a>";
		}else{
			$output = "<a class=\"".$link_class."\" href=\"javascript:void(0)\" onclick=\"appFavoriteItem(this,'".$item_type."','".(int)$item_id."','".(int)$customer_id."','".$token."');\" data-action=\"".$action."\" title=\"".htmlentities(_ADD_TO_WISHLIST)."\">".$link_text."</a>";
		}
		
		return $output;
	}

	/**
	 * Adds item to wishlist
	 * @param string $item_type
	 * @param int $item_id
	 * @param int $customer_id
	 * @param string $action
	 * @return bool
	 */
	public static function AddToList($item_type = '', $item_id = 0, $customer_id = 0)
	{
		$sql = "SELECT *
			FROM ".TABLE_WISHLIST."
			WHERE item_id = '".(int)$item_id."' AND item_type = '".$item_type."' AND customer_id = ".(int)$customer_id;
				
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			return false;	
		}else{
			$sql = "INSERT
				INTO ".TABLE_WISHLIST." (id, customer_id, item_id, item_type, date_added)
				VALUES (NULL, ".(int)$customer_id.", ".(int)$item_id.", '".encode_text($item_type)."', '".date('Y-m-d H:i:s')."')";
			
			return database_void_query($sql);			
		}
	}
	
	/**
	 * Removes item from wishlist
	 * @param string $item_type
	 * @param int $item_id
	 * @param int $customer_id
	 * @param string $action
	 * @return bool
	 */
	public static function RemoveFromList($item_type = '', $item_id = 0, $customer_id = 0)
	{
		$sql = "DELETE
				FROM ".TABLE_WISHLIST."
				WHERE customer_id = ".(int)$customer_id." AND item_id = ".(int)$item_id." AND item_type = '".encode_text($item_type)."'";
		
		return database_void_query($sql);
	}

}
