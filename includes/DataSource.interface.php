<?php
interface DataSource {
	// Method initialize
	//	This method is called when a datasource is loaded.
	//	It is required to initialize a connection to the datasource, if necessary,
	//	returning TRUE or FALSE based on the result of that connection initialization.
	// Parameters
	//	$source: Host, file or other respective source for the intended datasource.
	//	$credentials: Optional additional credentials for datasource, such as username, password, database name, etc.
	// Return
	//	Returns boolean TRUE on successful connection, checking that the connection is live and functional.
	//	Return boolean FALSE if connection fails.
	public function initialize($source, $credentials = NULL);
	
	// Method getSchedule
	//	This method is called when a schedule for a date or dates is requested by the user.
	//	For a single day, only $date is required. If only $date is provided, assume the user means $date, $date+86400.
	//	If both $date and $enddate are provided, aggregate all scheduled employees between $date and $enddate.
	// Parameters
	//	$date: Starting date for a single day's schedule, or a date range. Provided as a Unix Timestamp (Epoch).
	//	$enddate: Optional, specifies the end date for which a schedule is requested. If NULL is provided, assume $date+86400.
	// Return
	//	Returns a numerically indexed array of the form:
	//		Array (
	//			[int 0...] Array (
	//				'id': ID of schedule entry, numeric.
	//				'employee': Employee ID, numeric.
	//				'name': Employee's name, as a string.
	//				'arrival': Unix Timestamp indicating when the employee is intended to arrive.
	//				'departure': Unix Timestamp indicating when the employee is intended to depart.
	//				'clockin': Unix Timestamp indicating the time when the employee clocked in. May be NULL.
	//				'clockout': Unix Timestamp indicating the time when the employee clocked out. May be NULL.
	//				'clockedin': If clockin is not null and clockout is null, set to boolean TRUE, else FALSE.
	//			)
	//		)
	//	Returns FALSE on failure.
	public function getSchedule($date, $enddate = NULL);
	
	// Method addScheduleEntry
	//	This method is called when the user wishes to add a schedule entry to the datasource.
	// Parameters
	//	$employeeID: The ID of the employee to be added to the schedule.
	//	$arrival: A unix timestamp indicating the arrival time of the employee.
	//	$departure: A unix timestamp indicating the departure time of the employee.
	// Return
	//	Return TRUE on success.
	//	Return FALSE on failure.
	public function addScheduleEntry($employeeID, $arrival, $departure);
	
	// Method removeScheduleEntry
	//	This method is called when the user wishes to remove a schedule entry from the datasource.
	// Parameters
	//	$id: ID of the schedule entry to be removed.
	// Return
	//	Return TRUE on success.
	//	Return FALSE on failure.
	public function removeScheduleEntry($id);
	
	// Method getEmployees
	//	This method is called when a user wishes to receive a list of employees from the datasource.
	// Parameters
	//	$active: Optional boolean value of TRUE or FALSE.
	//		TRUE requires that all employees returned are active. FALSE returns all employees, regardless of being active.
	// Return
	//	Returns a numerically indexed array of the form:
	//		Array (
	//			[int 0...] Array (
	//				'id': ID of Employee, numeric.
	//				'lastname': Last name of employee, string.
	//				'firstname': First name of employee, string.
	//			)
	//		)
	//	Return FALSE on failure.
	public function getEmployees($active = TRUE);
	
	// Method getEmployee
	//	This method is called when a user wishes to receive the details for a single employee.
	// Parameters
	//	$id: Employee ID of employee for which details are to be queried
	// Return
	//	Return an associative array of the form:
	//		Array (
	//			'id': ID of Employee, numeric.
	//			'lastname': Last name of employee, string.
	//			'firstname': First name of employee, string.
	//		)
	//	Return FALSE on failure.
	public function getEmployee($id);
	
	// Method addEmployee
	//	This method is called to add an employee to the datasource.
	// Parameters
	//	$lastName: String indicating the last name of the employee
	//	$firstName: String indicating the first name of the employee
	// Return
	//	Return TRUE on success.
	//	Return FALSE on failure.
	public function addEmployee($lastName, $firstName);
	
	// Method removeEmployee
	//	This method is called to remove an employee based on an Employee ID
	// Parameters
	//	$id: Employee ID of employee to be removed
	// Return
	//	Return TRUE on success.
	//	Return FALSE on failure.
	public function removeEmployee($id);
	
	// Method editEmployee
	//	This method is called to modify details of an employee, based on their employee ID
	// Parameters
	//	$id: Employee ID of employee to be modified
	//	$lastName: New last name of employee
	//	$firstName: New first name of employee
	// Return
	//	Return TRUE on success.
	//	Return FALSE on failure.
	public function editEmployee($id, $lastName, $firstName);
	
	// Method clockIn
	//	This method is called when the user clocks in an employee.
	// Parameters
	//	$id: A numeric ID corresponding to a schedule entry.
	//	$hash: Optional hash corresponding to an employee's password hash.
	//		Compare this to the employee's hash in the datasource, and clock the employee in ONLY if this hash matches.
	//		If the hash does not match, return FALSE.
	//		If NULL, ensure the employee's hash in the datasource is NULL as well (Or the datasource's respective 'undefined' value).
	//	$time: Optional specification of a time when the employee clocks in. If NULL, assume time().
	// Return
	//	Return TRUE on success.
	//	Return FALSE on failure.
	public function clockIn($id, $hash = NULL, $time = NULL);
	
	// Method clockOut
	//	This method is called when the user clocks out an employee.
	//	Only clock employee out if already clocked in.
	// Parameters
	//	$id: A numeric ID corresponding to a schedule entry.
	//	$hash: Optional hash corresponding to an employee's password hash.
	//		Compare this to the employee's hash in the datasource, and clock the employee out ONLY if this hash matches.
	//		If the hash does not match, return FALSE.
	//		If NULL, ensure the employee's hash in the datasource is NULL as well (Or the datasource's respective 'undefined' value).
	//	$time: Optional specification of a time when the employee clocks in. If NULL, assume time().
	// Return
	//	Return TRUE on success.
	//	Return FALSE on failure.
	public function clockOut($id, $hash = NULL, $time = NULL);
}
?>
