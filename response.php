<?
	include("JSON.php");
	include("cfw.inc.php");
	
	$json = new Services_JSON();
	
	define(TODAY, date("Y-m-d"));
	
	
class sixhours {
	function sixhours() {
		mysql_connect("localhost","root","admin");
			
			$this->table = array(
				"Users"       => "sixhourday.users",
				"Projects"    => "sixhourday.projects",
				"Tasks"     => "sixhourday.tasks",
				"Entries"   => "sixhourday.entries"
			);		
	}
	
	function createNewProject($a_data) {
		$a_data["ProjectID"] = mysql_insert($a_data, $this->table["Projects"]);
		return($a_data);
	}
	
	function getProjectsByUser($id_user) {
		$a_list = mysql_query_list("select * from ".$this->table["Projects"]." where (UserID=".$id_user.") order by Name");			
		return($a_list);
	}
	
	function createNewTask($a_data) {
		$a_data["TaskID"] = mysql_insert($a_data, $this->table["Tasks"]);
		return($a_data);
	}
	
	function getTasksByProject($id_project) {
		$a_list = mysql_query_list("select * from ".$this->table["Tasks"]." where (ProjectID=".$id_project.") order by Name");			
		return($a_list);
	}
	
	function getUserById($id_user) {
		$sql      = mysql_query("select * from ".$this->table["Users"]." where (UserID=".$id_user.")");
		$a_record = mysql_fetch_assoc($sql);	
		
		return($a_record);
	}
	
	function hourMath($length, $hours) {
		return($length * $hours);
	}
	
	function saveEntry($a_data) {
		$a_user = $this->getUserById(1);
		
		$a_data = array(
			"UserID"    => $a_user["UserID"],
			"TaskID"    => $a_data["task"]["TaskID"],
			"ProjectID" => $a_data["project"]["ProjectID"],
			"Length"    => $a_data["time"]["Length"],
			"Hours"     => $this->hourMath($a_data["time"]["Length"], $a_user["Hours"]),
			"EntryDate" => $a_data["log"]["Date"],
			"Date"      => TODAY,
			"Summary"   => strip_tags($a_data["log"]["Summary"])
		);
		
		//print_r($a_data);
		
		$id = mysql_insert($a_data, $this->table["Entries"]);
	}
}	
	
	
$six = new sixhours;	
	
	if ($_POST["save"]) {
		$six->saveEntry($_POST["save"]["data"]);
	}
	
	switch($_GET["lookup"]) {
		default:break;
		
		case "newproject":
			$a_list = array(
				"UserID"    => 1,
				"Name"      => $_GET["Name"],
			);
			
			$a_list = $six->createNewProject($a_list);
			$output = $json->encode($a_list);
			
			break;
			
		case "newtask":
			$a_list = array(
				"ProjectID" => $_GET["ProjectID"],
				"Name"      => $_GET["Name"],
			);
			
			$a_list = $six->createNewTask($a_list);
			$output = $json->encode($a_list);
		
			break;
		
		case "projects":
			$a_list = $six->getProjectsByUser(1);
			$output = $json->encode($a_list);
			
			break;		
		
		case "tasks":
			$a_list = $six->getTasksByProject($_GET["ProjectID"]);
			
			$output = $json->encode($a_list);
			
			break;
	
	}
	
echo $output;	
?>