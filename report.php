<?php
require './bootstrap.php';
#require_once 'FirePHPCore/fb.php';

$config = new Zend_Config_Ini(CONFIG_PATH, ENV);
$couch = new Couch_Client($config->db->dsn, $config->db->name);
$collectionId = $_REQUEST['id'];

$report = new Models_Reporter($couch, $collectionId);
$res = $report->fetch();

$rendered_report_text = $report->render();
$rendered_about_text = $report->render_about_text();

// handle missing IDs more intelligently later
if (!$res){ header('Location: ../'); }

?><html>
<head>

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
	
    <div id="header">
		<a href="./"><img src="./ui/img/logo.png" alt="total-impact" width='200px' border=0/></a>
    </div>

    <div id="report">
        <h2>Impact report for <?php echo $report->getBestIdentifier(); ?></h2>
        <div id="report-meta">
            <p>Created <span class="created-at"><?php echo $report->getCreatedAt('j M, Y');?></span>
                with <span class="artifacts-count"><?php echo $report->getArtifactsCount(); ?></span>
                research artifacts. 

<p>Last updated at <span class="updated-at"><?php echo $report->getUpdatedAt('j M, Y');?>. </span>
	<p><a href="./update.php?id=<?php echo $collectionId; ?>">Update now</a> (may take a few minutes)
	<p><a href="./index.php?add-id=<?php echo $collectionId; ?>">Start over with this seed</a>
	<p><a href="./index.php">Start over fresh</a>
 	<p><a href="javascript:PopupRawReportText()">Download as text</a>
 	<p><a href="javascript:PopupRawCsv()">Download as CSV</a>

<p>Stable url: <a href="<?php echo "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . "?id=" . $collectionId; ?>"><?php echo "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . "?id=" . $collectionId; ?></a></p>  


        </div>
        <div id="metrics">
            <?php
				echo "$rendered_report_text";
            ?>
        </div>
    </div>
<div id="footer" class="section">
    <h3>Metrics are computed based on the following data sources:</h3>

    <?php
	echo "$rendered_about_text";
	?>

In this initial release, a snapshot of the impact data is captured the first time an url is displayed. In the future we are planning to periodically refresh the impact values.


</div>

<p><a href="https://cloudant.com/futon/document.html?total-impact%2Fdevelopment/<?php echo $_REQUEST['id']; ?>">Link to DB entry</a>

	<pre>
	<script language="javascript" type="text/javascript">
	<!---
	function PopupRawReportText()
	{
		//console.log("got called");

		//console.log(str);
		newwindow = window.open('','export',"width=320,height=210,scrollbars=yes");
		var doc = newwindow.document;
		doc.write("data:text/plain;charset=utf-8,");
		<?php $raw_report_text = json_decode($report->render_as_plain_text()); ?>
		doc.write("<?php echo $raw_report_text; ?><p>");
		doc.close();
			
	}
	function PopupRawCsv()
	{
		//console.log("got called");

		//console.log(str);
		newwindow = window.open('','export',"width=320,height=210,scrollbars=yes");
		var doc = newwindow.document;
		doc.write("data:text/plain;charset=utf-8,");
		<?php $raw_csv_text = json_decode($report->render_as_csv()); ?>
		doc.write("<?php echo $raw_csv_text; ?><p>");
		doc.close();
			
	}
	-->
	</script>
	</pre>


</body>
</html>
