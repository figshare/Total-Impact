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
            <link href='http://fonts.googleapis.com/css?family=Lobster+Two:400italic' rel='stylesheet' type='text/css' >

	    <title>total-Impact: <?php echo $report->getBestIdentifier() ?></title>
	    <link rel="stylesheet" type="text/css" href="ui/totalimpact.css" />
            <link rel="icon" type="image/png" href="ui/favicon.ico">
            <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
	    <script type="text/javascript" src="ui/jquery/jquery.tooltip.js"></script>
	    <script type="text/javascript" src="ui/jquery/jquery.zclip.js"></script>


	
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


<script type="text/javascript">

$.ajaxSetup ({  
    cache: false  
}); 
var ajax_load = "<img src='./ui/img/ajax-loader.gif' alt='loading...' />";  

$(document).ready(function(){

	$('ul.metrics li').tooltip();
        $('a#copy-permalink').zclip({
            path:'ui/jquery/ZeroClipboard.swf',
            copy:"test text",
            afterCopy:function(){
                $('a#copy-permalink').text('copied.');
            }
        });

	$('#about-metrics').hide();
	
});
</script>

	</head>
	<body class="report">
		<!-- START wrapper -->

		
			<!-- START header -->
                <div id="header">
                    <h1><a href="./index.php">total-impact</a></h1>
                    <ul id="nav">
                        <li><a href="./about.php">about</a></li>
                        <li><a href="http://twitter.com/#!/totalImpactdev">twitter</a></li>
                    </ul>
                </div><!-- END header -->
                <div id="wrapper">

			<!-- START report -->
		    <div id="report">
				<!-- START report-meta -->
		        <div id="report-meta">
                                <h2>report for <span class="title"><?php echo $report->getBestIdentifier(); ?></span></h2>
                                <a class="report-button" href="./report.php?id=<?php echo $collectionId; ?>&mode=list">download</a>
                                <a class="report-button" href="./?add-id=<?php echo $collectionId; ?>">refine</a>
                                <a class="report-button" href="./update.php?id=<?php echo $collectionId; ?>">run update</a>
		        	<span class="badge updated-at">updated <?php echo $report->getUpdatedAt('j M, Y');?></span>
		        	<span class="badge created-at">created <?php echo $report->getCreatedAt('j M, Y');?></span>
		        	<span class="badge artifacts-count"><?php echo $report->getArtifactsCount(); ?> artifacts</span>
		
		
                                <div id="share">
                                    <!-- based on code here: https://dev.twitter.com/docs/tweet-button -->
                                    <script src="//platform.twitter.com/widgets.js" type="text/javascript"></script>
                                    <span class="tweet-this">
                                      <a href="https://twitter.com/share" class="twitter-share-button"
                                      data-url="<?php echo "http://total-Impact.org/report.php?id=" . $collectionId?>"
                                      data-via="mytotalImpact"
                                      data-text="<?php echo "Check out My Total Impact: " . $report->getBestIdentifier() . " at";?>"
                                      data-count="horizontal">Tweet</a>
                                    </span>
                                    <span id="permalink"><span class="label"> Permalink: </span><a class="copyable", href="<?php echo "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . "?id=" . $collectionId; ?>"><?php echo "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . "?id=" . $collectionId; ?>"</a><a href="#" id="copy-permalink">copy</a></span>
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
			<p class="something-missing">More detail on <a target="_blank" href="./about.php#metrics">available metrics</a>.  Missing some artifacts or metrics? See <a target="_blank" href="./about.php#limitations">current limitations.</a>  </p>
						
		<div id="about-metrics" class="about-metrics">
		    <h3>Metrics are computed based on the following data sources:</h3>
		
		    <?php
			echo "$rendered_about_text";
			?>
		<p class="debugging">Debugging: <a target="_blank" href="./report.php?id=<?php echo $collectionId; ?>&mode=status">Status log</a>, <a target="_blank" href="https://cloudant.com/futon/document.html?total-impact%2Fdevelopment/<?php echo $_REQUEST['id']; ?>">DB entry</a>
		</p>

		</div>

		
		
		</div>
		<!-- END wrapper -->
			<div id="footer">
			an <a class="img" href="http://altmetrics.org" title="an altmetrics project"><img src="./ui/img/altmetrics_logo.png" alt="altmetrics" width="80" /></a> project.
			</div>

	</body>
	<?php } ?>
</html>
