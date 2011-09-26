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

<script type="text/javascript">
function xmlhttpPost(strURL, type, form_name, field_name, display_div_name) {
    var xmlHttpReq = false;
    var self = this;
	
    // Mozilla/Safari
    if (window.XMLHttpRequest) {
        self.xmlHttpReq = new XMLHttpRequest();
    }
    // IE
    else if (window.ActiveXObject) {
        self.xmlHttpReq = new ActiveXObject("Microsoft.XMLHTTP");
    }

	document.getElementById(display_div_name).innerHTML = "Loading...";
    self.xmlHttpReq.open('GET', strURL + "?" + getquerystring(type, form_name, field_name), true);
    self.xmlHttpReq.onreadystatechange = function() {
        if (self.xmlHttpReq.readyState == 4) {
			//document.getElementById('mendeley_profile_div').innerHTML = "Hello, <b>AJAX</b> world!";
			if (type == "mendeley_profile_quick") {
            	update_quick_report(self.xmlHttpReq.responseText, display_div_name);
			} else {
            	update_list(self.xmlHttpReq.responseText, display_div_name);
			}
		}
    }
    self.xmlHttpReq.send();
}

function getquerystring(type, form_name, field_name) {
    var form = document.forms[form_name];
    var word = form[field_name].value;
    qstr = 'type=' + escape(type) + '&name=' + escape(word);  // NOTE: no '?' before querystring
    return qstr;
}

function update_list(str, display_div_name){
	var decoded = eval( "(" + str + ")" );
	var artifactIds = decoded["artifactIds"];
		
    document.getElementById(display_div_name).innerHTML = "Added.";
    //document.getElementById(display_div_name).innerHTML = str;
	document.forms["id_form"].list.value = artifactIds + "\n" + document.forms["id_form"].list.value ;
}

function update_quick_report(str, display_div_name){
	var decoded = eval( "(" + str + ")" );
	var groups = decoded["groups"];
	var contacts = decoded["contacts"];
		
	if (groups.length > 0) {
    	document.getElementById(display_div_name).innerHTML = "<table border=0><tr><td>" + contacts + "<em>(random selection)</em></td><td>" + groups + "</td></tr></table>";
	}
}

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
			           <textarea rows=20 name="list" id="list"><?php echo $artifactIdsString; ?></textarea>
			           <input type="submit" name="run" value="Go!" />
					</form>
							
				</div>
				<div id="rightcol">
					Want help gathering your IDs? Pull from these sources:
					<hr />
					
					<p>Mendeley profile <b>publicly available</b> publications:
						<form name="mendeley_profile_form">
			            	<label for="name_field"><em>http://www.mendeley.com/profiles/</em></label>
				            <input name="name_field" type="text" size="20" placeholder="cameron-neylon"/>
							<input value="Add IDs!" type="button" 
								onclick='JavaScript:xmlhttpPost("./seed.php", "mendeley_profile", "mendeley_profile_form", 
																"name_field", "mendeley_profile_div")'></p>

							<div id="mendeley_profile_div">
							</div>
						</form>

					<hr />
			
					<p>Mendeley public group papers:
						<form name="mendeley_group_form">
			            	<label for="name_field"><em>http://www.mendeley.com/group/</em></label>
				            <input name="name_field" type="text" size="20" placeholder="1389803"/>
							<input value="Add IDs!" type="button" 
								onclick='JavaScript:xmlhttpPost("./seed.php", "mendeley_group", "mendeley_group_form", 
																"name_field", "mendeley_group_div")'></p>

							<div id="mendeley_group_div">
							</div>
						</form>

					<hr />
			
					<p>Slideshare public slidedecks:
						<form name="slideshare_profile_form">
			            	<label for="name_field"><em>http://slideshare.net/</em></label>
				            <input name="name_field" type="text" size="20" placeholder="cavlec"/>
							<input value="Add IDs!" type="button" 
								onclick='JavaScript:xmlhttpPost("./seed.php", "slideshare_profile", "slideshare_profile_form", 
																"name_field", "slideshare_profile_div")'></p>

							<div id="slideshare_profile_div">
							</div>
						</form>
			
					<hr />
			        
					<p>Dryad data packages <br>(dc:contributor.author value in "Show Full Metadata" from data package page):
						<form name="dryad_profile_form">
			            	<label for="name_field">Dryad author name</label>
				            <input name="name_field" type="text" size="20" placeholder="Otto, Sarah P."/>
							<input value="Add IDs!" type="button" 
								onclick='JavaScript:xmlhttpPost("./seed.php", "dryad_profile", "dryad_profile_form", 
																"name_field", "dryad_profile_div")'></p>

							<div id="dryad_profile_div">
							</div>
						</form>

					<hr />

					<p>PubMed IDs through Grant Numbers<br>
						<form name="pubmed_grant_form">
			            	<label for="name_field">Grant number</label>
				            <input name="name_field" type="text" size="20" placeholder="U54-CA121852"/>
							<input value="Add IDs!" type="button" 
								onclick='JavaScript:xmlhttpPost("./seed.php", "pubmed_grant", "pubmed_grant_form", 
																"name_field", "pubmed_grant_div")'></p>

							<div id="pubmed_grant_div">
							</div>
						</form>
						
				</div>
			</div>
			
			<div id="quickreport">
			<h2><a name="quick">quick reports</a></h2>
				
				<p>Want to see a quick report for your Mendeley contacts or public groups?</p>
					<form name="mendeley_profile_quick_form">
		            	<label for="name_field"><em>http://www.mendeley.com/profiles/</em></label>
			            <input name="name_field" type="text" size="20" placeholder="cameron-neylon"/>
						<input value="Pull" type="button" 
							onclick='JavaScript:xmlhttpPost("./seed.php", "mendeley_profile_quick", "mendeley_profile_quick_form", 
															"name_field", "mendeley_profile_quick_div")'></p>

						<div id="mendeley_profile_quick_div">
						</div>
					</form>
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
				
				<p><strong>Total-Impact</strong> <a href="http://www.mendeley.com/blog/developer-resources/what-the-scientific-community-wants-computers-to-do-for-them-the-results-of-the-plos-and-mendeley-call-for-apps/">needs more developers!</a>  Join us? <a href="mailto:total-impact@googlegroups.com">total-impact@googlegroups.com</a></p>

            	<p>Concept originally hacked at the <a href="http://www.beyond-impact.org/">Beyond Impact Workshop</a>. <a href="https://github.com/mhahnel/total-impact">Source and contributors.</a></p>
			</div>
			<!-- END footer -->
		</div>
		<!-- END wrapper -->
    </body>
</html>