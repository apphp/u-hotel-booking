
var DEBUG = true;
var lang_buttons = [];
var curr_buttons = [];

// Theme
//var theme = 'auto';
// if (document.location.search.indexOf('theme=') >= 0) {
//   theme = document.location.search.split('theme=')[1].split('&')[0];
// }

// Dom7
var $$ = Dom7;


// Init App
var app = new Framework7({
    id: 'com.uhotelbooking.app',
    root: '#app',
    theme: "auto",
    pushState: true,
    panel: {
       swipe: 'left',
    },
    view: {
        // Allow to push pages to browser stack and use hardware button
        pushState: true,
    },

  // data: function () {
  //   return {
  //     user: {
  //       firstName: 'John',
  //       lastName: 'Doe',
  //     },
  //   };
  // },
	on: {
		init: function () {

			// Load languages
			loadLanguages(this);

			// Load currencies
			loadCurrencies(this);

			// -------------------------------------
		  	console.log('App initialized');
		},
		pageInit: function () {
		  	console.log('Page initialized');
		},
  	},
  	methods: {
		///helloWorld: function () {app.dialog.alert('Hello World!');},
	},
	routes: routes,
	  // vi: {
	  //     placementId: 'pltd4o7ibb9rc653x14',
	  // },
	}
);



// Option 1. Using one 'page:init' handler for all pages
$$(document).on('page:init', function (e) {
    debug("page:init");
    // Set pull to refresh for new page
    pullToRefresh();
    ///alert("aaa");
});



// Set pull to refresh for new page
pullToRefresh();




// ========================================
// ACTION SHEETS


///////////////////////////////////////////////////////////////
// FUNCTIONS
///////////////////////////////////////////////////////////////

/**
 * Display debug messages
 * @param string message
 */
function debug(message) {
    if (DEBUG) {
        console.log(message);
    }
}

/**
 * Returns file content
 * @param string url
 * @return mixed bool|array
 */
function get_file_content(url) {
	if(url == null || url == ''){
		debug("get_file_content(): wrong param - " + url);
		return false;
	}
}



/**
 * Load currencies
 */
function loadCurrencies(app) {

	curr_buttons = [];
	curr_buttons[0] = {text: 'Select Currency', bold: true};
	currs_count = 0;

	app.request.get(
		'ajax/mobile.ajax.php?act=send&action=currencies_get_all',
		{},
		function (data) {
			// Replace page content with new data
			if ( data ) {

				var dt = JSON.parse(data);

				for (i = 0; i < dt.length; i++) {
					curr_buttons[++currs_count] = {text: dt[i].currency_symbol + ' ' + dt[i].currency_name, currency_code: dt[i].currency_code, onClick: function () { app.popover.close('.popover-links'); changeCurrency(this.currency_code); }};
				}
				curr_buttons[++currs_count] = {text: 'Cancel', color: 'red', onClick: function () { app.popover.close('.popover-links'); }};

				var ac2 = app.actions.create({
					buttons: curr_buttons,
					on: {
						opened: function () {
							console.log('Action Sheet opened')
						}
					}
				})
				$$('.menu-item-currencies').on('click', function () {
					ac2.open();
				});

			}else{
				debug("loadCurrencies(): empty data");
			}
			debug("loadCurrencies(): END");

		},
		function (xhr) {
			debug("loadCurrencies(): error on receiving data");
			debug("loadCurrencies(): END");
		},
	);
}


/**
 * Load languages
 */
function loadLanguages(app) {

	lang_buttons = [];
	lang_buttons[0] = {text: 'Select Language', bold: true};
	langs_count = 0;

	app.request.get(
		'ajax/mobile.ajax.php?act=send&action=languages_get_all',
		{},
		function (data) {
			// Replace page content with new data
			if ( data ){

				var dt = JSON.parse(data);

				for (i = 0; i < dt.length; i++) {
					lang_buttons[++langs_count] = {text: dt[i].lang_name, lang_abbrev: dt[i].lang_abbrev, onClick: function () { app.popover.close('.popover-links'); changeLanguage(this.lang_abbrev); }};
				}
				lang_buttons[++langs_count] = {text: 'Cancel', color: 'red', onClick: function () { app.popover.close('.popover-links'); }};


				var ac1 = app.actions.create({
					buttons: lang_buttons,
					on: {
						opened: function () {
							console.log('Action Sheet opened')
						}
					}
				})
				$$('.menu-item-languages').on('click', function () {
					ac1.open();
				});

			}else{
				debug("loadLanguages(): empty data");
			}
			debug("loadLanguages(): END");
		},
		function (xhr) {
			debug("loadLanguages(): error on receiving data");
			debug("loadLanguages(): END");
		},
	);

}


/**
 * Change language
 * @param string lang
 */
function changeLanguage(lang) {

	debug("changeLanguage(): START");

	if(lang == null || lang == ''){
		debug("changeLanguage(): wrong param - " + lang);
		return false;
	}

	var $ptrContent = $$('.partial-content');

	// Load page
	app.request.get(
		'index.php?mobile=home&lang=' + lang + '&render=partial',
		{},
		function (data) {
			// When loading done, we need to reset it
			app.ptr.done();

			// Replace page content with new data
			if ( data ){
				$ptrContent.html(data);
				debug("changeLanguage(): language changed to " + lang);
			}else{
				debug("changeLanguage(): empty data");
			}
			debug("changeLanguage(): END");
		},
		function (xhr) {
			debug("changeLanguage(): error on receiving data");
			debug("changeLanguage(): END");
		},
	);
}


/**
 * Change currency
 */
function changeCurrency() {

	debug("changeCurrency(): START");
}


/**
 * Pull to refresh
 */
function pullToRefresh(){

    // Pull to refresh content
    var $ptrContent = $$('.ptr-content');

    // Add 'refresh' listener on it
    $ptrContent.on('ptr:refresh', function (e) {

        var $ptrPage = $$(this).data('ptr-url');
        var $ptrParam = $$(this).data('ptr-url-param');
        var $ptrContent = $$(this).find('.partial-content');

        debug("start refreshing page: " + $ptrPage + " | param "  +$ptrParam);

        // Emulate 2s loading
        setTimeout(function () {

			// Load page
			app.request.get(
			'index.php?mobile=' + $ptrPage + ($ptrParam ? '&' + $ptrParam : '') + '&render=partial',
			{},
			function (data) {
				// When loading done, we need to reset it
				app.ptr.done();
				e.detail();

				debug("received new data...");

				// Replace page content with new data
				if ( data ){
				 	$ptrContent.html(data);
				 	debug("old content has been replaced with new data!!!");
				}else{
				 	debug("empty data received!!! :(");
				}

				debug("end refreshing page");
			},
			function (xhr) {
				app.ptr.done();
				e.detail();

				debug("error on receiving new data...");
			});

        }, 500);
    });
}





/////////////////////////////////////////////////////////


			//app.request.post("url", {"data to send":"aaaa"}, function (data) {
			///     //Here the response comes so hide loader
			//     app.preloader.show();
			//});

