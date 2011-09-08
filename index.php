<?php require_once './bootstrap.php'; 
#require_once 'FirePHPCore/fb.php';

// TRUE = disable all output buffering, 
// and automatically flush() 
// immediately after every print or echo 
ob_implicit_flush(TRUE);

?><!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>total-impact.org</title>
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
        <div id="header">
            <img src="./ui/img/logo.png" alt="total-impact" width='200px' /> 
            <p>
            Welcome to total-impact. This site allows you to track the impact of various online 
            research artifacts. It grabs metrics from many different sites and displays them all in one place.

            </p>
        </div>
        
        <div id="instr">
<em>
<p>If the demo appears broken right now, try this link to see a sample report:
<a href="http://total-impact.org/report.php?id=hljHeI">http://total-impact.org/report.php?id=hljHeI</a>
<p>
<b>Total-Impact <a href="http://www.mendeley.com/blog/developer-resources/what-the-scientific-community-wants-computers-to-do-for-them-the-results-of-the-plos-and-mendeley-call-for-apps/">needs more developers!</a>  Join us?</b>  Email total-impact@googlegroups.com
</em>

            <p>Enter the identifiers for the artifacts you want to track below. We'll give you a url for that set that automatically updates everytime you visit the page.</p>
            <p>To try it out, copy and paste these identifers below and hit Go!</p>
            <pre>
10.1371/journal.pbio.0060048
10.1371/journal.pbio.0050082
http://www.slideshare.net/phylogenomics/eisen
http://www.slideshare.net/phylogenomics/ben-franklin-award-slides
10.5061/dryad.8384
GSE2109
GSE22484
        </pre>
        </div>   

		<?php
			if (isset($_REQUEST['seed-id'])) {
				#FB::log("got seed-id");
				$collectionId = $_REQUEST['seed-id'];
           		$config = new Zend_Config_Ini(CONFIG_PATH, ENV);
            	$collection = new Models_Collection();
				$doc = $collection->fetch($collectionId, $config);
				$artifactIds = $doc->artifact_ids;
				$title = $doc->title;
				$artifactIdsString = implode('&#013;&#010;', $artifactIds);

			} else {
				$artifactIdsString = "";
				$title = "";
			}
     	?>

        <form method="PUT" name="main" action="./update.php">
            <label for="name">What's your name?</label>
            <input name="name" id="name" value="<?php echo $title; ?>" />
            <br>
            <br>
            
            <label for="list">Put your IDs here:</label><br>
            <textarea rows=10 cols=80 name="list" id="list"><?php echo $artifactIdsString; ?></textarea>
            
            <input type="hidden" name="submitted" value="true" /><br>
            <input type="submit" id="submit" value="Go!" />
        </form>
        
        <div id="footer">
            <p>
            Hacked at the <a href="http://www.beyond-impact.org/">Beyond Impact Workshop</a>. <a href="https://github.com/mhahnel/total-impact">Source and contributors.</a>
            </p>
        </div>
    </body>
</html>
