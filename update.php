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
        
       	<div id="loading">
        <?php		
            // show the user some kind of updating screen
            echo "<h2 class='loading'><img src='./ui/img/ajax-loader.gif'> Getting information now</h2>";
			echo $_SERVER['QUERY_STRING'];
		?>
		</div>
       	<div id="saving">
		<?php
			
           	$config = new Zend_Config_Ini(CONFIG_PATH, ENV);
            $collection = new Models_Collection();
			if (isset($_REQUEST['list'])) {
	            // save the new collection
	            $storedDoc = $collection->save($_REQUEST['name'], $_REQUEST['list'], $config);
	            $collectionId = $storedDoc->id;
			} else {
				$collectionId = $_REQUEST['id'];
			}
		?>
		</div>
       	<div id="updating">
		<?php
            // get the updates
			$collection->update($collectionId, $config);
            // redirect to the report page for this plugin
            echo "<script>location.href='./report.php?id=$collectionId'</script>";
		?>
		</div>
        <div id="footer">
            <p>
            Hacked at the <a href="http://www.beyond-impact.org/">Beyond Impact Workshop</a>. <a href="https://github.com/mhahnel/total-impact">Source and contributors.</a>
            </p>
        </div>
    </body>
</html>
