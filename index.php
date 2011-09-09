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
		</div>
		<?php
			if (isset($_REQUEST['run'])) {
				$query_string = $_SERVER['QUERY_STRING'];
            	echo "<h2 class='loading'><img src='./ui/img/ajax-loader.gif'> Getting information now</h2>";
            	echo "<script>location.href='./update.php?$query_string'</script>";
			}
			else {
				$artifactIdsString = $_REQUEST['list'];
				$title = $_REQUEST['name'];
				$seed = new Models_Seed();
				if (isset($_REQUEST['add-id'])) {
					$collectionId = $_REQUEST['add-id'];
	           		$config = new Zend_Config_Ini(CONFIG_PATH, ENV);
	            	$collection = new Models_Collection();
					$doc = $collection->fetch($collectionId, $config);
					$title = $doc->title;
					$artifactIds = $doc->artifact_ids;
					$artifactIdsString .= implode('&#013;&#010;', $artifactIds);
				}
				if (isset($_REQUEST['add-mendeley-profile'])) {
					$artifactIds = $seed->getMendeleyProfileArtifacts($_REQUEST['add-mendeley-profile']);
					$artifactIdsString .= '&#013;&#010;';
					$artifactIdsString .= implode('&#013;&#010;', $artifactIds);
				}
				if (isset($_REQUEST['add-slideshare-profile'])) {
					$artifactIds = $seed->getSlideshareProfileArtifacts($_REQUEST['add-slideshare-profile']);
					$artifactIdsString .= '&#013;&#010;';
					$artifactIdsString .= implode('&#013;&#010;', $artifactIds);
				}
				if (isset($_REQUEST['add-dryad-profile'])) {
					$artifactIds = $seed->getDryadProfileArtifacts($_REQUEST['add-dryad-profile']);
					$artifactIdsString .= '&#013;&#010;';
					$artifactIdsString .= implode('&#013;&#010;', $artifactIds);
				}
			}
     	?>
        <div id="header">
            Welcome to total-impact. This site allows you to track the impact of various online 
            research artifacts. It grabs metrics from many different sites and displays them all in one place.
        </div>

        <div id="instr">
            Enter the identifiers for the artifacts you want to track below. We'll give you a url for that set that automatically updates everytime you visit the page.</p>
            <p>To try it out, copy and paste these identifers below and hit Go!</p>
            <pre>
10.1371/journal.pbio.0050082
http://www.slideshare.net/phylogenomics/eisenall-hands
10.5061/dryad.8384
GSE2109</pre>
        </div>   

	<table>
	<tr>
        <form method="GET" name="main" action="./index.php">
		<td>
           <label for="name">What's your name?</label>
           <input name="name" id="name" value="<?php echo $title; ?>" />
           <br>
           <br>
           
           <label for="list">Put your IDs here:</label><br>
           <textarea rows=20 cols=60 name="list" id="list"><?php echo $artifactIdsString; ?></textarea>
           
           <input type="submit" name="run" value="Go!" />

		</td>
		<td>
			
		Want to add your data quickly?  Seed input from one of these sources:

		<hr>
		
		<p>Mendeley profile <b>publicly available</b> publications:
            <label for="add-mendeley-profile">Username <br><em>http://www.mendeley.com/profiles/</em></label>
            <input name="add-mendeley-profile" id="add-mendeley-profile" size="40" placeholder="graham-steel/"/>

		<hr>

		<div class="disabled">
		<p>Mendeley public group papers:
            <label for="add-mendeley-group">Group number <br><em>http://www.mendeley.com/groups/</em></label>
            <input name="add-mendeley-group" id="add-mendeley-group" size="40" placeholder="1389803"/>
		</div>
		<hr>

		<p>Slideshare public slidedecks:
            <label for="add-slideshare-profile">Username <br><em>http://slideshare.net/</em></label>
            <input name="add-slideshare-profile" id="add-slideshare-profile" size="40" placeholder="cavlec"/>

		<hr>
        
		<p>Dryad data packages <br>(dc:contributor.author value in "Show Full Metadata" from data package page):
            <label for="add-dryad-profile">Dryad author name</label>
            <input name="add-dryad-profile" id="add-dryad-profile" size="40" placeholder="Otto, Sarah P."/>


		<br/><input type="submit" name="add" value="Add!" />
        </form>

		</td>
	</tr>
	</table>

<em>
<p>If the demo appears broken right now, try this link to see a sample report:
<a href="http://total-impact.org/report.php?id=hljHeI">http://total-impact.org/report.php?id=hljHeI</a>
<p>
<b>Total-Impact <a href="http://www.mendeley.com/blog/developer-resources/what-the-scientific-community-wants-computers-to-do-for-them-the-results-of-the-plos-and-mendeley-call-for-apps/">needs more developers!</a>  Join us?</b>  Email total-impact@googlegroups.com
</em>

        <div id="footer">
            <p>
            Hacked at the <a href="http://www.beyond-impact.org/">Beyond Impact Workshop</a>. <a href="https://github.com/mhahnel/total-impact">Source and contributors.</a>
            </p>
        </div>
    </body>
</html>
