<?php

session_start(); 
include 'controller.php';


if(!isset($_SESSION['data_arr'])) {
    $_SESSION['data_arr'] = array();
    $_SESSION['proc_arr'] = array();
}


$id = $_GET["id"];
$oc = $_GET["oc"];
$dr = $_GET["dir"];

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
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script> 
        <!--<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>-->
        <script src="http://evanplaice.github.io/jquery-csv/src/jquery.csv.js"></script>

	<!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">

        <!-- jQuery library -->
        <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script> -->

        <!-- Latest compiled JavaScript -->
        <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>

        <script type="text/javascript" src="dist/jquery.jqplot.js"></script>
        <script type="text/javascript" src="dist/plugins/jqplot.json2.js"></script>
        <link rel="stylesheet" type="text/css" href="dist/jquery.jqplot.css" />


        <link href="http://getbootstrap.com/assets/css/docs.min.css" rel="stylesheet">

    </head>
    <body onunload="clear_vars()">
        <div class="header">
            <div class="header-box-left">
	        <div>
		    <img id="logo_link" onclick="goto_calculus()" src="/fieryperfmon/efi.logo"/>
		</div>
	    </div>    
	    <div class="header-box-right">
	        <div class="header-box-inner"><div>FieryPerfmon Calculus ID:</div></div>
		<div class="header-box-inner"><input id="id_box" type="text" name="cal_id" placeholder="Ex: 999999.t0"></div>
		<div class="header-box-inner"><div>Directory Name:</div></div>
		<div class="header-box-inner"><input id="dir_box" type="text" name="dir_name" placeholder="Ex: FieryPerfmon_1"></div>
		<div class="header-box-inner"><button id="calc_btn" onclick="ID_button_click()">Graph It!</button></div>
		<div class="header-box-inner"><div id="error_msg">* OOPS! Something went wrong. Please try again...</div></div>
	    </div>
        </div>
        <div class="content">
            <div class="sidebar left">
                <div class="sidebar-inner">
		    <div id="list1"><h2 id="list_header">Tolerance List:<br></h2><hr></div>
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
		    <div id="box-wrap-left">
		    </div>
		    <div id="box-wrap-right">
		        <div class="box-inner">Choose a CSV to upload:</div>
			<div class="box-inner"><input type="file" id="files" name="files[]" multiple /></div>
                        <!--<div class="box-inner"><button onclick="CSV_button_click()">Import CSV</button></div>-->
		    </div>
		</div>
	    </div>
        </div>
    </body>    
    <script>

    MAX_PAGE = 0;
    CUR_PAGE = 0;
    ROW_SIZE = 10;

    DATA_ARR = new Array();

    $(window).load(function() {

        var id = <?php echo $id; ?>; //Calculus ID
	var oc = <?php echo $oc; ?>; //Occurrence number of the test
        var dr = <?php echo $dr; ?>; //Directory of the files
        
	init_page (id, oc, dr);	

	var header = " " + id + "." + oc + "/" + dr;
	$("#list_header").append(header);

	if(isAPIAvailable()) {
            $('#files').bind('change', handleFileSelect);
        }
    });


    ///////////////////////////////////////////////////////////////////////////
    function isAPIAvailable() {
        // Check for the various File API support.
        if (window.File && window.FileReader && window.FileList && window.Blob) {
            // Great success! All the File APIs are supported.
            return true;
        } else {
            // source: File API availability - http://caniuse.com/#feat=fileapi
            // source: <output> availability - http://html5doctor.com/the-output-element/
            document.writeln('The HTML5 APIs used in this form are only available in the following browsers:<br />');
            // 6.0 File API & 13.0 <output>
            document.writeln(' - Google Chrome: 13.0 or later<br />');
            // 3.6 File API & 6.0 <output>
            document.writeln(' - Mozilla Firefox: 6.0 or later<br />');
            // 10.0 File API & 10.0 <output>
            document.writeln(' - Internet Explorer: Not supported (partial support expected in 10.0)<br />');
            // ? File API & 5.1 <output>
            document.writeln(' - Safari: Not supported<br />');
            // ? File API & 9.2 <output>
            document.writeln(' - Opera: Not supported');
            return false;
        }
    }

    function handleFileSelect(evt) {

        DATA_ARR = [];
        var files = evt.target.files; // FileList object
        var file = files[0];

        // read the file metadata
        var output = ''
            output += '<span style="font-weight:bold;">' + escape(file.name) + '</span><br />\n';
            output += ' - FileType: ' + (file.type || 'n/a') + '<br />\n';
            output += ' - FileSize: ' + file.size + ' bytes<br />\n';
            output += ' - LastModified: ' + (file.lastModifiedDate ? file.lastModifiedDate.toLocaleDateString() : 'n/a') + '<br />\n';

        // read the file contents
        printTable(file);
        
        // post the results
        //$('#list').append(output);
    }

    function printTable(file) {
        
        var reader = new FileReader();
        reader.readAsText(file);
        reader.onload = function(event) {
            var csv = event.target.result;
	    
            var data = $.csv.toArrays(csv);
            var arr = []; 
            var html = '<h2 id="list_header">Tolerance List:<br>'+file.name+'</h2><hr>\r\n';
	    html += '<table>\r\n';
	    var index = 0;
	    var str = "";
	    var first = 0;
            for(var row in data) {
	        if(first == 1) {

		    if(data[row][1] != " ") {
                        str = data[row][0].substring(2);
		        var n = str.indexOf("\\");
		        str = str.substring(n);
                        html += '<tr class="row_select" id="row' + index +'" onclick="start_selected('+index+')"><td id="name'+ index +'">'+str+'</td></tr>\r\n';
			arr[index] = [data[row]];

		        index++;
		    }
		} else {
		    first = 1;
		}
            }
	    html += '</table>';
	    
	    DATA_ARR = arr;
            $('#list1').html(html);

	    handle_row_display(0);
            //click the first row...
	    $("#row0").click(); 
        };
        reader.onerror = function(){ alert('Unable to read ' + file.fileName); };
    }
    ///////////////////////////////////////////////////////////////////////////

    function clear_vars(){
        DATA_ARR = [];
    }

    function init_page (id, oc, dr) {
        $.ajax({
            url: 'controller.php',
            method: 'POST',
	    data:  {'function': 'get_data_arr', 'id': id, 'oc': oc, 'dr': dr},
	    success: function(str){
	        
                var arr = JSON.parse(str);
	        DATA_ARR = arr;

		$.ajax({
                    url: 'controller.php',
                    method: 'POST',
	            data:  {'function': 'init_page', 'id': id, 'oc': oc, 'dr': dr, 'size' : ROW_SIZE},
	            success: function(str){
	                $("#list1").append(str);               

                        var length = DATA_ARR.length;
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
        });
    }

    function prev_page(){
        CUR_PAGE--;
	handle_row_display(CUR_PAGE);
    }

    function next_page(){
        CUR_PAGE++;
	handle_row_display(CUR_PAGE);
    }



    function handle_row_display(page_num){
  
        //alert(page_num);
        //hide and collapse the list and buttons...
        //$(".row_select").css({"visibility":"collapse"});
        $(".row_select").css({"display":"none"});

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
	    
	    //$(row_id).css({"visibility":"visible"});
	    $(row_id).css({"display":"inherit"});
	    
        }

    }


    function session_destroy(){
        <?php session_destroy();?>
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
        get_data_by_index(index);	
        row_selected(index);
    }


    function row_selected(index) {
        id_tag = "#row"+index;

	//RESET the selected row colors and highlight the new one...
	$(".row_select").css({"background-color":"white", "color":"#3572b0"});	
        $(id_tag).css({"background-color":"#3572b0", "color":"white"});

    }

    function get_data_by_index(index){
        index = parseInt(index);
        update_chart( DATA_ARR[index][0], index );
    }

    function update_chart(data, index){

        var name = "#name"+index;
	var proc_name = "<h2>"+$(name).html()+"</h2>"; 

        var plot_num = [];
        for(i = 1; i < data.length; i++) {
	    plot_num[i] = parseInt(data[i]);
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



