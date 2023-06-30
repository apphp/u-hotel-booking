<?php
/**
* @project uHotelBooking
* @copyright (c) 2019 ApPHP
* @author ApPHP <info@apphp.com>
* @site https://www.hotel-booking-script.com
* @license http://hotel-booking-script.com/license.php
*/

// *** Make sure the file isn't accessed directly
defined('APPHP_EXEC') or die('Restricted Access');
//--------------------------------------------------------------------------

if(Modules::IsModuleInstalled('booking') &&
  (ModulesSettings::Get('booking', 'is_active') != 'no') && 
  ($objLogin->IsLoggedInAs('owner','mainadmin', 'admin') || ($objLogin->IsLoggedInAs('hotelowner') && $objLogin->HasPrivileges('view_hotel_reports')))
){	

	$task = isset($_POST['task']) ? prepare_input($_POST['task']) : '';
	$sel_type = isset($_POST['sel_type']) ? prepare_input($_POST['sel_type']) : '';
	$sel_hotel = isset($_POST['sel_hotel']) ? prepare_input($_POST['sel_hotel']) : '';
	$date_from = isset($_POST['date_from']) ? prepare_input($_POST['date_from']) : '';
	$date_to = isset($_POST['date_to']) ? prepare_input($_POST['date_to']) : '';
	$msg = '';
	$where_clause = '';
	$date_from_sql = '';
	$date_to_sql = '';
	$nl = "\n";
	$error = false;

	$arr_hotels = array();
	$hotels_list = '';
	$hotels_count = Hotels::HotelsCount();
	if($objLogin->IsLoggedInAs('hotelowner')){
		$hotels_list = implode(',', $objLogin->AssignedToHotels());
		if(!empty($hotels_list)){
			$total_hotels = Hotels::GetAllHotels(TABLE_HOTELS.'.id IN ('.$hotels_list.')');
			foreach($total_hotels[0] as $key => $val) $arr_hotels[$val['id']] = $val['name'].($val['is_active'] == 0 ? ' ('._NOT_ACTIVE.')' : '');
		}else{
			$error = true;
			$msg = '<br>'.draw_important_message(_OWNER_NOT_ASSIGNED, false);
		}
	}else{
		if($hotels_count > 1){
			$total_hotels = Hotels::GetAllHotels();
			foreach($total_hotels[0] as $key => $val) $arr_hotels[$val['id']] = $val['name'].($val['is_active'] == 0 ? ' ('._NOT_ACTIVE.')' : '');
		}		
	}

	// Fix wrong dates
	if($date_from > $date_to){
		$date_to = $date_from;
	}

	if($objSettings->GetParameter('date_format') == 'mm/dd/yyyy'){
		$calendar_date_format = '%m-%d-%Y';
		if($date_from == '') $date_from = date('m-01-Y');
		if($date_to == '') $date_to = date('m-d-Y');
		$date_from_sql = date('Y-m-d', mktime(0, 0, 0, substr($date_from, 0, 2), substr($date_from, 3, 2), substr($date_from, 6, 4)));
		$date_to_sql   = date('Y-m-d', mktime(0, 0, 0, substr($date_to, 0, 2), substr($date_to, 3, 2), substr($date_to, 6, 4)));
	}else{
		$calendar_date_format = '%d-%m-%Y';
		if($date_from == '') $date_from = date('01-m-Y');
		if($date_to == '') $date_to = date('d-m-Y');
		$date_from_sql = date('Y-m-d', mktime(0, 0, 0, substr($date_from, 3, 2), substr($date_from, 0, 2), substr($date_from, 6, 4)));
		$date_to_sql   = date('Y-m-d', mktime(0, 0, 0, substr($date_to, 3, 2), substr($date_to, 0, 2), substr($date_to, 6, 4)));
	}
	
	if($task == 'prepare_report'){		
		if($sel_type == ''){
			$msg = draw_important_message(_SELECT_REPORT_ALERT, false);
		}elseif($sel_type == 'arriving' && $date_from == ''){
			$msg = draw_important_message(str_replace('_FIELD_', '\''._FROM.'\'', _FIELD_CANNOT_BE_EMPTY), false);
		}elseif($sel_type == 'departing' && $date_to == ''){
			$msg = draw_important_message(str_replace('_FIELD_', '\''._TO.'\'', _FIELD_CANNOT_BE_EMPTY), false);
		}elseif($sel_type == 'staying' && ($date_from == '' || $date_to == '')){
			$msg = draw_important_message(_DATE_EMPTY_ALERT, false);
		}

		if($msg == ''){
			if($sel_type == 'arriving'){
				$where_clause .= ' AND br.checkin >= \''.$date_from_sql.'\''.(($date_to != '') ? ' AND br.checkin <= \''.$date_to_sql.'\'' : '');
			}elseif($sel_type == 'departing'){
				$where_clause .= ' AND br.checkout >= \''.$date_from_sql.'\''.(($date_to != '') ? ' AND br.checkout <= \''.$date_to_sql.'\'' : '');
			}elseif($sel_type == 'staying'){
				$where_clause .= ' AND br.checkin <= \''.$date_from_sql.'\' AND br.checkout >= \''.$date_to_sql.'\'';
			}
			
			if($sel_hotel != ''){
				$where_clause .= ' AND r.hotel_id = '.(int)$sel_hotel;
			}
			
			// Show only hotels related to current hotel owner
			if($objLogin->IsLoggedInAs('hotelowner')){
				$where_clause .= ' AND r.hotel_id IN ('.$hotels_list.')';
			}
			
			$sql = 'SELECT
						b.booking_number,
						b.additional_info,
						IF(
							b.is_admin_reservation = 1,
							"Admin",
							CONCAT(c.first_name, " ", c.last_name)
						) as full_name,
						br.checkin,
						br.checkout,
						br.room_numbers,
						r.room_type
					FROM '.TABLE_BOOKINGS.' b
						INNER JOIN '.TABLE_BOOKINGS_ROOMS.' br ON b.booking_number = br.booking_number
						INNER JOIN '.TABLE_ROOMS.' r ON br.room_id = r.id
						LEFT OUTER JOIN '.TABLE_CUSTOMERS.' c ON b.customer_id = c.id					
					WHERE 1=1
						'.$where_clause;
			$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
			if($result[1] <= 0) $msg = draw_important_message(_NO_RECORDS_FOUND, false);
		}
	}
	
	// Start main content
	draw_title_bar(
		prepare_breadcrumbs(array(_BOOKINGS=>'',_INFO_AND_STATISTICS=>'',_REPORTS=>'')),
		(($task == 'prepare_report' && $msg == '') ? '<a href="javascript:window.print();"><img src="images/printer.png" alt="print" /> '._PRINT.'</a>' : '')
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
			{id:"B",label:"'._BOOKING_NUMBER.'",type:"string"},
			{id:"C",label:"'._CHECK_IN.'",type:"string"},
			{id:"D",label:"'._CHECK_OUT.'",type:"string"},
			{id:"E",label:"'._ROOM_TYPE.'",type:"string"},
			{id:"F",label:"'._ROOM_NUMBERS.'",type:"string"},
			{id:"G",label:"'._ADDITIONAL_INFO.'",type:"string"}],
		  rows:[ ';

            if(isset($result[1])){ 
				for($i = 0; $i < $result[1]; $i++){
					echo '{c:[
						{v:"'.addslashes($result[0][$i]['full_name']).'"},
						{v:"'.$result[0][$i]['booking_number'].'"},
						{v:"'.$result[0][$i]['checkin'].'"},
						{v:"'.$result[0][$i]['checkout'].'"},
						{v:"'.$result[0][$i]['room_type'].'"},
						{v:"'.addslashes($result[0][$i]['room_numbers']).'"},
						{v:"'.addslashes(str_replace(array("\r\n"), ' ', $result[0][$i]['additional_info'])).'"}
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
			<form name="frmReport" action="index.php?admin=mod_booking_reports" method="post" style="width:1000px;">
				'.draw_hidden_field('task', 'prepare_report', false).'
				'.draw_token_field(false).'			
				<table border="0">
				<tr>';				
					if($hotels_count > 1 && count($arr_hotels) > 0){
						echo '<td>';
						echo '<span style="font-size:12px;margin:0 5px;">'.(FLATS_INSTEAD_OF_HOTELS ? _FLAT : _HOTEL).':</span>';
						echo '<select class="mgrid_select chosen_select" name="sel_hotel">';
						echo '<option value="">-- '._ALL.' --</option>';
						foreach($arr_hotels as $key => $val){
							echo '<option value="'.$key.'" '.(($sel_hotel == $key) ? 'selected="selected"' : '').'>'.$val.'</option>';
						}
						echo '</select>';
						echo '</td>';
					}
					
				echo '
					<td>
						<span style="font-size:12px;margin:0 5px;">'._TYPE.':</span>
						<select class="mgrid_select" name="sel_type">
							<option value="">-- '._SELECT.' --</option>
							<option value="arriving" '.(($sel_type == 'arriving') ? 'selected="selected"' : '').'>'._PEOPLE_ARRIVING.'</option>
							<option value="departing" '.(($sel_type == 'departing') ? 'selected="selected"' : '').'>'._PEOPLE_DEPARTING.'</option>
							<option value="staying" '.(($sel_type == 'staying') ? 'selected="selected"' : '').'>'._PEOPLE_STAYING.'</option>
						</select>
					</td>
					<td>
						<span style="font-size:12px;margin:0 5px;">'._FROM.':</span>
						<input type="text" class="mgrid_text" size="9" readonly="readonly" name="date_from" id="date_from" value="'.$date_from.'" />
						<img id="date_from_cal" src="images/cal.gif" alt="" title="'._SET_DATE.'" style="margin-left:5px;margin-right:5px;cursor:pointer;" />
					</td>
					<td>
						<span style="font-size:12px;margin:0 5px;">'._TO.':</span>
						<input type="text" class="mgrid_text" size="9" readonly="readonly" name="date_to" id="date_to" value="'.$date_to.'" />
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
						'.(($task == 'prepare_report') ? '<input class="mgrid_button mgrid_button_cancel" type="button" name="btnReset" value="'._RESET.'" onclick="javascript:appGoTo(\'admin=mod_booking_reports\')" />' : '').'
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

