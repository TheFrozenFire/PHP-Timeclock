<?php
class SQLite_DataSource implements DataSource {
	private $connection;
	
	public function __construct($source, $credentials = NULL) {
		$this->initialize($source, $credentials);
	}
	
	public function initialize($source, $credentials = NULL) {
		if(!file_exists($source)) {
			try {
				$this->connection = new PDO("sqlite:$source");
			} catch (PDOException $e) {
				die($e);
			}
			$this->connection->query(
'CREATE TABLE "employees" ("id" INTEGER PRIMARY KEY  AUTOINCREMENT  NOT NULL , "last_name" TEXT NOT NULL , "first_name" TEXT NOT NULL , "active" INTEGER DEFAULT 1);
CREATE TABLE "schedule" ("id" INTEGER PRIMARY KEY  AUTOINCREMENT  NOT NULL , "employeeid" INTEGER NOT NULL , "arrival_time" INTEGER NOT NULL , "departure_time" INTEGER NOT NULL , "clockintime" INTEGER, "clockouttime" INTEGER);'
			);
		} else try {
			$this->connection = new PDO("sqlite:$source");
		} catch (PDOException $e) {
			die($e);
		}
		
		return TRUE;
	}
	
	public function getSchedule($date, $enddate = NULL) {
		$statement = $this->connection->prepare(
			'SELECT `schedule`.`id`, `schedule`.`employeeid`, `schedule`.`arrival_time`, `schedule`.`departure_time`, `schedule`.`clockintime`, `schedule`.`clockouttime`, `employees`.`last_name`, `employees`.`first_name` FROM `schedule`, `employees` WHERE `schedule`.`arrival_time` > :open_date AND `schedule`.`departure_time` < :close_date AND `employees`.`id` = `schedule`.`employeeid`'
		);
		
		$statement->bindValue(':open_date', $date);
		$statement->bindValue(':close_date', is_null($enddate)?$date+86400:$enddate);
		
		if($statement->execute()) {
			$results = $statement->fetchAll();
			if(empty($results)) return FALSE;
			
			$returnResults = array();
			
			foreach($results as $result) $returnResults[] = array(
				'id'=>$result['id'],
				'employee'=>$result['employeeid'],
				'name'=>"{$result['last_name']}, {$result['first_name']}",
				'arrival'=>$result['arrival_time'],
				'departure'=>$result['departure_time'],
				'clockin'=>$result['clockintime'],
				'clockout'=>$result['clockouttime'],
				'clockedin'=>(!is_null($result['clockintime']) && is_null($result['clockouttime']))
			);
			
			return $returnResults;
		} else return FALSE;
	}
	
	public function addScheduleEntry($employeeID, $arrival, $departure) {
		$statement = $this->connection->prepare(
			'INSERT INTO `schedule` (`employeeid`, `arrival_time`, `departure_time`) VALUES (:id, :arrival, :departure)'
		);
		
		$statement->bindValue(':id', $employeeID);
		$statement->bindValue(':arrival', $arrival);
		$statement->bindValue(':departure', $departure);
		
		return $statement->execute();
	}
	
	public function removeScheduleEntry($id) {
		$statement = $this->connection->prepare(
			'DELETE FROM `schedule` WHERE `id` = :id'
		);
		
		$statement->bindValue(':id', $id);
		
		return $statement->execute();
	}
	
	public function getEmployees($active = TRUE) {
		if($active) $statement = $this->connection->prepare(
			'SELECT `id`, `last_name`, `first_name` FROM `employees` WHERE `active` = 1'
		); else $statement = $this->connection->prepare(
			'SELECT `id`, `last_name`, `first_name` FROM `employees`'
		);
		
		if($statement->execute()) {
			$results = $statement->fetchAll();
			if(empty($results)) return FALSE;
			
			$returnResults = array();
			
			foreach($results as $result) $returnResults[] = array(
				'id'=>$result['id'],
				'lastname'=>$result['last_name'],
				'firstname'=>$result['first_name']
			);
			
			return $returnResults;
		} else return FALSE;
	}
	
	public function clockIn($id, $hash = NULL, $time = NULL) {
		if(is_null($time)) $time = time();
		$statement = $this->connection->prepare(
			'UPDATE `schedule` SET `clockintime` = :time WHERE `id` = :id'
		);
		
		$statement->bindValue(':time', $time);
		$statement->bindValue(':id', $id);
		
		return $statement->execute();
	}
	
	public function clockOut($id, $hash = NULL, $time = NULL) {
		if(is_null($time)) $time = time();
		$statement = $this->connection->prepare(
			'UPDATE `schedule` SET `clockouttime` = :time WHERE `id` = :id AND `clockintime` IS NOT NULL'
		);
		
		$statement->bindValue(':time', $time);
		$statement->bindValue(':id', $id);
		
		return $statement->execute();
	}
}
?>
