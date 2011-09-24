<?php

#require_once 'FirePHPCore/fb.php';

/**
 * This is a grabs artifact IDs given seeds
 *
 * @author heather
 */
class Models_Seed {
	
    public function getMendeleyProfileArtifacts($profileId) {
		$mendeleyUrlProfilePage = "http://www.mendeley.com/profiles/" . $profileId . "/";
		$requestProfilePage = new HttpRequest($mendeleyUrlProfilePage, HTTP_METH_GET);
		$responseProfilePage = $requestProfilePage->send();
		$bodyProfilePage = $responseProfilePage->getBody();

		/* For now only looks at the first page */
		$mendeleyUrlJournalPage = "http://www.mendeley.com/profiles/" . $profileId . "/publications/journal/";
		$requestJournalPage = new HttpRequest($mendeleyUrlJournalPage, HTTP_METH_GET);
		$responseJournalPage = $requestJournalPage->send();
		$bodyJournalPage = $responseJournalPage->getBody();
		
		$body = $bodyProfilePage . $bodyJournalPage;
		$regex_pattern = '/rft_id=info.*%2F(.*)(&|").*span/U';
		preg_match_all($regex_pattern, $body, $matches);
		#FB::log($matches);
		$artifactIds = $matches[1];
		$artifactIds = str_replace("%2F", '/', $artifactIds);
		return $artifactIds;
	}

    public function getMendeleyGroupArtifacts($groupId) {
	    $TOTALIMPACT_MENDELEY_KEY = "3a81767f6212797750ef228c8cb466bc04dca4ba1";
	    $MENDELEY_LOOKUP_FROM_DOI_URL_PART1 = "http://api.mendeley.com/oapi/documents/groups/";
		$MENDELEY_LOOKUP_FROM_DOI_URL_PART2 = "/docs/?details=true&items=100&consumer_key=" . $TOTALIMPACT_MENDELEY_KEY;
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
    
}

	#$a = new Models_Seed();
	#var_dump($a->getMendeleyProfileArtifacts("heather-piwowar"));

?>
