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
        <title>total-Impact: Uncover the invisible impact of research</title>
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
        <script type="text/javascript" src="./ui/js/home-ui.js"></script>

    </head>
    <body class="home">
        <div id="header">
            <div class="wrapper">
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
                } else {
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
        </div><!-- end header -->

        <div id="about">
            <div class="wrapper">
                <p id="tagline">Uncover the invisible impact of research.</p>
                <div id="instr">
                    <p class="howto">Create a collection of research objects you want to track. We'll provide you a report of the total impact of this collection.<br />You can peruse <a href="./report.php?id=MqAnvI">a sample report</a> or check out the most recently shared reports.</p>
                </div>
            </div>
        </div><!-- END about -->

        <!-- START input -->
        <div id="input">
            <div class="wrapper">
                <div id="importers" class="inputcol">
                    <h2 class="heading"><span class="largenum">1</span>Collect research objects</h2>
                    <ul>
                        <li>
                            <a class="toggler" id="mendeley_profile_toggler" title="Fill in the URL of a Mendeley profile to import its public publications">Mendeley profiles</a><br/>
                            <div class="toggler_contents" id="mendeley_profile_toggler_contents">
                                    <p class="prompt">http://www.mendeley.com/profiles/</p>
                                    <input id="mendeley_profile_input" name="profileId" type="text" size="20" value="heather-piwowar"/>
                                    <button class="import-button" id="mendeley_profile">Import</button>
                            </div>
                        </li>
                        <li>
                            <a class="toggler" id="mendeley_group_toggler" title="Fill in the URL of a public Mendeley to import the references shared within that group">Mendeley groups</a><br/>
                            <div class="toggler_contents" id="mendeley_group_toggler_contents">
                                    <p class="prompt">http://www.mendeley.com/group/</p>
                                    <input id="mendeley_group_input" name="groupId" type="text" size="20" value="530031"/>
                                    <button class="import-button" id="mendeley_group">Import</button>
                            </div>
                        </li>
                        <li>
                            <a class="toggler" id="slideshare_toggler" title="Fill in a Slideshare profile to import its public slidedecks">Slideshare</a><br/>
                            <div class="toggler_contents" id="slideshare_toggler_contents">
                                    <p class="prompt">http://www.slideshare.net/</p>
                                    <input id="slideshare_profile_input" name="slideshareName" type="text" size="20" value="cavlec"/>
                                    <button class="import-button" id="slideshare_profile">Import</button>
                            </div>
                        </li>
                        <li>
                            <a class="toggler" id="dryad_toggler" title="Fill in the dc:contributor.author value in Show Full Metadata to retrieve a list of datasets">Dryad</a><br/>
                            <div class="toggler_contents" id="dryad_toggler_contents">
                                    <p class="prompt" title="Fill in the dc:contributor.author value in <em>Show Full Metadata</em> to retrieve a list of datasets">Dryad author name</p>
                                    <input id="dryad_profile_input" name="dryadName" type="text" size="20" value="Otto, Sarah P."/>
                                    <button class="import-button" id="dryad_profile">Import</button>
                            </div>
                        </li>
                        <li>
                            <a class="toggler" id="pubmed_toggler" title="Fill in a grant number to retrieve publications from PubMed">PubMed</a><br/>
                            <div class="toggler_contents" id="pubmed_toggler_contents">
                                    <p class="prompt" title="Fill in a grant number to retrieve publications from PubMed">Grant number</p>
                                    <input id="pubmed_grant_input" name="grantId" type="text" size="20" value="U54-CA121852"/>
                                    <button class="import-button" id="pubmed_grant">Import</button>
                            </div>
                        </li>
                        <li>
                            <a class="toggler" id="github_users_toggler" title="Fill in a GitHub username to retrieve their public GitHub source code repositories">GitHub users</a><br/>
                            <div class="toggler_contents" id="github_users_toggler_contents">
                                    <p class="prompt">https://github.com/</p>
                                    <input id="github_users_input" name="username" type="text" size="20" value="egonw"/>
                                    <button class="import-button" id="github_users">Import</button>
                            </div>
                        </li>
                        <li>
                            <a class="toggler" id="github_orgs_toggler" title="Fill in a GitHub username to retrieve their public GitHub source code repositories">GitHub organizations</a><br/>
                            <div class="toggler_contents" id="github_orgs_toggler_contents">
                                    <p class="prompt">https://github.com/</p>
                                    <input id="github_orgs_input" name="orgname" type="text" size="20" value="bioperl"/>
                                    <button class="import-button" id="github_orgs">Import</button>
                            </div>
                        </li>
                        <li>
                            <a class="toggler" id="manual_toggler" title="Add, edit, and delete IDs">Add manually</a><br/>
                            <div class="toggler_contents" id="manual-add">
                                    <p class="prompt" title="Valid identifiers, one per line.  Valid identifiers include DOIs, dataset accession numbers, handles for preprints, and URLs for code and slides.">Paste in <a target="_blank" href="http://total-impact.org/about.php#whichartifacts">supported identifiers</a>  for research objects, one per line.</p>
                                    <textarea rows=15 name="list" id="manual_input" class="artifactList"><?php echo $artifactIdsString; ?></textarea>
                                    <button class="import-button" id="manual">Add to collection</button>
                            </div>
                        </li>
                    </ul>
                    <div class="something-missing"><p>Something missing on import?<br/> See a list of <a href="./about.php#limitations">current limitations.</a> </p></div>
                </div>
                <div id="edit-collection" class="inputcol">
                    <h2 class="heading"><span class="largenum">2</span>Confirm list</h2>
                    <p id="artcounter"><span class="count">0</span> objects in this collection <a href="#" id="clear-artifacts">clear</a></p>
                    <ul id="collection-list"></ul>
                </div>
                <div id="create-collection" class="inputcol">
                    <h2 class="heading"><span class="largenum">3</span>Create collection</h2>
                    <form name="id_form">
                        <p id="name-collection"><label for="name">collection name:</label></p>
                        <input name="name" id="name" title="Add a meaningful title to this collection" value="<?php echo $title; ?>" />
                        <button name="run" type="submit" id="go-button" class="go-button">get metrics</button>
                        <input name="list" id="artifactListHidden" type="hidden" value="<?php echo $artifactIdsString; ?>" />
                    </form>

                    <div class="quick-collection">
                        <p>&hellip; or fetch a quick collection based on 
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
        </div><!-- END input -->




        <div id="footer">
            <div class="wrapper">
                <div id="twitterfeed" class="footercol">

                    <!--<h2 class="grey" id="recent">Recent reports</h2>-->
                    <!-- https://twitter.com/about/resources/widgets/widget_search -->

                    <script src="http://widgets.twimg.com/j/2/widget.js"></script>
                    <script>
                        new TWTR.Widget({
                            version: 2,
                            type: 'search',
                            search: 'via @mytotalImpact',
                            interval: 30000,
                            subject: 'Latest tweeted reports:',
                            width: "100%",
                            height: 250,
                            theme: {
                                shell: {
                                    background: '#eee',
                                    color: '#000'
                                },
                                tweets: {
                                    background: '#eee',
                                    color: '#000',
                                    links: '#933'
                                }
                            },
                            features: {
                                scrollbar: false,
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
                <div class="recent-changes footercol">
                    <h4>Latest changes on <a href="https://github.com/mhahnel/Total-Impact/blob/master/CHANGES.md">GitHub</a></h4>
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
                <div class="altmetrics footercol">

                    an <a class="img" href="http://altmetrics.org" title="an altmetrics project"><img src="./ui/img/altmetrics_logo.png" alt="altmetrics" width="80"/></a> project.<br/>
                </div>
            </div>
        </div> <!-- end footer -->



    </body>
</html>