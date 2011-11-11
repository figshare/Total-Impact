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

  $(".toggler_contents").hide();
  $(".toggler").show();

  $('.toggler').click(function(){
		var myId = this.id;
  		$("#"+myId+"_contents").slideToggle();
	});

	var fulllist = $("textarea.artifactList").val();
	var numberartifacts = fulllist.split("\n").length - 1;
   	$("#number-artifacts").html(numberartifacts+"");
		
  $("button").click(function(){
	var myId = this.id;
	if ((myId.substring(0, 12) === "quick_report")) {
		var textId = "mendeley_profile_input";		
		var textVal = $('#'+textId).val();
		var divId = "quick_report_div";
	} else {
		var textId = this.id + "_input";		
		var textVal = $('#'+textId).val();
		var divId = this.id + "_div";
	}
	if (myId === "manual") {
		var fulllist = $("textarea.artifactList").val();
		var numberartifacts = fulllist.split("\n").length - 1;
    	$("#"+divId).html(numberartifacts + " IDs.");
	   	$("#number-artifacts").html(numberartifacts+"");
		$("#artifactListHidden").val($("textarea.artifactList").val());
	} else {
	    $("#"+divId).html("Loading...");
		$.get("./seed.php?type="+myId+"&name="+textVal, function(response,status,xhr){
			if (myId=="quick_report_contacts") {
				$("#"+divId).html("Click to go directly to report:<p/>" + response["contacts"]);
			} else if (myId=="quick_report_groups") {
				$("#"+divId).html("Click to go directly to report:<p/>" + response["groups"]);
			} else {
				/* var value = response["artifactIds"]+""; */
				/* value = value.replace(/\s+/gmi, "<br/>"); */
				/* $("div.artifactList").prepend(value + "<br/>"); */
				$("textarea.artifactList").val(response["artifactIds"] + "\n" + $("textarea.artifactList").val());
		    	$("#"+divId).html("Added " + response["artifactCount"] + " IDs.");
				var fulllist = $("textarea.artifactList").val();
				var numberartifacts = fulllist.split("\n").length - 1;
			   	$("#number-artifacts").html(numberartifacts+"");
				$("#artifactListHidden").val($("textarea.artifactList").val());
			}
		}, "json"); 
	}
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
                    <p class="howto">Gather the research objects you want to track. We'll provide you a permanent URL to track statistics about this collection. You can peruse <a target="_blank" href="./report.php?id=MqAnvI">a sample</a> and <a href="#recent">recently-shared reports</a>.</p>
                </div>
            </div><!-- END about -->

            <!-- START input -->
            <div id="input">
				<div id="importers">
					<h2 class="heading">1. collect research objects</h2>
					
						<p><a class="toggler" id="manual_toggler"><scan id="number-artifacts">0</scan></a> artifacts currently in the collection.</scan>  <a href="." id="clear-artifacts">clear</a></p>
					
                            <!--Want help gathering your IDs? Pull from these sources:-->


                            <a class="toggler" id="mendeley_profile_toggler" title="Fill in the URL of your Mendeley profile to import public publications in your profile">Mendeley profiles &raquo;</a><br/>

							<div class="toggler_contents" id="mendeley_profile_toggler_contents">
								
	                            <fieldset><legend><span>ids from</span> Mendeley Profiles</legend>
	                            <p class="prompt" title="Fill in the URL of your Mendeley profile to import public publications in your profile">Your Mendeley profile URL</p>
	                            <em class="url">http://www.mendeley.com/profiles/</em>
	                            <input id="mendeley_profile_input" name="profileId" type="text" size="20" value="heather-piwowar"/>
	                            <button class="import-button" id="mendeley_profile">Import</button>
	                            <div id="mendeley_profile_div">
	                            </div>
                            </div>

                            <a class="toggler" id="mendeley_group_toggler" title="Fill in the URL of your public Mendeley to import the references shared within group">Mendeley groups &raquo;</a><br/>
							<div class="toggler_contents" id="mendeley_group_toggler_contents">

	                            <fieldset><legend><span>ids from</span> Mendeley Groups</legend>
	                            <p class="prompt" title="Fill in the URL of your public Mendeley to import the references shared within group">Your Mendeley group URL</p>
	                            <em class="url">http://www.mendeley.com/group/</em>
	                        <input id="mendeley_group_input" name="groupId" type="text" size="20" value="530031"/>
	                            <button class="import-button" id="mendeley_group">Import</button>
	                            <div id="mendeley_group_div">
	                            </div>

	                            </fieldset>

							</div>

                            <a class="toggler" id="slideshare_toggler" title="Fill in your Slideshare profile to import your public slidedecks">Slideshare &raquo;</a><br/>
							<div class="toggler_contents" id="slideshare_toggler_contents">
	                            <fieldset><legend><span>ids from</span> Slideshare</legend>
	                            <p class="prompt" title="Fill in your Slideshare profile to import your public slidedecks">Your Slideshare profile URL</p>
	                            <em class="url">http://www.slideshare.net/</em>
	                        <input id="slideshare_profile_input" name="slideshareName" type="text" size="20" value="cavlec"/>
	                            <button class="import-button" id="slideshare_profile">Import</button>
	                            <div id="slideshare_profile_div">
	                            </div>
	                            </fieldset>
							</div>

                            <a class="toggler" id="dryad_toggler" title="Fill in the dc:contributor.author value in <em>Show Full Metadata</em> to retrieve your datasets">Dryad &raquo;</a><br/>
							<div class="toggler_contents" id="dryad_toggler_contents">
	                            <fieldset><legend><span>ids from</span> Dryad</legend>
	                            <p class="prompt" title="Fill in the dc:contributor.author value in <em>Show Full Metadata</em> to retrieve your datasets">Your Dryad author name</p>
	                        <input id="dryad_profile_input" name="dryadName" type="text" size="20" value="Otto, Sarah P."/>
	                            <button class="import-button" id="dryad_profile">Import</button>
	                            <div id="dryad_profile_div">
	                            </div>
	                            </fieldset>
							</div>

                            <a class="toggler" id="pubmed_toggler" title="Fill in your Grant number to retrieve publications from PubMed">PubMed &raquo;</a><br/>
							<div class="toggler_contents" id="pubmed_toggler_contents">
	                            <fieldset><legend><span>ids from</span> PubMed</legend>
	                            <p class="prompt" title="Fill in your Grant number to retrieve publications from PubMed">Your Grant number</p>
	                        <input id="pubmed_grant_input" name="grantId" type="text" size="20" value="U54-CA121852"/>
	                            <button class="import-button" id="pubmed_grant">Import</button>
	                            <div id="pubmed_grant_div">
	                            </div>
	                            </fieldset>
							</div>

                            <a class="toggler" id="manual_toggler" title="Add, edit, and delete IDs">Manual additions and edits &raquo;</a><br/>
							<div class="toggler_contents" id="manual_toggler_contents">
	                            <fieldset><legend><span>ids for</span> manual editing</legend>
		
		                       <p class="prompt" title="Valid identifiers, one per line.  Valid identifiers include DOIs, dataset accession numbers, handles for preprints, and URLs for code and slides.">Add and edit identifiers for research objects. <a target="_blank" href="http://total-impact.org/about.php#whichartifacts">Supported IDs types.</a></p>

		                       <textarea rows=15 name="list" id="manual_input" class="artifactList"><?php echo $artifactIdsString; ?></textarea>
		
	                            <button class="import-button" id="manual">Update</button>
	                            <div id="manual_div">
	                            </div>
	                            </fieldset>
							</div>


					<!--moved down here because link didn't work when in div above for some reason -->
                    <div class="something-missing"><p>Something missing on import?<br/> See a list of <a href="./about.php#limitations">current limitations.</a> </p></div>


							
                    </div>

                    <div id="enter-collection-meta">

							
                            <form name="id_form">
							<h2 class="heading">2. name the collection</h2>

                            <fieldset>

                       <p id="name-collection"><label for="name">Name:</label></p>
                       <input name="name" id="name" value="<?php echo $title; ?>" />

					<h2><span class="heading" id="go-get-heading">3. go </span><button name="run" type="submit" id="go-button" class="go-button"
                     	onmouseover="this.className='go-button_hover';"
                     	onmouseout="this.className='go-button';">get my metrics</button></h2>

                       <!--p><label for="list" title="Valid identifiers, one per line.  Valid identifiers include DOIs, dataset accession numbers, handles for preprints, and URLs for code and slides.">ID that will be imported:</label></p-->
                       <!--textarea rows=15 name="list" id="artifactList"><?php echo $artifactIdsString; ?></textarea-->
						
                       <input name="list" id="artifactListHidden" type="hidden" value="<?php echo $artifactIdsString; ?>" />
					




                            </fieldset>
                            </form>

						<div class="quick-collection">
							<hr>
								<p>... or explore a Quick Collection based on 
								                            <a class="toggler" id="mendeley_quick_reports_toggler" title="Fill in the URL of your public Mendeley profile to import the references of your publications">your Mendeley contacts and public groups &raquo;</a><br/>
										<div class="toggler_contents" id="mendeley_quick_reports_toggler_contents">
				                            <fieldset><legend><span>Quick collections from</span> Mendeley</legend>
				                            <table><tr><td>
				                            <em class="url">http://www.mendeley.com/profiles/</em>
				                            <input id="QR_mendeley_profile_input" name="profileId" type="text" size="20" value="heather-piwowar"/>
				                            </td><td>
				                            <br/><button class="import-button" id="quick_report_contacts" title="Fill in the URL of your public Mendeley profile to get direct links to reports for your contacts">Pull my contacts</button>
				                            <br/><button class="import-button" id="quick_report_groups" title="Fill in the URL of your public Mendeley profile to get direct links to reports for your PUBLIC groups">Pull my groups</button>
				                            </td></tr></table></fieldset>
				                            <div id="quick_report_div">
				                            </div>
			                            </div>
						</div>

                    </div>


            </div>


			
            <!-- END input -->

            <!-- START footer -->

            <div id="twitterfeed">

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
                              width: "100%",
                              height: 300,
                              theme: {
                                shell: {
                                  background: '#EEE',
                                  color: '#000'
                                },
                                tweets: {
                                  background: '#FFF',
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

	<div class="recent-changes">
	Recent changes <a class="more-recent-changes" target="_blank" href="https://github.com/mhahnel/Total-Impact/blob/master/CHANGES.md">(more)</a>
<?php
include_once "library/PHPMarkdownExtra1.2.4/markdown.php";
$fh = @fopen("CHANGES.md", "r");

$lines = "";
for ($i = 0; $i < 5; $i++) {
echo Markdown(fgets($fh));
#echo fgets($fh); 
#echo $i;
}
#@fclose($fh);

#$my_html = Markdown($lines);
#echo $my_html;
?>
</div>

	<div class="altmetrics">
	
        an <a class="img" href="http://altmetrics.org" title="an altmetrics project"><img src="./ui/img/altmetrics_logo.png" alt="altmetrics" width="80"/></a> project.<br/>
        source code on <a href="https://github.com/mhahnel/Total-Impact">github</a>
	</div>


        </div>
    </body>
</html>