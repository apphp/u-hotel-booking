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

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
	<meta name="description" content="">
	<meta name="author" content="ApPHP">

	<title>Getting Started - uHotelBooking REST API</title>

	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
	
	<style>
		.headerlink, .headerlink:hover { color: black; text-decoration: none; }
		.headerlink span { display:none; }
		.headerlink:hover span{ display:inline; }
	</style>
</head>
<body>

	<!-- Navigation -->
	<?php
		$page = 'getting-started.php';
		include('include/nav.php');
	?>

	<!-- Begin page content -->
	<div class="container" id="welcome">
		<div class="page-header">
			<h2>Getting Started</h2>
		</div>
		
		<ul>
			<li><a class="reference internal" href="#action-list">List</a></li>
			<li><a class="reference internal" href="#action-create">Create records</a></li>
			<li><a class="reference internal" href="#action-update">Update records</a></li>
			<li><a class="reference internal" href="#action-delete">Delete records</a></li>
		</ul>	
		<ul>	
			<li><a class="reference internal" href="#relations">Relations</a></li>
			<li><a class="reference internal" href="#permissions">Permissions</a></li>
			<li><a class="reference internal" href="#access_tables">Access for Tables</a></li>
			<li><a class="reference internal" href="#sanitizing-input">Sanitizing input</a></li>
			<li><a class="reference internal" href="#validating-input">Validating input</a></li>
			<li><a class="reference internal" href="#multi-database">Multi-database</a></li>
			<li><a class="reference internal" href="#binary-data">Binary data</a></li>
			<li><a class="reference internal" href="#sending-null">Sending NULL</a></li>
			<li><a class="reference internal" href="#errors">Errors</a></li>
		</ul>	
		<hr>
		<br>


		<h3>CRUD</h3>
		<p>You can do all CRUD (Create, Read, Update, Delete) operations and one extra List operation. Here is how:</p>
		<br>

		
		<h4 id="action-list"><a class="headerlink" href="#welcome" title="Permalink to this headline">List <span>&#182;</span></a></h4>
		<p>List all records of a database table.</p>
<pre>
```		
GET http://localhost/api.php/categories		
```

Output:
```
{"categories":{"columns":["id","name"],"records":[["1","Internet"],["3","Web development"]]}}
```
</pre>
		<hr>
		<br>
			

		<h4><a class="headerlink" href="#welcome" title="Permalink to this headline">List + Transform <span>&#182;</span></a></h4>
		<p>List all records of a database table and transform them to objects.</p>
<pre>
```
GET http://localhost/api.php/categories?transform=1
```

Output:
```
{"categories":[{"id":"1","name":"Internet"},{"id":"3","name":"Web development"}]}
```
</pre>
		NB: This transform is CPU and memory intensive and can also be executed client-side (see: [lib](https://github.com/mevdschee/php-crud-api/tree/master/lib)).
		<hr>
		<br>


		<h4><a class="headerlink" href="#welcome" title="Permalink to this headline">List + Filter <span>&#182;</span></a></h4>
		<p>Search is implemented with the "filter" parameter. You need to specify the column name, a comma, the match type, another commma and the value you want to filter on. These are supported match types:</p>

		<ul>
		<li>cs: contain string (string contains value)</li>
		<li>sw: start with (string starts with value)</li>
		<li>ew: end with (string end with value)</li>
		<li>eq: equal (string or number matches exactly)</li>
		<li>ne: not equal (string or number doen not match)</li>
		<li>lt: lower than (number is lower than value)</li>
		<li>le: lower or equal (number is lower than or equal to value)</li>
		<li>ge: greater or equal (number is higher than or equal to value)</li>
		<li>gt: greater than (number is higher than value)</li>
		<li>in: in (number is in comma separated list of values)</li>
		<li>ni: not in (number is not in comma separated list of values)</li>
		<li>is: is null (field contains "NULL" value)</li>
		<li>no: not null (field does not contain "NULL" value)</li>
		</ul>
		
<pre>
```
GET http://localhost/api.php/categories?filter=name,eq,Internet
GET http://localhost/api.php/categories?filter=name,sw,Inter
GET http://localhost/api.php/categories?filter=id,le,1
GET http://localhost/api.php/categories?filter=id,lt,2
```

Output:
```
{"categories":{"columns":["id","name"],"records":[["1","Internet"]]}}
```
</pre>
		<hr>
		<br>
			
		
		<h4><a class="headerlink" href="#welcome" title="Permalink to this headline">List + Filter + Satisfy <span>&#182;</span></a></h4>
		<p>Multiple filters can be applied by using "filter[]" instead of "filter" as a parameter name. Then the parameter "satisfy" is used to indicate whether "all" (default) or "any" filter should be satisfied to lead to a match:</p>
<pre>
```
GET http://localhost/api.php/categories?filter[]=id,eq,1&filter[]=id,eq,3&satisfy=any
GET http://localhost/api.php/categories?filter[]=id,ge,1&filter[]=id,le,3&satisfy=all
GET http://localhost/api.php/categories?filter[]=id,ge,1&filter[]=id,le,3
```

Output:
```
{"categories":{"columns":["id","name"],"records":[["1","Internet"],["3","Web development"]]}}
```
</pre>
		<hr>
		<br>
			
			
		<h4><a class="headerlink" href="#welcome" title="Permalink to this headline">List + Column selection <span>&#182;</span></a></h4>
		<p>By default all columns are selected. With the "columns" parameter you can select specific columns. Multiple columns should be comma separated. An asterisk ("*") may be used as a wildcard to indicate "all columns":</p>
<pre>
```
GET http://localhost/api.php/categories?columns=name
GET http://localhost/api.php/categories?columns=categories.name
```

Output:
```
{"categories":{"columns":["name"],"records":[["Web development"],["Internet"]]}}
```
</pre>
		NB: Columns that are used to include related entities are automatically added and cannot be left out of the output.
		<hr>
		<br>
			
			
		<h4><a class="headerlink" href="#welcome" title="Permalink to this headline">List + Order <span>&#182;</span></a></h4>
		<p>With the "order" parameter you can sort. By default the sort is in ascending order, but by specifying "desc" this can be reversed:</p>
<pre>
```
GET http://localhost/api.php/categories?order=name,desc
```

Output:
```
{"categories":{"columns":["id","name"],"records":[["3","Web development"],["1","Internet"]]}}
```
</pre>
		<hr>
		<br>


		<h4><a class="headerlink" href="#welcome" title="Permalink to this headline">List + Order + Pagination <span>&#182;</span></a></h4>
		<p>The "page" parameter holds the requested page. The default page size is 20, but can be adjusted (e.g. to 50):</p>
<pre>
```
GET http://localhost/api.php/categories?order=id&page=1
GET http://localhost/api.php/categories?order=id&page=1,50
```

Output:
```
{"categories":{"columns":["id","name"],"records":[["1","Internet"],["3","Web development"]],"results":2}}
```
</pre>
		NB: Pages that are not ordered cannot be paginated.
		<hr>
		<br>
			
			
		<h4><a class="headerlink" href="#welcome" title="Permalink to this headline">Create <span>&#182;</span></a></h4>
		<p>You can easily add a record using the POST method (x-www-form-urlencoded, see rfc1738). The call returns the "last insert id".</p>
<pre>
```
POST http://localhost/api.php/categories
id=1&name=Internet
```

Output:
```
1
```
</pre>
		<hr>
		<br>
			
			
		<h4><a class="headerlink" href="#welcome" title="Permalink to this headline">Read <span>&#182;</span></a></h4>
		<p>If you want to read a single object you can use:</p>
<pre>
```
GET http://localhost/api.php/categories/1
```

Output:
```
{"id":"1","name":"Internet"}
```
</pre>
		<hr>
		<br>


		<h4 id="action-create"><a class="headerlink" href="#welcome" title="Permalink to this headline">Create (with JSON) <span>&#182;</span></a></h4>
		<p>Alternatively you can send a JSON object in the body. The call returns the "last insert id".</p>
<pre>
```
POST http://localhost/api.php/categories
{"id":"1","name":"Internet"}
```

Output:
```
1
```
</pre>
		Note that the fields that are not specified in the request get the default value as specified in the database.
		<hr>
		<br>
			
			
		<h4 id="action-update"><a class="headerlink" href="#welcome" title="Permalink to this headline">Update <span>&#182;</span></a></h4>
		<p>Editing a record is done with the PUT method. The call returns the rows affected.</p>
<pre>
```
PUT http://localhost/api.php/categories/2
id=1&name=Internet+networking
```

Output:
```
1
```
</pre>
		<hr>
		<br>
			
			
		<h4><a class="headerlink" href="#welcome" title="Permalink to this headline">Update (with JSON) <span>&#182;</span></a></h4>
		<p>Alternatively you can send a JSON object in the body. The call returns the rows affected.</p>
<pre>
```
PUT http://localhost/api.php/categories/2
{"id":"1","name":"Internet networking"}
```

Output:
```
1
```
</pre>
		Note that only fields that are specified in the request will be updated.
		<hr>
		<br>
			
			
		<h4 id="action-delete"><a class="headerlink" href="#welcome" title="Permalink to this headline">Delete <span>&#182;</span></a></h4>
		<p>The DELETE verb is used to delete a record. The call returns the rows affected.</p>
<pre>
```
DELETE http://localhost/api.php/categories/2
```

Output:
```
1
```
</pre>
		<hr>
		<br>
		

		<h4 id="relations"><a class="headerlink" href="#welcome" title="Permalink to this headline">Relations <span>&#182;</span></a></h4>
		<p>The explanation of this feature is based on the data structure from the ```blog.sql``` database file. This database is a very simple blog data structure with corresponding foreign key relations between the tables. These foreign key constraints are required as the relationship detection is based on them, not on column naming.</p>
		<p class="text-danger">Pay attention, that if you may use relations on InnoDB database only!</p>
		<p>You can get the "post" that has "id" equal to "1" with it's corresponding "categories", "tags" and "comments" using:</p>
<pre>
```
GET http://localhost/api.php/posts?include=categories,tags,comments&filter=id,eq,1
```

Output:
```
{
    "posts": {
        "columns": [
            "id",
            "user_id",
            "category_id",
            "content"
        ],
        "records": [
            [
                "1",
                "1",
                "1",
                "blog started"
            ]
        ]
    },
    "post_tags": {
        "relations": {
            "post_id": "posts.id"
        },
        "columns": [
            "id",
            "post_id",
            "tag_id"
        ],
        "records": [
            [
                "1",
                "1",
                "1"
            ],
            [
                "2",
                "1",
                "2"
            ]
        ]
    },
    "categories": {
        "relations": {
            "id": "posts.category_id"
        },
        "columns": [
            "id",
            "name"
        ],
        "records": [
            [
                "1",
                "anouncement"
            ]
        ]
    },
    "tags": {
        "relations": {
            "id": "post_tags.tag_id"
        },
        "columns": [
            "id",
            "name"
        ],
        "records": [
            [
                "1",
                "funny"
            ],
            [
                "2",
                "important"
            ]
        ]
    },
    "comments": {
        "relations": {
            "post_id": "posts.id"
        },
        "columns": [
            "id",
            "post_id",
            "message"
        ],
        "records": [
            [
                "1",
                "1",
                "great"
            ],
            [
                "2",
                "1",
                "fantastic"
            ]
        ]
    }
}
```

You can call the ```php_crud_api_tranform()``` function to structure the data hierarchical like this:
```
{
    "posts": [
        {
            "id": "1",
            "post_tags": [
                {
                    "id": "1",
                    "post_id": "1",
                    "tag_id": "1",
                    "tags": [
                        {
                            "id": "1",
                            "name": "funny"
                        }
                    ]
                },
                {
                    "id": "2",
                    "post_id": "1",
                    "tag_id": "2",
                    "tags": [
                        {
                            "id": "2",
                            "name": "important"
                        }
                    ]
                }
            ],
            "comments": [
                {
                    "id": "1",
                    "post_id": "1",
                    "message": "great"
                },
                {
                    "id": "2",
                    "post_id": "1",
                    "message": "fantastic"
                }
            ],
            "user_id": "1",
            "category_id": "1",
            "categories": [
                {
                    "id": "1",
                    "name": "anouncement"
                }
            ],
            "content": "blog started"
        }
    ]
}
```
</pre>
		This transform function is available for PHP and JavaScript in the files ```php_crud_api_tranform.php``` and ```php_crud_api_tranform.js``` in the "lib" folder.
		<hr>
		<br>
			
			
		<h4 id="permissions"><a class="headerlink" href="#welcome" title="Permalink to this headline">Permissions <span>&#182;</span></a></h4>
		<p>
			By default access permissions are specified for limited number of tables in <code>modules/additional/rest_api/api.class.php</code>.
			If you want to change this list go to and change this class property: <code>$available_tables = array(...);</code>
		</p>
<pre>
protected $available_tables = array(
	'customers'              => array('create' => false, 'read' => true,  'update' => true,  'delete' => true),
	'hotels'                 => array('create' => false, 'read' => true,  'update' => true,  'delete' => false),
	'hotels_description'     => array('create' => false, 'read' => true,  'update' => true,  'delete' => false),
	'rooms'                  => array('create' => true,  'read' => true,  'update' => true,  'delete' => true),
	'rooms_description'      => array('create' => true,  'read' => true,  'update' => true,  'delete' => true),
	'rooms_availabilities'   => array('create' => false, 'read' => true,  'update' => false, 'delete' => true),
	'rooms_prices'           => array('create' => false, 'read' => true,  'update' => false, 'delete' => true),
	'bookings'               => array('create' => false, 'read' => true,  'update' => true,  'delete' => false),
	'bookings_rooms'         => array('create' => false, 'read' => true,  'update' => false, 'delete' => false),
);	
</pre>		
		<hr>
		<br>


		<h4 id="access_tables"><a class="headerlink" href="#welcome" title="Permalink to this headline">Access for tables <span>&#182;</span></a></h4>
		<p>
			To open (edit) access to the table you need to perform one or two actions.
		</p>
		<ol>
			<li>
				<p>
					Add the necessary rights to read, add, edit or delete in file <code>modules/additional/rest_api/api.class.php</code><br/>
					Admin:
<pre>
protected $available_tables_admin = array(
	...
	[tabel_name] => array('create' => false|true, 'read' => false|true,  'update' => false|true, 'delete' => false|true),
);	
</pre>
					Hotel Owner:
<pre>
protected $available_tables = array(
	...
	[tabel_name] => array('create' => false|true, 'read' => false|true,  'update' => false|true, 'delete' => false|true),
);	
</pre>
					Note: If you only need a limit on the committed actions, further changes do not have to.
				</p>
				<br/>
			</li>
			<li>
				If you need to make additional checks or filtering, then do the following<br>
				a.) Create new API class in <code>include/classes/api/[TableName]Api.class.php</code> (See example: <code>include/classes/api/ExampleApi.class.php</code>) and make the desired changes in the classroom<br/>
				b.) Add new line in file <code>include/connection.php</code>
<pre>
$api_classes = array(
	...
	'[TableName]Api',
);
</pre>
			</li>
		</ol>
		<hr>
		<br>


		<h4 id="sanitizing-input"><a class="headerlink" href="#welcome" title="Permalink to this headline">Sanitizing input <span>&#182;</span></a></h4>
		<p>By default all input is accepted and sent to the database. If you want to strip (certain) HTML tags before storing you may specify a <code>input_sanitizer</code> function that returns the adjusted value.</p>
		<hr>
		<br>

		<h4 id="validating-input"><a class="headerlink" href="#welcome" title="Permalink to this headline">Validating input <span>&#182;</span></a></h4>
		<p>By default all input is accepted. If you want to validate the input, you may specify a <code>input_validator</code> function that returns a boolean indicating whether or not the value is valid.</p>
		<hr>
		<br>

		<h4 id="multi-database"><a class="headerlink" href="#welcome" title="Permalink to this headline">Multi-Database <span>&#182;</span></a></h4>
		<p>
			The code also supports multi-database API's. These have URLs where the first segment in the path is the database and not the table name.
			This can be enabled by NOT specifying a database in the configuration. Also the permissions in the configuration should contain a dot character to seperate the database from the table name. The databases 'mysql', 'information_schema' and 'sys' are automatically blocked.
		</p>
		<hr>
		<br>

		
		<h4 id="binary-data"><a class="headerlink" href="#welcome" title="Permalink to this headline">Binary data <span>&#182;</span></a></h4>
		<p>Binary fields are automatically detected and data in those fields is returned using base64 encoding.</p>
<pre>
```
GET http://localhost/api.php/categories/2
```

Output:

```
{"id":"2","name":"funny","icon":"ZGF0YQ=="}
```

When sending a record that contains a binary field you will also have to send base64 encoded data.
```
PUT http://localhost/api.php/categories/2
icon=ZGF0YQ
```
</pre>		
		In the above example you see how binary data is sent. Both "base64url" and standard "base64" are allowed (see rfc4648).
		<hr>
		<br>


		<h4 id="sending-null"><a class="headerlink" href="#welcome" title="Permalink to this headline">Sending NULL <span>&#182;</span></a></h4>
		<p>When using the POST method (x-www-form-urlencoded, see rfc1738) a database <code>NULL</code> value can be set using a parameter with the <code>__is_null</code> suffix:</p>
<pre>
```
PUT http://localhost/api.php/categories/2
name=Internet&icon__is_null
```
</pre>
		<p>When sending JSON data, then sending a <code>NULL</code> value for a nullable database field is easier as you can use the JSON <code>"null"</code> value (without quotes).</p>

<pre>
```
PUT http://localhost/api.php/categories/2
{"name":"Internet","icon":null}
```
</pre>
		<hr>
		<br>


		<h4 id="errors"><a class="headerlink" href="#welcome" title="Permalink to this headline">Errors <span>&#182;</span></a></h4>
		<p>The following types of 404 'Not found' errors may be reported:</p>

		<ul>
			<li>entity (could not find entity)</li>
			<li>object (instance not found on read)</li>
			<li>input (instance not found on create)</li>
			<li>subject (instance not found on update)</li>
			<li>1pk (primary key not found or composite)</li>
		</ul>
		<hr>
		<br><br><br><br><br>

	</div>
  
</body>
</html>
