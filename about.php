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

			<h2><a NAME="Artifacts">What artifacts can we enter?</a></h2>
	        	<p>Please follow these formats exactly:</p>
					<table width="80%">
						<tr><th>TYPE</th><th>EXAMPLE</th><th>NOTE</th></tr>
						<tr><td>doi</td><td>10.1234/abcd</td></tr>
						<tr><td>url</td><td>http://somewhere.com/more</td><td>Must include http prefix</td></tr>
						<tr><td>slideshare slides</td><td>example</td><td>Must include http prefix</td></tr>
						<tr><td>Dryad data</td><td>10.1234/abcd</td></tr>
						<tr><td>PubMed ID</td><td>123456</td></tr>
						<tr><td>Genbank</td><td>sd234234</td></tr>
						<tr><td>PDB</td><td>1234</td></tr>
						<tr><td>GEO</td><td>GSE1234</td></tr>
						<tr><td>Mendeley UUID</td><td>sdfsdfdf</td></tr>
					</table>
					<p>Not currently supported:  PubMed Central IDs</p>
					<p><em>Add info about FigShare</em></p>

			<h2><a NAME="Limitations">Limitations</a></h2>
	        	<p>Some limitations:
					<ul>
						<li>only first page of the Mendeley profile
						<li>only first 100 artifacts from Mendeley groups
					</ul></p>

			<h2><a NAME="Missing">Why are some artifacts missing metrics?</a></h2>
	        	<p>Only do relevant.  Also, sometimes the artifacts were received without sufficient information to use all metrics.  For example, the system sometimes can't figure out the DOI from a Mendeley UUID or URL.</p>

			<h2><a NAME="History">History</a></h2>
	           	<p>Concept originally hacked at the <a href="http://www.beyond-impact.org/">Beyond Impact Workshop</a>. <a href="https://github.com/mhahnel/total-impact">Source and contributors.</a></p>

			<h2><a NAME="Metrics">Metrics</a></h2>
			    <p>Metrics are computed based on the following data sources:</p>
		
			    <?php
				echo "$rendered_about_text";
				?>

			<h2><a NAME="Contact">Contact us</a></h2>
	        	<p><em>TBD</em></p>

		
			</div>
			<!-- END instr -->
    </body>
</html>