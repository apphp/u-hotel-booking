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

	// Prepare Main Content
	ob_start();
	if(Application::Get('page') != '' && Application::Get('page') != 'home'){							
		if(file_exists('page/'.Application::Get('page').'.php')){	 
			include_once('page/'.Application::Get('page').'.php');
		}else{
			include_once('page/404.php');
		}
	}elseif(Application::Get('customer') != ''){					
		if(Modules::IsModuleInstalled('customers') && file_exists('customer/'.Application::Get('customer').'.php')){	
			include_once('customer/'.Application::Get('customer').'.php');
		}else{
			include_once('customer/404.php');
		}
	}elseif((Application::Get('admin') != '') && file_exists('admin/'.Application::Get('admin').'.php')){
		include_once('admin/'.Application::Get('admin').'.php');
	}else{
		if(Application::Get('template') == 'admin'){
			include_once('admin/home.php');
		}else{
			include_once('page/pages.php');										
		}
	}
	$main_content = ob_get_contents();
	ob_end_clean();

?>
        
<!-- CONTENT -->
<div class="container">
    <div class="container pagecontainer offset-0">	

        <!-- FILTERS -->
        <div class="col-md-3 filters offset-0">

			<?php if(!empty($cars_count['cars'])){
				$currency_rate = Application::Get('currency_rate');
				$currency_format = get_currency_format();
			?>
				<div class="filtertip">
					<div class="padding20">
						<p class="size14"><?= _FOUND_AGENCIES; ?>: <b><?= $cars_count['agencies'];?></b></p>
						<p class="size14"><?= _TOTAL_CARS; ?>: <b><?= $cars_count['cars'];?></b></p>					
						<p class="size22 bold"><span class="size14 normal darkblue"><?= _STARTING;?></span> <span class="countprice"><?= Currencies::PriceFormat($cars_count['min_price_per_day'] * $currency_rate, '', '', $currency_format);?></span> <span class="size14 normal darkblue">/<?= _DAY;?></span></p>
					</div>
					<div style="bottom: -9px;" class="tip-arrow"></div>
				</div>
			<?php } ?>
		
            <div class="hpadding20">
                <!-- LEFT COLUMN -->
                <?php
                    // Draw menu tree
                    echo Menu::DrawMenu('left', false, array('reservation'=>'cars'));
                ?>                            
                <!-- END OF LEFT COLUMN -->
            </div>
            <!-- END OF BOOK FILTERS -->	

            <div class="clearfix"></div>
            <br/><br/>
            
		</div>
		<!-- END OF FILTERS -->

        <!-- LIST CONTENT-->
		<?php
			$make_sort = !empty($_POST['sort_make']) && in_array(strtolower($_POST['sort_make']), array('asc', 'desc')) ? strtolower($_POST['sort_make']) : '';
			$price_sort = !empty($_POST['sort_price']) && in_array(strtolower($_POST['sort_price']), array('asc', 'desc')) ? strtolower($_POST['sort_price']) : '';
			$view_type = !empty($_POST['view_type']) && in_array(strtolower($_POST['view_type']), array('list', 'grid')) ? strtolower($_POST['view_type']) : 'list';
		?>

        <div class="rightcontent col-md-9 offset-0">
	        <?php if(Application::Get('page') == 'check_cars_availability' && !empty($search_result)){ ?>
				<div class="hpadding20">
					<!-- Top filters -->
					<div style="opacity: 1;" class="topsortby">
						<div class="col-md-4 offset-0">
							<div class="left wh30percent mt7"><b><?= _SORT_BY; ?>:</b></div>
							<div class="left wh70percent">
								<select class="my-form-control sort-manufacturer">
									<option <?= empty($make_sort) ? ' selected="selected"' : ''; ?> value=""><?= _MAKE; ?></option>
									<option <?= $make_sort == 'asc' ? ' selected="selected"' : ''; ?> value="asc"><?= _ASCENDING; ?></option>
									<option <?= $make_sort == 'desc' ? ' selected="selected"' : ''; ?> value="desc"><?= _DESCENDING; ?></option>
								</select>
							</div>
						</div>
						<div class="col-md-4">
							<div class="right wh90percent">
								<select class="my-form-control sort-price">
									<option <?= empty($price_sort) ? ' selected="selected"' : ''; ?> value=""><?= _PRICE; ?></option>
									<option <?= $price_sort == 'asc' ? ' selected="selected"' : ''; ?> value="asc"><?= _LOWEST_HIGHEST; ?></option>
									<option <?= $price_sort == 'desc' ? ' selected="selected"' : ''; ?> value="desc"><?= _HIGHEST_LOWEST; ?></option>
								</select>
							</div>
						</div>
						<div class="col-md-4 offset-0">
							<div class="right">
								<button class="listbtn<?= $view_type == 'list' ? ' active' : ''; ?>">&nbsp;</button>
								<button class="gridbtn<?= $view_type == 'grid' ? ' active' : ''; ?>">&nbsp;</button>
							</div>
						</div>
					</div>
					<!-- End of topfilters-->
				</div>
			<?php } ?>
			<div class="hpadding20">
			<script>
				$(document).ready(function(){
					$("div.topsortby button.listbtn").click(function(){
						if(!$(this).hasClass("active")){
							$("#view_type").val('list');
							$("#cars-reservation-form").submit();
						}
					});
	
					$("div.topsortby button.gridbtn").click(function(){
						if(!$(this).hasClass("active")){
							$("#view_type").val('grid');
							$("#cars-reservation-form").submit();
						}
					});
					$("div.topsortby select.sort-manufacturer").change(function(){
						var make_sort = $("div.topsortby select.sort-manufacturer option:selected").val();
						$("#make_id").val(make_sort);
						$("#cars-reservation-form").submit();
					});
	
					$("div.topsortby select.sort-price").change(function(){
						var price_sort = $("div.topsortby select.sort-price option:selected").val();
						$("#sort_price").val(price_sort);
						$("#cars-reservation-form").submit();
					});
				});
			</script>
			<!-- MAIN CONTENT -->
			<?php
				// Print Main Content
				echo $main_content;
			?>
			</div>
		</div>
		<!-- END OF LIST CONTENT-->    

    </div>
</div>
<!-- END OF CONTENT -->

