<?php
require_once './vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

$capsule = new Capsule;

$password = trim(file_get_contents('db_password.txt'));

$capsule->addConnection([
    'driver'    => 'pgsql',
    'host'      => 'localhost',
    'port'      => '5432', // remember to replace your own connection port
    'database'  => 'ICL', // remember to replace your own database name 
    'username'  => 'postgres', // remember to replace your own username
    'password'  => $password, // remember to replace your own password 
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

// Make sure you set the event dispatcher
$capsule->setEventDispatcher(new Dispatcher(new Container));

// Boot Eloquent
$capsule->bootEloquent();

// Set the Capsule instance to global, so it can be accessed statically
$capsule->setAsGlobal();
?>