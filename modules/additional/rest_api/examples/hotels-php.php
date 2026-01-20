<?php
/**
 * REST API module for uHotelBooking script
 *
 * @project uHotelBooking
 * @copyright (c) 2019 ApPHP
 * @author ApPHP <info@apphp.com>
 * @site http://www.hotel-booking-script.com
 * @license http://hotel-booking-script.com/license.php
 */	  

require('include/functions.php');
include('include/config.inc.php');

$arr_compares = array(
'cs' => '%like%',
'sw' => 'like%',
'ew' => '%like',
'eq' => '=',
'ne' => '<>',
'lt' => '<',
'le' => '<=',
'ge' => '>=',
'gt' => '>',
'in' => 'in',
'ni' => 'not in',
'is' => 'is',
'no' => 'is not',
);

$field_name = isset($_GET['field_name']) ? htmlentities($_GET['field_name']) : '';
$field_value = isset($_GET['field_value']) ? htmlentities($_GET['field_value']) : '';
$compare_type = isset($_GET['compare_type']) && isset($arr_compares[$_GET['compare_type']]) ? $_GET['compare_type'] : 'eq';
$method = isset($_POST['_method']) && in_array(strtolower($_POST['_method']), array('get', 'post', 'put', 'delete')) ? strtolower($_POST['_method']): 'get';

$dbug = null;
$dbugGet = null;
$reply = '';
$url = $site_url.'api.php/hotels';
$full_url = $url.'?api_key='.$api_key.'&api_token='.$api_token;
if(!empty($field_name)){
	$full_url .= '&filter[]='.$field_name.','.$compare_type.','.$field_value;
}

if($method == 'get'){
	$url = $full_url;
	$dbugGet =& $dbug;
}elseif(in_array($method, array('post', 'put', 'delete'))){
	$id = 0;
	$arr_data = $_POST;
	$arr_data['api_key'] = $api_key;
	$arr_data['api_token'] = $api_token;

	if(isset($arr_data['api_id'])){
		$id = $arr_data['api_id'];
		$url .= '/'.$id;
		unset($arr_data['api_id']);
	}
	unset($arr_data['_method']);


	$data = http_build_query($arr_data);
	$reply = call(strtoupper($method), $url, $data, $dbug);

	// If add new record
	if($method == 'post'){
		$id = (int)$reply;
	}elseif($method == 'delete'){
		$id = 0;
	}
}

$response = call('GET', $full_url, array(), $dbugGet);
$result = json_decode($response, true);
$dbug = (array)$dbug;

$columns = isset($result['hotels']['columns']) ? $result['hotels']['columns'] : null;
$records = isset($result['hotels']['records']) ? $result['hotels']['records'] : null;
$access  = isset($result['hotels']['api_access']) ? $result['hotels']['api_access'] : null;

$output = '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
	<meta name="description" content="">
	<meta name="author" content="">

	<title>uHotelBooking API Examples</title>

	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

	<!-- Include the jQuery library -->
	<script src="//code.jquery.com/jquery-1.11.3.min.js"></script>

	<script src="include/main.js"></script>
</head>
<body>

	<!-- Navigation -->
	<?php
		$page = 'examples-index.php';
		include('include/nav.php');
	?>

	<!-- Begin page content -->
	<div class="container">
		<div class="page-header"><h1>Hotels</h1></div>
		
		<h4>URL:</h4>
		<?= !empty($url) ? '<pre>'.$url.'</pre>' : ''; ?>
		<hr>
	<?php if($method != 'get'){ ?>
		<h4><?= strtoupper($method); ?>:</h4>
		<?= !empty($arr_data) ? '<pre>'.print_data($arr_data).'</pre>' : ''; ?>
		<hr>
		<h4>Response:</h4>
		<?= !empty($reply) ? '<pre>'.htmlentities($reply).'</pre>' : ''; ?>
		<hr>
	<?php }elseif(empty($result) || (isset($result['error']) && $result['error'])) { ?>
		<h4><?= !empty($result) ? 'Error Message' : 'Response'; ?>:</h4>
		<?= !empty($result) ? '<pre>'.$result['error_message'].'</pre>' : '<pre>'.htmlentities($response).'</pre>'; ?>
		<hr>
	<?php } ?>
	<?php if(!empty($dbug) && isset($dbug['sql'])) { ?>
		<h4>SQL (debug mode):</h4>
		<?= '<pre>'.htmlentities($dbug['sql']).'</pre>'; ?>
		<hr>
	<?php } ?>

	<?php if(!empty($result)){ ?>
		<h4>Go to Hotels Description<h4>
		<form action="hotels-description-php.php" method="get" id="go-hotel-description-form">
			<table class="table table-bordered" id="go-table">
			<tr>
				<td width="200px">
					<input type="text" name='hotel_id' id="hotel_id" class="form-control" value="<?= !empty($id) ? $id : ''; ?>" placeholder="Hotel ID">
				</td>
				<td colspan='2'>
					<button type="button" data-type="btn-go-hotel-description" class="btn btn-sm btn-primary">Go</button>
				</td>
			</tr>
			</table>
		</form>
		<h4>Search<h4>
		<form action="hotels-php.php" method="get">
		<table class="table table-bordered">
		<tbody>
		<tr>
			<td width="230px">
				<select name="field_name" class="form-control" style="width:210px">
					<option>-- field --</option>
					<?php
						foreach($columns as $column){
							echo '<option '.($field_name == $column ? 'selected="selected"' : '').' value="'.$column.'">'.humanize($column).'</option>';
						}
					?>
				</select>
			</td>
			<td width="90px">
				<select name="compare_type" class="form-control" style="width:100px">
					<option>-- field --</option>
					<?php
						foreach($arr_compares as $key => $value){
							echo '<option '.($compare_type == $key ? 'selected="selected"' : '').' value="'.$key.'">'.$value.'</option>';
						}
					?>
				</select>
			</td>
			<td width="200px">
				<input type="text" name="field_value" class="form-control" value="<?= $field_value; ?>" placeholder="">
			</td>
			<td>
				<a href="hotels-php.php" class="btn btn-sm btn-default">Clear</a>&nbsp;
				<button type="submit" class="btn btn-sm btn-primary">Search</button>
			</td>
		</tr>
		</tbody>
		</table>
		</form>
		<hr/>

		<?php if(isset($access['add']) && $access['add'] == 1){ ?>
		<h4>Add Record<h4>
		<small>
			* Addition of hotels is held in a two-stage:
			<ol>
				<li>Add a new entry in the table of hotels</li>
				<li>Adding new entries to the table hotels_description</li>
			</ol>
		</small>
		<form action="hotels-php.php" method="post" id="add-form">
		<input type="hidden" name="_method" value="post"/>
		<table class="table table-bordered" id="add-table">
		<tr>
			<td width="200px">
			</td>
			<td colspan='2'>
				<button type="button" data-type="add-btn-reset"  class="btn btn-sm btn-default">Reset</button>
				<button type="submit" data-type="add-btn-submit" class="btn btn-sm btn-primary">+ Add New Record</button>
				<button type="button" data-type="add-btn-add-field" class="btn btn-sm btn-success pull-right">Add Field</button>
			</td>
		</tr>
		<tr class="add-new-field" data-number="0">
			<td>
				<select class="all-fields form-control" data-type="select-field" data-number="0">
					<option>-- field --</option>
					<?php
						foreach($columns as $column){
							if($column == 'id') continue;
							echo '<option '.($field_name == $column ? 'selected="selected"' : '').' value="'.$column.'">'.humanize($column).'</option>';
						}
					?>
				</select>
			</td>
			<td>
				<input class="form-control" data-type="add-textbox-field" data-number="0" type="text" value="">
			</td>
			<td style="width:60px;">
				<button type="button" class="btn btn-sm btn-warning" data-type="add-btn-delete" data-number="0">Delete Field</button>
			</td>
		</tr>
		</table>
		</form>
		<hr/>

		<?php } ?>
		<?php if(isset($access['edit']) && $access['edit'] == 1 && !empty($records)){ ?>
		<h4>Edit Record<h4>
		<form action="hotels-php.php" method="post" id="edit-form">
		<input type="hidden" name="_method" value="put"/>
		<table class="table table-bordered" id="edit-table">
		<tr>
			<td width="200px">
				<input type="text" id="edit_input_id" class="form-control" value="" placeholder="ID">
				<input type="hidden" name="api_id" value=""/>
			</td>
			<td colspan='2'>
				<button type="button" data-type="edit-btn-set-id"    class="btn btn-sm btn-primary">Set ID</button>
				<button type="button" data-type="edit-btn-reset"     class="btn btn-sm btn-default" style="display:none;">Reset</button>
				<button type="submit" data-type="edit-btn-submit"    class="btn btn-sm btn-primary" style="display:none;">Update Record</button>
				<button type="button" data-type="edit-btn-add-field" class="btn btn-sm btn-success pull-right" style="display:none;">Add Field</button>
			</td>
		</tr>
		</table>
		</form>
		<hr/>
		<?php } ?>
		<?php if(isset($access['delete']) && $access['delete'] == 1 && !empty($records)){ ?>
		<h4>Delete Record<h4>
		<small>
			* Removal of hotels is held in a two-stage:
			<ol>
				<li>Delete all entries in the table hotels_description</li>
				<li>Deleting an entry in the table of hotels</li>
			</ol>
		</small>
		<form action="hotels-php.php" method="post" id="delete-form">
		<input type="hidden" name="_method" value="delete"/>
		<table class="table table-bordered" id="delete-table">
		<tr>
			<td width="200px">
				<input type="text" id="delete_input_id" class="form-control" value="" placeholder="ID">
				<input type="hidden" name="api_id" value=""/>
			</td>
			<td colspan='2'>
				<button type="button" data-type="delete-btn-submit" class="btn btn-sm btn-primary">Delete</button>
			</td>
		</tr>
		</table>
		</form>
		<?php } ?>
	<?php } ?>
		<?php
			//echo '<pre>';
			//print_r($result);
			//echo '</pre>';
		
			if(!empty($records)){

				$output .= '<div class="table-responsive" style="font-size:14px">';
				$output .= '<table class="table table-bordered">';
				$output .= '<thead>';
				$output .= '<tr>';
				foreach($columns as $column){
					if($column == 'map_code') continue;
					$output .= '<th><nobr>'.humanize($column).'</nobr></th>';
				}
				$output .= '</tr>';
				$output .= '</thead>';

				$output .= '<tbody>';
				if(!empty($records)){
					foreach($records as $fields){
						$output .= '<tr>';
						foreach($fields as $key => $field){
							if($key == '8') continue;
							$output .= '<td>'.$field.'</td>';
						}
						$output .= '</tr>';
					}
				}
				$output .= '</tbody>';

				$output .= '</table>';
				$output .= '</div>';
				
			}else{
				$output .= '<div class="alert alert-info text-center">No records found!</div>';
			}
			
			echo $output;
		?>
		<p><br></p>
		<p><br></p>

	</div>

	<script>
		var all_info = <?= (json_decode($response) !== null) ? $response : 'null'; ?>;
		var url_hd = <?= json_encode($site_url.'api.php/hotels_description?api_key='.$api_key.'&api_token='.$api_token); ?>;

		$(document).ready(function(){
			if(all_info !== null){
				init(all_info.hotels, ['id']);
			}
			// Remove event handler
			$('[data-type=delete-btn-submit]').unbind('click');

			// Add new event handler
			$('[data-type=delete-btn-submit]').click(function(){
				var idExists = false, tmpID = '', editID = $('#delete_input_id').val();
				for(i = 0, maxCount = all_info.hotels.records.length; i < maxCount; i++){
					tmpID = table_info.records[i][keyID];
					if(tmpID == editID){
						idExists = true;

						$.ajax({
							url: url_hd + "&filter[]=hotel_id,eq," + editID,
							global: false,
							type: "GET",
							dataType: "html",
							async:true,
							error: function(html){
								alert("AJAX: cannot connect to the server or server response error! Please try again later.");
							},
							success: function(html){
								var obj = jQuery.parseJSON(html);
								if(obj.hotels_description !== undefined && obj.hotels_description.records.length > 0){
									alert('Not all recording for this hotel in the table hotels_description removed');
								}else{
									if(confirm('Are you sure you want to delete this record?')){
										$('#delete-table input[name=api_id]').val(editID);
										$('#delete-form').submit();
									}
								}
							}
						});	
					}
				}

				if(idExists == false){
					alert('This ID does not exist');
				}
			});

			/*** HOTELS DESCRIPTION ***/
			$('[data-type=btn-go-hotel-description]').click(function(){
				var idExists = false, tmpID = '', hotelID = $('#hotel_id').val();
				
				for(i = 0, countHotels = all_info.hotels.records.length; i < countHotels; i++){
					// keyID in file include/main.js
					tmpID = all_info.hotels.records[i][keyID];
					if(tmpID == hotelID){
						idExists = true;
						$('#go-hotel-description-form').submit();
					}
				}

				if(idExists == false){
					alert('This ID does not exist');
				}
			});
		})
	</script>
</body>
</html>
