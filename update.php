<?php require_once './bootstrap.php'; 
#require_once 'FirePHPCore/fb.php';

// TRUE = disable all output buffering, 
// and automatically flush() 
// immediately after every print or echo 
ob_implicit_flush(TRUE);

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
		 "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:laImpactng="en">
    <head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <title>total-Impact: updating</title>
        <link href='http://fonts.googleapis.com/css?family=Lobster+Two:400italic' rel='stylesheet' type='text/css' />

        <link rel="stylesheet" type="text/css" href="./ui/totalimpact.css" />
		<link rel="icon" type="image/png" href="ui/favicon.ico">
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
    <body class="">
        
			<!-- START header -->
                <div id="header">
                    <h1><a href="./index.php">total-impact</a></h1>
                    <ul id="nav">
                        <li><a href="./about.php">about</a></li>
                        <li><a href="http://twitter.com/#!/totalImpactdev">twitter</a></li>
                    </ul>
                </div><!-- END header -->
                
		<!-- START wrapper -->
		<div id="wrapper">

	        
			<!-- START loading -->
	       	<div id="loading">
	        <?php		
	            // show the user some kind of updating screen
	            echo "<h2 class='loading'><img src='./ui/img/ajax-loader.gif'> we're compiling your report now...<span>(it generally takes a few minutes to cook)</span></h2>";
				#echo $_SERVER['QUERY_STRING'];
			?>
			</div>
			<!-- END loading -->
			<!-- START saving -->
	       	<div id="saving">
			<?php
	           	$config = new Zend_Config_Ini(CONFIG_PATH, ENV);
	            $collection = new Models_Collection();


				if (isset( $_REQUEST['id'])) {
					$collectionId = $_REQUEST['id'];
				} else {
					if (isset($_REQUEST['quickreport'])) {
						$seed = new Models_Seeder();
						if (isset($_REQUEST['mendeleygroup'])) {
							$artifactIdList = $seed->getMendeleyGroupArtifacts($_REQUEST['mendeleygroup']);
							$artifactIds = implode("\n", $artifactIdList); # \n has to be in DOUBLE quotes not single quotes
						} elseif (isset($_REQUEST['mendeleyprofile'])) {
							$artifactIdList = $seed->getMendeleyProfileArtifacts($_REQUEST['mendeleyprofile']);
							$artifactIds = implode("\n", $artifactIdList); # \n has to be in DOUBLE quotes not single quotes
						}
					} else {
						$artifactIds = $_REQUEST['list'];
					}
				
					if (isset($_REQUEST['name'])) {
						$title = $_REQUEST['name'];
					} else {
						$title = "";
					}

		            // save the new collection
		            $storedDoc = $collection->create($title, $artifactIds , $config);
		            $collectionId = $storedDoc->id;
				}			
			?>
			</div>
			<!-- END saving -->
			<!-- START updating -->
			<!-- END updating -->
			<!-- START footer -->
			<!-- END footer -->
		</div>
		<!-- END wrapper -->
        <div id="footer">
        an <a class="img" href="http://altmetrics.org" title="an altmetrics project"><img src="./ui/img/altmetrics_logo.png" alt="altmetrics" width="80"/></a> project.
        </div>
        <div id="updating">
                <?php
                        error_log("now update");
            // get the updates
                        $collection->update($collectionId, $config);
            // redirect to the report page for this plugin
            echo "<script>location.href='./report.php?id=$collectionId'</script>";
                ?>

        </div>
    </body>
</html>
