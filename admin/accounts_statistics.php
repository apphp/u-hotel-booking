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

if($objLogin->IsLoggedInAs('owner','mainadmin') && Modules::IsModuleInstalled('customers')){
	
    define ('TABS_DIR', 'modules/tabs/');
    require_once(TABS_DIR.'tabs.class.php');
	
	$first_tab_content 	= '';
	$second_tab_content = '';
	$third_tab_content 	= '';	
	$tabid 				= isset($_POST['tabid']) ? prepare_input($_POST['tabid']) : '1_1';
	$nl = "\n";
	
	$chart_type = isset($_POST['chart_type']) ? prepare_input($_POST['chart_type']) : 'columnchart';
	$year 		= isset($_POST['year']) ? prepare_input($_POST['year']) : date('Y');
	$google_map_api_key = $objSettings->GetParameter('google_api');

	if($tabid == '1_1'){		
		echo '<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>';
	}else{
		echo '<script type="text/javascript" src="https://www.google.com/jsapi"></script>';	
	}

	$total_countries = Countries::GetAllCountries();
	$arr_countries = array();
	foreach($total_countries[0] as $key => $val){
		$arr_countries[$val['abbrv']] = $val['name'];
	}

	if($tabid == '1_1'){		
 		$sql = 'SELECT COUNT(*) as cnt, b_country
				FROM '.TABLE_CUSTOMERS.'
				GROUP BY b_country';
				
		$result = database_query($sql, DATA_AND_ROWS, ALL_ROWS);
		
		$first_tab_content_1 = '';
		$first_tab_content = '<script type="text/javascript">
			google.charts.load("current", {
				"packages":["geochart"],
				// Note: you will need to get a mapsApiKey for your project.
				// See: https://developers.google.com/chart/interactive/docs/basic_load_libs#load-settings
				"mapsApiKey": "'.$google_map_api_key.'"
			});
			google.charts.setOnLoadCallback(drawRegionsMap);
			
			function drawRegionsMap() {
				var data = google.visualization.arrayToDataTable([';
				
				$first_tab_content .= $nl." ['"._COUNTRY."', '"._CUSTOMERS."'],";
				if($result[1] > 0){
					for($i=0; $i < $result[1]; $i++){
						$abbrev = isset($result[0][$i]['b_country']) ? $result[0][$i]['b_country'] : '';
						$country_name = isset($arr_countries[$abbrev]) ? encode_text($arr_countries[$abbrev]) : '';
						$first_tab_content .= $nl." ['".$country_name."', ".(int)$result[0][$i]['cnt']."],";
					}
				}
				$first_tab_content .= $nl.' ]);';
				
		$first_tab_content .= '	
				var options = {"legend":true};				
				var chart = new google.visualization.GeoChart(document.getElementById("map_canvas"));
				chart.draw(data, options);
			};
			</script>
			<div id="map_canvas" style="width:600px;padding:1px 10px 1px 10px;"></div>		
		';
		
	}elseif($tabid == '1_2') {		
		
		$second_tab_content = '
			<script type="text/javascript">
				function drawVisualization(){
				// Create and populate the data table.
				var data = new google.visualization.DataTable();
				data.addColumn("string", "'._MONTH.'");
				data.addColumn("number", "'._REGISTRATIONS.'");';
				
				$selStatType = 'COUNT(*)';
				$join_clause = '';
				$where_clause = ' ';

				$sql = 'SELECT
				  (SELECT '.$selStatType.' FROM '.TABLE_CUSTOMERS.' u '.$join_clause.' WHERE SUBSTRING(u.date_created, 6, 2) = \'01\' AND SUBSTRING(u.date_created, 1, 4) = '.(int)$year.' '.$where_clause.') as month1,
				  (SELECT '.$selStatType.' FROM '.TABLE_CUSTOMERS.' u '.$join_clause.' WHERE SUBSTRING(u.date_created, 6, 2) = \'02\' AND SUBSTRING(u.date_created, 1, 4) = '.(int)$year.' '.$where_clause.') as month2,
				  (SELECT '.$selStatType.' FROM '.TABLE_CUSTOMERS.' u '.$join_clause.' WHERE SUBSTRING(u.date_created, 6, 2) = \'03\' AND SUBSTRING(u.date_created, 1, 4) = '.(int)$year.' '.$where_clause.') as month3,
				  (SELECT '.$selStatType.' FROM '.TABLE_CUSTOMERS.' u '.$join_clause.' WHERE SUBSTRING(u.date_created, 6, 2) = \'04\' AND SUBSTRING(u.date_created, 1, 4) = '.(int)$year.' '.$where_clause.') as month4,
				  (SELECT '.$selStatType.' FROM '.TABLE_CUSTOMERS.' u '.$join_clause.' WHERE SUBSTRING(u.date_created, 6, 2) = \'05\' AND SUBSTRING(u.date_created, 1, 4) = '.(int)$year.' '.$where_clause.') as month5,
				  (SELECT '.$selStatType.' FROM '.TABLE_CUSTOMERS.' u '.$join_clause.' WHERE SUBSTRING(u.date_created, 6, 2) = \'06\' AND SUBSTRING(u.date_created, 1, 4) = '.(int)$year.' '.$where_clause.') as month6,
				  (SELECT '.$selStatType.' FROM '.TABLE_CUSTOMERS.' u '.$join_clause.' WHERE SUBSTRING(u.date_created, 6, 2) = \'07\' AND SUBSTRING(u.date_created, 1, 4) = '.(int)$year.' '.$where_clause.') as month7,
				  (SELECT '.$selStatType.' FROM '.TABLE_CUSTOMERS.' u '.$join_clause.' WHERE SUBSTRING(u.date_created, 6, 2) = \'08\' AND SUBSTRING(u.date_created, 1, 4) = '.(int)$year.' '.$where_clause.') as month8,
				  (SELECT '.$selStatType.' FROM '.TABLE_CUSTOMERS.' u '.$join_clause.' WHERE SUBSTRING(u.date_created, 6, 2) = \'09\' AND SUBSTRING(u.date_created, 1, 4) = '.(int)$year.' '.$where_clause.') as month9,
				  (SELECT '.$selStatType.' FROM '.TABLE_CUSTOMERS.' u '.$join_clause.' WHERE SUBSTRING(u.date_created, 6, 2) = \'10\' AND SUBSTRING(u.date_created, 1, 4) = '.(int)$year.' '.$where_clause.') as month10,
				  (SELECT '.$selStatType.' FROM '.TABLE_CUSTOMERS.' u '.$join_clause.' WHERE SUBSTRING(u.date_created, 6, 2) = \'11\' AND SUBSTRING(u.date_created, 1, 4) = '.(int)$year.' '.$where_clause.') as month11,
				  (SELECT '.$selStatType.' FROM '.TABLE_CUSTOMERS.' u '.$join_clause.' WHERE SUBSTRING(u.date_created, 6, 2) = \'12\' AND SUBSTRING(u.date_created, 1, 4) = '.(int)$year.' '.$where_clause.') as month12
				  FROM '.TABLE_CUSTOMERS;         
	
				$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
					
		$second_tab_content .= $nl.' data.addRows(12);';
		
		if($result[1] >= 0){
			$second_tab_content .= draw_set_values($result[0], $chart_type, _REGISTRATIONS);
		}
				 
		$second_tab_content .= ' } </script>';
		
		$second_tab_content .= '<script type="text/javascript">';
		$second_tab_content .= $nl.' google.load("visualization", "1", {packages: ["'.$chart_type.'"]});';
		$second_tab_content .= $nl.' google.setOnLoadCallback(drawVisualization);';
		$second_tab_content .= $nl.' function frmStatistics_Submit() { document.frmStatistics.submit(); }';
		$second_tab_content .= '</script>';	
		
		$second_tab_content .= get_chart_changer('1_2', $chart_type, $year);
		
		$second_tab_content .= '<div id="div_visualization" style="width:660px;height:310px;">
		<img src="images/ajax_loading.gif" style="margin:100px auto;" alt="" /></div>';
		
	}elseif($tabid == '1_3') {		
		
		$third_tab_content = '
			<script type="text/javascript">
				function drawVisualization(){
				// Create and populate the data table.
				var data = new google.visualization.DataTable();
				data.addColumn("string", "'._MONTH.'");
				data.addColumn("number", "'._REGISTRATIONS.'");';
				
				$selStatType = 'COUNT(*)';
				$join_clause = '';
				$where_clause = ' ';

				$sql = 'SELECT
				  (SELECT '.$selStatType.' FROM '.TABLE_CUSTOMERS.' u '.$join_clause.' WHERE SUBSTRING(u.date_lastlogin, 6, 2) = \'01\' AND SUBSTRING(u.date_lastlogin, 1, 4) = '.(int)$year.' '.$where_clause.') as month1,
				  (SELECT '.$selStatType.' FROM '.TABLE_CUSTOMERS.' u '.$join_clause.' WHERE SUBSTRING(u.date_lastlogin, 6, 2) = \'02\' AND SUBSTRING(u.date_lastlogin, 1, 4) = '.(int)$year.' '.$where_clause.') as month2,
				  (SELECT '.$selStatType.' FROM '.TABLE_CUSTOMERS.' u '.$join_clause.' WHERE SUBSTRING(u.date_lastlogin, 6, 2) = \'03\' AND SUBSTRING(u.date_lastlogin, 1, 4) = '.(int)$year.' '.$where_clause.') as month3,
				  (SELECT '.$selStatType.' FROM '.TABLE_CUSTOMERS.' u '.$join_clause.' WHERE SUBSTRING(u.date_lastlogin, 6, 2) = \'04\' AND SUBSTRING(u.date_lastlogin, 1, 4) = '.(int)$year.' '.$where_clause.') as month4,
				  (SELECT '.$selStatType.' FROM '.TABLE_CUSTOMERS.' u '.$join_clause.' WHERE SUBSTRING(u.date_lastlogin, 6, 2) = \'05\' AND SUBSTRING(u.date_lastlogin, 1, 4) = '.(int)$year.' '.$where_clause.') as month5,
				  (SELECT '.$selStatType.' FROM '.TABLE_CUSTOMERS.' u '.$join_clause.' WHERE SUBSTRING(u.date_lastlogin, 6, 2) = \'06\' AND SUBSTRING(u.date_lastlogin, 1, 4) = '.(int)$year.' '.$where_clause.') as month6,
				  (SELECT '.$selStatType.' FROM '.TABLE_CUSTOMERS.' u '.$join_clause.' WHERE SUBSTRING(u.date_lastlogin, 6, 2) = \'07\' AND SUBSTRING(u.date_lastlogin, 1, 4) = '.(int)$year.' '.$where_clause.') as month7,
				  (SELECT '.$selStatType.' FROM '.TABLE_CUSTOMERS.' u '.$join_clause.' WHERE SUBSTRING(u.date_lastlogin, 6, 2) = \'08\' AND SUBSTRING(u.date_lastlogin, 1, 4) = '.(int)$year.' '.$where_clause.') as month8,
				  (SELECT '.$selStatType.' FROM '.TABLE_CUSTOMERS.' u '.$join_clause.' WHERE SUBSTRING(u.date_lastlogin, 6, 2) = \'09\' AND SUBSTRING(u.date_lastlogin, 1, 4) = '.(int)$year.' '.$where_clause.') as month9,
				  (SELECT '.$selStatType.' FROM '.TABLE_CUSTOMERS.' u '.$join_clause.' WHERE SUBSTRING(u.date_lastlogin, 6, 2) = \'10\' AND SUBSTRING(u.date_lastlogin, 1, 4) = '.(int)$year.' '.$where_clause.') as month10,
				  (SELECT '.$selStatType.' FROM '.TABLE_CUSTOMERS.' u '.$join_clause.' WHERE SUBSTRING(u.date_lastlogin, 6, 2) = \'11\' AND SUBSTRING(u.date_lastlogin, 1, 4) = '.(int)$year.' '.$where_clause.') as month11,
				  (SELECT '.$selStatType.' FROM '.TABLE_CUSTOMERS.' u '.$join_clause.' WHERE SUBSTRING(u.date_lastlogin, 6, 2) = \'12\' AND SUBSTRING(u.date_lastlogin, 1, 4) = '.(int)$year.' '.$where_clause.') as month12
				  FROM '.TABLE_CUSTOMERS;         
	
				$result = database_query($sql, DATA_AND_ROWS, FIRST_ROW_ONLY);
		
		$third_tab_content .= $nl.' data.addRows(12);';
		
		if($result[1] >= 0){
			$third_tab_content .= draw_set_values($result[0], $chart_type, _LOGINS);
		}
				   
		$third_tab_content .= ' } </script>';

		$third_tab_content .= '<script type="text/javascript">';
		$third_tab_content .= $nl.' google.load("visualization", "1", {packages: ["'.$chart_type.'"]});';
		$third_tab_content .= $nl.' google.setOnLoadCallback(drawVisualization);';
		$third_tab_content .= $nl.' function frmStatistics_Submit() { document.frmStatistics.submit(); }';
		$third_tab_content .= '</script>';

		$third_tab_content .= get_chart_changer('1_3', $chart_type, $year);		
		
		$third_tab_content .= '<div id="div_visualization" style="width:660px;height:310px;">
		<img src="images/ajax_loading.gif" style="margin:100px auto;" alt="" /></div>';
	}
	

	$tabs = new Tabs(1, 'xp', TABS_DIR, '?admin=accounts_statistics');
	$tabs->SetToken(Application::Get('token'));
	//$tabs->SetHttpVars(array('admin'));
 
	$tab1=$tabs->AddTab(_CUSTOMERS.' ('._MAP_OVERLAY.')', $first_tab_content);
	$tab2=$tabs->AddTab(_CUSTOMERS.' ('._REGISTRATIONS.')', $second_tab_content);
	$tab3=$tabs->AddTab(_CUSTOMERS.' ('._LOGINS.')', $third_tab_content);
	 
	## +---------------------------------------------------------------------------+
	## | 2. Customizing:                                                           |
	## +---------------------------------------------------------------------------+
	## *** set container's width in pixels (px), inches (in) or points (pt)
	$tabs->SetWidth('100%');
 
	## *** set container's height in pixels (px), inches (in) or points (pt)
	$tabs->SetHeight('auto'); // 'auto'
 
	## *** set alignment inside the container (left, center or right)
	$tabs->SetAlign('left');
 
	## *** set container's color in RGB format or using standard names
	/// $tabs->SetContainerColor('#64C864');
	## *** set border's width in pixels (px), inches (in) or points (pt)
	/// $tabs->SetBorderWidth('5px');
	## *** set border's color in RGB format or using standard names
	/// $tabs->SetBorderColor('#64C864');
	/// $tabs->SetBorderColor('blue');
	/// $tabs->SetBorderColor('#445566');
	## *** show debug info - false|true
	$tabs->Debug(false);
	## *** allow refresh selected tabs - false|true
	/// $tabs->AllowRefreshSelectedTabs(true);
	## *** set form submission type: 'get' or 'post'
	$tabs->SetSubmissionType('post');

	draw_title_bar(prepare_breadcrumbs(array(_ACCOUNTS=>'', _STATISTICS=>'')));

	draw_content_start();	
	$tabs->Display();
	draw_content_end();

}else{
	draw_title_bar(_ADMIN);
	draw_important_message(_NOT_AUTHORIZED);
}

