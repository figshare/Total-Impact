<?php
#require_once './../bootstrap.php'; 
#require_once 'FirePHPCore/fb.php';

/**
 * This is a grabs artifact IDs given seeds
 *
 * @author heather
 */
class Models_Seeder {
	#private $mendeley_profile_cache;

        private $creds;
	
    function __construct(Zend_Config_Ini $creds) {
                $this->creds = $creds;
	}

	
    public function getMendeleyProfilePage($profileId, $suffix="") {
		#if (isset($this->mendeley_profile_cache->$profileId)) {
		#	$bodyProfilePage = $this->mendeley_profile_cache->$profileId;
		#} else {
			$mendeleyUrlProfilePage = "http://www.mendeley.com/profiles/" . $profileId . $suffix . "/";
			
			$requestProfilePage = new HttpRequest($mendeleyUrlProfilePage, HTTP_METH_GET);
			$responseProfilePage = $requestProfilePage->send();
			$bodyProfilePage = $responseProfilePage->getBody();
			#$this->mendeley_profile_cache->$profileId = $bodyProfilePage;
		#}
		return $bodyProfilePage;
	}
			
	public function getAllMendeleyPubPages($profileId) {
		$bodyPublicationsPage = $this->getMendeleyProfilePage($profileId, "/publications");

		$regex_pattern = '|http://www.mendeley.com/profiles/' . $profileId . '/publications/(\w+)/|Ums';
		preg_match_all($regex_pattern, $bodyPublicationsPage, $subsections, PREG_SET_ORDER);

		$body = $bodyPublicationsPage; // starts off with a default section
		foreach ($subsections as $subsection) {	
			$bodySubsectionPublicationsPage = $this->getMendeleyProfilePage($profileId, "/publications/" . $subsection[1]);
			$body .= $bodySubsectionPublicationsPage;
		}
		return($body);		
	}
	
	public function extractMendeleyBiblio($body) {
#<span class="Z3988" title="ctx_ver=Z39.88-2004&amp;rfr_id=info%3Asid%2Fmendeley.com%2Fmendeley&amp;rft_val_fmt=info%3Aofi%2Ffmt%3Akev%3Amtx%3Ajournal&amp;rft.genre=proceeding&amp;rft.date=2008&amp;rft.pages=4&amp;rft.atitle=Linking+database+submissions+to+primary+citations+with+PubMed+Central&amp;rft.aulast=Piwowar&amp;rft.aufirst=Heather+A&amp;rft.au=Chapman%2C+Wendy+W"></span>
		$all_artifacts = array();
		$regex_pattern = '/<span class="Z3988" title="(.*)"><.span>/Ums';
		preg_match_all($regex_pattern, $body, $hits, PREG_SET_ORDER);
		foreach ($hits as $hit) {
			$hit_sections = explode("&amp;", $hit[1]);
			$biblio = array();
			foreach ($hit_sections as $section) {
				$section_parts = explode("=", $section);
				$biblio[$section_parts[0]] = $section_parts[1];
			}
			$all_artifacts[] = $biblio;
		}
		return($all_artifacts);
	}

	public function lookUpMendeleyPaper($biblio) {
		$MENDELEY_LOOKUP_FROM_DOI_URL_PART1 = "http://api.mendeley.com/oapi/documents/search/title%3A";
		$MENDELEY_LOOKUP_FROM_DOI_URL_PART2 = "/?consumer_key=" . $this->creds->apis->Mendeley_key;

		$title = $biblio["rft.atitle"];
		$title = preg_replace('/%\w\w/', '', $title);

		$mendeleyUrlLookupPage = $MENDELEY_LOOKUP_FROM_DOI_URL_PART1 . $title . "%20year%3A" . $biblio["rft.date"] . "%20authors%3A" . $biblio["rft.aulast"] . $MENDELEY_LOOKUP_FROM_DOI_URL_PART2;
		#print_r("\n" . $mendeleyUrlLookupPage);
		$requestLookupPage = new HttpRequest($mendeleyUrlLookupPage, HTTP_METH_GET);
		$requestLookupPage->setOptions(array("timeout"=>10, "useragent"=>"total-Impact"));

		try {
   			$responseLookupPage = $requestLookupPage->send();
			$bodyLookupPage = $responseLookupPage->getBody();
			$bodyLookupJson = json_decode($bodyLookupPage);
		} catch (Exception $ex) {
   			$bodyLookupJson = null;
		}

		return($bodyLookupJson);		
	}
	
    public function getMendeleyProfileArtifacts($profileId) {
		$body = $this->getAllMendeleyPubPages($profileId);
		$biblio_array = $this->extractMendeleyBiblio($body);
		#print_r($biblio_array);
		$ids = array();
		foreach ($biblio_array as $biblio) {
			$id = "";
			#print_r($biblio);
			if (array_key_exists("rft_id", $biblio)) {
				$regex_pattern = '/info.*?%2F(?P<id>.*)/';
				preg_match($regex_pattern, $biblio["rft_id"], $matches);
				if ($matches) {
					$id = urldecode($matches["id"]);
					$ids[] = $id;
				}
			}
			if ($id === "") {
				$lookupResponse = $this->lookUpMendeleyPaper($biblio);
				if (count($lookupResponse->documents) > 0) {
					$id = $lookupResponse->documents[0]->uuid;
					$ids[] = $id;
					#print_r("\nSUCCESS!\n");
				} else {
					#print_r("\n");
					#print_r($biblio);
					#print_r(json_encode($lookupResponse) . "\n");
				}
			}
		}
		return($ids);
	}
	
	
    public function getMendeleyProfileGroupsDisplay($profileId) {
		$bodyProfilePage = $this->getMendeleyProfilePage($profileId);
		$regex_pattern = '/groups.(\d+)\/.*">(.*)</U';
		preg_match_all($regex_pattern, $bodyProfilePage, $matches, PREG_SET_ORDER);
		$combo = "";
		shuffle($matches);
		$sliced = array_slice($matches, 0, 7);
		foreach ($sliced as $match) {
			$id = $match[1];
			$title = $match[2];
			$combo .= '<a href="./update.php?quickreport&name=' . $title . '&mendeleygroup=' . $id . '">' . $title . '</a><br/>';
		}
		return $combo;
	}

    public function getMendeleyProfileContactsDisplay($profileId) {
		$bodyProfilePage = $this->getMendeleyProfilePage($profileId);
		$regex_pattern = '/profiles.(\S+)\/.*profile">(.*)<\/a>/U';
		preg_match_all($regex_pattern, $bodyProfilePage, $matches, PREG_SET_ORDER);
		$combo = '<a href="./update.php?quickreport&name=' . $profileId . '&mendeleyprofile=' . $profileId . '">' . $profileId . '</a><br/>';
		foreach ($matches as $match) {
			$id = $match[1];
			$title = $match[2];
			$combo .= '<a href="./update.php?quickreport&name=' . $title . '&mendeleyprofile=' . $id . '">' . $title . '</a><br/>';
		}
		return $combo;
	}
	
    public function getMendeleyGroupArtifacts($groupId) {
	
	    $MENDELEY_LOOKUP_FROM_DOI_URL_PART1 = "http://api.mendeley.com/oapi/documents/groups/";
		$MENDELEY_LOOKUP_FROM_DOI_URL_PART2 = "/docs/?details=true&items=100&consumer_key=" . $this->creds->apis->Mendeley_key;
		$mendeleyUrlGroupPage = $MENDELEY_LOOKUP_FROM_DOI_URL_PART1 . $groupId . $MENDELEY_LOOKUP_FROM_DOI_URL_PART2;
		$requestGroupPage = new HttpRequest($mendeleyUrlGroupPage, HTTP_METH_GET);
		$responseGroupPage = $requestGroupPage->send();
		$bodyGroupPage = $responseGroupPage->getBody();
		$body = json_decode($bodyGroupPage);

		$id_list = array();
		foreach ($body->documents as $artifact) {
			if (isset($artifact->url)) {
	    		$id_list[] = $artifact->uuid;
	    		#$id_list[] = $artifact->url;
			}
		}
		
		return $id_list;
	}
	
    public function getSlideshareProfileArtifacts($profileId) {
		$slideshareProfilePage = "http://www.slideshare.net/" . $profileId . "/presentations";
		$requestProfilePage = new HttpRequest($slideshareProfilePage, HTTP_METH_GET);
		$responseProfilePage = $requestProfilePage->send();
		$body = $responseProfilePage->getBody();
		
		$regex_pattern = '/<a title=.* href="(.' . $profileId . '.*)"/U';
		preg_match_all($regex_pattern, $body, $matches);
		$artifactIds = $matches[1];
		foreach ($artifactIds as &$value) {
		    $value = "http://www.slideshare.net" . $value;
		}
		return $artifactIds;
	}
 
    public function getDryadProfileArtifacts($profileId) {
		$profileId = urlencode(strtolower($profileId));
		$profileId = str_replace('+', '%5C+', $profileId);
		$dryadProfilePage = "http://datadryad.org/discover?field=dc.contributor.author_filter&fq=dc.contributor.author_filter%3A" . $profileId;
		$requestProfilePage = new HttpRequest($dryadProfilePage, HTTP_METH_GET);
		$responseProfilePage = $requestProfilePage->send();
		$body = $responseProfilePage->getBody();
		
		$regex_pattern = '/(10.5061.dryad.*)<.span/U';
		preg_match_all($regex_pattern, $body, $matches);
		$artifactIds = $matches[1];
		return $artifactIds;
	}

    public function getPubMedGrantArtifacts($grantId) {
		$grantId = urlencode(strtolower($grantId));
		$grantIdString = "(" . $grantId . "[grant number] OR " . $grantId . "-*[grant number])";
		$grantEsearchUrl = "http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi" . "?db=pubmed&retmax=100&tool=total-impact.org&email=total-impact@googlegroups.com&term=" . urlencode($grantIdString);
		$requestProfilePage = new HttpRequest($grantEsearchUrl, HTTP_METH_GET);
		$responseProfilePage = $requestProfilePage->send();
		$body = $responseProfilePage->getBody();

		$regex_pattern = '/<Id>(.*)<\/Id>/U';
		preg_match_all($regex_pattern, $body, $matches);
		$artifactIds = $matches[1];
		return $artifactIds;
	}
    
    public function getGithubArtifacts($apiurl, $profileId) {
		$request = new HttpRequest($apiurl, HTTP_METH_GET);
		$response = $request->send();
		$bodyPage = $response->getBody();
		$body = json_decode($bodyPage);

		$id_list = array();
		foreach ($body as $repo) {
			if (isset($repo->name)) {
	    		$id_list[] = "http://github.com/" . $profileId . "/" . $repo->name;
			}
		}
		
		return $id_list;
	}

    public function getGithubUsersArtifacts($profileId) {
		$apiurl = "https://api.github.com/users/" . $profileId . "/repos";
		$id_list = $this->getGithubArtifacts($apiurl, $profileId);
		return $id_list;
	}

    public function getGithubOrgsArtifacts($profileId) {
		$apiurl = "https://api.github.com/orgs/" . $profileId . "/repos";
		$id_list = $this->getGithubArtifacts($apiurl, $profileId);
		return $id_list;
	}
	
}

	
        #$a = new Models_Seeder( new Zend_Config_Ini(CREDS_PATH) );
	#var_dump($a->getGithubOrgArtifacts("bioperl")); 
	#var_dump($a->getMendeleyProfileArtifacts("aliaksandr-birukou")); 
	#var_dump($a->getMendeleyProfileArtifacts("bill-hooker")); 
	#var_dump($a->getMendeleyProfileArtifacts("iain-hrynaszkiewicz")); 
	#var_dump($a->getMendeleyProfileArtifacts("heather-piwowar"));
	#var_dump($a->getMendeleyProfileArtifacts("cameron-neylon"));
	#var_dump($a->getAllMendeleyPubPages("heather-piwowar"));
	
?>
