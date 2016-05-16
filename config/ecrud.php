<?php

return [
	// Database Migrations Path
	'migrations_path' => database_path().'/migrations',
    // The default layout to extends in the curd files
    'extends' => null,

    // The default section
    'section' => null,

    // the form components [ plain , bootstrap , foundation ]
    // plain will create form without any classes or ids
    'framework' => 'bootstrap',
];
