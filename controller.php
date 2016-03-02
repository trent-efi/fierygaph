<?php
    session_start(); 


$action = $_POST['function'];

switch($action) {
    case 'init_page': $id = $_POST['id']; $oc = $_POST['oc']; $dr = $_POST['dr']; $size = $_POST['size']; echo init_page($id, $oc, $dr, $size);  break;
    case 'get_data_by_index': $index = $_POST['index']; echo get_data_by_index($index); break;
    case 'check_url': $url = $_POST['url']; echo check_url($url); break;
    case 'get_data_arr' : $id = $_POST['id']; $oc = $_POST['oc']; $dr = $_POST['dr']; echo get_data_arr($id, $oc, $dr); break;
}

///////////////////////////////////////////////////////////////////////////////
// Initial function called during the start of the page:
///////////////////////////////////////////////////////////////////////////////

/**************************************
 * Initialize the page. Sets list of
 * proceses and adds them to a 
 * container to referance later.
 *************************************/
function init_page($id, $oc, $dr, $size){

    //Example:
    //\\C3-763642I1-GAL\Process(Fiery)\Thread Count

    $data_arr = $_SESSION['data_arr'];
    $proc_arr = $_SESSION['proc_arr'];
    
    $index = 0;

    //$list_tol = get_tolerances($id, $oc, $dr);//ENABLE IF USING TOLERANCES
    $list_all = get_all_procs($id, $oc, $dr);
 

    $list = "<table>";
    ///////////////////////////////////////////////////////////////////////////
    // Lists all the processes from the tolerance file...
    ///////////////////////////////////////////////////////////////////////////
    //Generate list of process in tolerance file...
    //Find the data for each tolreance and store in a global array...
    /*foreach($list_tol as $tol) {
        //echo var_dump($tol);
        if ( !empty($tol) ) {
            foreach($list_all as $all[0]) {
	        //echo "all[0]: ".$all[0][0]."<br>";
	        if ( !empty($all[0]) ) {
		    $proc = substr($all[0][0], 2);//gets rid of first two '\' chars
                    $pos = strpos($proc, '\\');
		    $proc = substr($proc, $pos);

                    $tol = preg_replace( '/[^[:print:]]/', '',$tol);
                    $proc = preg_replace( '/[^[:print:]]/', '',$proc);		    
		    if($tol == $proc) {
			$line = "<tr class='row_select' id='row".$index."' onclick='start_selected(".$index.")'><td id='name".$index."'>".$proc."</td></tr>";

			$list = $list.$line;
		        $data_arr[] = $all;
			$index++;			
			break;
		    }
	        }
	    }
	}
    }*/
    ///////////////////////////////////////////////////////////////////////////
    // Lists all of the processes reguardless of tolerance file...
    ///////////////////////////////////////////////////////////////////////////
    $first = 0;
    $page = 0;
    foreach($list_all as $all[0]){
        if($first != 0){
	    if($all[0][1] != " ") {
	        $proc = substr($all[0][0], 2);//gets rid of first two '\' chars
                $pos = strpos($proc, '\\');
	        $proc = substr($proc, $pos);
                $proc = preg_replace( '/[^[:print:]]/', '',$proc);
                $line = "<tr class='row_select' id='row".$index."' onclick='start_selected(".$index.")'><td id='name".$index."'>".$proc."</td></tr>";
	        $list = $list.$line;
                $data_arr[] = $all;
	        $proc_arr[] = $proc;
	        $index++;	
	    }
	} else {
	    $first = 1;
	}
    }

    $extra_rows = $index % $size;
    $extra_rows = $size - $extra_rows;
    $i = 0;

    while($i < $extra_rows){
	$list = $list."<tr class='row_select' id='row".$index."'><td></td></tr>";
	$index++;
        $i++;	
    }

    ///////////////////////////////////////////////////////////////////////////
    $_SESSION['data_arr'] = $data_arr;
    $_SESSION['proc_arr'] = $proc_arr;
    $list = $list."</table><hr>";

    return $list;
}


function get_data_arr($id, $oc, $dr){
    $data_arr = array();

    $list_all = get_all_procs($id, $oc, $dr);

    ///////////////////////////////////////////////////////////////////////////
    // Lists all of the processes reguardless of tolerance file...
    ///////////////////////////////////////////////////////////////////////////
    $first = 0;
    foreach($list_all as $all[0]){
        
        if($first != 0){
	    if($all[0][1] != " ") {
                $data_arr[] = $all;
	    }
	} else {
	    $first = 1;
	}
    }
    //echo "<script>console.log('in PHPH');<script>";
    return json_encode($data_arr);
}

/**************************************
 * Parse test_FieryPerfmon.txt for
 * tolerances
 *************************************/
function get_tolerances($id, $oc, $dr){

    $url = "http://calculus-logs.efi.internal/logs/".$id.".".$oc."/".$dr."/test_FieryPerfmon.txt";
    $data = file_get_contents($url);

    if (empty($data)) {
        return "BAD INPUT: ".$id."<br>";
    }

    $rows = explode("\n", $data);
    $s = array();
    $temp = array();

    //Example output from "test_FieryPerfmon.txt"
    //PROCESS: \Memory\Available Bytes Tolerance -25
    foreach($rows as $row) {

	$arr = explode(" ", trim($row) );
        if(count($arr) > 0){ 
	    if($arr[0] == "PROCESS:"){
		$loc = strpos($row, "Tolerance");
		$str = substr($row, 9, $loc - 10 );
                $s[] = $str;
	    }
	}

             
    } 
  
    return $s;
}

/**************************************
 * Parse FieryPerfmon_1.csv into 
 * an array
 *************************************/
function get_all_procs($id, $oc, $dr) {

    //Data files:
    //http://calculus-logs.efi.internal/logs/763642.t1/FieryPerfmon_1/FieryPerfmon_1.csv //rows
    //http://calculus-logs.efi.internal/logs/763642.t1/FieryPerfmon_1/FieryPerfmon.csv  //columns

    $url = "http://calculus-logs.efi.internal/logs/".$id.".".$oc."/".$dr."/FieryPerfmon_1.csv";
    //$url = "http://calculus-logs.efi.internal/logs/765006.t0/FieryPerfmon_1/FieryPerfmon_1.csv";

    $data = file_get_contents($url);

    if (empty($data)) {
        return "BAD INPUT: ".$id."<br>";
    }

    $rows = explode("\n",$data);
    $s = array();
   
    foreach($rows as $row) {
        $s[] = str_getcsv($row);
    }
    //echo var_dump($s);

    return $s;
}


///////////////////////////////////////////////////////////////////////////////
// Functions that are called after page has started:
///////////////////////////////////////////////////////////////////////////////

/**************************************
 * Lookup the values at the given 
 * index from the SESSION variable and 
 * return the data for the chart.
 *************************************/
function get_data_by_index($index) {
    $data_arr = $_SESSION['data_arr'];

    $data = "";
    $i = 0;
    $length = count($data_arr[$index][0]);
    foreach($data_arr[$index][0] as $num){
        if($i > 0) {
	    if($i < ($length - 1) ) {
                $data = $data.$num.", ";
	    } else {
	        $data = $data.$num;
	    }
	}
	$i++;
    }

    //echo "PHP: ".var_dump($data);
    return $data;
}

/**************************************
 * Checks if a valid FieryPerfmon url
 * is passed in as the argument.
 *************************************/
function check_url($url){

    $data = file_get_contents($url);

    if (empty($data)) {
        return "false";
    } else {
        return "true";
    }


    
}
?>
