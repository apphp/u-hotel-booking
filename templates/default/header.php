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

?>

<!-- Top wrapper -->			  
<div class="navbar-wrapper2 navbar-fixed-top _debug">
<div class="container">
<div class="navbar mtnav">

	<?php if($objLogin->IsLoggedInAsAdmin()){ ?>
		<a class="back-to" href="index.php?preview=no"><?= _BACK_TO_ADMIN_PANEL; ?></a>
	<?php } ?>

    <div class="container offset-3">
        <!-- Navigation-->
        <div class="navbar-header">
            <button data-target=".navbar-collapse" data-toggle="collapse" class="navbar-toggle" type="button">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a href="index.php" class="navbar-brand">
                <div class="navbar-brand-text"><?= $objSiteDescription->DrawHeader('header_text'); ?></div>
                <!--<img src="<?php //echo APPHP_BASE; ?>templates/<?php //echo Application::Get('template');?>/images/logo.png" alt="Logo" class="logo"/>-->
            </a>
        </div>

        <div class="navbar-collapse collapse">

            <?php if(!$objLogin->IsLoggedInAsAdmin()){ ?>			
            <ul class="nav navbar-nav navbar-right">
                <!-- language -->
                <li style="padding-top:8px;padding-left:12px;display:inline-block;">
                    <?php				
                        $objLang  = new Languages();				
                        if($objLang->GetLanguagesCount('front-end') > 1){
                            echo '<div style="margin-top:5px;float:left;">';
                            $objLang->DrawLanguagesBar();
                            echo '</div>';
                        }					
                    ?>		
                </li>		
                <!-- currencies -->
                <li style="padding-top:7px;">
                    <?= Currencies::GetCurrenciesDDL(true, false, array('select_class'=>'form-control mySelectBoxClass')); ?>
                </li>
            </ul>
            <?php } ?>            

            <?php 
                // Draw header menu
                Menu::DrawHeaderMenu(true, array('wrapper_class'=>'nav navbar-nav navbar-right'));
            ?>		  
        </div>
    </div>    
</div>
</div>
</div>
<!-- /Top wrapper -->	

