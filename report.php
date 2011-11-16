<?php
require_once './bootstrap.php';
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
	//@@@TODO Make file format and name configurable
	$rendered_report_text = $report->render_as_list();
	$filename = "total-impact_".$collectionId."_".date('Y-m-d').".tsv";
	header('Content-Disposition: attachment; filename="'.$filename.'"');
	header('Content-Type: text/tab-delimited-values; charset=utf-8');
	echo $rendered_report_text;
} else {
	if ($mode == "status") {
		$rendered_report_text = $report->render_status();
		$rendered_about_text = $report->render_about_text();	
	} else {
		$mode = "base";
		$rendered_report_text = $report->render(False);  //don't show zeros
		$rendered_about_text = $report->render_about_text();	
	}

// handle missing IDs more intelligently later
if (!$res){ header('Location: ../'); }

include_once('./header.php');
?>
		    <div id="report">
				<!-- START report-meta -->
		        <div id="report-meta">
                                <h2>report for <span class="title"><?php echo $report->getBestIdentifier(); ?></span></h2>
                                <a class="report-button" href="./report.php?id=<?php echo $collectionId; ?>&mode=list">download data</a>
                                <!--a class="report-button" href="./?add-id=<?php echo $collectionId; ?>">refine</a-->
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
                                      data-text="<?php echo "Check out the total-Impact of " . $report->getBestIdentifier() . ": ";?>"
                                      data-count="horizontal">Tweet</a>
                                    </span>
                                    <span id="permalink"><span class="label"> Permalink: </span><a class="copyable", href="<?php echo "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . "?id=" . $collectionId; ?>"><?php echo "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . "?id=" . $collectionId; ?></a><a href="#" id="copy-permalink">copy</a></span>
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
			<p class="something-missing">More detail on <a target="_blank" href="./about.php#whichmetrics">available metrics</a>.  Missing some artifacts or metrics? See <a target="_blank" href="./about.php#limitations">current limitations.</a>  </p>
						
		<div id="about-metrics" class="about-metrics">
		    <h3>Metrics are computed based on the following data sources:</h3>
		
		    <?php
			echo "$rendered_about_text";
			?>
		</div>
			<?php include_once('./footer.php'); ?>

	</body>
</html>
<?php } ?>