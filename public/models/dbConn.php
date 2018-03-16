<?php
// ----------------------------------------------------------------
// ----------------------------------------------------------------
// -------------------Database Connection Object-------------------
// ----------------------------------------------------------------
// ----------------------------------------------------------------

class dbConn extends pdo{
    public function dbConn(){
		// Load in config
		$config;
		if(file_exists('../config.json')){
			$config = file_get_contents('../config.json');
		} else{
			$config = file_get_contents('../../config.json');
		}
		$config = json_decode($config, true);

        // Sort variables
        $dbhost = $config['DATABASE']['HOST'];
		$dbport = $config['DATABASE']['PORT'];
		$dbuser = $config['DATABASE']['USER'];
		$dbpass = $config['DATABASE']['PASS'];
        $dbname = $config['DATABASE']['NAME'];
        
        try{
			$dsn = "mysql:dbname=".$dbname.";host=".$dbhost.";port=".$dbport;
			parent::__construct($dsn, $dbuser, $dbpass);
		} catch(PDOException $e){
			 var_dump($e);
		}
    }
}

?>
