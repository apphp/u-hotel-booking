<?php

/**
 *	Class Modules
 *  -------------- 
 *  Description : encapsulates modules properties
 *	Written by  : ApPHP
 *	Version     : 1.0.8
 *  Updated	    : 19.03.2018
 *	Usage       : Core Class (ALL)
 *	Differences : no
 *	
 *	PUBLIC:				  	STATIC:				 	PRIVATE:
 * 	------------------	  	---------------     	---------------
 *	__construct				IsModuleInstalled		CleanModuleTables
 *	__destruct              Init					ReadModuleXml
 *	DrawModules										DrawModulesByType
 *	DrawModulesOnDashboard							DrawSingleModule
 *	GetAllModules									RunSqlDump
 *	InstallAdditionalModule							CopyFile
 *	UninstallAdditionalModule						DeleteFile
 *	InstallModule									CreateDir
 *	UninstallModule									DeleteDir
 *	BeforeUpdateRecord
 *	AfterUpdateRecord
 *	BeforeEditRecord
 *	
 *
 *  1.0.8
 *  	- added links to modules management pages from Modules > Management page
 *  	- added new module Conference Management
 *  	- module name placed under icon
 *  	- improved links for management and settings pages
 *  	-
 *  1.0.7
 *      - fixed error on missing table
 *      - added show_on_dashboard property
 *      - added DrawModulesOnDashboard() 
 *      - fixed issue for show_on_dashboard
 *      - added drawing of modules icon on dashboard
 *  1.0.6
 *	    - ucfirst() replaced with ucwords()
 *      - replaced SQL CASEs with 'enum' types
 *      - replaced <img> for installed/uninstalled
 *      - fixed issue with floating img
 *      - fixed bug with drawing __mgDoModeAlert()
 *  1.0.5
 *      - added Init() method to prevent unneded checks for modules
 *      - added draw_token_field()
 *      - fixed HTML error in DrawModules()
 *      - added description for each module in title attribute
 *      - fixed bug on drawinf installed/uninstalled images      
 *  1.0.4
 *  	- added drawing of modules only if module acces is allowed in DrawModules()
 *  	- added split to system and additional modules
 *  	- blocking for operations with system modules
 *  	- added check for empty dependent tables
 *  	- added alert on un-installation operation
 *  1.0.3
 *      - re-done using of 'module_tables' field
 *      - added private method CleanModuleTables() 
 *      - added recursive un-installing modules 
 *      - added check on installation of dependent module
 *      - added possibility to truncate or not related tables
 *	
 **/

class Modules extends MicroGrid {

	protected $debug = false;
	
	// ------------------
	public $modulesCount;

	protected $modules;

	private $id;
	private $is_installed = '';
	private $module_type = '';
	private $module_name = '';
	private static $arr_modules = array();

	private $additional_modules = array(
		'rest_api' 			    => 'Rest API',
		'channel_manager' 	    => 'Channel Manager',
		'conferences' 		    => 'Conferences',
		'car_rental' 		    => 'Car Rental',
        'property_management' 	=> 'Property Management',
        'tours_and_attractions'	=> 'Tours & Attractions'
    );


	//==========================================================================
    // Class Constructor
	// 		@param $id
	//==========================================================================
	function __construct($id = '')
	{
		parent::__construct();

		//////////////////////////////////////////////////////////////////
		$this->id = $id;
		if($this->id != ''){
			$sql = 'SELECT * 
					FROM '.TABLE_MODULES.'
					WHERE id = \''.(int)$this->id.'\'';
			$this->modules = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		}else{
			$this->modules = $this->GetAllModules();
		}
		$this->modulesCount = $this->modules[1];
		//////////////////////////////////////////////////////////////////

		$this->additional_modules = array(
			'rest_api' 			    => defined('_REST_API') ? _REST_API : 'Rest API',
			'channel_manager' 	    => defined('_CHANNEL_MANAGER') ? _CHANNEL_MANAGER : 'Channel Manager',
			'conferences' 		    => defined('_CONFERENCES') ? _CONFERENCES : 'Conferences',
			'car_rental' 		    => defined('_CAR_RENTAL') ? _CAR_RENTAL : 'Car_Rental',
			'property_management' 	=> defined('_PROPERTY_MANAGEMENT') ? _PROPERTY_MANAGEMENT : 'Property Management',
			'tours_and_attractions' => defined('_TOURS_AND_ATTRACTIONS') ? _TOURS_AND_ATTRACTIONS : 'Tours and Attractions',
		);

		$this->params = array();
		
		## for standard fields
		if(isset($_POST['is_installed'])) $this->params['is_installed'] = prepare_input($_POST['is_installed']);
		
		## for checkboxes 
		$this->params['show_on_dashboard'] = isset($_POST['show_on_dashboard']) ? prepare_input($_POST['show_on_dashboard']) : '0';

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
		$this->tableName 	= TABLE_MODULES;
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=modules';
		$this->actions      = array('add'=>true, 'edit'=>true, 'details'=>true, 'delete'=>true);
		$this->actionIcons  = true;
		$this->allowRefresh = true;

		$this->allowLanguages = false;
		$this->languageId  	= ''; //
		$this->WHERE_CLAUSE = ''; // WHERE .... / 'WHERE language_id = \''.$this->languageId.'\'';				
		$this->ORDER_CLAUSE = 'ORDER BY module_type DESC, priority_order DESC'; 
		
		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 20;

		$this->isSortingAllowed = true;

		$this->isFilteringAllowed = false;
		// define filtering fields
		$this->arrFilteringFields = array(
			// 'Caption_1'  => array('table'=>'', 'field'=>'', 'type'=>'text', 'sign'=>'=|like%|%like|%like%', 'width'=>'80px'),
			// 'Caption_2'  => array('table'=>'', 'field'=>'', 'type'=>'dropdownlist', 'source'=>array(), 'sign'=>'=|like%|%like|%like%', 'width'=>'130px'),
		);

		$arr_installed = array('0'=>_NO, '1'=>_YES);
		$arr_module_type = array('0'=>_SYSTEM, '1'=>_APPLICATION, '2'=>_ADDITIONAL);

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
		$this->VIEW_MODE_SQL = 'SELECT '.$this->primaryKey.',
									CONCAT(UCASE(SUBSTRING(name, 1, 1)),LCASE(SUBSTRING(name, 2))) as mod_name,
									icon_file,
									name_const,
									description_const, 
									module_tables,
									dependent_modules,
									CASE
										WHEN is_installed = 1 THEN \'success_sign.gif\'
										ELSE \'error_sign.gif\'								
									END as mod_is_installed,
									CASE
										WHEN module_type = 0 THEN \'success_sign.gif\'
										ELSE \'error_sign.gif\'								
									END as mod_module_type
								FROM '.$this->tableName;		
		// define view mode fields
		$this->arrViewModeFields = array(
			'icon_file' 		=> array('title'=>_IMAGE, 'type'=>'image', 'align'=>'left', 'width'=>'60px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'image_width'=>'42px', 'image_height'=>'42px', 'target'=>'images/modules_icons/', 'no_image'=>'no_image.png'),
			'mod_name'      	=> array('title'=>_NAME, 'type'=>'label', 'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'', 'format'=>'', 'format_parameter'=>''),
			'mod_is_installed'  => array('title'=>_STATUS, 'type'=>'image', 'align'=>'center', 'width'=>'120px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'image_width'=>'16px', 'image_height'=>'16px', 'target'=>'images/', 'no_image'=>'error_sign.gif'),
		);
		
		//---------------------------------------------------------------------- 
		// ADD MODE
		// - Validation Type: alpha|numeric|float|alpha_numeric|text|email|ip_address|password|date
		// 	 Validation Sub-Type: positive (for numeric and float)
		//   Ex.: 'validation_type'=>'numeric', 'validation_type'=>'numeric|positive'
		// - Validation Max Length: 12, 255... Ex.: 'validation_maxlength'=>'255'
		// - Validation Max Value: 12, 255... Ex.: 'validation_maximum'=>'99.99'
		//---------------------------------------------------------------------- 
		// define add mode fields
		$this->arrAddModeFields = array(		    

		);

		//---------------------------------------------------------------------- 
		// EDIT MODE
		// - Validation Type: alpha|numeric|float|alpha_numeric|text|email|ip_address|password|date
		//   Validation Sub-Type: positive (for numeric and float)
		//   Ex.: 'validation_type'=>'numeric', 'validation_type'=>'numeric|positive'
		// - Validation Max Length: 12, 255... Ex.: 'validation_maxlength'=>'255'
		// - Validation Max Value: 12, 255... Ex.: 'validation_maximum'=>'99.99'
		//---------------------------------------------------------------------- 
		$this->EDIT_MODE_SQL = 'SELECT
								'.$this->tableName.'.'.$this->primaryKey.',
								CONCAT(REPLACE(UCASE('.$this->tableName.'.name),"_","&nbsp;")) as mod_name,
								'.$this->tableName.'.name,								        
								'.$this->tableName.'.name_const,
								'.$this->tableName.'.description_const, 								
								'.$this->tableName.'.icon_file,
								'.$this->tableName.'.module_tables,
								'.$this->tableName.'.dependent_modules,
								'.$this->tableName.'.is_installed,
								'.$this->tableName.'.module_type,
								'.$this->tableName.'.show_on_dashboard,
								"0" as truncate_tables,
								IF('.TABLE_VOCABULARY.'.key_text IS NOT NULL, '.TABLE_VOCABULARY.'.key_text, "") as mod_description
							FROM '.$this->tableName.'
								LEFT OUTER JOIN '.TABLE_VOCABULARY.' ON ('.$this->tableName.'.description_const = '.TABLE_VOCABULARY.'.key_value AND '.TABLE_VOCABULARY.'.language_id = \''.Application::Get('lang').'\')
							WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';		
		// define edit mode fields
		$this->arrEditModeFields = array(		
			'mod_name'          => array('title'=>_NAME, 'type'=>'label'),
			'mod_description'   => array('title'=>_DESCRIPTION, 'type'=>'label'),
			'icon_file'         => array('title'=>_ICON_IMAGE, 'type'=>'image', 'width'=>'', 'readonly'=>true, 'required'=>false, 'target'=>'images/modules_icons/', 'no_image'=>'', 'random_name'=>true, 'overwrite_image'=>false, 'unique'=>false, 'image_width'=>'96px', 'image_height'=>'96px', 'thumbnail_create'=>false, 'thumbnail_field'=>'', 'thumbnail_width'=>'', 'thumbnail_height'=>''),
			'module_type'       => array('title'=>_MODULE_TYPE, 'type'=>'enum', 'width'=>'', 'required'=>false, 'readonly'=>true, 'source'=>$arr_module_type, 'unique'=>false),
			'show_on_dashboard' => array('title'=>_SHOW_ON_DASHBOARD, 'type'=>'checkbox', 'readonly'=>false, 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),
			'truncate_tables'   => array('title'=>_TRUNCATE_RELATED_TABLES, 'type'=>'checkbox', 'readonly'=>false, 'default'=>'0', 'true_value'=>'1', 'false_value'=>'0', 'unique'=>false),
			'is_installed'      => array('title'=>_INSTALLED, 'type'=>'enum', 'width'=>'', 'required'=>true, 'readonly'=>false, 'source'=>$arr_installed, 'unique'=>false, 'javascript_event'=>'onchange="appToggleElementReadonly(this.value,0,\'truncate_tables\',false,true,false)"'),
		);
		
		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(

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
		$sql = 'SELECT name, module_type, is_installed FROM '.TABLE_MODULES;		
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		for($i=0; $i < $result[1]; $i++){			
			self::$arr_modules[$result[0][$i]['name']] = array('installed'=>($result[0][$i]['is_installed'] == '1') ? true : false, 'type'=>$result[0][$i]['module_type']);
		}
	}	

	/**
	 * Returns all modules array
	 * @param $params - array of arguments
	 */
	public function GetAllModules($params = array())
	{		
		$sql = 'SELECT *
				FROM '.TABLE_MODULES.'
				ORDER BY priority_order ASC';		
		return database_query($sql, DATA_AND_ROWS, ALL_ROWS);
	}

	/**
	 *	Draws all modules
	 *		return: html output
	 */
	public function DrawModules()
	{
		global $objLogin;
		$margin = 'margin:-97px 0px 0px -44px;';
		$nl = "\n";
		
		if($this->modulesCount > 0){			
			$this->IncludeJSFunctions();
			echo '<form name="frmMicroGrid_'.$this->tableName.'" id="frmMicroGrid_'.$this->tableName.'" action="'.$this->formActionURL.'" method="post">'.$nl;
			draw_hidden_field($this->uPrefix.'mg_action', 'view'); echo $nl;
			draw_hidden_field('mg_rid', ''); echo $nl;
			draw_hidden_field('mg_sorting_fields', 'id'); echo $nl;
			draw_hidden_field('mg_sorting_types', ''); echo $nl;
			draw_hidden_field('mg_page', ''); echo $nl;
			draw_hidden_field('mg_operation', ''); echo $nl;
			draw_hidden_field('mg_operation_type', ''); echo $nl;
			draw_hidden_field('mg_operation_field', ''); echo $nl;
			draw_hidden_field('mg_search_status', ''); echo $nl;
			draw_hidden_field('mg_language_id', ''); echo $nl;
			draw_hidden_field('mg_operation_code', self::GetRandomString(20)); echo $nl;
			draw_token_field(); echo $nl;
			
			echo '<table width="100%" border="0" cellspacing="0" cellpadding="1">';			
			echo '<tr><td>';
			
			// SYSTEM
			$modules_output = $this->DrawModulesByType('0');
			if($modules_output != ''){
				echo draw_sub_title_bar(_SYSTEM_MODULES, false);
				echo $modules_output;
			}
			
			echo '</td></tr><tr><td>';
			
			// APPLICATION
			$modules_output = $this->DrawModulesByType('1');
			if($modules_output != ''){
				echo draw_sub_title_bar(_APPLICATION_MODULES, false);
				echo $modules_output;
			}

			echo '</td></tr><tr><td>';

			
			// ADDITIONAL
			echo draw_sub_title_bar(_ADDITIONAL_MODULES, false);

			$modules_output = '';
			foreach($this->additional_modules as $code => $module){				
				
				if(!Modules::IsModuleInstalled($code) && file_exists('modules/additional/'.$code.'/')){						
					// Read module xml file
					$xml = $this->ReadModuleXml($code);
					
					if(is_object($xml)){
						$module_path = 'modules/additional/'.$code.'/';
						$href = '';//isset($href_parts[0]) ? $href_parts[0] : '';
						$icon_file = $xml->icon && file_exists($module_path.$xml->icon) ? $module_path.$xml->icon : 'images/modules_icons/modules.png';
						$name = isset($xml->name) ? $xml->name : $code;
						$short_name = isset($xml->shortName) ? $xml->shortName : '';
						$description = $xml->description;
						
						$is_installed = false;
						$modules_output.= '<div style="width:120px;float:'.Application::Get('defined_left').';text-align:center;margin:5px;">
							<div>'.($href ? '<a href="'.$href.'">' : '').'<b>'.($short_name ? $short_name : $name).'</b>'.($href ? '</a>' : '').'</div>
							<div><img src="'.$icon_file.'" title="'.$description.'" alt="icon" style="cursor:help;margin:2px;border:1px solid #dedede"></div>			
							<div>'.($is_installed ? '<img src="images/success_sign.gif" style="position:absolute;'.$margin.'" alt="installed">' : '<img src="images/error_sign.gif" style="position:absolute;'.$margin.'" alt="removed">').'</div>
							<div><a href="index.php?admin=modules&module='.$code.'&action=install">[ '._INSTALL.' ]</a></div>
						</div>';
					}
					//<div><a href="javascript:void(0);" onclick="javascript:__mgDoPostBack(\''.$this->tableName.'\', \'edit\', \''.$this->modules[0][$i]['id'].'\');">[ '._EDIT_WORD.' ]</a></div>
				}else{

					$module = array();
					for($i=0; $i < $this->modules[1]; $i++){
						if($this->modules[0][$i]['name'] == $code){
							$module = $this->modules[0][$i];
							break;
						}
					}
					
					if(!empty($module)){
						$modules_output .= $this->DrawSingleModule($module);
					}
				}
			}

			if(!empty($modules_output)){
				echo $modules_output;
			}else{
				echo '&nbsp;&nbsp;&nbsp;&nbsp;'._MODULES_NOT_FOUND;
			}
			
			echo '</td></tr>';
			echo '</table>';
			echo '</form>'.$nl;
		}
	}

	/**
	 * Draws modules on dashboard
	 * @param $draw
	 */
	public function DrawModulesOnDashboard($draw = true)
	{
		global $objLogin;
		$output = '';

		if($objLogin->IsLoggedInAs('owner','mainadmin','admin')){
			$output .= '<div style="width:120px;float:'.Application::Get('defined_left').';text-align:center;margin:5px;">
				<div><a href="index.php?admin=modules" title="'._CLICK_TO_VIEW.'"><b>'._MODULES.'</b></a></div>
				<div><a href="index.php?admin=modules" title="'._CLICK_TO_VIEW.'"><img src="images/modules_icons/modules.png" alt="icon" style="margin:2px;border:1px solid #dedede"></a></div>
			</div>';
		}
		
		for($i=0; $i < $this->modules[1]; $i++){
			if($this->modules[0][$i]['show_on_dashboard'] == '1' && ($this->modules[0][$i]['is_installed'] == 1)){
				if($objLogin->IsLoggedInAs($this->modules[0][$i]['settings_access_by'])){
					$open_a = $close_a = '';
					$href = 'index.php?admin='.(($this->modules[0][$i]['management_page'] != '') ? $this->modules[0][$i]['management_page'] : $this->modules[0][$i]['settings_page']);
					if($href !== 'index.php?admin='){
						$open_a = '<a href="'.$href.'" title="'._CLICK_TO_VIEW.'">';
						$close_a = '</a>';
					}
					$output .= '<div style="width:120px;float:'.Application::Get('defined_left').';text-align:center;margin:5px;">
						<div>'.$open_a.'<b>'.(defined($this->modules[0][$i]['name_const']) ? decode_text(constant($this->modules[0][$i]['name_const'])) : humanize($this->modules[0][$i]['name_const'])).'</b>'.$close_a.'</div>
						<div>'.$open_a.'<img src="images/modules_icons/'.$this->modules[0][$i]['icon_file'].'" alt="icon" style="margin:2px;border:1px solid #dedede">'.$close_a.'</div>
					</div>';
				}
			}
		}
		
		if($draw) echo $output;
		else return $output;
	}	

	/**
	 *	Installs a Additional Module
	 * @param $module
	 *		return: boolean
	 */
	public function InstallAdditionalModule($module = '')
	{
		global $objSession;
		$msg = '';
		
		// block operation in demo mode
		if(strtolower(SITE_MODE) == 'demo'){
			echo draw_important_message(_OPERATION_BLOCKED, false);
			return false;
		}

		if(!isset($module, $this->additional_modules)){
			$msg = draw_important_message(_MODULE_NOT_FOUND, false);		
		}elseif(Modules::IsModuleInstalled($module)){
			$msg = draw_important_message(_MODULE_ALREADY_INSTALLED, false);
		}else{
			// Read module xml file
			$xml = $this->ReadModuleXml($module);
			
			if(!is_object($xml)){
				$msg = draw_important_message(_WRONG_PARAMETER_PASSED, false);
			}else{
				$sql = isset($xml->files->data->install) ? $xml->files->data->install : '';
				$sql_dump = file('modules/additional/'.$module.'/data/'.$sql);
				$error_mg = array();
				$return = array('msg'=>'', 'msg_success' => '', 'error_type'=>'');
				$base_path = $_SERVER['DOCUMENT_ROOT'].get_base_path();
				
				if(empty($sql_dump)){
					$msg = draw_important_message(_WRONG_PARAMETER_PASSED, false);
				}else{					
					$this->RunSqlDump($sql_dump, $error_mg);
					
					if(!empty($error_mg)){						
						$msg_text = '';
						foreach($error_mg as $error_m){
							$msg_text .= $error_m.'<br>';
						}
						$msg = draw_important_message($msg_text, false);
					}else{
						$return['msg_success'] .= '<br><b>Running Install SQL:</b> <br> - file: '.$sql.' ... <span style="color:darkgreen;">&#10003; OK</span>';

						// Run throw all files structure to create directories
						$return['msg_success'] .= '<br><b>Creating directories:</b>';
						foreach($xml->files->children() as $folder_key => $folder){
							foreach($folder->children() as $file){
								if(isset($file['createDirectory'])){
									$return['msg_success'] .= '<br> - directory: '.$base_path.$file['createDirectory'];
									$this->CreateDir($base_path.$file['createDirectory'], $return);
								}
							}
						}
						$return['msg_success'] .= !empty($directories_message) ? $directories_message : '<br> - not needed';

						// Run throw all files structure
						$return['msg_success'] .= '<br><b>Copying files:</b>';
						foreach($xml->files->children() as $folder_key => $folder){
							foreach($folder->children() as $file){
								// Has children - run in sub-folder
								$children = $file->children();
								if(count($children)){
									foreach($children as $child_file){
										$dir_name = $file->getName();
										if(isset($child_file['installationPath'])){
											$return['msg_success'] .= '<br> - file: '.$base_path.$child_file['installationPath'].$dir_name.$child_file[0];
											$this->CopyFile($base_path.'modules/additional/'.$module.'/'.$folder_key.'/'.$dir_name.'/', $base_path.$child_file['installationPath'], $child_file[0], $return);
										}
									}
								}
								if(isset($file['installationPath'])){
									$return['msg_success'] .= '<br> - file: '.$base_path.$file['installationPath'].$file[0];
									$this->CopyFile($base_path.'modules/additional/'.$module.'/'.$folder_key.'/', $base_path.$file['installationPath'], $file[0], $return);
								}
							}
						}
						
						// Recreate vocabulary files:
						$objVocabulary = new Vocabulary();
						$objVocabulary->RewriteVocabularyFile(true);
						$return['msg_success'] .= '<br><b>Re-creating vocabulary fiels</b> ... <span style="color:darkgreen;">&#10003; OK</span>';

						if(!empty($return['error_type'])){
							$objSession->SetMessage('notice', draw_important_message($return['msg'], false));
						}else{
							$objSession->SetMessage('notice', draw_success_message(humanize($module, 'strtoupper').': '._MODULE_INSTALLED.'<br>'.$return['msg_success'], false));
						}

						redirect_to('index.php?admin=modules'.($module == 'channel_manager' ? '&module=channel_manager' : '').'&t='.time());
					}				
				}				
			}
		}
		
		echo $msg;
	}

	/**
	 * Uninstalls a Additional Module
	 * @param $module
	 * @return boolean
	 */
	public function UninstallAdditionalModule($module = '')
	{
		global $objSession;
		$msg = '';
		
		// block operation in demo mode
		if(strtolower(SITE_MODE) == 'demo'){
			echo draw_important_message(_OPERATION_BLOCKED, false);
			return false;
		}

		if(!isset($module, $this->additional_modules)){
			$msg = draw_important_message(_MODULE_NOT_FOUND, false);		
		}elseif(!Modules::IsModuleInstalled($module)){
			$msg = draw_important_message(_MODULE_NOT_FOUND, false);
		}else{
			// Read module xml file
			$xml = $this->ReadModuleXml($module);
			
			if(!is_object($xml)){
				$msg = draw_important_message(_WRONG_PARAMETER_PASSED, false);
			}else{
				$sql = isset($xml->files->data->install) ? $xml->files->data->uninstall : '';
				$sql_dump = file('modules/additional/'.$module.'/data/'.$sql);
				$error_mg = array();
				$return = array('msg'=>'', 'msg_success' => '', 'error_type'=>'');
				$base_path = $_SERVER['DOCUMENT_ROOT'].get_base_path();
				
				if(empty($sql_dump)){
					$msg = draw_important_message(_WRONG_PARAMETER_PASSED, false);
				}else{					
					$this->RunSqlDump($sql_dump, $error_mg);

					if(!empty($error_mg)){
						$msg_text = '';
						
						foreach($error_mg as $error_m){
							$msg_text .= $error_m.'<br>';
						}						
						
						if(!empty($msg_text)){
							$msg = draw_important_message($msg_text, false);
						}
					}else{
						$return['msg_success'] .= '<br><b>Running Uninstall SQL:</b> <br> - file: '.$sql.' ... <span style="color:darkgreen;">&#10003; OK</span>';

						// Run throw all files structure
						$return['msg_success'] .= '<br><b>Deleting files:</b>';
						foreach($xml->files->children() as $folder_key => $folder){
							foreach($folder->children() as $file){
								// Has children - run in sub-folder
								$children = $file->children();
								if(count($children)){
									foreach($children as $child_file){
										if(isset($child_file['installationPath'])){
											$return['msg_success'] .= '<br> - file: '.$base_path.$child_file['installationPath'].$child_file[0];
											$this->DeleteFile($base_path.$child_file['installationPath'].$child_file[0], $return);
										}
									}
								}
								
								if(isset($file['installationPath'])){
									$return['msg_success'] .= '<br> - file: '.$base_path.$file['installationPath'].$file[0];
									$this->DeleteFile($base_path.$file['installationPath'].$file[0], $return);
								}
							}
						}

						// Run throw all files structure to remove directories
						$return['msg_success'] .= '<br><b>Deleting directories:</b>';
						foreach($xml->files->children() as $folder_key => $folder){
							foreach($folder->children() as $file){
								if(isset($file['createDirectory'])){
									$return['msg_success'] .= '<br> - directory: '.$base_path.$file['createDirectory'];
									$this->DeleteDir($base_path.$file['createDirectory'], $return);
								}
							}
						}

						// Recreate vocabulary files:
						$objVocabulary = new Vocabulary();
						$objVocabulary->RewriteVocabularyFile(true);
						$return['msg_success'] .= '<br><b>Re-creating vocabulary fiels</b> ... <span style="color:darkgreen;">&#10003; OK</span>';

						if(!empty($return['error_type'])){
							$objSession->SetMessage('notice', draw_important_message($return['msg'], false));
						}else{
							$objSession->SetMessage('notice', draw_success_message(humanize($module, 'strtoupper').': '._MODULE_UNINSTALLED.'<br>'.$return['msg_success'], false));
						}
						
						redirect_to('index.php?admin=modules'.($module == 'channel_manager' ? '&module=channel_manager' : ''));
					}				
				}				
			}
		}
		
		echo $msg;
	}

	/**
	 *	Installs a Module
	 *		return: boolean
	 */
	public function InstallModule()
	{
		// Block operation in demo mode
		if(strtolower(SITE_MODE) == 'demo'){
			$this->error = _OPERATION_BLOCKED;
			return false;
		}

		$mid = $this->module_name;

		$sql = 'SELECT name FROM '.TABLE_MODULES.' WHERE dependent_modules LIKE \'%'.$mid.'%\' AND is_installed = 0';
		$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		if($result[1] > 0){
			$first_module = ucwords(str_replace('_', ' ', $mid));
			$second_module = ucwords(str_replace('_', ' ', $result[0]['name']));
			$this->error = str_replace(array('_FIRST_MODULE_', '_SECOND_MODULE_'), array($first_module, $second_module), _CANNOT_INSTALL_DEPENDENT_MODULE);
			return false;							
		}else{			
			$sql = 'UPDATE '.TABLE_MODULES.' SET is_installed = 1 WHERE name = \''.$mid.'\'';		
			if(database_void_query($sql)){
				$this->modules = $this->GetAllModules();
				$this->modulesCount = $this->modules[1];
				return true;
			}else{
				$this->error = _WRONG_PARAMETER_PASSED;
				return false;			
			}			
		}		
	}

	/**
	 *	Un-Installs Module
	 *		return: boolean
	 */
	public function UninstallModule()
	{
		// block operation in demo mode
		if(strtolower(SITE_MODE) == 'demo'){
			$this->error = _OPERATION_BLOCKED;
			return false;
		}

		$mid = $this->module_name;
		
		// Additional module uninstall in a separate way
		if(isset(self::$arr_modules[$mid]['type']) && self::$arr_modules[$mid]['type'] == 2){
			$this->UninstallAdditionalModule($mid);
		}else{			
			$sql = 'UPDATE '.TABLE_MODULES.' SET is_installed = 0 WHERE name = \''.$mid.'\' AND module_type = 1';
			if(database_void_query($sql, false, true)){
				$sql = 'SELECT name, name_const, description_const, module_tables, dependent_modules FROM '.TABLE_MODULES.' WHERE name = \''.$mid.'\'';
				$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
				if($result[1] > 0){	
					// check dependent modules
					if(isset($result[0]['dependent_modules'])){
						$modules_arr = explode(',', $result[0]['dependent_modules']);
						foreach($modules_arr as $module){
							if($module != '') $this->UninstallModule(array('mid'=>$module));
						}				
					}				
				}
	
				$this->modules = $this->GetAllModules();
				$this->modulesCount = $this->modules[1];
				
				return true;
			}else{
				//echo $sql;			
				$this->error = _WRONG_PARAMETER_PASSED;
				return false;			
			}		
		}
		
	}

	/**
	 * Checks if a module is installed
	 * @param $module_name
	 * return boolean
	 */
	public static function IsModuleInstalled($module_name)
	{		
		if(isset(self::$arr_modules[$module_name]['installed']) && self::$arr_modules[$module_name]['installed'] == true){
			return true;
		}else{
			return false;			
		}
	}
	
	/**
	 * Clean module tables
	 * @param $module_name
	 */
	private function CleanModuleTables($module_tables = '')
	{
		$module_tables_arr = explode(',', $module_tables);
		foreach($module_tables_arr as $table){
			if($table != ''){
				if(defined('TABLE_'.strtoupper(trim($table)))){
					$sql = 'TRUNCATE '.constant('TABLE_'.strtoupper(trim($table)));
				}else{					
					$sql = 'TRUNCATE '.trim(DB_PREFIX.$table);
				}
				database_void_query($sql);
			}
		}
	}

	/**
	 * Before-Updating function
	 */
	public function BeforeUpdateRecord()
	{	
		$sql = 'SELECT name, is_installed, module_type FROM '.$this->tableName.' WHERE '.$this->primaryKey.' = '.$this->curRecordId;
		$result = database_query($sql, DATA_ONLY, FIRST_ROW_ONLY);
        if(isset($result['is_installed'])){
			$this->is_installed = $result['is_installed'];
			$this->module_name = $result['name'];			
			if($result['module_type'] == '0'){
				$this->error = _SYSTEM_MODULE_ACTIONS_BLOCKED;
				return false;
			}
		}		
		return true;
	}
	
	/**
	 * After-Updating function
	 */
	public function AfterUpdateRecord()
	{
		$is_installed = self::GetParameter('is_installed', false);
		$truncate_tables = self::GetParameter('truncate_tables', false);
		
		if($this->is_installed == '0' && $is_installed == '1'){
			if($this->InstallModule()){
				$this->error = _MODULE_INSTALLED;
			}

            // Clear module tables
            if($truncate_tables){
                foreach($this->modules[0] as $key => $module){
                    if($module['id'] == $this->curRecordId){
                        $this->CleanModuleTables($module['module_tables']);
                        break;
                    }
                }
            }
        }elseif($this->is_installed == '1' && $is_installed == '0'){
			if($this->UninstallModule()){
				$this->error = _MODULE_UNINSTALLED;
			}		
		}
		
		return true;
	}
	
	/**
	 * Before-Editing function
	 */
	public function BeforeEditRecord()
	{
		echo '<script type="text/javascript">
			function __mgDoModeAlert(){
				if(jQuery(\'#is_installed\').val() == \'0\' && confirm(\''._MODULE_UNINSTALL_ALERT.'\')){
					// do nothing
					return true;
				}
				else if(jQuery(\'#is_installed\').val() == \'1\'){
					return true;
				}
				return false;
			}
			</script>';
			
		return true;
	}	

	/**
	 * Reads the module info.xml file
	 * @param string $moduleCode the module code
	 * @return the contents of the XML file or error message 
	 */
	private function ReadModuleXml($moduleCode)
	{
		$path = 'modules/additional/'.$moduleCode.'/info.xml';
		
		if(!file_exists($path)) {
			return 'Could not read file <b>'.$path.'</b>! Please check if this file exists.';
		}
		
    	// Load XML file for module info
		$xml = simplexml_load_file($path);
		if(!is_object($xml)){		
			return 'Failed to load XML file <b>'.$path.'</b>.';
		} 
		
		return $xml;
	}
	
	/**
	 * Draws modules by type
	 * @param $type
	 */
	private function DrawModulesByType($type = '0')
	{
		$modules_output = '';
		for($i=0; $i < $this->modules[1]; $i++){
			if($this->modules[0][$i]['module_type'] == $type){
				$modules_output .= $this->DrawSingleModule($this->modules[0][$i]);
			}
		}
		
		return $modules_output;
	}
	
	/**
	 * Draws single module
	 * @param array $module
	 * @return array
	 */
	private function DrawSingleModule($module = array())
	{
		global $objLogin;
		$margin = 'margin:-97px 0px 0px -44px;';
		
		$output = '';
		
		if(!empty($module)){
			if($objLogin->IsLoggedInAs($module['settings_access_by'])){
				$href = '';
				if($module['is_installed']){					
					$href = ($module['management_page'] != '') ? 'index.php?admin='.$module['management_page'] : 'index.php?admin='.$module['settings_page'];
					$href_parts = explode(',', $href);
					$href = isset($href_parts[0]) ? $href_parts[0] : '';
				}
				
				$output .= '<div style="width:120px;float:'.Application::Get('defined_left').';text-align:center;margin:5px;">
					<div>'.($href ? '<a href="'.$href.'">' : '').'<b>'.(defined($module['name_const']) ? decode_text(constant($module['name_const'])) : humanize($module['name_const'])).'</b>'.($href ? '</a>' : '').'</div>
					<div><img src="images/modules_icons/'.$module['icon_file'].'" title="'.@decode_text(constant($module['description_const'])).'" alt="icon" style="cursor:help;margin:2px;border:1px solid #dedede"></div>
					<div>'.(($module['is_installed'] == 1) ? '<img src="images/success_sign.gif" style="position:absolute;'.$margin.'" alt="installed">' : '<img src="images/error_sign.gif" style="position:absolute;'.$margin.'" alt="removed">').'</div>
					<div><a href="javascript:void(0);" onclick="javascript:__mgDoPostBack(\''.$this->tableName.'\', \'edit\', \''.$module['id'].'\');">[ '._EDIT_WORD.' ]</a></div>
				</div>';
			}
		}
		
		return $output;
	}

	/**
	 * Prepare and run SQL dump
	 * @param $sql_dump
	 * @param &$error_mg
	 * @return bool
	*/
	private function RunSqlDump($sql_dump, &$error_mg)
	{
		if(empty($sql_dump)){
			return false;
		}
		
		$sql_dump = str_ireplace('<DB_PREFIX>', DB_PREFIX, $sql_dump);
	
		// add ';' at the end of file
		if(substr($sql_dump[count($sql_dump)-1], -1) != ';') $sql_dump[count($sql_dump)-1] .= ';';
		
		$query = '';
		foreach($sql_dump as $sql_line){
			$tsl = trim((function_exists('utf8_decode')) ? utf8_decode($sql_line) : $sql_line);
			if(($sql_line != '') && (substr($tsl, 0, 2) != '--') && (substr($tsl, 0, 1) != '?') && (substr($tsl, 0, 1) != '#')) {
				$query .= $sql_line;
				if(preg_match('/;\s*$/', $sql_line)){
					if((strlen(trim($query)) > 5) && !database_void_query($query)){	
						$error_mg[] = (SITE_MODE == 'development' ? $query : '').database_error();
						break;
					}
					$query = '';
				}
			}
		}
		
		return true;
	}

	/**
	 * Performs copying operation
	 * @param string $source
	 * @param string $dest
	 * @param string $filename
	 * @param array &$return
	 */
    private function CopyFile($source, $dest, $filename, &$return)
    {
        // Create directories recursively if needed
        if (!file_exists($dest)) {
            if (!mkdir($dest, 0777, true)) {
                $return['msg'] .= (($return['msg'] != '') ? '<br>' : '').str_ireplace(array('{directory}'), array($dest), 'An error occurred while creating the directory <b>{directory}</b>');
                $return['error_type'] = 'warning';
                $return['msg_success'] .= ' ... <span style="color:darkred;">Failed</span>';
            }
        }

		//echo '<br>'.$source.$filename;
		//echo '<br>'.$dest.$filename;
        if(!copy($source.$filename, $dest.$filename)){
            $return['msg'] .= (($return['msg'] != '') ? '<br>' : '').str_ireplace(array('{source}', '{destination}'), array($filename, $dest.$filename), 'An error occurred while copying the file <b>{source}</b> to <b>{destination}</b>');
            $return['error_type'] = 'warning';
            $return['msg_success'] .= ' ... <span style="color:darkred;">Failed</span>';
        }else{
            $return['msg_success'] .= ' ... <span style="color:darkgreen;">&#10003; OK</span>';
        }        
    }

	/**
	 * Performs deleting file operation
	 * @param string $destFile
	 * @param array &$return
	 */
    private function DeleteFile($destFile, &$return)
    {                                
        if(!empty($destFile)){
			if(!unlink($destFile)){
				$return['msg'] .= (($return['msg'] != '') ? '<br>' : '').str_ireplace(array('{source}'), array($destFile), 'An error occurred while deleting the file from <b>{source}</b>');
				$return['error_type'] = 'warning';
				$return['msg_success'] .= ' ... <span style="color:darkred;">Failed</span>';
			}else{
				$return['msg_success'] .= ' ... <span style="color:darkgreen;">&#10003; OK</span>';
			}
		}
    }
	
	/**
	 * Performs creating directory
	 * @param string $destDir
	 * @param array &$return
	 */
    private function CreateDir($destDir, &$return)
    {                                
        if(!empty($destDir)){
			if(!mkdir($destDir)){
				$return['msg'] .= (($return['msg'] != '') ? '<br>' : '').str_ireplace(array('{destination}'), array($destDir), 'An error occurred while creating directory <b>{destination}</b>');
				$return['error_type'] = 'error';
				$return['msg_success'] .= ' ... <span style="color:darkred;">Failed</span>';
			}else{
				$return['msg_success'] .= ' ... <span style="color:darkgreen;">&#10003; OK</span>';
			}
		}
    }
	
	/**
	 * Performs deleting directory operation
	 * @param string $destFile
	 * @param array &$return
	 */
    private function DeleteDir($destFile, &$return)
    {                                
        if(!empty($destFile)){
			if(!rmdir($destFile)){
				$return['msg'] .= (($return['msg'] != '') ? '<br>' : '').str_ireplace(array('{destination}'), array($destFile), 'An error occurred while deleting directory <b>{destination}</b>');
				$return['error_type'] = 'error';
				$return['msg_success'] .= ' ... <span style="color:darkred;">Failed</span>';
			}else{
				$return['msg_success'] .= ' ... <span style="color:darkgreen;">&#10003; OK</span>';
			}
		}
    }

}
