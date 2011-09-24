<?php
require './bootstrap.php';
#require_once 'FirePHPCore/fb.php';

$config = new Zend_Config_Ini(CONFIG_PATH, ENV);
$couch = new Couch_Client($config->db->dsn, $config->db->name);
$collectionId = "RwIjOc"; #TODO: substitute with a collection with ID of"EXAMPLE_ALL_PLUGINS"

$report = new Models_Reporter($couch, $collectionId);
$res = $report->fetch();

$rendered_about_text = $report->render_about_text();	
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
		 "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
    <head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <title>Total Impact</title>
        <link rel="stylesheet" type="text/css" href="./ui/totalimpact.css" />

		<script type="text/javascript">
		//Google Analytics code

		  var _gaq = _gaq || [];
		  _gaq.push(['_setAccount', 'UA-23384030-1']);
		  _gaq.push(['_trackPageview']);

		  (function() {
		    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		  })();

		</script>

    </head>
    <body>

		<!-- START wrapper -->
		<div id="wrapper">
			
			<!-- START header -->
	        <div id="header">
	            <a href="./index.php"><img src="./ui/img/ti_logo.png" alt="total-impact" width='200px' /></a> 
	        </div>
			<!-- END header -->

			<!-- START instr -->

	        <div id="instr">

			<h2><a NAME="What">What is Total Impact?</a></h2>
	        	<p>Total Impact tracks the real-time online impact of various research artifacts. It aggregates impact metrics from many data sources and displays them all in one place.</p>

			<h2><a NAME="Why?">Why?</a></h2>
	        	<p><em>TBD</em></p>

			<h2><a NAME="History">History</a></h2>
	           	<p>Concept originally hacked at the <a href="http://www.beyond-impact.org/">Beyond Impact Workshop</a>. <a href="https://github.com/mhahnel/total-impact">Source and contributors.</a></p>

			<h2><a NAME="Contact">Contact us</a></h2>
	        	<p><em>TBD</em></p>

			<h2><a NAME="Metrics">Metrics</a></h2>
			    <h3>Metrics are computed based on the following data sources:</h3>
		
			    <?php
				echo "$rendered_about_text";
				?>
		
			</div>
			<!-- END instr -->
    </body>
</html>