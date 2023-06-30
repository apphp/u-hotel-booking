<?php

/**
 *	Class ModulesSettings
 *  -------------- 
 *  Description : encapsulates Modules Settings operations & properties
 *	Written by  : ApPHP
 *  Version		: 1.1.1
 *  Updated	    : 30.03.2018
 *	Usage       : Core Class (ALL)
 *	Differences : no
 *
 *	PUBLIC:				  		STATIC:				 	PRIVATE:
 * 	------------------	  		---------------     	---------------
 *	__construct                 Init
 *	__construct_public          Get
 *	__construct_admin
 *	__destruct
 *	DrawEditMode
 *	UpdateRecord	
 *	InactiveDependentModules
 *	CanActivateModule
 *
 *	DONE:
 *	1.1.1
 *		- added positive float validation
 *		-
 *		-
 *		-
 *		-
 *	1.1.0
 *	    - added changed to RTL languages
 *	    - removed 'settings_name' field
 *	    - added check for non-empty for optional emails
 *	    - added mgrid_button_cancel
 *	    - added alpha/betic type
 *	1.0.9
 *	    - change page size to 50
 *	    - removed 2 params $operation & $operation_field from DrawEditMode()
 *	    - changes mysq l_error() with database_error()
 *	    - mysq l_real_escape_string() replaced with encode_text()
 *	    - added translation for settings name
 *	1.0.8
 *	    - ucfirst() replaced with ucwords()
 *	    - added mysq l_real_escape_string() for UPDATE statement
 *	    - added check fro left/right side placements according to template allowed values
 *	    - <font> replaced with <span>
 *	    - fixed error in validation email fields
 *	1.0.7
 *		- added new type 'html size' - like 120px, pt etc.
 *		- updated Init()
 *		- removed GetSettings()
 *		- added $this->isFilteringAllowed = false;
 *		- added $val['maxlength'] = '15'; for unsigned float
 *	1.0.6
 *	    - _FIELD_MUST_BE_INT + check for integer
 *	    - added Init() for module settings
 *	    - added 'positive integer' type
 *	    - fixed problems with maxlength for view/edit modes
 *	    - fixed problem with inserting signed integers
 *	1.0.5
 *	    - bug fixed - double form submit with subUpdateRecord button
 *	    - added __construct_public and __construct_admin
 *	    - added using of translations for settings description constants (required field to modules_settings table)
 *	    - added prepare_input for 'enum' type
 *	    - removed settings_description
 *	
 **/

class ModulesSettings extends MicroGrid {
	
	protected $debug = false;

    //-----------------	
	private $moduleName;
	private $arrSettings;
	private static $arrModuleSettings = array();

	//==========================================================================
    // Class Constructor
	//		@param $module_name
	//==========================================================================
	function __construct($module_name = '')
	{
		parent::__construct();
		
		global $objLogin;
		
		$this->moduleName = $module_name;
		$this->primaryKey = 'id';
		$this->tableName  = TABLE_MODULES_SETTINGS;
		$this->isFilteringAllowed = false;

        if($objLogin->IsLoggedInAsAdmin()){	
			$this->__construct_admin();
		}	
		$this->__construct_public();		
	}

	//==========================================================================
    // Constructor public
	//==========================================================================
	function __construct_public()
	{		
		// save module settings
		$this->arrSettings = array();
		$sql = 'SELECT settings_key, settings_value, key_is_required
		        FROM '.$this->tableName.'
				WHERE module_name = \''.$this->moduleName.'\'';
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		for($i=0; $i < $result[1]; $i++){
			$this->arrSettings[$result[0][$i]['settings_key']] = array('value'=>$result[0][$i]['settings_value'], 'required'=>$result[0][$i]['key_is_required']);	
		}
	}
		
	//==========================================================================
    // Constructor admin
	//==========================================================================
	function __construct_admin()
	{
		$this->params = array();		
		if(isset($_POST['key_display_type'])) $this->params['key_display_type'] = prepare_input($_POST['key_display_type']);
		if(isset($_POST['settings_key'])) $this->params['settings_key'] = prepare_input($_POST['settings_key']);
		if(isset($_POST['module_name'])) $this->params['module_name'] = prepare_input($_POST['module_name']);
		if(isset($_POST['settings_value'])){
			if($this->params['key_display_type'] == 'text'){
			    $this->params['settings_value'] = prepare_input($_POST['settings_value'], false, 'low');
			}elseif($this->params['key_display_type'] == 'enum'){
				$this->params['settings_value'] = prepare_input($_POST['settings_value'], false, 'medium');
			}else{
				$this->params['settings_value'] = prepare_input($_POST['settings_value']);
			}
	    }

		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=mod_'.$this->moduleName.'_settings';
		$this->actions      = array('add'=>false, 'edit'=>true, 'details'=>true, 'delete'=>false);

		$this->isAlterColorsAllowed = true;
		$this->isPagingAllowed = true;
		$this->pageSize = 50;
		$this->isSortingAllowed = true;
		
		$this->ORDER_CLAUSE = 'ORDER BY '.$this->tableName.'.id ASC'; 

		//---------------------------------------------------------------------- 
		// VIEW MODE
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT '.$this->tableName.'.'.$this->primaryKey.',
									settings_key,
									CASE
										WHEN key_display_type = "enum" THEN CONCAT(UCASE(MID(settings_value,1,1)),MID(settings_value,2))
										WHEN key_display_type = "yes/no" AND settings_value = "yes" THEN CONCAT("<span class=green>", UCASE(MID(settings_value,1,1)), MID(settings_value,2), "</span>")
										WHEN key_display_type = "yes/no" AND settings_value = "no" THEN CONCAT("<span class=darkred>", UCASE(MID(settings_value,1,1)), MID(settings_value,2), "</span>")
										WHEN key_display_type = "text" THEN ""
										ELSE settings_value
									END as settings_value,
                                    IF(voc1.key_text IS NOT NULL, voc1.key_text, settings_name_const) as settings_name,
									IF(voc2.key_text IS NOT NULL, voc2.key_text, settings_description_const) as settings_description,
									key_display_type
								FROM '.$this->tableName.'
									LEFT OUTER JOIN '.TABLE_VOCABULARY.' voc1 ON ('.$this->tableName.'.settings_name_const = voc1.key_value AND voc1.language_id = \''.Application::Get('lang').'\')
									LEFT OUTER JOIN '.TABLE_VOCABULARY.' voc2 ON ('.$this->tableName.'.settings_description_const = voc2.key_value AND voc2.language_id = \''.Application::Get('lang').'\')
								WHERE									
									module_name = \''.$this->moduleName.'\'';		
		$this->arrViewModeFields = array(
			'settings_name'        => array('title'=>_PARAMETER,  'type'=>'label', 'width'=>'20%', 'align'=>'left'),
			'settings_description' => array('title'=>_DESCRIPTION,  'type'=>'label', 'width'=>'55%', 'align'=>'left'),
			'settings_value'       => array('title'=>_VALUE, 'type'=>'label', 'width'=>'15%', 'align'=>'center', 'maxlength'=>'30')
		);
		
		//---------------------------------------------------------------------- 
		// ADD MODE
		//---------------------------------------------------------------------- 
		// define add mode fields
		$arr_key_display_types = array('string'=>'string', 'numeric'=>'numeric', 'enum'=>'enum');
		$this->arrAddModeFields = array(
			'settings_key'   		=> array('title'=>_KEY,   'type'=>'textbox', 'required'=>true),
			'settings_value' 		=> array('title'=>_VALUE, 'type'=>'textbox', 'required'=>true),			
			'key_display_type'      => array('title'=>_KEY_DISPLAY_TYPE, 'type'=>'enum', 'source'=>$arr_key_display_types, 'required'=>true),
			'module_name'    		=> array('title'=>'',    'type'=>'hidden',  'required'=>true, 'default'=>$this->moduleName),
		);

		//---------------------------------------------------------------------- 
		// EDIT MODE
		//---------------------------------------------------------------------- 
		$this->EDIT_MODE_SQL = 'SELECT '.$this->tableName.'.'.$this->primaryKey.',
								settings_key,
								settings_value,
                                IF(voc1.key_text IS NOT NULL, voc1.key_text, settings_name_const) as settings_name,
                                IF(voc2.key_text IS NOT NULL, voc2.key_text, settings_description_const) as settings_description,
								key_display_type,
								CASE
									WHEN key_display_type = "enum" THEN CONCAT(UCASE(MID(settings_value,1,1)),MID(settings_value,2))
									WHEN key_display_type = "yes/no" AND settings_value = "yes" THEN CONCAT("<span class=green>", UCASE(MID(settings_value,1,1)), MID(settings_value,2), "</span>")
									WHEN key_display_type = "yes/no" AND settings_value = "no" THEN CONCAT("<span class=darkred>", UCASE(MID(settings_value,1,1)), MID(settings_value,2), "</span>")
									WHEN key_display_type = "text" THEN ""
									ELSE settings_value
								END as m_settings_value								
								FROM '.$this->tableName.'
									LEFT OUTER JOIN '.TABLE_VOCABULARY.' voc1 ON ('.$this->tableName.'.settings_name_const = voc1.key_value AND voc1.language_id = \''.Application::Get('lang').'\')
									LEFT OUTER JOIN '.TABLE_VOCABULARY.' voc2 ON ('.$this->tableName.'.settings_description_const = voc2.key_value AND voc2.language_id = \''.Application::Get('lang').'\')
							WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';		
		// define edit mode fields
		$this->arrEditModeFields = array(
			'settings_name' 		=> array('title'=>_PARAMETER,  'type'=>'label'),		
			'settings_description' 	=> array('title'=>_DESCRIPTION,  'type'=>'label'),		
			'settings_value' 		=> array('title'=>_VALUE, 'type'=>'textbox'),
			'settings_key'   		=> array('title'=>'',   'type'=>'hidden'),
			'key_display_type'   	=> array('title'=>'',   'type'=>'hidden'),			
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			'settings_name' 		=> array('title'=>_PARAMETER,  'type'=>'label'),		
			'settings_description' 	=> array('title'=>_DESCRIPTION,  'type'=>'label'),
			'm_settings_value' 		=> array('title'=>_VALUE, 'type'=>'label'),
			'settings_key'   		=> array('title'=>_KEY,   'type'=>'hidden'),			
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
	 *	Initialize class
	 */
	public static function Init()
	{
		$sql = 'SELECT
					ms.id,
					ms.module_name,
					ms.settings_key,
					ms.settings_value
				FROM '.TABLE_MODULES.' m
					INNER JOIN '.TABLE_MODULES_SETTINGS.' ms ON m.name = ms.module_name
				WHERE m.is_installed = 1';
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		for($i=0; $i < $result[1]; $i++){			
			self::$arrModuleSettings[$result[0][$i]['module_name']][$result[0][$i]['settings_key']] = $result[0][$i]['settings_value'];
		}
	}	

	/**
	 * Get module settings parameter
	 * @param $module_name
	 * @param $param_name
	 */
	public static function Get($module_name = '', $param_name = '')
	{
		return isset(self::$arrModuleSettings[$module_name][$param_name]) ? self::$arrModuleSettings[$module_name][$param_name] : '';
	}	

	/***********************************************************************
	 *
	 *	Draw Edit Mode
	 *	
	 ***********************************************************************/
	public function DrawEditMode($rid = '0', $buttons = array('reset'=>false, 'cancel'=>true), $swipe = false)
	{		
		$this->IncludeJSFunctions();

		// load XML file
		global $objSettings;
		$allowed_placement = array();
		if(@file_exists('templates/'.$objSettings->GetTemplate().'/info.xml')){
			$xml = simplexml_load_file('templates/'.$objSettings->GetTemplate().'/info.xml');
			if(isset($xml->menus->menu)){
				foreach($xml->menus->menu as $menu){
					$allowed_placement[] = strtolower($menu).' side';
				}							
			}
		}		
		
		$sorting_fields  = self::GetParameter('sorting_fields');
		$sorting_types   = self::GetParameter('sorting_types');
		$page 			 = self::GetParameter('page');
		$operation 		 = self::GetParameter('operation');
		$operation_type  = self::GetParameter('operation_type');
		$operation_field = self::GetParameter('operation_field');
		$search_status   = self::GetParameter('search_status');
		$is_required 	 = false;
		$nl              = "\n";
		
		echo $nl.'<form name="frmMicroGrid_'.$this->tableName.'" id="frmMicroGrid_'.$this->tableName.'" action="'.$this->formActionURL.'" method="post" enctype="multipart/form-data">'.$nl;
		draw_hidden_field('mg_action', 'update'); echo $nl;
		draw_hidden_field('mg_rid', $rid); echo $nl;
		draw_hidden_field('mg_sorting_fields', $sorting_fields); echo $nl;
		draw_hidden_field('mg_sorting_types', $sorting_types); echo $nl;
		draw_hidden_field('mg_page', $page); echo $nl;
		draw_hidden_field('mg_operation', ''); echo $nl;
		draw_hidden_field('mg_operation_type', ''); echo $nl;
		draw_hidden_field('mg_operation_field', ''); echo $nl;
		draw_hidden_field('mg_search_status', $search_status); echo $nl;
		draw_hidden_field('mg_language_id', $this->languageId); echo $nl;
		draw_token_field(); echo $nl;
		
		// save filter (search) data for view mode
		if($this->isFilteringAllowed){
			foreach($this->arrFilteringFields as $key => $val){
				if($val['type'] == 'text'){
					$filter_field_value = ($search_status == 'active') ? $filter_field_value = self::GetParameter('filter_by_'.$key, false) : '';
					draw_hidden_field('filter_by_'.$key, $filter_field_value); echo $nl;
				}
			}
		}

		// prepare password fields
		foreach($this->arrEditModeFields as $key => $val){
			if(preg_match('/separator/i', $key) && is_array($val)){
				foreach($val as $v_key => $v_val){
					if($v_key != 'separator_info'){						
						if($v_val['type'] == 'password'){
							$password_field = $this->UncryptPasswordValue($v_key, $v_val);
							str_replace($v_key, $password_field, $this->arrEditModeFields);
						}											
					}					
				}				
			}else{
				if($val['type'] == 'password'){
					$password_field = $this->UncryptPasswordValue($key, $val);
					$this->EDIT_MODE_SQL = str_replace($this->tableName.'.'.$key, $password_field, $this->EDIT_MODE_SQL);
				}									
			}
		}				

		$this->EDIT_MODE_SQL = str_replace('_RID_', $rid, $this->EDIT_MODE_SQL);
		$result = database_query($this->EDIT_MODE_SQL, DATA_AND_ROWS);		
		if($this->debug) $this->arrSQLs['select_edit_mode'] = $this->EDIT_MODE_SQL;					

		// draw hidden fields
		foreach($this->arrEditModeFields as $key => $val){
			if(preg_match('/separator/i', $key) && is_array($val)){
				foreach($val as $v_key => $v_val){
					if($v_key != 'separator_info'){						
						// delete file/image
						if($operation == 'remove' && $operation_field != '' && ($v_key == $operation_field)){
							$this->RemoveFileImage($rid, $operation_field, $v_val['target'], $result[0][0][$v_key]);
							$result[0][0][$v_key] = '';
						}
						if($v_val['type'] == 'hidden'){
							draw_hidden_field($v_key, $result[0][0][$v_key]);
							echo $nl;
						}
					}					
				}				
			}else{
				// delete file/image
				if($operation == 'remove' && $operation_field != '' && ($key == $operation_field)){
					$this->RemoveFileImage($rid, $operation_field, $val['target'], $result[0][0][$key]);
					$result[0][0][$key] = '';
				}
				if($val['type'] == 'hidden'){
					if(isset($this->arrSettings[$result[0][0][$key]]['required']) && $this->arrSettings[$result[0][0][$key]]['required'] == '1') $is_required = true;
					draw_hidden_field($key, $result[0][0][$key]);
					echo $nl;
				}
			}
		}			
		
		// draw Edit Form
		echo '<table width="100%" class="mgrid_table">'.$nl;
		echo '<tr><td colspan="2" height="5px" nowrap="nowrap"></td></tr>';
		foreach($this->arrEditModeFields as $key => $val){
			if(preg_match('/separator/i', $key) && is_array($val)){
				echo '</table><br />'.$nl;
				echo '<fieldset style="padding:5px;">'.$nl;
				echo '<legend>'.$val['separator_info']['legend'].'</legend>'.$nl;
				echo '<table width="100%" class="mgrid_table">'.$nl;		
				foreach($val as $v_key => $v_val){
					if($v_key != 'separator_info'){						
						echo '<tr>'.$nl;
						echo '<td width="27%">'.$v_val['title'].(($is_required) ? ' <span class="required">*</span>' : '').':</td>'.$nl;
						echo '<td style="text-align:'.Application::Get('defined_left').';padding-left:6px;">';
                        if(isset($this->params[$v_key]) && $this->params[$v_key] !== '' && ($v_val['type'] != 'checkbox')){
							echo $this->DrawFieldByType('edit', $v_key, $v_val, $this->params, false);
						}else{
							echo $this->DrawFieldByType('edit', $v_key, $v_val ,$result[0][0], false);
						}
                        echo '</td>'.$nl;
						echo '</tr>'.$nl;
					}
				}
				echo '</table>'.$nl;
				echo '</fieldset>'.$nl;				
				echo '<table width="100%" class="mgrid_table">'.$nl;		
			}else{
				if($val['type'] != 'hidden'){
					echo '<tr>'.$nl;
					echo '<td align="'.Application::Get('defined_left').'" width="20%">'.$val['title'].(($is_required && $key == 'settings_value') ? ' <span class="required">*</span>' : '').':</td>'.$nl;
                    
					// prepare some settings depended on field type
					if($key == 'settings_value'){
						$sql = 'SELECT key_display_type, key_display_source FROM '.$this->tableName.' WHERE '.$this->primaryKey.'='.$rid;
						if($row = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY)){								
							if($row['key_display_type'] == 'enum'){
								$key_display_source = explode(',', $row['key_display_source']);
								$enum_source = array();
								foreach($key_display_source as $kds_key){
									if(count($allowed_placement) > 0 && in_array($kds_key, array('left side', 'right side'))){
										if(in_array($kds_key, $allowed_placement)) $enum_source[$kds_key] = ucwords($kds_key);	
									}else{
										$enum_source[$kds_key] = ucwords($kds_key);	
									}
								}
								$val['type'] = 'enum';
								$val['source'] = $enum_source;
							}elseif($row['key_display_type'] == 'yes/no'){
								$enum_source = array('no'=>_NO, 'yes'=>_YES);
								$val['type'] = 'enum';
								$val['source'] = $enum_source;									
							}elseif($row['key_display_type'] == 'numeric'){
								$val['width'] = '50px';
							}elseif($row['key_display_type'] == 'text'){
								$val['type'] = 'textarea';
								$val['width'] = '530px';
								$val['height'] = '170px';
							}elseif($row['key_display_type'] == 'integer' || $row['key_display_type'] == 'positive integer' || $row['key_display_type'] == 'unsigned integer'){
								$val['width'] = '50px';
								$val['maxlength'] = '5';
							}elseif($row['key_display_type'] == 'positive float' || $row['key_display_type'] == 'unsigned float'){
								$val['width'] = '90px';
								$val['maxlength'] = '15'; 
							}elseif($row['key_display_type'] == 'html size'){
								$val['width'] = '100px';
								$val['maxlength'] = '8';
							}elseif($row['key_display_type'] == 'alpha' || $row['key_display_type'] == 'alphabetic'){
								$val['width'] = '210px';
								$val['maxlength'] = '100';
							}else{								
								$val['width'] = '270px';
								$val['maxlength'] = '255';
							}
						}							
					}
                    echo '<td align="'.Application::Get('defined_left').'">';
					if(isset($this->params[$key]) && $this->params[$key] !== '' && ($val['type'] != 'checkbox')){
						echo $this->DrawFieldByType('edit', $key, $val, $this->params, false);
					}else{
						echo $this->DrawFieldByType('edit', $key, $val, $result[0][0], false);
					}
                    echo '</td>'.$nl;
					echo '</tr>'.$nl;				
				}
			}
		}
		echo '<tr><td colspan="2" height="5px" nowrap="nowrap"></td></tr>';
		echo '<tr>
				<td align="'.Application::Get('defined_left').'" colspan="2">
					<input class="mgrid_button" type="button" name="subUpdateRecord" value="'._BUTTON_UPDATE.'" onclick="javascript:__mgDoPostBack(\''.$this->tableName.'\', \'update\');">
					<input class="mgrid_button mgrid_button_cancel" type="button" name="btnCancel" value="'._BUTTON_CANCEL.'" onclick="javascript:__mgDoPostBack(\''.$this->tableName.'\', \'view\');">
				</td>
			  <tr>'.$nl;		
		echo '</table><br />'.$nl;
		echo '</form>'.$nl;

		if($this->errorField != '') echo '<script type="text/javascript">__mgSetFocus(\''.$this->errorField.'\');</script>';	   
		
		$this->DrawRunningTime();
		$this->DrawErrors();
		$this->DrawWarnings();
		$this->DrawSQLs();	
		$this->DrawPostInfo();	
	}

	/***************************************************************************
	 *
	 *	UPDATE RECORD
	 *	
	 **************************************************************************/
	public function UpdateRecord($rid = '0')
	{
		//----------------------------------------------------------------------
		// check if all required fields not empty
		if($rid == '0' || $rid == '' || !is_numeric($rid)){
			$this->error = _WRONG_PARAMETER_PASSED;
			return false;
		}
		
		foreach($this->arrEditModeFields as $key => $val){
			
			if($key == 'settings_key'){
				$this->errorField = 'settings_value';
				$sql = 'SELECT
                            ms.key_display_type,
                            ms.key_is_required,
                            IF(voc.key_text IS NOT NULL, voc.key_text, settings_name_const) as settings_name,
                            ms.settings_value 
						FROM '.$this->tableName.' ms
                            LEFT OUTER JOIN '.TABLE_VOCABULARY.' voc ON (ms.settings_name_const = voc.key_value AND voc.language_id = \''.Application::Get('lang').'\')
					    WHERE ms.settings_key = \''.$this->params[$key].'\' AND ms.module_name = \''.$this->moduleName.'\'';					
				if($row = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY)){
					if(($row['key_is_required'] == '1') && ($this->params['settings_value'] == '')){
						$this->error = str_replace('_FIELD_', '<b>'.$row['settings_name'].'</b>', _FIELD_CANNOT_BE_EMPTY);						
						return false;					
					}
					
					if($row['key_display_type'] == 'email' && $this->params['settings_value'] !== '' && !check_email_address($this->params['settings_value'])){
						$this->error = str_replace('_FIELD_', '<b>'.$row['settings_name'].'</b>', _FIELD_MUST_BE_EMAIL);						
						return false;						
					}elseif($row['key_display_type'] == 'numeric' && !is_numeric($this->params['settings_value'])){
						$this->error = str_replace('_FIELD_', '<b>'.$row['settings_name'].'</b>', _FIELD_MUST_BE_NUMERIC);
						return false;
					}elseif($row['key_display_type'] == 'integer' && (!$this->IsInteger($this->params['settings_value']) || ($this->params['settings_value'] < 0))){
						$this->error = str_replace('_FIELD_', '<b>'.$row['settings_name'].'</b>', _FIELD_MUST_BE_INT);
						return false;						
					}elseif($row['key_display_type'] == 'positive integer'){
						if(!$this->IsInteger($this->params['settings_value']) || ($this->params['settings_value'] <= 0)){
							$this->error = str_replace('_FIELD_', '<b>'.$row['settings_name'].'</b>', _FIELD_MUST_BE_POSITIVE_INT);
							return false;
						}else{
							$this->params['settings_value'] = (int)$this->params['settings_value'];
						}
					}elseif($row['key_display_type'] == 'unsigned integer'){
						if(!$this->IsInteger($this->params['settings_value'], true) || ($this->params['settings_value'] < 0)){
							$this->error = str_replace('_FIELD_', '<b>'.$row['settings_name'].'</b>', _FIELD_MUST_BE_UNSIGNED_INT);
							return false;
						}else{
							$this->params['settings_value'] = (int)$this->params['settings_value'];
						}
					}elseif($row['key_display_type'] == 'positive float'){
						if(!$this->IsFloat($this->params['settings_value']) || ($this->params['settings_value'] <= 0)){
							$this->error = str_replace('_FIELD_', '<b>'.$row['settings_name'].'</b>', _FIELD_MUST_BE_FLOAT_POSITIVE);
							return false;
						}
					}elseif($row['key_display_type'] == 'unsigned float' && (!$this->IsFloat($this->params['settings_value'], true) || ($this->params['settings_value'] < 0))){
						$this->error = str_replace('_FIELD_', '<b>'.$row['settings_name'].'</b>', _FIELD_MUST_BE_UNSIGNED_FLOAT);
						return false;						
					}elseif($row['key_display_type'] == 'yes/no' && (strtolower($this->params['settings_value']) != 'yes' && strtolower($this->params['settings_value']) != 'no')){
						$this->error = str_replace('_FIELD_', '<b>'.$row['settings_name'].'</b>', _FIELD_MUST_BE_BOOLEAN);
						return false;						
					}elseif($row['key_display_type'] == 'html size' && !preg_match('/^[0-9]{1,4}[\.]{0,1}[0-9]{0,1}(px|em|pt|%){0,1}$/i', $this->params['settings_value'])){							
						$this->error = str_replace('_FIELD_', '<b>'.$row['settings_name'].'</b>', _FIELD_MUST_BE_SIZE_VALUE);
						return false;													
					}elseif(($row['key_display_type'] == 'alpha' || $row['key_display_type'] == 'alphabetic') && !preg_match('/^[a-zA-Z]+$/', $this->params['settings_value'])){
						$this->error = str_replace('_FIELD_', '<b>'.$row['settings_name'].'</b>', _FIELD_MUST_BE_ALPHA);
						return false;													
					}
				}
				$this->errorField = '';
			}			
		}
		 
		//----------------------------------------------------------------------
		// block if this is a demo mode
		if(strtolower(SITE_MODE) == 'demo'){
			$this->error = _OPERATION_BLOCKED;
			return false;				
		}
		
		//----------------------------------------------------------------------
		// update
		$sql = 'UPDATE '.$this->tableName.' SET ';
			$fields_count = 0;
			foreach($this->params as $key => $val){
				if(array_key_exists($key, $this->arrEditModeFields)){
					if($fields_count++ > 0) $sql .= ',';
					$sql .= $key.' = \''.encode_text($val).'\'';					
				}
			}				
		$sql .= ' WHERE '.$this->primaryKey.'='.(int)$rid;
		
		if($this->debug) $this->arrSQLs['update_sql'] = $sql;
		if(database_void_query($sql) < 0 || database_error() != ''){
			if($this->debug) $this->arrErrors['update_sql'] = database_error();						
			$this->error = _TRY_LATER;
			return false;
		}else{
			return true;
		}			
	}	

	/**
	 *	Inactives dependent modules
	 */
	public function InactiveDependentModules()
	{
		$sql = 'SELECT name, module_tables, dependent_modules FROM '.TABLE_MODULES.' WHERE name = \''.$this->moduleName.'\'';
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			$dependent_modules_arr = explode(',', $result[0]['dependent_modules']);
			foreach($dependent_modules_arr as $table){
				$sql = 'UPDATE '.TABLE_MODULES_SETTINGS.' SET settings_value = \'no\' WHERE module_name = \''.$table.'\' AND settings_key = \'is_active\'';
				database_void_query($sql);
			}
		}
	}
	
	/**
	 *	Checks if module can be activated 
	 */
	public function CanActivateModule()
	{
		$sql = 'SELECT
					'.TABLE_MODULES.'.name,
					'.TABLE_MODULES_SETTINGS.'.settings_key,
					'.TABLE_MODULES_SETTINGS.'.settings_value
				FROM '.TABLE_MODULES.'
					INNER JOIN '.TABLE_MODULES_SETTINGS.' ON '.TABLE_MODULES.'.name = '.TABLE_MODULES_SETTINGS.'.module_name
				WHERE
					'.TABLE_MODULES.'.dependent_modules LIKE \'%'.$this->moduleName.'%\'';
					
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			if($result[0]['settings_key'] == 'is_active' && $result[0]['settings_value'] == 'no'){				
				$first_module = ucwords(str_replace('_', ' ', $this->moduleName));
				$second_module = ucwords(str_replace('_', ' ', $result[0]['name']));
				$this->error = str_replace(array('_FIRST_MODULE_', '_SECOND_MODULE_'), array($first_module, $second_module), _CANNOT_ACTIVATE_DEPENDENT_MODULE);
				return false;
			}
		}
		return true;
	}	

}
