<?php
    session_start(); 
    

    if(!isset($_SESSION['data_arr'])) {
        $_SESSION['data_arr'] = array();
	$_SESSION['name_arr'] = array();
	$_SESSION['proc_arr'] = array();
    }

    include 'controller.php';

    $id = $_GET["id"];
    $oc = $_GET["oc"];
    $dr = $_GET["dir"];
    //$fp = $_GET['fp'];

    $action_id = $id.".".$oc;
 
  

    /*if (empty($id)){
        echo "EMPTY: VAR<br>";
    } 
    if (!is_numeric($id) ) {
        echo "NOT NUMERIC".$id."<br>";
    }*/
?>
<!DOCTYPE html5>
<html lang="en">
    <head>
        <meta charset="utf-8" />
	<link rel="stylesheet" href="style.css">
	<title>FieryPerfmon Graph Portal:</title>
	<!-- The JQuery Library used on other JQplot projects... -->
	<!-- <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script> -->

	<!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">

        <!-- jQuery library -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>

        <!-- Latest compiled JavaScript -->
        <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>

        <script type="text/javascript" src="dist/jquery.jqplot.js"></script>
        <script type="text/javascript" src="dist/plugins/jqplot.json2.js"></script>
        <link rel="stylesheet" type="text/css" href="dist/jquery.jqplot.css" />

	<link href="/style/bootstrap.min.css" rel="stylesheet">
        <link href="/style/highlight.css" rel="stylesheet">
        <link href="/style/bootstrap-switch.css" rel="stylesheet">
        <link href="http://getbootstrap.com/assets/css/docs.min.css" rel="stylesheet">
        <link href="/style/main.css" rel="stylesheet">

	<!--<script src="js/jquery.min.js"></script>-->
        <!--<script src="js/bootstrap.min.js"></script>-->
        <script src="js/bootstrap-switch.js"></script>
    </head>
    <body onunload="session_destroy()">
        <div class="header">
            <div class="header-inner"><div><img id="logo_link" onclick="goto_calculus()" src="/fieryperfmon/efi.logo"/></div></div>       
        </div>
        <div class="content">
            <div class="sidebar left">
                <div class="sidebar-inner">
		    <div id="list1"><h2 id="list_header">Tolerance List:</h2><hr></div>
		    <div id="next-wrapper">
                        <div id="prev" onclick="prev_page()">PREV<img id="prev_next" src="/fieryperfmon/prev.png"/></div>
		        <div id="next" onclick="next_page()"><img id="prev_next" src="/fieryperfmon/next.png"/>NEXT</div>			
		    </div>

		</div>
            </div>
            <div class="content-inner">
	        <div id="chart1"></div>
	    </div> 
        </div>
        <div class="footer">
            <div class="footer-inner">
	        <div class="footer-box-wrapper">
		    <div class="box-inner"><div>FieryPerfmon Calculus ID:</div></div>
		    <div class="box-inner"><input id="id_box" type="text" name="cal_id"></div>
		    <div class="box-inner"><div>Directory Name:</div></div>
		    <div class="box-inner"><input id="dir_box" type="text" name="dir_name"></div>
		    <div class="box-inner"><button onclick="ID_button_click()">Graph It!</button></div>
		    <div class="box-inner"><div id="error_msg">* OOPS! Something went wrong. Please try again...<div></div>
		</div>
	    </div>
        </div>
    </body>    
    <script>

    MAX_PAGE = 0;
    CUR_PAGE = 0;
    ROW_SIZE = 25; 

    $(document).ready(function(){

        //console.log(window.location.hostname); 

        var id = <?php echo $id; ?>; //Calculus ID
	var oc = <?php echo $oc; ?>; //Occurrence number of the test
        var dr = <?php echo $dr; ?>; //Directory of the files
        
	init_page (id, oc, dr);
	var header = " " + id + "." + oc + "/" + dr;
	$("#list_header").append(header);	
    });

    function init_page (id, oc, dr) {
        console.log(id);
        $.ajax({
            url: 'controller.php',
            method: 'POST',
	    data:  {'function': 'init_page', 'id': id, 'oc': oc, 'dr': dr},
	    success: function(str){
	        $("#list1").append(str);
		//This will 'CLICK' the newly generated table row from the return str
                //This 'CLICK' will call start_selected() in javascript


                var length = <?php echo count($_SESSION['proc_arr']);?>;
		MAX_PAGE = Math.floor(length / ROW_SIZE);
		if(MAX_PAGE <= 0) {
		    MAX_PAGE = 1;
		}
		
                handle_row_display(0);
                //click the first row...
		$("#row0").click(); 
	    }   
        });
    }

    function prev_page(){
        CUR_PAGE--;
	handle_row_display(CUR_PAGE);
        //alert("PREV");
    }

    function next_page(){
        CUR_PAGE++;
	handle_row_display(CUR_PAGE);
        //alert("CURR: "+CUR_PAGE+" MAX: " + MAX_PAGE);
    }



    function handle_row_display(page_num){
   
        //hide and collapse the list and buttons...
        $(".row_status").css({"visibility":"collapse"});
        $("#prev").css({"visibility":"hidden"});
        $("#next").css({"visibility":"hidden"});

        if (page_num == 0 && page_num < MAX_PAGE) {
	    $("#next").css({"visibility":"visible"});
	} 
	if (page_num > 0 && page_num < MAX_PAGE ) {
	    $("#prev").css({"visibility":"visible"});
            $("#next").css({"visibility":"visible"});
	} 
	if (page_num >= MAX_PAGE) {
	    $("#prev").css({"visibility":"visible"});
	}
	
	var row_id = "";
	var start = page_num * ROW_SIZE;
	var end = start + ROW_SIZE;
	for(var i = start; i < end; i++){
            row_id = "#row"+i;
	    $(row_id).css({"visibility":"visible"});
        }

    }

    function session_destroy(){
        <?php session_destroy();?>
	console.log("page unload");
    }

    function CSV_button_click(){
        var file_path = document.getElementsByName('fpath')[0].value;
	new_url = "http://"+window.location.hostname+"/fieryperfmon/?fp=\"1\"";
	<?php session_destroy(); ?>
	window.location.assign(new_url);
    }

    function ID_button_click(){
        $("#error_msg").css("visibility","hidden");
	var cal_id = document.getElementsByName('cal_id')[0].value;
	var dir_name =  document.getElementsByName('dir_name')[0].value;
        var arr = [];
        arr = cal_id.split(".");
	var id = arr[0];
	var oc = arr[1];


        var url = build_url(cal_id, dir_name);

	if( check_url(url) == 'true' ) {
	    
	    console.log("RETURNED TRUE");
	    new_url = "http://"+window.location.hostname+"/fieryperfmon/?id=\""+id+"\"&oc=\""+oc+"\"&dir=\""+dir_name+"\"";
	    <?php session_destroy(); ?>
	    window.location.assign(new_url)
	} else {
	    console.log("RETURNED FALSE");
            $("#error_msg").css("visibility","visible");
	}
    }

    function build_url(id, dir){
        return "http://calculus-logs.efi.internal/logs/"+id+"/"+dir+"/FieryPerfmon_1.csv";
    }

    function check_url(url){
        return $.ajax({
            url: 'controller.php',	
            method: "POST",
	    data: {'function': 'check_url', 'url': url},
            cache: false,
            async: false
        }).responseText;
    }



    function start_selected(index) {
        row_selected(index);
        get_data_by_index(index);	
    }


    function row_selected(index) {
        id_tag = "#row"+index;

	//RESET the selected row colors and highlight the new one...
	$(".row_select").css({"background-color":"white", "color":"#3572b0"});
        $(id_tag).css({"background-color":"#3572b0", "color":"white"});
    }

    function get_data_by_index(index){
        $.ajax({
            url: 'controller.php',
            method: 'POST',
	    data:  {'function': 'get_data_by_index', 'index': index},
	    success: function(data){
		update_chart(data, index);
	    }   
        });
    
    }

    function update_chart(data, index){
        var name = "#name"+index;
	//var val = document.getElementById("name0").value; 
	var proc_name = "<h2>"+$(name).html()+"</h2>"; 

	var plot_str = data.split(",");
        var plot_num = [];
        for(i = 0; i < plot_str.length; i++) {
	    plot_num[i] = parseInt(plot_str[i]);
	}

	var options = {};        

	options = {
	    title: proc_name,
	    cursor: {
                show: true,
                zoom: true
            },
            axes: {
                xaxis: {
		    label:'Time (minutes)',
		    tickInterval: 1,
                    renderer: $.jqplot.CategoryAxisRenderer
                },
                yaxis: {
		    renderer: $.jqplot.CategoryAxisRenderer
		}
            },
	    grid: {
                backgroundColor: '#EBEBEB',
                borderWidth: 0,
                gridLineColor: 'grey',
                gridLineWidth: 1,
                borderColor: 'black'
            }
	};

        plot1 = $.jqplot('chart1', [plot_num], options);
        plot1.replot( { resetAxes: true } );
    }


    function goto_calculus(){
        window.location.assign("http://calculus.efi.com/requests/mine");
    }


    </script>
</html>



