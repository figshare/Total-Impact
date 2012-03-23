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
                                    background: '#eeeeee',
                                    color: '#000'
                                },
                                tweets: {
                                    background: '#eeeeee',
                                    color: '#000',
                                    links: '#b20'
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

                    total-impact is an <a class="img" href="http://altmetrics.org" title="an altmetrics project"><img src="./ui/img/altmetrics_logo.png" alt="altmetrics" width="80"/></a> project, supported by:
<ul>
	<li><a class="img" href="http://www.nsf.gov/"><img src="./ui/img/nsf-logo.gif" alt="altmetrics" width="80"/></a></li>
	<li><a class="img" href="http://www.dataone.org/"><img src="./ui/img/DataONE_LOGO.jpg" alt="altmetrics" width="80"/></a></li>
	<li><a class="img" href="http://www.soros.org/"><img src="./ui/img/OSF_logo.jpg" alt="altmetrics" width="80"/></a></li>
	<li><a href="http://gradschool.unc.edu/programs/royster">Royster Society of Fellows</a></li>
</ul>

                </div>
            </div>
        </div> <!-- end footer -->







