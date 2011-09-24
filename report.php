<?php
require './bootstrap.php';
#require_once 'FirePHPCore/fb.php';

$config = new Zend_Config_Ini(CONFIG_PATH, ENV);
$couch = new Couch_Client($config->db->dsn, $config->db->name);
$collectionId = $_REQUEST['id'];

$report = new Models_Reporter($couch, $collectionId);
$res = $report->fetch();

$mode = $_REQUEST['mode'];
if (!isset($mode)) {
	$mode = "base";
}

if ($mode == "list") {
	$rendered_report_text = $report->render_as_list();
} elseif ($mode == "status") {
	$rendered_report_text = $report->render_status();
	$rendered_about_text = $report->render_about_text();	
} else {
	$mode = "base";
	$rendered_report_text = $report->render();
	$rendered_about_text = $report->render_about_text();	
}


// handle missing IDs more intelligently later
if (!$res){ header('Location: ../'); }

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
		 "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
            <?php
				if ($mode=="list") {
						echo '<head><meta http-equiv="content-type" content="text/plain; charset=utf-8" /></head>';
						echo "<body>$rendered_report_text</body>";
				} else {
			?>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	    <title>Total Impact: <?php echo $report->getBestIdentifier() ?></title>
	    <link rel="stylesheet" type="text/css" href="ui/totalimpact.css" />
	    <script type="text/javascript" src="ui/jquery/jquery-1.4.2.js"></script>
	    <script type="text/javascript" src="ui/jquery/jquery.tools.min.js"></script>
	    <script type="text/javascript" src="ui/protovis-3.2/protovis-r3.2.js"></script>
	
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

			<!-- START report -->
		    <div id="report">
		        <h2>Impact report for <?php echo $report->getBestIdentifier(); ?></h2>
				<!-- START report-meta -->
		        <div id="report-meta">
		            <p>Created <span class="created-at"><?php echo $report->getCreatedAt('j M, Y');?></span>
		                with <span class="artifacts-count"><?php echo $report->getArtifactsCount(); ?></span>
		                research artifacts.</p>
					<p>Last updated at <span class="updated-at"><?php echo $report->getUpdatedAt('j M, Y');?>. </span></p>
						<ol>							
						<li><a href="./update.php?id=<?php echo $collectionId; ?>">Update now</a> (may take a few minutes)</li>
						<li><a href="./index.php?add-id=<?php echo $collectionId; ?>">Start over with this seed</a></li>
						<li><a href="./index.php">Start over fresh</a></li>
					 	<li><a href="./report.php?id=<?php echo $collectionId; ?>&mode=list">View as plain text list</a></li>
					 	<li><a href="./report.php?id=<?php echo $collectionId; ?>&mode=status">View log</a></li>
						</ol>

					<p>Stable url: <a href="<?php echo "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . "?id=" . $collectionId; ?>"><?php echo "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . "?id=" . $collectionId; ?></a></p>
		        </div>
				
				<!-- END report-meta -->

				<!-- START metrics -->
		        <div id="metrics">
		            <?php
						echo "$rendered_report_text";
		            ?>
		        </div>
				<!-- END metrics -->
		    </div>
			<!-- END report -->

		<!-- START footer -->
		<div id="footer" class="section">
		    <h3>Metrics are computed based on the following data sources:</h3>
		
		    <?php
			echo "$rendered_about_text";
			?>
		
		</div>
		<!-- END footer -->
		
		<p><a href="https://cloudant.com/futon/document.html?total-impact%2Fdevelopment/<?php echo $_REQUEST['id']; ?>">Link to DB entry</a></p>
		
		
		</div>
		<!-- END wrapper -->
	</body>
	<?php } ?>
</html>
