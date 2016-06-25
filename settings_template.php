<?php
//modulo delle KEYs per funzionamento dei bot (da non committare)

//Telegram
define('TELEGRAM_BOT','');
define('BOT_WEBHOOK', 'https://YYYYYYYYYYY/start.php');
define('LOG_FILE', 'telegram.log');

// Your database
$db_path=dirname(__FILE__).'/./db.sqlite';
define ('DB_NAME', "sqlite:". $db_path);
define('DB_TABLE',"user");
define('DB_TABLE_GEO',"segnalazioni");
define('DB_CONF', 0666);
define('DB_ERR', "errore database SQLITE");

// Your Openstreetmap Query settings
define('AROUND', 1000);						//Number of meters to calculate radius to search
define('MAX', 10);							//max number of points to search
define('TAG','"emergency"="defibrillator"');			//tag to search accoring to Overpass_API Query Language


?>
