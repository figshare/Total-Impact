<?php 

require_once './bootstrap.php'; 
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
        <title>total-Impact</title>
        <link href='http://fonts.googleapis.com/css?family=Lobster+Two:400italic' rel='stylesheet' type='text/css' />
        <link rel="stylesheet" type="text/css" href="./ui/totalimpact.css" />
		<link rel="icon" type="image/png" href="ui/favicon.ico" />

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
<!--<script type="text/javascript" src="./ui/jquery/jquery.qtip-1.0.0-rc3.min.js"></script>-->
<script type="text/javascript">

$.ajaxSetup ({  
    cache: false  
}); 
var ajax_load = "<img src='./ui/img/ajax-loader.gif' alt='loading...' />";  

$(document).ready(function(){
    // tooltips

		
  $("button").click(function(){
	var myId = this.id;
	if ((myId.substring(0, 12) == "quick_report")) {
		var textId = "mendeley_profile_input";		
		var textVal = $('#'+textId).val();
		var divId = "quick_report_div";
	} else {
		var textId = this.id + "_input";		
		var textVal = $('#'+textId).val();
		var divId = this.id + "_div";
	}
    $("#"+divId).html("Loading...");
	$.get("./seed.php?type="+myId+"&name="+textVal, function(response,status,xhr){
		if (myId=="quick_report_contacts") {
			$("#"+divId).html("Click to go directly to report:<p/>" + response["contacts"]);
		} else if (myId=="quick_report_groups") {
			$("#"+divId).html("Click to go directly to report:<p/>" + response["groups"]);
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
    <body class="home">
    	<!-- START header -->
        <div id="header">
            <h1><a href="./index.php">total-impact</a></h1>
            <ul id="nav">
                <li><a href="./about.php">about</a></li>
                <li><a href="http://twitter.com/#!/totalImpactdev">twitter</a></li>
            </ul>
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
        </div>
	<!-- START wrapper -->
        <div id="wrapper">
            <div id="about">
                <p id="tagline">uncovering the invisible impacts of your research.</p>
                <div id="instr">
                    <p class="howto">Enter the identifiers for a collection of artifacts you want to track. We'll provide you a permanent URL to track statistics about this collection. You can peruse <a target="_blank" href="./report.php?id=MqAnvI">a sample</a> and <a href="#recent">recently-shared reports</a>.</p>
                </div>
            </div><!-- END about -->

            <!-- START input -->
            <div id="input">
                    <div id="enter-mendeley">
                            <!--Want help gathering your IDs? Pull from these sources:-->
                            <fieldset><legend><span>ids from</span> Mendeley</legend>
                            <p class="prompt">Your Mendeley group URL<a class="tooltip" onmouseover="tooltip.show('Fill in the URL of your public Mendeley to import the references shared within group</em>', 300);" onmouseout="tooltip.hide();"><sup>?</sup></a></p>
                            <em class="url">http://www.mendeley.com/group/</em>
                        <input id="mendeley_group_input" name="groupId" type="text" size="20" value="530031"/>
                            <button id="mendeley_group">Import</button>
                            <div id="mendeley_group_div">
                            </div>

                            <p class="prompt">Your Mendeley profile URL<a class="tooltip" onmouseover="tooltip.show('Fill in the URL of your public Mendeley profile to import the references of your publications', 300);" onmouseout="tooltip.hide();"><sup>?</sup></a></p>
                            <em class="url">http://www.mendeley.com/profiles/</em>
                            <table><tr><td>
                            <input id="mendeley_profile_input" name="profileId" type="text" size="20" value="heather-piwowar"/>
                            </td><td>
                            <br/><button id="mendeley_profile">Import profile pubs</button>
                            <br/><button id="quick_report_contacts">Pull my contacts</button><a class="tooltip" onmouseover="tooltip.show('Fill in the URL of your public Mendeley profile to get direct links to reports for your contacts', 300);" onmouseout="tooltip.hide();"><sup>?</sup></a>
                            <br/><button id="quick_report_groups">Pull my groups</button><a class="tooltip" onmouseover="tooltip.show('Fill in the URL of your public Mendeley profile to get direct links to reports for your PUBLIC groups', 300);" onmouseout="tooltip.hide();"><sup>?</sup></a>
                            </td></tr></table>
                            <div id="mendeley_profile_div">
                            </div>
                            <div id="quick_report_div">
                            </div>

                            </fieldset>

                    </div>

                    <div id="enter-other">
                            <fieldset><legend><span>ids from</span> Slideshare</legend>
                            <p class="prompt">Your Slideshare profile URL<a class="tooltip" onmouseover="tooltip.show('Fill in your Slideshare profile to import your public slidedecks', 300);" onmouseout="tooltip.hide();"><sup>?</sup></a></p>
                            <em class="url">http://www.slideshare.net/</em>
                        <input id="slideshare_profile_input" name="slideshareName" type="text" size="20" value="cavlec"/>
                            <button id="slideshare_profile">Import</button>
                            <div id="slideshare_profile_div">
                            </div>
                            </fieldset>

                            <fieldset><legend><span>ids from</span> Dryad</legend>
                            <p class="prompt">Your Dryad author name<a class="tooltip" onmouseover="tooltip.show('Fill in the dc:contributor.author value in <em>Show Full Metadata</em> to retrieve your datasets', 300);" onmouseout="tooltip.hide();"><sup>?</sup></a></p>
                        <input id="dryad_profile_input" name="dryadName" type="text" size="20" value="Otto, Sarah P."/>
                            <button id="dryad_profile">Import</button>
                            <div id="dryad_profile_div">
                            </div>
                            </fieldset>

                            <fieldset><legend><span>ids from</span> PubMed</legend>
                            <p class="prompt">Your Grant number<a class="tooltip" onmouseover="tooltip.show('Fill in your Grant number to retrieve publications from PubMed', 300);" onmouseout="tooltip.hide();"><sup>?</sup></a></p>
                        <input id="pubmed_grant_input" name="grantId" type="text" size="20" value="U54-CA121852"/>
                            <button id="pubmed_grant">Import</button>
                            <div id="pubmed_grant_div">
                            </div>
                            </fieldset>

							
                    </div>

                    <div id="enter-ids">
                            <form name="id_form">
                            <fieldset><legend>create a collection:</legend>
                       <p><label for="list">List your IDs here</label><a class="tooltip" onmouseover="tooltip.show('Valid identifiers, one per line.  Valid identifiers include DOIs, dataset accession numbers, handles for preprints, and URLs for code and slides.', 200);" onmouseout="tooltip.hide();"><sup>?</sup></a> <a target="_blank" href="examples.php">(cool examples)</a></p>
                       <textarea rows=15 name="list" id="artifactList"><?php echo $artifactIdsString; ?></textarea>

                       <p id="name-collection"><label for="name">Name this collection</label><a class="tooltip" onmouseover="tooltip.show('You can add a custom name to identify this collection', 200);" onmouseout="tooltip.hide();"><sup>?</sup></a></p>
                       <input name="name" id="name" value="<?php echo $title; ?>" />

                       <input type="submit" id="go-button" name="run" value="get my metrics!" />
                            </fieldset>
                            </form>

                    </div>

                            <!-- div class="floatr"><p><form action="./about.php" method="get" target="_blank"><input type="submit" class="FAQ" value="FAQ"></form></div -->




            </div>


			
            <!-- END input -->

            <!-- START footer -->

            <div id="twitterfeed">

					<!--moved down here because link didn't work when in div above for some reason -->
                    <div class="something-missing"><p>Something missing on import?<br/> See a list of <a href="./about.php#limitations">current limitations.</a> </p></div>

                    <h2><a name="recent">recently-shared reports</a></h2>
                    <!-- https://twitter.com/about/resources/widgets/widget_search -->

                    <script src="http://widgets.twimg.com/j/2/widget.js"></script>
                    <script>
                            new TWTR.Widget({
                              version: 2,
                              type: 'search',
                              search: 'via @mytotalImpact',
                              interval: 30000,
                              title: 'Recent public reports: "via @mytotalImpact"',
                              subject: 'Tweet yours to see it here!',
                              width: "80%",
                              height: 200,
                              theme: {
                                shell: {
                                  background: '#DDD',
                                  color: '#000'
                                },
                                tweets: {
                                  background: '#EEE',
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

        </div><!-- END wrapper -->
        <div id="footer">
        <p>an <a class="img" href="http://altmetrics.org" title="an altmetrics project"><img src="./ui/img/altmetrics_logo.png" alt="altmetrics" width="80"/></a> project.</p>
        <p>source code on <a href="https://github.com/mhahnel/Total-Impact">github</a>
        </div>
    </body>
</html>