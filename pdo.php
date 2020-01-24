<?php 
class connection{

	public $error = false;

	public $lastId = 0;

	private $user = DB_USER; // DB user login

	private $pass = DB_PASS; // DB user password

	private $dbn = DB_NAME; // DB name 

	private $dbs = DB_SERV; // server name usually localhost => 127.0.0.1
	
	private $debug = DB_DEBUG; // should be passed here from config fayl or in main file.

	private $pdo;

	//"127.0.0.1", "devdata1", "qweqwe4455", "devdata1"



	 function __construct(){

		

		try{		

			$this->pdo = new PDO('mysql:host='.$this->dbs.';dbname='.$this->dbn.';charset=utf8mb4', $this->user, $this->pass,   

			array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));



		}catch(PDOException $ex){

			$ex->getMessage();
			
			if($this->debug){
				// looking at situtation
				printf("Code: %s, Message: %s", $ex->getCode(), $ex->getMessage()) ;
			}
			$res = $this->error_parser($ex->getCode());
		}

		try{		

			$this->pdo->exec("use ".$this->dbn);

		}catch(PDOException $ex){

			if($this->debug){
				// looking at situtation
				printf("Code: %s, Message: %s", $ex->getCode(), $ex->getMessage()) ;
				
			}
			
			$this->error = $ex->getCode();

			$res = $this->error_parser($ex->errorInfo[1]);
		}

		

	 }

	 function select(string $sql){

		# file_put_contents("seletcs$cnt.txt", $sql);

		# $cnt++;

		$stmt = $this->pdo->prepare($sql);

		try{

			$stmt->execute();

			//file_put_contents('lastSelection_result', json_encode($dbdata = $stmt->fetchAll(PDO::FETCH_ASSOC)));
			return $dbdata = $stmt->fetchAll(PDO::FETCH_ASSOC);

		}catch(PDOException $ex){

			file_put_contents('errors/DB_ERROR_SELECT.log', $ex->getMessage());
			
			if($this->debug){
				// looking at situtation
				printf("Code: %s, Message: %s", $ex->getCode(), $ex->getMessage()) ;
				
			}
			$this->error = $ex->getCode();

		}

	 }

	 function insert(string $sql){

        $stmt = $this->pdo->prepare($sql);

		file_put_contents('errors/DB_ERROR_INSERT_SQL.log', $sql);

		try{

			$stmt->execute();	

			$this->lastId = $this->pdo->lastInsertId();

			file_put_contents('lastidfromconnection.log', $this->lastId);

		}catch(PDOException $ex){

			file_put_contents('errors/DB_ERROR_INSERT.log', $ex->getCode());

			$this->error = $ex->getCode();

			return $ex->getCode();

		}

		if($stmt->rowCount() == 1)
			return $stmt->rowCount();
		else
			return false;

	 }

	 function update(string $sql){

        $stmt = $this->pdo->prepare($sql);

		file_put_contents('errors/DB_ERROR_UPDATE_SQL.log', $sql);

		try{

			$stmt->execute();	

		}catch(PDOException $ex){

			file_put_contents('errors/DB_ERROR_UPDATE.log', $ex->getCode());

			$this->error = $ex->getCode();

			return $ex->getCode();
		}

		if(is_numeric($rows = $stmt->rowCount()))
			return $rows;
		else
			return false;
	 }

	 function getLastInsertId(){
		return $this->lastId;
     }
     
	 function remove(string $sql){

		file_put_contents('errors/DB_REMOVE_SQL.log', $sql);

		$stmt = $this->pdo->prepare($sql);

		try{

			$stmt->execute();	

		}catch(PDOException $ex){

			$this->error = $ex->getCode();
			if($this->debug){
				// looking at situtation
				printf("Code: %s, Message: %s", $ex->getCode(), $ex->getMessage()) ;
				
			}
			file_put_contents('errors/DB_ERROR_REMOVE.log', $ex->getMessage());

		}

		if($stmt->rowCount() == 1)
			return true;
		else
			return 'error';
	 }

	 function error_parser(string $errcode){
		 echo $errcode;
		 switch($errcode){

			 case '1049':

				$res = $this->create_database($this->dbn);

			 break;

			 case '1146':

				$res = $this->create_table();

				if($res != true)

					return $res;

				else

					die;

			 break;

			 case '1044':

				$res = 'please, ask to administrator to give privileges';

			 break;

			 case '1064':

			 echo 2222222;

				echo $res = 'There was occured unknown error while creating! please, check your SQL REQUEST!';

			 break;

			 case '3D000': // when a db is not chosen then the is returned

				$res = $this->choose_database();

			 break;

			 case '1045':

				return ' You have entered incorrect username  OR password for connection!';

			 break;

			 default: $res = 'Unknown error occured!';

		 }

		 return $res;

	 }
}



?>