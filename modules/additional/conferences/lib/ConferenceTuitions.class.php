<?php

/**
 *	Class ConferenceTuitions
 *  --------------
 *	Description : 
 *	Written by  : ApPHP
 *	Version     : 1.0.0
 *  Updated	    : 02.05.2017
 *  Usage       : uHotelBooking
 *
 *	PUBLIC:				  	STATIC:				 	PRIVATE:
 * 	------------------	  	---------------     	---------------
 *	__construct
 *	__destruct
 *	
 **/


class ConferenceTuitions extends MicroGrid {
	
	protected $debug = false;
	
	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct($conference_id)
	{		
		parent::__construct();
        
        global $objLogin;
		
		$this->params = array();
		
		## for standard fields
		if(isset($_POST['name']))  $this->params['name'] = prepare_input($_POST['name']); else $this->params['name'] = '';
		if(isset($_POST['price_type_1']))  $this->params['price_type_1'] = prepare_input($_POST['price_type_1']); else $this->params['price_type_1'] = '';
		if(isset($_POST['price_type_2']))  $this->params['price_type_2'] = prepare_input($_POST['price_type_2']); else $this->params['price_type_2'] = '';
		if(isset($_POST['price_type_3']))  $this->params['price_type_3'] = prepare_input($_POST['price_type_3']); else $this->params['price_type_3'] = '';
		
		$this->params['conference_id'] = (int)$conference_id;
		
		$this->primaryKey 	= 'id';
		$this->tableName 	= DB_PREFIX.'conference_tuitions';
		$this->dataSet 		= array();
		$this->error 		= '';
		$this->formActionURL = 'index.php?admin=mod_conference_tuitions&cid='.(int)$conference_id;
		$this->actions      = array('add'=>true, 'edit'=>true, 'details'=>true, 'delete'=>true);
		$this->actionIcons  = true;
		$this->allowRefresh = true;
		$this->allowTopButtons = false;
		$this->alertOnDelete = ''; // leave empty to use default alerts

		$this->allowLanguages = false;
        $this->WHERE_CLAUSE = (int)$conference_id != 0 ? 'WHERE conference_id = '.(int)$conference_id : '';
		$this->ORDER_CLAUSE = 'ORDER BY sp.name ASC';
		
		$this->isAlterColorsAllowed = true;

		$this->isPagingAllowed = true;
		$this->pageSize = 50;

		$this->isSortingAllowed = true;

		$this->isExportingAllowed = false;
		$this->arrExportingTypes = array('csv'=>false);
		
		$this->isFilteringAllowed = true;
		// define filtering fields
		$this->arrFilteringFields = array(
			_NAME => array('table'=>'sp', 'field'=>'name', 'type'=>'text', 'sign'=>'%like%', 'width'=>'110px', 'visible'=>true),
		);

		$pre_default_currency = '';
		$post_default_currency = '';
		$default_currency_info = Currencies::GetDefaultCurrencyInfo();
		if(!empty($default_currency_info)){
			if($default_currency_info['symbol_placement'] == 'before'){
				$pre_default_currency = $default_currency_info['symbol'];
			}else{
				$post_default_currency = $default_currency_info['symbol'];
			}
		}

		// prepare arrays
		$arr_is_active = array('0'=>'<span class=no>'._NO.'</span>', '1'=>'<span class=yes>'._YES.'</span>');

		//---------------------------------------------------------------------- 
		// VIEW MODE
		//---------------------------------------------------------------------- 
		$this->VIEW_MODE_SQL = 'SELECT
                                    sp.'.$this->primaryKey.',
                                    sp.name,
                                    sp.price_type_1,
                                    sp.price_type_2,
                                    sp.price_type_3
								FROM '.$this->tableName.' sp';
		// define view mode fields
		$this->arrViewModeFields = array(
			'name'         => array('title'=>_NAME, 'type'=>'label', 'align'=>'left', 'width'=>'290px', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'45', 'format'=>'', 'format_parameter'=>''),
			'price_type_1' => array('title'=>_CONFERENCE_PRICE_TYPE_1, 'type'=>'label', 'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'10', 'format'=>'', 'format_parameter'=>'', 'pre_html'=>$pre_default_currency, 'post_html'=>$post_default_currency),
			'price_type_2' => array('title'=>_CONFERENCE_PRICE_TYPE_2, 'type'=>'label', 'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'10', 'format'=>'', 'format_parameter'=>'', 'pre_html'=>$pre_default_currency, 'post_html'=>$post_default_currency),
			'price_type_3' => array('title'=>_CONFERENCE_PRICE_TYPE_3, 'type'=>'label', 'align'=>'left', 'width'=>'', 'sortable'=>true, 'nowrap'=>'', 'visible'=>'', 'tooltip'=>'', 'maxlength'=>'10', 'format'=>'', 'format_parameter'=>'', 'pre_html'=>$pre_default_currency, 'post_html'=>$post_default_currency),
		);
		
		//---------------------------------------------------------------------- 
		// ADD MODE
		//---------------------------------------------------------------------- 
		// define add mode fields
		$this->arrAddModeFields = array(		    
			'name'		   => array('title'=>_NAME,  'type'=>'textbox', 'width'=>'150px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'15', 'default'=>'', 'validation_type'=>'text'),
			'price_type_1' => array('title'=>_CONFERENCE_PRICE_TYPE_1, 'type'=>'textbox', 'width'=>'100px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'10', 'default'=>'', 'validation_type'=>'float|positive', 'pre_html'=>$pre_default_currency.' ', 'post_html'=>' '.$post_default_currency),
			'price_type_2' => array('title'=>_CONFERENCE_PRICE_TYPE_2, 'type'=>'textbox', 'width'=>'100px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'10', 'default'=>'', 'validation_type'=>'float|positive', 'pre_html'=>$pre_default_currency.' ', 'post_html'=>' '.$post_default_currency),
			'price_type_3' => array('title'=>_CONFERENCE_PRICE_TYPE_3, 'type'=>'textbox', 'width'=>'100px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'10', 'default'=>'', 'validation_type'=>'float|positive', 'pre_html'=>$pre_default_currency.' ', 'post_html'=>' '.$post_default_currency),
			'conference_id' => array('title'=>'', 'type'=>'hidden', 'required'=>true, 'readonly'=>false, 'default'=>$conference_id),
		);

		//---------------------------------------------------------------------- 
		// EDIT MODE
		//---------------------------------------------------------------------- 
		$this->EDIT_MODE_SQL = 'SELECT
                                    '.$this->tableName.'.'.$this->primaryKey.',
                                    '.$this->tableName.'.name,
                                    '.$this->tableName.'.price_type_1,
                                    '.$this->tableName.'.price_type_2,
                                    '.$this->tableName.'.price_type_3
							FROM '.$this->tableName.'
							WHERE '.$this->tableName.'.'.$this->primaryKey.' = _RID_';		
		// define edit mode fields
		$this->arrEditModeFields = array(
			'name'		   => array('title'=>_NAME,  'type'=>'textbox', 'width'=>'150px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'15', 'default'=>'', 'validation_type'=>'text'),
			'price_type_1' => array('title'=>_CONFERENCE_PRICE_TYPE_1, 'type'=>'textbox', 'width'=>'60px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'10', 'default'=>'0', 'validation_type'=>'float|positive', 'pre_html'=>$pre_default_currency.' ', 'post_html'=>' '.$post_default_currency),
			'price_type_2' => array('title'=>_CONFERENCE_PRICE_TYPE_2, 'type'=>'textbox', 'width'=>'60px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'10', 'default'=>'0', 'validation_type'=>'float|positive', 'pre_html'=>$pre_default_currency.' ', 'post_html'=>' '.$post_default_currency),
			'price_type_3' => array('title'=>_CONFERENCE_PRICE_TYPE_3, 'type'=>'textbox', 'width'=>'60px', 'required'=>true, 'readonly'=>false, 'maxlength'=>'10', 'default'=>'0', 'validation_type'=>'float|positive', 'pre_html'=>$pre_default_currency.' ', 'post_html'=>' '.$post_default_currency),
		);

		//---------------------------------------------------------------------- 
		// DETAILS MODE
		//----------------------------------------------------------------------
		$this->DETAILS_MODE_SQL = $this->EDIT_MODE_SQL;
		$this->arrDetailsModeFields = array(
			'name'		   => array('title'=>_NAME,  'type'=>'label'),
			'price_type_1' => array('title'=>_CONFERENCE_PRICE_TYPE_1, 'type'=>'label'),
			'price_type_2' => array('title'=>_CONFERENCE_PRICE_TYPE_2, 'type'=>'label'),
			'price_type_3' => array('title'=>_CONFERENCE_PRICE_TYPE_3, 'type'=>'label'),
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
     * 	Get all countries array
     * 	@param $where - where clause
     */
	public static function GetConferenceTuitions($conference_id = '', $where_clause = '')
	{
        $tableName = DB_PREFIX.'conference_tuitions';

        // Build WHERE clause
		$sql = 'SELECT '.$tableName.'.*
				FROM '.$tableName.'
				WHERE conference_id = '.(int)$conference_id.'
				'.$where_clause;

		return database_query($sql, DATA_AND_ROWS);
    }
}
?>
