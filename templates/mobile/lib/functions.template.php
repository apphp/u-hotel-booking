<?php

/**
 * Draws homepage navbar
 * @return HTML
 */
function draw_homepage_navbar()
{
	$output = '<div class="navbar">
		<div class="navbar-inner">
			<div class="left">
				<a href="#" class="link icon-only panel-open" data-panel="left">
					<i class="icon f7-icons ios-only">menu</i>
					<i class="icon material-icons md-only">menu</i>
				</a>
			</div>
			<div class="title sliding"><a href="/home/" class="panel-close">'.MOBILE_APP_NAME_SHORT.'</a></div>
			<div class="right">
	            <a href="#" class="link popover-open" data-popover=".popover-links">
					<i class="f7-icons">more_vertical_fill</i>
				</a>
			</div>
		</div>
	</div>
	
	<div class="popover popover-links">
    <div class="popover-inner">
      <div class="list">
        <ul>
        	<!--<li><a href="#" class="list-button actions-open" data-actions=".my-langs">'._LANGUAGES.'</a></li>-->
          	<li><a class="menu-item-languages list-button item-link" href="#">'._LANGUAGES.'</a></li>
          	<li><a class="menu-item-currencies list-button item-link" href="#">'._CURRENCIES.'</a></li>
        </ul>
      </div>
    </div>
    </div>    
    ';
	
//<div class="right">
//	<a class="link icon-only searchbar-enable" data-searchbar=".searchbar-components">
//		<i class="icon f7-icons ios-only">search_strong</i>
//		<i class="icon material-icons md-only">search</i>
//	</a>
//</div>
//<form data-search-container=".components-list" data-search-in="a"
//	  class="searchbar searchbar-expandable searchbar-components searchbar-init">
//	<div class="searchbar-inner">
//		<div class="searchbar-input-wrap">
//			<input type="search" placeholder="'._SEARCH_KEYWORDS.'"/>
//			<i class="searchbar-icon"></i>
//			<span class="input-clear-button"></span>
//		</div>
//		<span class="searchbar-disable-button">'._BUTTON_CANCEL.'</span>
//	</div>
//</form>
	
	return $output;
}

/**
 * Draws page navbar
 * @param string $title
 * @return HTML
 */
function draw_page_navbar($title = '')
{
	$output = '<div class="navbar">
        <div class="navbar-inner sliding">
            <div class="left">
                <a href="#" class="link back">
                    <i class="icon icon-back"></i>
                    <span class="ios-only">'._BUTTON_BACK.'</span>
                </a>
            </div>
            <div class="title">'.$title.'</div>
			<div class="right">
				<a href="#" class="link icon-only panel-open" data-panel="left">
					<i class="icon material-icons">menu</i>
				</a>
			</div>
        </div>
    </div>';
	
	return $output;
}


/**
 * Draws page content beginning part
 * @param  string $page_url
 * @param  string $page_url_param
 * @return HTML
 */
function draw_page_content_start($page_url = '', $page_url_param = '')
{
	$output = '<!-- Page content must have additional "ptr-content" class -->
	<div class="page-content ptr-content" data-ptr-url="'.$page_url.'" data-ptr-url-param="'.$page_url_param.'" data-ptr-distance="55">
		<!-- Default pull to refresh pre-loader-->
			<div class="ptr-preloader">
			<div class="preloader"></div>
			<div class="ptr-arrow"></div>
		</div>
		<div class="partial-content">';

	return $output;
}

/**
 * Draws page content ending part
 * @return HTML
 */
function draw_page_content_end()
{
	$output = '</div><!--partial-content-->
	</div><!--page-content-->';

	return $output;
}
