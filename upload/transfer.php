<?php
    session_start(); // Starting Session
 
    //echo phpinfo(); 
    echo "Received {$_FILES['userfile']['name']} - its size is {$_FILES['userfile']['size']}<br>";  

    //$allowed = array( "csv");

    //if(in_array($_FILES['userfile']['name'], $allowed))
    //{
    //    echo " IN THE HIZOUZEEEEE...";
    //}

    $filename = $_FILES['userfile']['name'];


    $value = move_uploaded_file($filename, "http://10.101.7.2/fieryperfmon/".$filename ); 
    echo var_dump($filename)."<br>";
    if ( $value ) {
        
        print "Received {$filename} - its size is {$_FILES['userfile']['size']}";
    } else {
       print "TREEEENT Upload failed!";
       //$_SESSION['file_upload'] = "TRENT Upload Failed: Transfer Error";
    }
 

    

    //header("location: index.php"); // Redirecting To Other Page

?>

