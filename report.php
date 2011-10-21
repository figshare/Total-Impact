<?php
require './bootstrap.php';
#require_once 'FirePHPCore/fb.php';

$config = new Zend_Config_Ini(CONFIG_PATH, ENV);
$couch = new Couch_Client($config->db->dsn, $config->db->name);
$collectionId = $_REQUEST['id'];

$report = new Models_Reporter($couch, $collectionId);
$res = $report->fetch();

if (isset($_REQUEST['mode'])) {
	$mode = $_REQUEST['mode'];
} else {
	$mode = "base";
}

if ($mode == "list") {
	$rendered_report_text = $report->render_as_list();
} elseif ($mode == "status") {
	$rendered_report_text = $report->render_status();
	$rendered_about_text = $report->render_about_text();	
} else {
	$mode = "base";
	$rendered_report_text = $report->render(False);  //don't show zeros
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

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
<script type="text/javascript" src="ui/jquery/jquery.tooltip.js"></script>
<script type="text/javascript">

$.ajaxSetup ({  
    cache: false  
}); 
var ajax_load = "<img src='./ui/img/ajax-loader.gif' alt='loading...' />";  

$(document).ready(function(){

	$('.metrics-div.metric-img-name').tooltip();
	$('#about-metrics').hide();
	
});
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
					<div class="permalink" id="permalink">Permalink: <a href="<?php echo "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . "?id=" . $collectionId; ?>"><?php echo "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . "?id=" . $collectionId; ?></a></div>
		        	<div class="badge artifacts-count"><?php echo $report->getArtifactsCount(); ?> artifacts</div> 
		        	<div class="badge created-at">created <?php echo $report->getCreatedAt('j M, Y');?></div>
		        	<div class="badge updated-at">updated <?php echo $report->getUpdatedAt('j M, Y');?></div>
		
					<div class="report-button">
						<form action="./update.php" method="get">
						<input type="hidden" name="id" value="<?php echo $collectionId; ?>" />
						<input type="submit" class="report" value="Update now"></form>
					</div>
		
					<div class="report-button">
						<form action="./report.php" method="get" target="_blank">
						<input type="hidden" name="id" value="<?php echo $collectionId; ?>"  />
						<input type="hidden" name="mode" value="list" />
						<input type="submit" class="report" value="Download metrics"></form>
					</div>
		
					<div class="report-button">
						<form action="./index.php" method="get">
						<input type="hidden" name="add-id" value="<?php echo $collectionId; ?>" />
						<input type="submit" class="report" value="Refine report"></form>
					</div>
		
					<div class="report-button"><form action="./index.php" method="get">
						<input type="submit" class="report" value="Start over"></form>
					</div>
		
					<div class="report-button"><form action="./about.php" method="get" target="_blank">
						<input type="submit" class="FAQ" value="FAQ"></form>
					</div>
			
					<!-- based on code here: https://dev.twitter.com/docs/tweet-button -->
					<script src="//platform.twitter.com/widgets.js" type="text/javascript"></script>
					<div class="tweet-this">
					  <a href="https://twitter.com/share" class="twitter-share-button"
					  data-url="<?php echo "http://total-impact.org/report.php?id=" . $collectionId?>"
					  data-via="mytotalimpact"
					  data-text="<?php echo "Check out My Total Impact: " . $report->getBestIdentifier() . " at";?>"
					  data-count="horizontal">Tweet</a>
					</div>
		        </div>
				
				<!-- END report-meta -->

				<!-- START metrics -->
				<!-- @@@ we could use large icons to visually identify different types of artifacts -->
				<!-- @@@ it'd be useful to separate artifact metadata from artifact metrics, Slideshare and Dryad display both in the same element -->
		        <div id="metrics">
		            <?php
						echo "$rendered_report_text";
		            ?>
		        </div>
				<!-- END metrics -->
		    </div>
			<!-- END report -->

		<!-- START footer -->
			<p class="something-missing"><a href="./about.php#metrics">More details on available metrics.</a> Missing some artifacts or metrics? See <a href="./about.php#limitations">current limitations.</a>  </p>
						
		<div id="about-metrics" class="about-metrics">
		    <h3>Metrics are computed based on the following data sources:</h3>
		
		    <?php
			echo "$rendered_about_text";
			?>
		<p class="debugging">Debugging: <a target="_blank" href="./report.php?id=<?php echo $collectionId; ?>&mode=status">Status log</a>, <a target="_blank" href="https://cloudant.com/futon/document.html?total-impact%2Fdevelopment/<?php echo $_REQUEST['id']; ?>">DB entry</a>
		</p>

		</div>

		<div id="footer">
			<table border=0 width=100%><tr>
			<td><a href="./about.php">about total-impact</a></td>
			<td align="center">Reactions and bugs welcome to <a href="http://twitter.com/#!/totalimpactdev">@totalimpactdev</a></td>			
			<td align="left"><a class="img" href="http://altmetrics.org" title="an altmetrics project"><img src="./ui/img/altmetrics_logo.png" alt="altmetrics" width="80" style="margin-bottom:5px" /></a></td>
			</tr>
		</div>
		
		
		
		</div>
		<!-- END wrapper -->
	</body>
	<?php } ?>
</html>
