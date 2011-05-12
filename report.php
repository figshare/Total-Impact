<?php
require './bootstrap.php';

function display($id, Couch_Client $couch) {
    $doc = $couch->getDoc($id);
    return $doc;
    
}

$artifactId = $_GET['id'];
$doc = display($artifactId, $couch);


$sourcesData = json_encode($doc->sources);
$title = $doc->title;



?><html>
<head>
<link rel="stylesheet" type="text/css" href="totalimpact.css" />
<title>Total Impact</title>
    <script type="text/javascript" src="./library/jquery/jquery-1.4.2.js"></script>
	<script type="text/javascript" src="./library/jquery/jquery.tools.min.js"></script>
	<script type="text/javascript" src="./library/protovis-3.2/protovis-r3.2.js"></script>
	
	<script language="javascript" type="text/javascript">
        
        artifacts = {};
        
        $(document).ready(function() {
			if (!window.console) 
    			console = { log: function() { } };

			console.log("Starting up");
			
			/* 
			source1 = {};
			source1['type'] = "presentation";
			source1['metric_value'] = 200;
			source1['metric_name'] = 'number of views';
			source1['source_name'] = "Slideshare";
			source1['icon'] = 'http://public.slidesharecdn.com/images/favicon.ico';
			
			artifact1 = {};
			artifact1['sources'] = [source1];
        	
        	
			artifacts['http://www.slideshare.net/Frank.van.Harmelen/semantic-web-good-news'] = artifact1;
			
			source2 = {};
			source2['type'] = "article";
			source2['metric_value'] = 20;
			source2['metric_name'] = 'number of views';
			source2['source_name'] = "Mendeley";
			source2['icon'] = 'http://www.mendeley.com/favicon.ico';
			
			artifact2 = {};
			artifact2['sources'] = [source2];
        	
       		artifacts['http://www.ncbi.nlm.nih.gov/pubmed/18466268'] = artifact2;
			
			*/
            
			artifacts = eval(<?php echo $sourcesData ?>);
                        
			artTotal = 0;
			presTotal = 0;
			dataTotal = 0;
			postTotal = 0;
			
				
        	
            for (id in artifacts) {
            	art = artifacts[id];
            	console.log(art);
            	
            	for (source_index in art['sources']) {
            		source = art['sources'][source_index]
            		
            		if (source['type'] == 'presentation') {
            			var e = $("<div class='entry'></div>")
							.append("<div class='id'>" + id + "</div>")
 	           				.append("<div class='metric_value'>" + source['metric_value'] + "</div>")
 	           				.append("<div class='metric_info'><div class='metric_img'><img src='" + unescape(source['icon']) + "'/></div><div class='metric_name'>" + source['metric_name'] + "</div></div>")
            				.appendTo('#presentations');
            		
            			presTotal = presTotal + source['metric_value'];
            			
            		}
            		else if (source['type'] == 'article') {
            			var e = $("<div class='entry'></div>")
							.append("<div class='id'>" + id + "</div>")
 	           				.append("<div class='metric_value'>" + source['metric_value'] + "</div>")
 	           				.append("<div class='metric_info'><div class='metric_img'><img src='" + unescape(source['icon']) + "'/></div><div class='metric_name'>" + source['metric_name'] + "</div></div>")
            				.appendTo('#articles');
            			
            			artTotal = artTotal + source['metric_value'];
            		}
            		else if (source['type'] == 'dataset') {
            			var e = $("<div class='entry'></div>")
							.append("<div class='id'>" + id + "</div>")
 	           				.append("<div class='metric_value'>" + source['metric_value'] + "</div>")
 	           				.append("<div class='metric_info'><div class='metric_img'><img src='" + unescape(source['icon']) + "'/></div><div class='metric_name'>" + source['metric_name'] + "</div></div>")
            				.appendTo('#datasets');
            			
            			dataTotal = dataTotal + source['metric_value'];
            		}
            		
            		else if (source['type'] == 'post') {
            			var e = $("<div class='entry'></div>")
							.append("<div class='id'>" + id + "</div>")
 	           				.append("<div class='metric_value'>" + source['metric_value'] + "</div>")
 	           				.append("<div class='metric_info'><div class='metric_img'><img src='" + unescape(source['icon']) + "'/></div><div class='metric_name'>" + source['metric_name'] + "</div></div>")
            				.appendTo('#posts');
            			
            			dataTotal = dataTotal + source['metric_value'];
            		}
            		
            		
                } 
            	
            }
            
            metrics = [artTotal, presTotal, dataTotal, postTotal];
            generateGraph(metrics);
        
	    });
	    
	    jQuery(document).ready(function(){
	        
	    	$('#metrics :header').click(function() {
				console.log("got called");
				$(this).next().toggle('slow');
				return false;
				
			}).next().show();
		});
	
	
		function generateGraph(metrics) {
			
			
			viz = new pv.Panel()
    		.width(145)
    		.height(400)
    		.canvas('graph');
    
			var bar = viz.add(pv.Bar)
			.data(metrics)
			.top(function(d) { return this.index * 35; })
			.width(function(d) { return d*2; })
			.height(20)
			.left(function(d) { return 1; })
			.fillStyle("rgba(30, 120, 180, .3)");
			
			
    		
    		bar.add(pv.Label)
			.left(function() {return bar.left() + 5;})
			.top(function() { return bar.top() + 18; })
    		.textAlign("left")
    		.font("10px sans-serif")
    		.textBaseline("bottom")
    		.textStyle("black")
            .text(function(d) { if (this.index == 0) return "Articles: " + d; 
            	if (this.index == 1) return "Presentations: " + d; 
            	if (this.index == 2) return "Datasets: " + d;
            	if (this.index == 3) return "Posts: " + d; });
			viz.root.render();
		}
			
	function DownloadJSON2CSV()
	{
		//console.log("got called");
    	
    	var str = '';
    	
    	for (var id in artifacts) {
    		sources = artifacts[id]['sources'];
    		for (var src_index in sources) {
    			src = sources[src_index];
    			str +=  id + ',' + src['source_name'] + ',"' + src['metric_name'] + '",' + src['metric_value'] + '\r\n';
    		}
        		
        }

        

		//console.log(str);
		window.open( "data:text/csv;charset=utf-8," + escape(str))
	}
	
	</script>	
	
	
	
	
</head>

<body>

<div id="header">
<img src="logo.png" alt="Total-Impact" width='150px' /> 
Bob
<div id="outputs">
<a href="http://twitter.com/share" class="twitter-share-button" data-text="See my Total Impact" data-count="horizontal" data-via="pgroth">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script><br>
<a href="javascript:DownloadJSON2CSV()">Download to CSV</a>
</div>
</div>

<div id="viz">
Totals by type<br>
<div id="graph">

</div>
</div>


<div id="metrics">
<h3 class="section-title">- Articles</h3>
<div id="articles" class="section">
</div>

<h3 class="section-title">- Presentations</h3>

<div id="presentations" class="section">
</div>

<h3 class="section-title" >- Datasets</h3>

<div id="datasets" class="section" >
</div>


<h3 class="section-title">- Posts</h3>

<div id="posts" class="section">
</div>

<h3 class="section-title"></h3>

<!-- 2011-05-12 ADDED by Aliaksandr Birukou-->
<div id="legend" class="section">
Metrics are computed based on the following data sources:

<ul type="square" class="body">
<li>
<a href="http://www.crossref.org/"><img src="http://www.crossref.org/favicon.ico" border='0'>CrossRef</a> - an official Digital Object Identifier (DOI) Registration Agency of the International DOI Foundation. 
<br>For a <strong>DOI</strong> it returns: 
<ul>
	<li><strong><span class="param">journal</span></strong>: <span class="desc">the journal, where the paper is published,</span></li>
	<li><strong><span class="param">date</span></strong>: <span class="desc">the date of the publication,</span></li> 
	<li><strong><span class="param">title</span></strong>: <span class="desc">the title of the publication.</span></li>
</ul></li>
</ul>
<ul type="square" class="body">
<li>
<a href="http://datadryad.org/"><img src="http://dryad.googlecode.com/svn-history/r4402/trunk/dryad/dspace/modules/xmlui/src/main/webapp/themes/Dryad/images/favicon.ico" border='0'>Dryad</a> - an international repository of data underlying peer-reviewed articles in the basic and applied biosciences. <br>For a <strong>DOI</strong> to a data package in Dryad, it returns:
<ul>
	<li><strong><span class="param">page views</span></strong>: <span class="desc">the number of views of the item by Dryad users.</span></li>
</ul></li>
</ul>
<ul type="square" class="body">
<li>
<a href="http://www.facebook.com/"><img src="http://www.facebook.com/favicon.ico" border='0'>Facebook</a> - a social networking service. 
<br>For a <strong>DOI</strong> it returns: 
   <ul>
   	<li><strong><span class="param">likes</span></strong>: <span class="desc">the number of users who liked the article,</span></li>
   	<li><strong><span class="param">shares</span></strong>: <span class="desc">the number of users who shared the article,</span></li>
   	<li><strong><span class="param">comments</span></strong>: <span class="desc">the number of users who commented on the article,</span></li>
   	<li><strong><span class="param">clicks</span></strong>: <span class="desc">the number of users who clicked on the article.</span></li>
</ul></li>
</ul>
<ul type="square" class="body">
<li>
<a href="http://www.icpsr.umich.edu/icpsrweb/ICPSR/"><img src="http://www.icpsr.umich.edu/favicon.ico" border='0'>Inter-University Consortium for Political and Social Research (ICPSR)</a> - an organization that provides access to an extensive collection of downloadable data.
<br>For a <strong>DOI</strong> to a dataset in ICPSR, it returns: 
<ul>
	<li><strong><span class="param">related_refs</span></strong>: <span class="desc">number of related articles, as collected by ICPSR.</span></li>
</ul></li>
</ul>
<ul type="square" class="body">
<li>
<a href="http://www.mendeley.com/"><img src="http://www.mendeley.com/favicon.ico" border='0'>Mendeley</a> - a research management tool for desktop and web.
<br>For a <strong>DOI</strong> it returns:
<ul>
	<li><strong><span class="param">readers</span></strong>: <span class="desc">the number of readers of the article,</span></li>
	<li><strong><span class="param">groups</span></strong>: <span class="desc">the number of groups of the article,</span></li>
</ul></li>
</ul>
<ul type="square" class="body">
<li>
<a href="http://www.plos.org/"><img src="http://www.plosbiology.org/images/favicon.ico" border='0'>PLoS</a> - nonprofit publisher of open access articles in science and medicine.
<br>For a <strong>DOI</strong> to an article published in PLoS, it returns:
<ul>
	<li><strong><span class="param">readers</span></strong>: <span class="desc">the number of downloads of the PLoS article,</span></li>
</ul></li>
</ul>
<ul type="square" class="body">
<li>
<a href="http://www.slideshare.net/"><img src="http://www.slideshare.net/favicon.ico" border='0'>Slideshare</a> - the best way to share presentations, documents and professional videos. 
<br>For an <strong>URL</strong> to a slide presentation, it returns:
<ul>
	<li><strong><span class="param">downloads</span></strong>: <span class="desc">the number of downloads of the presentation,</span></li>
	<li><strong><span class="param">favorites</span></strong>: <span class="desc">the number of users who added the presentation in their favorites,</span></li>
	<li><strong><span class="param">comments</span></strong>: <span class="desc">the number of comments on the presentation,</span></li>
	<li><strong><span class="param">views</span></strong>: <span class="desc">the number of views of the presentation.</span></li>
</ul></li>
</ul>

In this initial release, a snapshot of the impact data is captured the first time an url is displayed. In the future we are planning to periodically refresh the impact values.


</div>
<!-- END OF ADDED by Aliaksandr Birukou-->


</div>

</body>
</html>