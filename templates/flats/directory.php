<?php

return array(
    // module components
    'components' => array(
        'DirectoryComponent' => array('enable' => true, 'class' => 'DirectoryComponent'),
    ),

    'urlManager' => array(
        'rules' => array(
            'listings/view/id/([0-9]+)[\/]?$' => 'listings/view/id/{$0}',
            'listings/view/id/([0-9]+)/(.*?)[\/]?$' => 'listings/view/id/{$0}',
            'listings/view/([0-9]+)[\/]?$' => 'listings/view/id/{$0}',
            'listings/view/([0-9]+)/(.*?)[\/]?$' => 'listings/view/id/{$0}',
            'listings/([0-9]+)[\/]?$' => 'listings/view/id/{$0}',
            'listings/([0-9]+)/(.*?)[\/]?$' => 'listings/view/id/{$0}',
            'categories/view/id/([0-9]+)[\/]?$' => 'categories/view/id/{$0}',
            'categories/view/id/([0-9]+)/(.*?)[\/]?$' => 'categories/view/id/{$0}',
            'categories/view/([0-9]+)[\/]?$' => 'categories/view/id/{$0}',
            'categories/view/([0-9]+)/(.*?)[\/]?$' => 'categories/view/id/{$0}',
            'categories/([0-9]+)[\/]?$' => 'categories/view/id/{$0}',
            'categories/([0-9]+)/(.*?)[\/]?$' => 'categories/view/id/{$0}',
        ),
    ),

	// Default Backend url (optional, if defined - will be used as application default settings)
	'backendDefaultUrl' => 'directoryInfo/manage',

    // Default settings (optional, if defined - will be used as application default settings)
	//'defaultErrorController' => 'Error',
    'defaultController' => 'Home',
    'defaultAction' => 'index',
);
