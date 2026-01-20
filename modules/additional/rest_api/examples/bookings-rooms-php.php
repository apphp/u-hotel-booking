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

$error = 0;
$errorMessage = '';
$dbug = null;
$dbugGet = null;
$booking_number = isset($_GET['booking_number']) ? htmlentities($_GET['booking_number']) : '';
$field_name = isset($_GET['field_name']) ? htmlentities($_GET['field_name']) : '';
$field_value = isset($_GET['field_value']) ? htmlentities($_GET['field_value']) : '';
$compare_type = isset($_GET['compare_type']) && isset($arr_compares[$_GET['compare_type']]) ? $_GET['compare_type'] : 'eq';
$method = isset($_POST['_method']) && in_array(strtolower($_POST['_method']), array('get', 'post', 'put', 'delete')) ? strtolower($_POST['_method']): 'get';

if(empty($booking_number)){
	$error = 1;
	$errorMessage = 'Field Booking Number should not be empty';
}else{
	$url = $site_url.'api.php/bookings';
	$full_url = $url.'?api_key='.$api_key.'&api_token='.$api_token;
	$full_url .= '&filter[]=booking_number,eq,'.$booking_number;
	$response = call('GET', $full_url);
	$result = json_decode($response, true);

	$records = isset($result['bookings']['records']) ? $result['bookings']['records'] : null;
	if(empty($records)){
		$error = 1;
		$errorMessage = 'Booking with such number does not exist';
	}
}

if(!$error){
	$reply = '';
	$url = $site_url.'api.php/bookings_rooms';
	$full_url = $url.'?api_key='.$api_key.'&api_token='.$api_token;
	if(!empty($booking_number)){
		$full_url .= '&filter[]=booking_number,eq,'.$booking_number;
	}
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

	$columns = isset($result['bookings_rooms']['columns']) ? $result['bookings_rooms']['columns'] : null;
	$records = isset($result['bookings_rooms']['records']) ? $result['bookings_rooms']['records'] : null;
	$access  = isset($result['bookings_rooms']['api_access']) ? $result['bookings_rooms']['api_access'] : null;

	$output = '';

	// Prepare Language IDs
	$language_ids = array();
	if(!empty($records)){
		$col_lang_id = array_search('language_id', $columns);
		foreach($records as $one_record){
			$language_ids[] = $one_record[$col_lang_id];
		}
	}

	// Prepare Languages
	$url_lang = $site_url.'api.php/languages?api_key='.$api_key.'&api_token='.$api_token.'&filter[]=is_active,eq,1&transform=1';

	$response_lang = call('GET', $url_lang);
	$result_lang = json_decode($response_lang, true);

	$arr_lang = isset($result_lang['languages']) ? $result_lang['languages'] : null;
	//$records_lang = isset($result_lang['languages']['records']) ? $result_lang['languages']['records'] : null;
	$all_lang = array();
	foreach($arr_lang as $lang){
		if(!in_array($lang['abbreviation'], $language_ids)){
			$all_lang[$lang['abbreviation']] = $lang['lang_name_en'];
		}
	}
}
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
		<div class="page-header">
			<h1>Bookings Rooms</h1>
			<h3>Back to <a href='bookings-php.php'>Bookings</a><h3>
		</div>
		
	<?php if($error){ ?>
		<div class="alert alert-danger"><?= $errorMessage; ?></div>
	<?php }else{ ?>
		<h4>URL:</h4>
		<?php
			echo !empty($url) ? '<pre>'.$url.'</pre>' : '';
		?>
		<hr>
	<?php if($method != 'get'){ ?>
		<h4><?= strtoupper($method); ?>:</h4>
		<?php
			echo !empty($arr_data) ? '<pre>'.print_data($arr_data).'</pre>' : '';
		?>
		<hr>
		<h4>Response:</h4>
		<?php
			echo !empty($reply) ? '<pre>'.htmlentities($reply).'</pre>' : '';
		?>
		<hr>
	<?php }elseif(empty($result) || (isset($result['error']) && $result['error'])) { ?>
		<h4><?= !empty($result) ? 'Error Message' : 'Response'; ?>:</h4>
		<?php
			echo !empty($result) ? '<pre>'.$result['error_message'].'</pre>' : '<pre>'.htmlentities($response).'</pre>';
		?>
		<hr>
	<?php } ?>
	<?php if(!empty($dbug) && isset($dbug['sql'])) { ?>
		<h4>SQL (debug mode):</h4>
		<?php
			echo '<pre>'.htmlentities($dbug['sql']).'</pre>';
		?>
		<hr>
	<?php } ?>

		<?php if(!empty($result)){ ?>
		<h4>Search<h4>
		<form action="bookings-rooms-php.php?booking_number=<?= $booking_number; ?>" method="get">
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
				<a href="bookings-rooms-php.php" class="btn btn-sm btn-default">Clear</a>&nbsp;
				<button type="submit" class="btn btn-sm btn-primary">Search</button>
			</td>
		</tr>
		</tbody>
		</table>
		</form>
		<hr/>

		<?php if(isset($access['add']) && $access['add'] == 1 && !empty($all_lang)){ ?>
		<h4>Add Record<h4>
		<form action="bookings-rooms-php.php?booking_number=<?= $booking_number; ?>" method="post" id="add-form">
			<input type="hidden" name="_method" value="post"/>
			<input type="hidden" name="booking_number" value="<?= $booking_number; ?>"/>
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
			<!--tr>
				<td>
					<label class="form-control">Hotel Id</label>
				</td>
				<td>
					<input class="form-control" name="hotel_id" type="text" value="">
				</td>
				<td></td>
			</tr-->
			<tr class="add-new-field" data-number="0">
				<td>
					<select class="all-fields form-control" data-type="select-field" data-number="0">
						<option>-- field --</option>
						<?php
							foreach($columns as $column){
								if($column == 'booking_number' || $column == 'id') continue;
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
		<form action="bookings-rooms-php.php?booking_number=<?= $booking_number; ?>" method="post" id="edit-form">
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
		<h4>Delete Record</h4>
		<form action="bookings-rooms-php.php?booking_number=<?= $booking_number; ?>" method="post" id="delete-form">
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
					$output .= '<th><nobr>'.humanize($column).'</nobr></th>';
				}
				$output .= '</tr>';
				$output .= '</thead>';

				$output .= '<tbody>';
				foreach($records as $fields){
					$output .= '<tr>';
					foreach($fields as $key => $field){
						if($key == '19') continue;
						$output .= '<td>'.$field.'</td>';
					}
					$output .= '</tr>';
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
	<?php } ?>
	</div>
<?php if(!$error){ ?>
	<script>
		var all_info = <?= (json_decode($response) !== null) ? $response : 'null'; ?>;

		$(document).ready(function(){
			if(all_info !== null){
				init(all_info.bookings_rooms, ['id', 'booking_number']);
			}
		})
	</script>
<?php } ?>
</body>
</html>
