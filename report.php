<?php
require_once './bootstrap.php';
#require_once 'FirePHPCore/fb.php';

$config = new Zend_Config_Ini(CONFIG_PATH, ENV);
$dbCreds = new Zend_Config_Ini(CREDS_PATH, 'db');
$couch = new Couch_Client($dbCreds->dsn, $dbCreds->name);
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
    $filename = "total-impact_" . $collectionId . "_" . date('Y-m-d') . ".tsv";
    header('Content-Disposition: attachment; filename="' . $filename . '"');
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
    if (!$res) {
        header('Location: ../');
    }

    define("PAGE_SUBTITLE", $report->getBestIdentifier());
    include_once('./header.php');
?>
    <div id="report">    
        <!-- START report-meta -->    
        <div class="wrapper">    
            <div id="report-meta">    
                <h2><span class="title"><?php echo $report->getBestIdentifier(); ?></span></h2>
                <div id="report-button">
                    <a class="report-button" id="update-report-button" name="<?php echo $collectionId; ?>" href="#">run update</a>
                    <a class="report-button" href="/csv/<?php echo $collectionId; ?>">download data</a>
                </div>
                <div id="report-info">
                    <span class="badge artifacts-count"><span class="num"><?php echo $report->getArtifactsCount(); ?></span> artifacts;</span>
                    <span class="badge updated-at">updated <span class="date"><?php echo $report->getUpdatedAt('j M, Y'); ?></span></span>
                </div>
                <!--<span class="badge created-at">created <?php echo $report->getCreatedAt('j M, Y'); ?></span>-->
                <!--a class="report-button" href="./?add-id=<?php echo $collectionId; ?>">refine</a-->
    
    
                <div id="share">    
                    <!-- based on code here: https://dev.twitter.com/docs/tweet-button -->    
                    <span id="permalink"><span class="label"> Permalink: </span><a class="copyable", href="<?php echo "http://" . $_SERVER['HTTP_HOST'] . "/collection/" . $collectionId; ?>"><?php echo "http://" . $_SERVER['HTTP_HOST'] . "/collection/" . $collectionId; ?></a><a href="#" id="copy-permalink">copy</a></span>
                    <script src="//platform.twitter.com/widgets.js" type="text/javascript"></script>    
                    <span class="tweet-this">    
                        <a href="https://twitter.com/share" class="twitter-share-button"    
                           data-url="<?php echo "http://total-impact.org/collection/" . $collectionId ?>"
                           data-via="mytotalImpact"    
                           data-text="<?php echo "Check out the total-Impact of " . $report->getBestIdentifier() . ": "; ?>"
                           data-count="horizontal">Tweet</a>    
                    </span>    
                </div>    
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
        </div><!-- END metrics -->
    </div><!-- END report -->

<!-- START footer -->
<p class="something-missing">More detail on <a href="./about/#whichmetrics">available metrics</a>.  Missing some artifacts or metrics? See <a href="./about/#limitations">current limitations.</a>  </p>

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
