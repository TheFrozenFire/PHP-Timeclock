<?php
if(!class_exists('gtk')) die('You do not have PHP-GTK installed or enabled. Please enable PHP-GTK before proceeding.');
error_reporting(E_ALL);

chdir(dirname(__FILE__));

require_once('includes/TimeClock_GUI.class.php');
require_once('includes/DataSource.interface.php');
require_once('includes/DataSources/SQLite_DataSource.datasource.php');

//$credentials = unserialize(file_get_contents('database.cfg'));

if(!$datasource = new SQLite_DataSource(dirname(__FILE__).'/phptimeclock.sqlite')) die('Could not initialize data source');

$gui = new TimeClock_GUI($datasource);

Gtk::main();
?>
