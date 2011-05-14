<?php require_once './bootstrap.php'; ?><!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>total-impact.org</title>
        <link rel="stylesheet" type="text/css" href="./ui/totalimpact.css" />
    </head>
    <body>
        <div id="header">
            <img src="./ui/img/logo.png" alt="Total-Impact" width='200px' /> 
            <p>
            Welcome to Total-Impact. This site allows you to track the impact of various online 
            research artifacts. It grabs metrics from many different sites all in one place.
            </p>
        </div>

        <?php
        if (isset($_POST['submitted'])){
            $collection = new Collection(
                    $couch,
                    $_POST['name'],
                    $_POST['ids']);
            $id = $collection->make();
            sleep(1);
            $updater = new Updater($couch, new Zend_Http_Client, $configs);
            $updater->update();
            // echo "<script>location.href='report.php?id=$id'</script>";

        }
        else {
            ?>
        <div id="instr">
            <p>Enter the identifiers for the artifacts you want to track below. We'll give you a url for that set that automatically updates everytime you visit the page.</p>
            <p>To try it out, copy and paste these identifers below and hit Go!</p>
            <pre>
10.1371/journal.pbio.0060048
10.1371/journal.pbio.0050082
http://www.slideshare.net/phylogenomics/eisen
http://www.slideshare.net/phylogenomics/ben-franklin-award-slides
10.5061/dryad.8384
            </pre>
        </div>        
        <form method="POST" name="main" action="./index.php">
            <label for="name">What's your name?</label>
            <input name="name" id="name" />
            <br>
            <br>
            
            <label for="ids">Put your IDs here:</label><br>
            <textarea rows=10 cols=80 name="ids" id="ids"></textarea>
            
            <input type="hidden" name="submitted" value="true" /><br>
            <input type="submit" id="submit" value="Go!" />
        </form>
        
        <?php } ?>
        <div id="footer">
            <p>
            Hacked at the <a href="http://www.beyond-impact.org/">Beyond Impact Workshop</a>. <a href="https://github.com/mhahnel/Total-Impact">Source and contributors.</a>
            </p>
        </div>
    </body>
</html>
