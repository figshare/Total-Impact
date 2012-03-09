<?php
require './bootstrap.php';
#require_once 'FirePHPCore/fb.php';

$config = new Zend_Config_Ini(CONFIG_PATH, ENV);
$dbCreds = new Zend_Config_Ini(CREDS_PATH, 'db');
$couch = new Couch_Client($dbCreds->dsn, $dbCreds->name);
$collectionId = "MqAnvI"; #TODO: substitute with a collection with ID of"EXAMPLE_ALL_PLUGINS"

$report = new Models_Reporter($couch, $collectionId);
$res = $report->fetch();

$rendered_about_text = $report->render_about_text();
define('PAGE_SUBTITLE', "about");
include('./header.php');
?><div id="faq"><div class="wrapper">
        <div id="toc"></div>

        <h2 id="what">what is total-Impact?</h2>

        <p>Total-Impact is a website that makes it quick and easy to view the impact of a wide range of research output.  It goes beyond traditional measurements of research output -- citations to papers -- to embrace a much broader evidence of use across a wide range of scholarly output types. The system aggregates impact data from many sources and displays it in a single report, which is given a permaurl for dissemination and can be updated any time.

        <h2 id="audience">who is it for?</h2>

        <ul>
            <li><b>researchers</b> who want to know how many times their work has been downloaded, bookmarked, and blogged
            <li><b>research groups</b> who want to look at the broad impact of their work and see what has demonstrated interest
            <li><b>funders</b> who want to see what sort of impact they may be missing when only considering citations to papers
            <li><b>repositories</b> who want to report on how their research artifacts are being discussed
            <li><b>all of us</b> who believe that people should be rewarded when their work (no matter what the format) makes a positive impact (no matter what the venue).  Aggregating evidence of impact will facilitate appropriate rewards, thereby encouraging additional openness of useful forms of research output.
        </ul>

        <h2 id="uses">how should it be used?</h2>

        Total-Impact data can be:
        <ul>
            <li>highlighted as indications of the <em>minimum</em> impact a research artifact has made on the community
            <li>explored more deeply to see who is citing, bookmarking, and otherwise using your work
            <li>run to collect usage information for mention in biosketches
            <li>included as a link in CVs
            <li>analyzed by downloading detailed metric information
        </ul>

        <h2 id="pooruses">how <em>shouldn’t</em> it be used?</h2>

        <p>Some of these issues relate to the early-development phase of total-Impact, some reflect our early-understanding of altmetrics, and some are just common sense.  Total-Impact reports shouldn't be used:

        <ul>
            <li><b>as indication of comprehensive impact</b>
                <p>Total-Impact is in early development.  See <a href="#limitations">limitations</a> and take it all with a grain of salt.

            <li><b>for serious comparison</b>
                <p>Total-Impact is currently better at collecting comprehensive metrics for some artifacts than others, in ways that are not clear in the report.  Extreme care should be taken in comparisons.  Numbers should be considered minimums.  Even more care should be taken in comparing collections of artifacts, since some total-Impact is currently better at identifying artifacts identified in some ways than others.  Finally, some of these metrics can be easily gamed.  This is one reason we believe having many metrics is valuable.

            <li><b>as if we knew exactly what it all means</b>
                <p>The meaning of these metrics are not yet well understood; see <a href="#meaning">section</a> below.

            <li><b>as a substitute for personal judgement of quality</b>
                <p>Metrics are only one part of the story.  Look at the research artifact for yourself and talk about it with informed colleagues.

        </ul>

        <h2 id="meaning">what do these number actually mean?</h2>

        <p>The short answer is: probably something useful, but we’re not sure what. We believe that dismissing the metrics as “buzz” is short-sited: surely people bookmark and download things for a reason. The long answer, as well as a lot more speculation on the long-term significance of tools like total-Impact, can be found in the nascent scholarly literature on “altmetrics.”

        <p><a href="http://altmetrics.org/manifesto/">The Altmetrics Manifesto</a> is a good, easily-readable introduction to this literature, while the proceedings of the recent <a href="http://altmetrics.org/workshop2011/">altmetrics11</a> workshop goes into more detail. You can check out the shared <a href="http://www.mendeley.com/groups/586171/alt-metrics/papers/">altmetrics library</a> on Mendeley for more even relevant research. Finally, the poster <a href="http://jasonpriem.com/self-archived/two-altmetrics-tools.pdf">Uncovering impacts: CitedIn and total-Impact, two new tools for gathering altmetrics</a>, recently submitted to the 2012 iConference, describes a case study using total-Impact to evaluate a set of research papers funded by NESCent; it has some brief statistical analysis and some visualisations of the results.

        <h2 id="whichartifacts">what kind of research artifacts can be tracked?</h2>

        Total-Impact currently tracks a wide range of research artifacts, including papers, datasets, software, preprints, and slides.

        <p>Because the software is in early development it has limited robustness for input variations: please pay close attention to the expected format and follow it exactly.  For example, inadvertently including a "doi:" prefix, or omitting "http" from a url may render the IDs unrecognizable by the system.  Add each ID on a separate line in the input box.

        <table class="permitted-artifact-ids" border=1>
            <tr><th>artifact type</th><th>host</th><th>supported ID format</th><th>example</th><tr>
            <tr><td>a published paper</td><td>any journal that issues DOIs</td><td>DOI (simply the DOI alone)</td><td>10.1371/journal.pcbi.1000361</td></tr>
            <tr><td>a published paper</td><td>PubMed</td><td>PubMed ID (no prefix)</td><td>17808382</td></tr>
            <tr><td>a published paper</td><td>Mendeley</td><td>Mendeley UUID</td><td>ef35f440-957f-11df-96dc-0024e8453de8</td></tr>
            <tr><td>dataset</td><td>Genbank</td><td>accession number</td><td>AF313620</td></tr>
            <tr><td>dataset</td><td>PDB</td><td>accession number</td><td>2BAK</td></tr>
            <tr><td>dataset</td><td>Gene Expression Omnibus</td><td>accession number</td><td>GSE2109</td></tr>
            <tr><td>dataset</td><td>ArrayExpress</td><td>accession number</td><td>E-MEXP-88</td></tr>
            <tr><td>dataset</td><td>Dryad</td><td>DOI</td><td>10.5061/dryad.1295</td></tr>
            <!--tr><td>dataset</td><td>ICPSR</td><td>DOI</td><td>10.3886/ICPSR03131</td></tr-->
            <!--tr><td>dataset</td><td>ORNL DAAC</td><td>DOI</td><td>10.3334/ORNLDAAC/912</td></tr-->
            <tr><td>software</td><td>GitHub</td><td>URL (starting with http)</td><td>https://github.com/mhahnel/total-Impact</td></tr>
            <tr><td>software</td><td>SourceForge</td><td>URL</td><td>http://sourceforge.net/projects/aresgalaxy</td></tr>
            <!--tr><td>generic artifact</td><td>RePEc</td><td>URL</td><td>http://ideas.repec.org/a/ags/ajaeau/22364.html</td></tr-->
            <!--tr><td>generic artifact</td><td>UMN Institutional Archive</td><td>URL</td><td>http://conservancy.umn.edu/handle/107490</td></tr-->
            <!--tr><td>preprint</td><td>arXiv</td><td>URL</td><td>http://arxiv.org/abs/cond-mat/0012330</td></tr-->
            <tr><td>slides</td><td>SlideShare</td><td>URL</td><td>http://www.slideshare.net/phylogenomics/eisenall-hands</td></tr>
            <tr><td>generic url</td><td>A conference paper, website resource, etc.</td><td>URL</td><td>http://opensciencesummit.com/program/</td></tr>

        </table>

        <p>Identifiers are automatically exploded to include synonyms when possible (PubMed IDs to DOIs, DOIs to URLs, etc).

        <p>Stay tuned, we expect to support more artifact sources soon!  Want to see something included that isn't here?  See the <a href="#howhelp">How can I help</a> section below.

        <h2 id="whichmetrics">which metrics are measured?</h2>

        <p>Metrics are computed based on the following data sources:</p>

        <?php
        echo "$rendered_about_text";
        ?>


        <h2 id="whereisif">where is the journal impact factor?</h2>

        <p>We do not include the Journal Impact Factor (or any similar proxy) on purpose.  As has been <a href="https://www.zotero.org/groups/impact_factor_problems/items">repeatedly shown</a>, the Impact Factor is not appropriate for judging the quality of individual research artifacts.  Individual article citations reflect much more about how useful papers actually were.  Better yet are article-level metrics, as initiated by PLoS, in which we examine traces of impact beyond citation.  Total-Impact broadens this approach to reflect <b>artifact-level metrics</b>, by inclusion of preprints, datasets, presentation slides, and other research output formats.

        <h2 id="similar">where is my other favourite metric?</h2>

        <p>We only include open metrics here, and so far only a selection of those.  We welcome contributions of plugins.  Your plugin need not reside on our server: you can host it if we can call it with our REST interface.  Write your own and tell us about it.

        <p>You can also check out these similar tools:
        <ul>
            <li><a href="http://altmetric.com/interface/explorer.html">altmetric.com API demo</a>
            <li><a href="http://citedin.org/">CitedIn</a>
            <li><a href="http://code.google.com/p/alt-metrics/">PLoS Article-Level Metrics application</a>
            <li><a href="http://readermeter.org/">ReaderMeter</a>
            <li><a href="http://sciencecard.org/">Science Card</a>
        </ul>

        <h2 id="limitations">what are the current limitations of the system?</h2>

        <p>Total-Impact is in early development and has many limitations.  Some of the ones we know about:

        <h3>Gathering IDs and quick reports sometimes miss artifacts</h3>
        <ul>
            <li>misses papers in Mendeley profiles that aren't returned in a title/author/year search
            <li>Mendeley groups detail page only shows public groups
            <li>seeds only first 100 artifacts from Mendeley groups
            <li>doesn’t handle dois for books properly
        </ul>

        <h3>Artifacts are sometimes missing metrics</h3>
        <ul>
            <li>doesn’t display metrics with a zero value, though this information is included in raw data for download
            <li>sometimes the artifacts were received without sufficient information to use all metrics. For example, the system sometimes can't figure out the DOI from a Mendeley UUID or URL.
        </ul>

        <h3>Metrics sometimes have values that are too low</h3>
        <ul>
            <li>some sources have multiple records for a given artifact.  Total-Impact only identifies one copy and so only reports the impact metrics for that record.  It makes no current attempt to aggregate across duplications within a source.
        </ul>

        <h3>Other</h3>
        <ul>
            <li>max of 250 artifacts in a report; artifact list that are too long are truncated and a note is displayed on the report.
        </ul>

        Tell us about bugs! <a href="http://twitter.com/#!/totalImpactdev">@totalImpactdev</a> (or via email to total-Impact@googlegroups.com)

        <h2 id="isitopen">is this data Open?</h2>

        <p>We’d like to make all of the data displayed by total-Impact available under CC0.  Unfortunately, the terms-of-use of most of the data sources don’t allow that. We're trying to figure out how to handle this.
        <p>An option to restrict the displayed reports to Fully Open metrics — those suitable for commercial use — is on the To Do list.
        <p>The total-Impact software itself is fully open source under an MIT license.  <a href="https://github.com/mhahnel/total-Impact">GitHub</a>

        <h2 id="api">does total-Impact have an api?</h2>

        <p>yes! We have a <a href="https://docs.google.com/document/d/1My8fdD88a3_6fh9h6p3I2m9BDcMy0ddC5vcGQqbafFc/edit">full roadmap of an api spec</a> and have implemented the main piece. Please don’t use it heavily or in production yet; we haven't implemented good caching.  It is still early days: we welcome your feedback on how to make it useful and easy.

        <div>Initial implementation includes:
            <ul>
                <li>GET /items/ID1,ID2,ID3 or GET /items/ID1,ID2,ID3.html</li>
                <ul>
                    <li>returns html for those IDs, as it would appear on the total-impact website.</li>
                </ul>
                <li>GET /items/ID1,ID2,ID3.json</li>
                <ul>
                    <li>all metrics info in json format</li>
                </ul>
                <li>GET /items/ID1,ID2,ID3.xml</li>
                <ul>
                    <li>all metrics info in xml format</li>
                </ul>
                <li>GET /items/ID1,ID2,ID3.json?fields=biblio,aliases,metrics,debug</li>
                <ul>
                    <li>allows subsetting the metrics info returned</li>
                </ul>
            </ul>
            Examples:  (to try other IDs replace / in IDs with %252F)
            <ul>
                <li>html: <a href="http://total-impact.org/api/v1/items/18428094,10.1371%252Fjournal.pmed.0020124,http:%252F%252Fopensciencesummit.com%252Fprogram%252F,10.5061%252Fdryad.8048">http://total-impact.org/api/v1/items/18428094,10.1371%252Fjournal.pmed.0020124,http:%252F%252Fopensciencesummit.com%252Fprogram%252F,10.5061%252Fdryad.8048</a></li>
                <li>html, just metrics (good for easy embedding) <a href="http://total-impact.org/api/v1/items/18428094,10.1371%252Fjournal.pmed.0020124,http:%252F%252Fopensciencesummit.com%252Fprogram%252F,10.5061%252Fdryad.8048?fields=metrics">http://total-impact.org/api/v1/items/18428094,10.1371%252Fjournal.pmed.0020124,http:%252F%252Fopensciencesummit.com%252Fprogram%252F,10.5061%252Fdryad.8048?fields=metrics</a></li>
                <li>json: <a href="http://total-impact.org/api/v1/items/18428094,10.1371%252Fjournal.pmed.0020124,http:%252F%252Fopensciencesummit.com%252Fprogram%252F,10.5061%252Fdryad.8048.json?fields=metrics">http://total-impact.org/api/v1/items/18428094,10.1371%252Fjournal.pmed.0020124,http:%252F%252Fopensciencesummit.com%252Fprogram%252F,10.5061%252Fdryad.8048.json?fields=metrics</a></li>
                <li>just biblio (api supports returning just subsets of data elements) <a href="http://total-impact.org/api/v1/items/18428094,10.1371%252Fjournal.pmed.0020124,http:%252F%252Fopensciencesummit.com%252Fprogram%252F,10.5061%252Fdryad.8048.json?fields=biblio">http://total-impact.org/api/v1/items/18428094,10.1371%252Fjournal.pmed.0020124,http:%252F%252Fopensciencesummit.com%252Fprogram%252F,10.5061%252Fdryad.8048.json?fields=biblio</a></li>
            </ul>


            <h2 id="who">who developed total-Impact?</h2>

            <p>Concept originally hacked at the <a href="http://www.beyond-impact.org/">Beyond Impact Workshop</a>, part of the Beyond Impact project funded by the Open Society Foundations <a href="https://github.com/mhahnel/Total-Impact/contributors">(initial contributors)</a>.
                Continued development was done on personal time, plus some discretionary time while funded through <a href="http://dataone.org">DataONE</a> (Heather Piwowar) and a UNC Royster Fellowship (Jason Priem). Recently, Total-Impact was selected for £17,000 of further development funding from the Beyond Impact project.

            <h2 id="learned">what have you learned?</h2>

            <ul>
                <li>the multitude of IDs for a given artifact is a bigger problem than we guessed.  Even articles that have DOIs often also have urls, PubMed IDs, PubMed Central IDs, Mendeley IDs, etc.  There is no one place to find all synonyms, yet the various APIs often only work with a specific one or two ID types.  This makes comprehensive impact-gathering time consuming and error-prone.
                <li>some data is harder to get than we thought (wordpress stats without requesting consumer key information)
                <li>some data is easier to get than we thought (vendors willing to work out special agreements, permit web scraping for particular purposes, etc)
                <li>lack of an author-identifier makes us reliant on user-populated systems like Mendeley for tracking author-based work (we need ORCID and we need it now!)
                <li>API limits like those on PubMed Central (3 request per second) make their data difficult to incorporate in this sort of application
            </ul>

            <h2 id="howhelp">how can I help?</h2>

            <ul>
                <li><b>can you write code?</b>  Dive in!  github url: <a href="https://github.com/mhahnel/total-Impact">https://github.com/mhahnel/total-Impact</a>.
                <li><b>do you have data?</b>  If it is already available in some public format, let us know so we can add it.  If it isn’t, either please open it up or contact us to work out some mutually beneficial way we can work together.
                <li><b>do you have money?</b>  We need money  :)  We need to fund future development of the system and are actively looking for appropriate opportunities.
                <li><b>do you have ideas?</b>  Maybe enhancements to total-Impact would fit in with a grant you are writing, or maybe you want to make it work extra-well for your institution’s research outputs.  We’re interested: please get in touch (see bottom).
                <li><b>do you have energy?</b>  We need better “see what it does” documentation, better lists of collections, etc.  Make some and tell us, please!
                <li><b>do you have anger that your favourite data source is missing?</b>  After you confirm that its data isn't available for open purposes like this, write to them and ask them to open it up... it might work.  If the data is open but isn't included here, let us know to help us prioritize.
                <li><b>can you email, blog, post, tweet, or walk down the hall to tell a friend?</b>  See the <a href="#cool">this is so cool</a> section for your vital role....
            </ul>

            <h2 id="cool">this is so cool.</h2>

            <p>Thanks!  We agree :)
            <p>You can help us.  We are currently trying to a) win the PLoS/Mendeley Binary Battle because that sounds fun, b) raise funding for future total-Impact development, and c) justify spending more time on this ourselves.
            <p>Buzz and testimonials will help.  Tweet your reports.  Sign up for Mendeley, add public publications to your profile, and make some public groups.  Tweet, blog, send email, and show off total-Impact at your next group meeting to help spread the word.
            <p>Tell us how cool it is at <a href="http://twitter.com/#!/totalImpactdev">@totalImpactdev</a> (or via email to total-Impact@googlegroups.com) so we can consolidate the feedback.

            <h2 id="suggestion">I have a suggestion!</h2>

            <p><b>We want to hear it.</b>  Send it to us at <a href="http://twitter.com/#!/totalImpactdev">@totalImpactdev</a> (or via email to total-Impact@googlegroups.com).  Total-Impact development will slow for a bit while we get back to our research-paper-writing day jobs, so we aren’t sure when we’ll have another spurt of time for implementation.... but we want to hear your idea now so we can work on it as soon as we can.
        </div><!-- end wrapper -->
    </div><!-- end faq -->
</div>
<?php include('./footer.php'); ?>

</body>
</html>