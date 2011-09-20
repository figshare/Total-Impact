<?php require_once './bootstrap.php'; 
#require_once 'FirePHPCore/fb.php';

// TRUE = disable all output buffering, 
// and automatically flush() 
// immediately after every print or echo 
ob_implicit_flush(TRUE);

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
	            <img src="./ui/img/ti_logo.png" alt="total-impact" width='200px' /> 
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
	        	<p>Total Impact tracks the real-time online impact of various research artifacts. It aggregates impact metrics from many data sources and displays them all in one place.</p>
	        </div>
			<!-- END header -->

			<!-- START instr -->

	        <div id="instr">
	            <p>Enter below the identifiers for a collection of artifacts you want to track. We'll provide you a permanent URL to automatically update statistics about this collection.</p>
	            <p>To try it out, copy and paste these identifers below and hit Go! (or follow <a href="http://total-impact.org/report.php?id=hljHeI">this link</a> for a sample report)</p>
	            <pre>
	10.1371/journal.pbio.0050082
	10.1371/journal.pone.0000308
	http://www.slideshare.net/phylogenomics/eisenall-hands
	10.5061/dryad.8384
	GSE2109</pre>
			</div>
			<!-- END instr -->
			
			<!-- START input -->
			<div id="input"> 	
				<form method="GET" name="main" action="./index.php">
				<div id="leftcol">
			           <label for="name">Choose a name for this collection</label><br />
			           <input name="name" id="name" value="<?php echo $title; ?>" />
			           <br />
			           
			           <label for="list">List your IDs here:</label><br>
			           <textarea rows=20 name="list" id="list"><?php echo $artifactIdsString; ?></textarea>
			           <input type="submit" name="run" value="Go!" />		
				</div>
				<div id="rightcol">
					Want to add your data quickly?  Seed input from one of these sources:
			
					<hr />
					
					<p>Mendeley profile <b>publicly available</b> publications:
			            <label for="add-mendeley-profile">Username <br><em>http://www.mendeley.com/profiles/</em></label>
			            <input name="add-mendeley-profile" id="add-mendeley-profile" size="40" placeholder="cameron-neylon"/>
			
					<hr>
			
					<div class="disabled">
					<p>Mendeley public group papers:
			            <label for="add-mendeley-group">Group number <br><em>http://www.mendeley.com/groups/</em></label>
			            <input name="add-mendeley-group" id="add-mendeley-group" size="40" placeholder="1389803"/>
					</div>
					<hr />
			
					<p>Slideshare public slidedecks:
			            <label for="add-slideshare-profile">Username <br><em>http://slideshare.net/</em></label>
			            <input name="add-slideshare-profile" id="add-slideshare-profile" size="40" placeholder="cavlec"/>
			
					<hr />
			        
					<p>Dryad data packages <br>(dc:contributor.author value in "Show Full Metadata" from data package page):
			            <label for="add-dryad-profile">Dryad author name</label>
			            <input name="add-dryad-profile" id="add-dryad-profile" size="40" placeholder="Otto, Sarah P."/>
			
			
					<br /><input type="submit" name="add" value="Add!" />
				</div>
		        </form>
				<!--<p>Adding artifacts from profile pages currently only adds the first full page of artifacts, not all pages</p>-->
				<p><strong>Total-Impact</strong> <a href="http://www.mendeley.com/blog/developer-resources/what-the-scientific-community-wants-computers-to-do-for-them-the-results-of-the-plos-and-mendeley-call-for-apps/">needs more developers!</a>  Join us? <a href="mailto:total-impact@googlegroups.com">total-impact@googlegroups.com</a></p>
			</div>
			<!-- END input -->

			<!-- START footer -->
			<div id="footer">
            	<p>Concept originally hacked at the <a href="http://www.beyond-impact.org/">Beyond Impact Workshop</a>. <a href="https://github.com/mhahnel/total-impact">Source and contributors.</a></p>
			</div>
			<!-- END footer -->
		</div>
		<!-- END wrapper -->
    </body>
</html>