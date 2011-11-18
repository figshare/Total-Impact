<?php
require_once './bootstrap.php';
#require_once 'FirePHPCore/fb.php';
// TRUE = disable all output buffering, 
// and automatically flush() 
// immediately after every print or echo 
ob_implicit_flush(TRUE);
if (isset($_REQUEST['run'])) {
    $query_string = $_SERVER['QUERY_STRING'];
    echo "<h2 class='loading'><img src='./ui/img/ajax-loader.gif'> Getting information now</h2>";
    echo "<script>location.href='./update.php?$query_string'</script>";
} else {
    $title = "my collection";
    $artifactIdsString = "10.1371/journal.pcbi.1000361
17808382
ef35f440-957f-11df-96dc-0024e8453de8
2BAK
GSE2109
10.5061/dryad.1295
ttp://www.slideshare.net/phylogenomics/eisenall-hands";
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
include('./header.php');
?>
<div id="about">
    <div class="wrapper">
        <p id="tagline">Uncover the invisible impact of research.</p>
        <div id="instr">
            <p class="howto">Create a collection of research objects you want to track. We'll provide you a report of the total impact of this collection.<br />You can peruse <a href="./report.php?id=MqAnvI">a sample report</a> or check out the most <a id="link-to-recently-shared" href="#twitterfeed">recently shared reports.</a></p>
        </div>
    </div>
</div><!-- END about -->

<!-- START input -->
<div id="input">
    <div class="wrapper">
        <div id="importers" class="inputcol">
            <h2 class="heading">Collect research objects</h2>
            <div id="manual-add">
                <h3 class="entry-method">Paste object IDs,</h3>
                <fieldset id="paste-ids">
                    <div class="toggler_contents">
                        <p class="prompt"  >Add one DOI, PubMed ID, URL, or other <a target="_blank" href="http://total-impact.org/about.php#whichartifacts">supported identifier</a> per line:</p>
                        <textarea rows="11" cols="40" id="manual_input" class="artifactList" ><?php echo $artifactIdsString; ?></textarea>
                        <button class="import-button" id="manual">Add to collection</button>
                    </div>
                </fieldset>
            </div>
            <div id="pullers">
                <h3 clas="entry-method">...or pull object IDs from existing collections.</h3>
                <ul>
                    <li>
                        <a class="toggler" id="mendeley_profile_toggler" >Mendeley profiles</a><br/>
                        <div class="toggler_contents" id="mendeley_profile_toggler_contents">
                            <p class="prompt">http://www.mendeley.com/profiles/</p>
                            <input id="mendeley_profile_input" name="profileId" type="text" size="20" value="heather-piwowar"/>
                            <button class="import-button" id="mendeley_profile">Import</button>
                        </div>
                    </li>
                    <li>
                        <a class="toggler" id="mendeley_group_toggler" >Mendeley groups</a><br/>
                        <div class="toggler_contents" id="mendeley_group_toggler_contents">
                            <p class="prompt">http://www.mendeley.com/group/</p>
                            <input id="mendeley_group_input" name="groupId" type="text" size="20" value="530031"/>
                            <button class="import-button" id="mendeley_group">Import</button>
                        </div>
                    </li>
                    <li>
                        <a class="toggler" id="slideshare_toggler" >Slideshare</a><br/>
                        <div class="toggler_contents" id="slideshare_toggler_contents">
                            <p class="prompt">http://www.slideshare.net/</p>
                            <input id="slideshare_profile_input" name="slideshareName" type="text" size="20" value="cavlec"/>
                            <button class="import-button" id="slideshare_profile">Import</button>
                        </div>
                    </li>
                    <li>
                        <a class="toggler" id="dryad_toggler" >Dryad datasets</a><br/>
                        <div class="toggler_contents" id="dryad_toggler_contents">
                            <p class="prompt" title="Fill in the dc:contributor.author value in <em>Show Full Metadata</em> to retrieve a list of datasets">Dryad author name</p>
                            <input id="dryad_profile_input" name="dryadName" type="text" size="20" value="Otto, Sarah P."/>
                            <button class="import-button" id="dryad_profile">Import</button>
                        </div>
                    </li>
                    <li>
                        <a class="toggler" id="pubmed_toggler" >PubMed grants</a><br/>
                        <div class="toggler_contents" id="pubmed_toggler_contents">
                            <p class="prompt" title="Fill in a grant number to retrieve publications from PubMed">Grant number</p>
                            <input id="pubmed_grant_input" name="grantId" type="text" size="20" value="U54-CA121852"/>
                            <button class="import-button" id="pubmed_grant">Import</button>
                        </div>
                    </li>
                    <li>
                        <a class="toggler" id="github_users_toggler" >GitHub users</a><br/>
                        <div class="toggler_contents" id="github_users_toggler_contents">
                            <p class="prompt">https://github.com/</p>
                            <input id="github_users_input" name="username" type="text" size="20" value="egonw"/>
                            <button class="import-button" id="github_users">Import</button>
                        </div>
                    </li>
                    <li>
                        <a class="toggler" id="github_orgs_toggler">GitHub organizations</a><br/>
                        <div class="toggler_contents" id="github_orgs_toggler_contents">
                            <p class="prompt">https://github.com/</p>
                            <input id="github_orgs_input" name="orgname" type="text" size="20" value="bioperl"/>
                            <button class="import-button" id="github_orgs">Import</button>
                        </div>
                    </li>
                </ul>
                <div class="something-missing"><p>Something missing on import?<br/> See a list of <a href="./about.php#limitations">current limitations.</a> </p></div>
            </div>
        </div>
        <div id="edit-collection" class="inputcol">
            <h2 class="heading">Review collection</h2>
            <p id="artcounter"><span class="count">0</span> objects in this collection <a href="#" id="clear-artifacts">clear</a></p>
            <ul id="collection-list"></ul>
        </div>
        <div id="create-collection" class="inputcol">
            <h2 class="heading">Create report</h2>
            <form name="id_form" id="id-form">
                <h3 id="name-collection"><label for="name">Name your collection:</label></h3>
                <input name="name" id="name" title="Add a meaningful title to this collection" value="<?php echo $title; ?>" />
                <button name="run" type="submit" id="go-button" class="go-button">get my metrics!</button>
                <input name="list" id="artifacts-list" type="hidden" value="<?php echo $artifactIdsString; ?>" />
            </form>

            <div class="quick-collection">
                <p>&hellip; or fetch a quick collection based on
                    <a class="toggler" id="mendeley_quick_reports_toggler">your Mendeley contacts and public groups &raquo;</a></p>
                <div class="toggler_contents" id="mendeley_quick_reports_toggler_contents">
                    <p class="prompt">http://www.mendeley.com/profiles/</p>
                    <input id="QR_mendeley_profile_input" name="profileId" type="text" size="20" value="heather-piwowar"/>
                    <button class="import-button" id="quick_report_contacts" title="Fill in the URL of your public Mendeley profile to get direct links to reports for your contacts">Pull my contacts</button>
                    <button class="import-button" id="quick_report_groups" title="Fill in the URL of your public Mendeley profile to get direct links to reports for your PUBLIC groups">Pull my groups</button>
                </div>
            </div>

        </div>


    </div>
</div><!-- END input -->
<!-- begin footer--><?php include('./footer.php') ?><!--end footer-->
</body>
</html>