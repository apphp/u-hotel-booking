<?php
/**
* @project uHotelBooking
* @copyright (c) 2019 ApPHP
* @author ApPHP <info@apphp.com>
* @site http://www.hotel-booking-script.com
* @license http://hotel-booking-script.com/license.php
*/

// *** Make sure the file isn't accessed directly
defined('APPHP_EXEC') or die('Restricted Access');
//--------------------------------------------------------------------------

if(Modules::IsModuleInstalled('car_rental') && (ModulesSettings::Get('car_rental', 'is_active') != 'no') && 
	($objLogin->IsLoggedInAs('owner','mainadmin','agencyowner'))
){	

	$task = isset($_POST['task']) ? prepare_input($_POST['task']) : '';
	$sel_type = isset($_POST['sel_type']) ? prepare_input($_POST['sel_type']) : '';
	$sel_car_agency = isset($_POST['sel_car_agency']) ? prepare_input($_POST['sel_car_agency']) : '';
	$date_from = isset($_POST['date_from']) ? prepare_input($_POST['date_from']) : '';
	$date_to = isset($_POST['date_to']) ? prepare_input($_POST['date_to']) : '';
	$msg = '';
	$where_clause = '';
	$date_from_sql = '';
	$date_to_sql = '';
	$nl = "\n";
	$error = false;
	$datetime_format = get_datetime_format();

	$arr_agencies = array();
	$agencies_list = '';
	$agencies_count = CarAgencies::AgenciesCount();
	if($objLogin->IsLoggedInAs('agencyowner')){
		$agencies_list = implode(',', $objLogin->AssignedToCarAgencies());
		if(!empty($agencies_list)){
			$total_agencies = CarAgencies::GetAllActive(TABLE_CAR_AGENCIES.'.id IN ('.$agencies_list.')');
			foreach($total_agencies[0] as $key => $val) $arr_agencies[$val['id']] = $val['name'];
		}else{
			$error = true;
			$msg = '<br>'.draw_important_message(__CAR_OWNER_NOT_ASSIGNED, false);
		}
	}else{
		if($agencies_count > 1){
			$total_agencies = CarAgencies::GetAllActive();
			foreach($total_agencies[0] as $key => $val) $arr_agencies[$val['id']] = $val['name'];
		}		
	}

	// Prepare makes array		
	$total_makes = Makes::GetAllActive();
	$arr_makes = array();
	foreach($total_makes[0] as $key => $val){
		$arr_makes[$val['id']] = $val['name'];
	}

	// Fix wrong dates
	if($date_from > $date_to){
		$date_to = $date_from;
	}

	if($objSettings->GetParameter('date_format') == 'mm/dd/yyyy'){
		$calendar_date_format = '%m-%d-%Y';
		$date_from_sql = date('Y-m-d', mktime(0, 0, 0, substr($date_from, 0, 2), substr($date_from, 3, 2), substr($date_from, 6, 4)));
		$date_to_sql   = date('Y-m-d', mktime(0, 0, 0, substr($date_to, 0, 2), substr($date_to, 3, 2), substr($date_to, 6, 4)));
		if($date_from == '') $date_from = date('m-01-Y');
		if($date_to == '') $date_to = date('m-d-Y');
	}else{
		$calendar_date_format = '%d-%m-%Y';
		$date_from_sql = date('Y-m-d', mktime(0, 0, 0, substr($date_from, 3, 2), substr($date_from, 0, 2), substr($date_from, 6, 4)));
		$date_to_sql   = date('Y-m-d', mktime(0, 0, 0, substr($date_to, 3, 2), substr($date_to, 0, 2), substr($date_to, 6, 4)));
		if($date_from == '') $date_from = date('01-m-Y');
		if($date_to == '') $date_to = date('d-m-Y');
	}

	if($task == 'prepare_report'){		
		if($sel_type == ''){
			$msg = draw_important_message(_SELECT_REPORT_ALERT, false);
		}elseif($sel_type == 'picking_up' && $date_from == ''){
			$msg = draw_important_message(str_replace('_FIELD_', '\''._FROM.'\'', _FIELD_CANNOT_BE_EMPTY), false);
		}elseif($sel_type == 'returning' && $date_to == ''){
			$msg = draw_important_message(str_replace('_FIELD_', '\''._TO.'\'', _FIELD_CANNOT_BE_EMPTY), false);
		}elseif($sel_type == 'in_rental' && ($date_from == '' || $date_to == '')){
			$msg = draw_important_message(_DATE_EMPTY_ALERT, false);
		}

		if($msg == ''){
			if($sel_type == 'picking_up'){
				$where_clause .= ' AND r.date_from >= \''.$date_from_sql.'\''.(($date_to != '') ? ' AND r.date_from <= \''.$date_to_sql.'\'' : '');
			}elseif($sel_type == 'returning'){
				$where_clause .= ' AND r.date_to >= \''.$date_from_sql.'\''.(($date_to != '') ? ' AND r.date_to <= \''.$date_to_sql.'\'' : '');
			}elseif($sel_type == 'in_rental'){
				$where_clause .= ' AND r.date_from <= \''.$date_from_sql.'\' AND r.date_to >= \''.$date_to_sql.'\'';
			}
			
			if($sel_car_agency != ''){
				$where_clause .= ' AND r.car_agency_id = '.(int)$sel_car_agency;
			}
			
			// Show data for related agency only
			if($objLogin->IsLoggedInAs('agencyowner')){
				$where_clause .= ' AND r.car_agency_id IN ('.$agencies_list.')';
			}
			
			$sql = 'SELECT
						r.reservation_number,
						r.additional_info,
						IF(
							r.is_admin_reservation = 1,
							"Admin",
							CONCAT(c.first_name, " ", c.last_name)
						) as full_name,
						r.car_agency_id,
						r.car_vehicle_type_id,
						r.location_from_id,
						r.location_to_id,
						r.date_from,
						r.time_from,
						r.date_to,
						r.time_to,
						CONCAT(r.date_from, " ", r.time_from) mod_date_from,
						CONCAT(r.date_to, " ", r.time_to) mod_date_to,
						vcd.name as vehicle_type_name,
						v.make_id,
						v.model,
						v.registration_number
					FROM '.TABLE_CAR_AGENCY_RESERVATIONS.' r
						INNER JOIN '.TABLE_CAR_AGENCY_VEHICLE_TYPES.' vt ON r.car_vehicle_type_id = vt.id
						INNER JOIN '.TABLE_CAR_AGENCY_VEHICLE_CATEGORIES_DESCRIPTION.' vcd ON vt.vehicle_category_id = vcd.agency_vehicle_category_id AND vcd.language_id = \''.Application::Get('lang').'\'
						LEFT OUTER JOIN '.TABLE_CUSTOMERS.' c ON r.customer_id = c.id
						LEFT OUTER JOIN '.TABLE_CAR_AGENCY_VEHICLES.' v ON r.car_vehicle_id = v.id
					WHERE 1=1
						'.$where_clause;
			$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
			if($result[1] <= 0) $msg = draw_important_message(_NO_RECORDS_FOUND, false);
			
			// prepare locations array		
			$total_agencies_locations = CarAgenciesLocations::GetAgenciesLocations();
			$arr_agencies_locations = array();
			foreach($total_agencies_locations[0] as $key => $val){
				$arr_agencies_locations[$val['id']] = $val['name'];
			}
		}
	}
	
	// Start main content
	draw_title_bar(
		prepare_breadcrumbs(array(_CAR_RENTAL=>'',_CAR_RESERVATIONS=>'',_REPORTS=>'')),
		(($task == 'prepare_report' && $msg == '') ? '<a href="javascript:window.print();"><img src="images/printer.png" alt="" /> '._PRINT.'</a>' : '')
	);

	//if($objSession->IsMessage('notice')) echo $objSession->GetMessage('notice');
	echo $msg;
	
	draw_content_start();

	echo '<link type="text/css" rel="stylesheet" href="modules/jscalendar/skins/aqua/theme.css" />
	<script type="text/javascript" src="modules/jscalendar/calendar.js"></script>
	<script type="text/javascript" src="modules/jscalendar/lang/calendar-'.((file_exists('modules/jscalendar/lang/calendar-'.Application::Get('lang').'.js')) ? Application::Get('lang') : 'en').'.js"></script>
	<script type="text/javascript" src="modules/jscalendar/calendar-setup.js"></script>
	
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
		google.load("visualization", "1", {packages: ["table"]});
    </script>
    <script type="text/javascript">
		var visualization;
		var data;
		var options = {\'showRowNumber\': true};';

	if($msg == ''){				
		echo 'function drawVisualization() {
		  // Create and populate the data table.
		  var dataAsJson =
		  {
		  cols:[
			{id:"A",label:"'._VISITOR.'",type:"string"},
			{id:"B",label:"'._RESERVATION_NUMBER.'",type:"string"},
			{id:"C",label:"'._RENTAL_FROM.'",type:"string"},
			{id:"D",label:"'._LOCATION.'",type:"string"},
			{id:"E",label:"'._RENTAL_TO.'",type:"string"},
			{id:"F",label:"'._LOCATION.'",type:"string"},
			{id:"G",label:"'._CAR_AGENCY.'",type:"string"},
			{id:"H",label:"'._VEHICLE_TYPE.'",type:"string"},
			{id:"I",label:"'._VEHICLE.'",type:"string"},
			{id:"J",label:"'._ADDITIONAL_INFO.'",type:"string"}],
		  rows:[ ';

            if(isset($result[1])){ 
				for($i = 0; $i < $result[1]; $i++){
					
					$location_from_id = $result[0][$i]['location_from_id'];
					$location_to_id = $result[0][$i]['location_to_id'];
					$car_agency_id = $result[0][$i]['car_agency_id'];
					$make = isset($arr_makes[$result[0][$i]['make_id']]) ? $arr_makes[$result[0][$i]['make_id']] : '';
					
					echo '{c:[
						{v:"'.addslashes($result[0][$i]['full_name']).'"},
						{v:"'.$result[0][$i]['reservation_number'].'"},
						{v:"'.date($datetime_format, strtotime($result[0][$i]['mod_date_from'])).'"},
						{v:"'.(isset($arr_agencies_locations[$location_from_id]) ? htmlentities($arr_agencies_locations[$location_from_id]) : '').'"},
						{v:"'.date($datetime_format, strtotime($result[0][$i]['mod_date_to'])).'"},
						{v:"'.(isset($arr_agencies_locations[$location_to_id]) ? htmlentities($arr_agencies_locations[$location_to_id]) : '').'"},
						{v:"'.(isset($arr_agencies[$car_agency_id]) ? htmlentities($arr_agencies[$car_agency_id]) : '').'"},
						{v:"'.htmlentities($result[0][$i]['vehicle_type_name']).'"},
						{v:"'.htmlentities($make.' '.$result[0][$i]['model'].' '.$result[0][$i]['registration_number']).'"},
						{v:"'.addslashes($result[0][$i]['additional_info']).'"}
					]}';
					echo (($i < $result[1]-1) ? ',' : '').$nl;
				}
			}
			
		echo '
			]};
			data = new google.visualization.DataTable(dataAsJson);
		  
			// Set paging configuration options
			// Note: these options are changed by the UI controls in the example.
			options["page"] = "enable";
			options["pageSize"] = 20;
			options["pagingSymbols"] = {prev: "prev", next: "next"};
			options["pagingButtonsConfiguration"] = "auto";
		  
			// Create and draw the visualization.
			visualization = new google.visualization.Table(document.getElementById("table"));
		  
			draw();
		}';
	}else{
		echo 'function drawVisualization() {} ';
	}
	
	echo '    
    function draw() {
		'.(($task == 'prepare_report' && $msg == '') ? 'visualization.draw(data, options);' : '').'  
    }    

    google.setOnLoadCallback(drawVisualization);

    // sets the number of pages according to the user selection.
    function setNumberOfPages(value){
		if(value){
			options["pageSize"] = parseInt(value, 10);
			options["page"] = "enable";
		}else{
			options["pageSize"] = null;
			options["page"] = null;  
		}
		draw();
    }
    </script>';

    if(!$error){
		echo '<div class="table-responsive" style="margin-bottom:10px; padding:5px; border:1px solid #cccccc;">
			<form name="frmReport" action="index.php?admin=mod_car_rental_reports" method="post" style="width:1000px;">
				'.draw_hidden_field('task', 'prepare_report', false).'
				'.draw_token_field(false).'			
				<table border="0">
				<tr>';				
					if($agencies_count > 1 && count($arr_agencies) > 0){
						echo '<td>';
						echo '<span style="font-size:12px;margin:0 5px;">'._CAR_AGENCY.':</span>';
						echo '<select class="mgrid_select" name="sel_car_agency">';
						echo '<option value="">-- '._ALL.' --</option>';
						foreach($arr_agencies as $key => $val){
							echo '<option value="'.$key.'" '.(($sel_car_agency == $key) ? 'selected="selected"' : '').'>'.$val.'</option>';
						}
						echo '</select>';
						echo '</td>';
					}
					
				echo '
					<td>
						<span style="font-size:12px;margin:0 5px;">'._TYPE.':</span>
						<select class="mgrid_select" name="sel_type">
							<option value="">-- '._SELECT.' --</option>
							<option value="picking_up" '.(($sel_type == 'picking_up') ? 'selected="selected"' : '').'>'._CAR_PICKING_UP.'</option>
							<option value="returning" '.(($sel_type == 'returning') ? 'selected="selected"' : '').'>'._CAR_RETURNING.'</option>
							<option value="in_rental" '.(($sel_type == 'in_rental') ? 'selected="selected"' : '').'>'._CARS_IN_RENTAL.'</option>
						</select>
					</td>
					<td>
						<span style="font-size:12px;margin:0 5px;">'._FROM.':</span>
						<input type="textbox" class="mgrid_text" size="9" readonly="readonly" name="date_from" id="date_from" value="'.$date_from.'" />
						<img id="date_from_cal" src="images/cal.gif" alt="" title="'._SET_DATE.'" style="margin-left:5px;margin-right:5px;cursor:pointer;" />
					</td>
					<td>
						<span style="font-size:12px;margin:0 5px;">'._TO.':</span>
						<input type="textbox" class="mgrid_text" size="9" readonly="readonly" name="date_to" id="date_to" value="'.$date_to.'" />
						<img id="date_to_cal" src="images/cal.gif" alt="" title="'._SET_DATE.'" style="margin-left:5px;margin-right:5px;cursor:pointer;" />
					</td>
					<td>
						<span style="font-size:12px;margin:0 7px;">'._ROWS.':</span>
						<select class="mgrid_select" onchange="setNumberOfPages(this.value)">
							<option value="">'._ALL.'</option>
							<option value="3">3</option>
							<option value="5">5</option>
							<option value="10">10</option>
							<option value="15">15</option>
							<option selected="selected" value="20">20</option>
							<option value="50">50</option>
							<option value="100">100</option>
						</select>
					</td>
					<td>
						<span style="margin:0 5px;"></span>
						'.(($task == 'prepare_report') ? '<input class="mgrid_button mgrid_button_cancel" type="button" name="btnReset" value="'._RESET.'" onclick="javascript:appGoTo(\'admin=mod_car_rental_reports\')" />' : '').'
						<input class="mgrid_button" type="submit" name="btnSubmit" value="'._SEARCH.'" />
					</td>
				</tr>
				</table>			
			</form>
		</div>
		<div id="table" style="width:100%"></div>
		
		<script type="text/javascript"> 
		Calendar.setup({firstDay : '.($objSettings->GetParameter('week_start_day')-1).', inputField : "date_from", ifFormat : "'.$calendar_date_format.'", showsTime : false, button : "date_from_cal"});
		Calendar.setup({firstDay : '.($objSettings->GetParameter('week_start_day')-1).', inputField : "date_to", ifFormat : "'.$calendar_date_format.'", showsTime : false, button : "date_to_cal"});
		</script>';
	}

	draw_content_end();
}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}

