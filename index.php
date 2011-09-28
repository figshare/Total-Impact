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

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
<script type="text/javascript">

$.ajaxSetup ({  
    cache: false  
}); 
var ajax_load = "<img src='./ui/img/ajax-loader.gif' alt='loading...' />";  

$(document).ready(function(){
		
  $("button").click(function(){
	var myId = this.id
	var textId = this.id + "_input";
	var textVal = $('#'+textId).val();
	var divId = this.id + "_div";
    $("#"+divId).html("Loading...");
	$.get("./seed.php?type="+myId+"&name="+textVal, function(response,status,xhr){
		if (myId=="quick_report") {
			var groups = response["groups"];
			var contacts = response["contacts"];
			var fullText = "<table border=0><tr><td>" + contacts + "<em>(random selection)</em></td><td>" + groups + "</td></tr></table>";
			$("#quick_report_div").html(fullText);
		} else {
			$("#artifactList").val(response["artifactIds"] + "\n" + $("#artifactList").val());
	    	$("#"+divId).html("Added " + response["artifactCount"] + " IDs.");
		}
	}, 
	"json"); 
	}).error(function(){ alert("error!");}); 
  });
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
					$title = "";
					$artifactIdsString = "";
					if (isset($_REQUEST['list'])) {
						$artifactIdsString = $_REQUEST['list'];
					}
					if (isset($_REQUEST['name'])) {
						$title = $_REQUEST['name'];
					}
					if (isset($_REQUEST['add-id'])) {
						$collectionId = $_REQUEST['add-id'];
		           		$config = new Zend_Config_Ini(CONFIG_PATH, ENV);
		            	$collection = new Models_Collection();
						$doc = $collection->fetch($collectionId, $config);
						$title = $doc->title;
						$artifactIds = $doc->artifact_ids;
						$artifactIdsString .= implode('&#013;&#010;', $artifactIds);
					}
				}
	     	?>
	        	<p>Total Impact tracks the real-time online impact of various research artifacts. It aggregates impact metrics from many data sources and displays them all in one place.</p>
	        </div>
			<!-- END header -->

			<!-- START instr -->

	        <div id="instr">
	            <p>Enter below the identifiers for a collection of artifacts you want to track. We'll provide you a permanent URL to automatically update statistics about this collection.  Peruse <a target="_blank" href="./report.php?id=hljHeI">a sample</a>, <a href="#quick">quick reports</a>, and <a href="#recent">recently-shared reports</a>.</p>
	
	            <p>To try it out, copy and paste these identifers below and hit <b>Go!</b> 
	            <pre>
	10.1371/journal.pbio.0050082
	10.1371/journal.pone.0000308
	http://www.slideshare.net/phylogenomics/eisenall-hands
	10.5061/dryad.8384
	GSE2109
	</pre>
			</div>
			<!-- END instr -->
			
			<!-- START input -->
			<div id="input"> 	
				<div id="leftcol">
					<form name="id_form">
			           <label for="name">Choose a name for this collection</label><br />
			           <input name="name" id="name" value="<?php echo $title; ?>" />
			           <br />
			           
			           <label for="list">List your IDs here:</label><br>
			           <textarea rows=20 name="list" id="artifactList"><?php echo $artifactIdsString; ?></textarea>
			           <input type="submit" name="run" value="Go!" />
					</form>
							
				</div>
				<div id="rightcol">
					Want help gathering your IDs? Pull from these sources:
					<hr />
					
					<p>Mendeley profile <b>publicly available</b> publications:
					<p><em>http://www.mendeley.com/profiles/</em>
					<input id="mendeley_profile_input" name="profileId" type="text" size="20" value="cameron-neylon"/>
					<button id="mendeley_profile">Add IDs!</button>
					</p>
					<div id="mendeley_profile_div">
					</div>
					<hr />
			
					<p>Mendeley public group publications:
					<p><em>http://www.mendeley.com/group/</em>
				    <input id="mendeley_group_input" name="groupId" type="text" size="20" value="1389803"/>
					<button id="mendeley_group">Add IDs!</button>
					</p>
					<div id="mendeley_group_div">
					</div>
					<hr />
			
					<p>Slideshare public slidedecks:
					<p><em>http://www.slideshare.net/</em>
				    <input id="slideshare_profile_input" name="slideshareName" type="text" size="20" value="cavlec"/>
					<button id="slideshare_profile">Add IDs!</button>
					</p>
					<div id="slideshare_profile_div">
					</div>
					<hr />
			        
					<p>Dryad data packages <br>(dc:contributor.author value in "Show Full Metadata"):
					<p><em>Dryad author name</em>
				    <input id="dryad_profile_input" name="dryadName" type="text" size="20" value="Otto, Sarah P."/>
					<button id="dryad_profile">Add IDs!</button>
					</p>
					<div id="dryad_profile_div">
					</div>
					<hr />

					<p>PubMed IDs through Grant Numbers<br>
					<p><em>Grant number</em>
				    <input id="pubmed_grant_input" name="grantId" type="text" size="20" value="U54-CA121852"/>
					<button id="pubmed_grant">Add IDs!</button>
					</p>
					<div id="pubmed_grant_div">
					</div>
					<hr />
						
				</div>
			</div>
			
			<div id="quick_report_section">
			<h2><a name="quick">quick reports</a></h2>
				
				<p>Want to see a quick report for your Mendeley contacts or public groups?</p>
		        <p><em>http://www.mendeley.com/profiles/</em>
	            <input id="quick_report_input" name="profileId" type="text" size="20" value="cameron-neylon"/>
				<button id="quick_report">Pull Mendeley links</button>
				</p>
				<div id="quick_report_div">
				</div>
			</div>
						
			<!-- END input -->

			<!-- START footer -->

			<div id="twitterfeed">

				<h2><a name="recent">recently-shared reports</a></h2>
				<p>Check them out:</p>
				<!-- https://twitter.com/about/resources/widgets/widget_search -->
			
				<script src="http://widgets.twimg.com/j/2/widget.js"></script>
				<script>
					new TWTR.Widget({
					  version: 2,
					  type: 'search',
					  search: 'via @mytotalimpact',
					  interval: 30000,
					  title: 'Recent public reports: "via @mytotalimpact"',
					  subject: 'Tweet yours to see it here!',
					  width: 'auto',
					  height: 200,
					  theme: {
					    shell: {
					      background: '#ccc',
					      color: '#000'
					    },
					    tweets: {
					      background: '#ccc',
					      color: '#000',
					      links: '#933'
					    }
					  },
					  features: {
					    scrollbar: true,
					    loop: false,
					    live: true,
					    hashtags: true,
					    timestamp: true,
					    avatars: true,
					    toptweets: true,
					    behavior: 'all'
					  }
					}).render().start();
				</script>

			</div>

			<div id="footer">
				<p>Missing something? See <a href="./about.php#Limitations">current limitations.</a></p>
				<p>Reactions and bugs welcome to <a href="http://twitter.com/#!/totalimpactdev">@totalimpactdev</a></p>
				
				<p><strong>Total-Impact</strong> <a href="http://www.mendeley.com/blog/developer-resources/what-the-scientific-community-wants-computers-to-do-for-them-the-results-of-the-plos-and-mendeley-call-for-apps/">needs more developers!</a>  Join us? <a href="mailto:total-impact@googlegroups.com">total-impact@googlegroups.com</a></p>

            	<p>Concept originally hacked at the <a href="http://www.beyond-impact.org/">Beyond Impact Workshop</a>. <a href="https://github.com/mhahnel/total-impact">Source and contributors.</a></p>
			</div>
			<!-- END footer -->
		</div>
		<!-- END wrapper -->
    </body>
</html>