<?php require_once './bootstrap.php'; 

	function seed($name, $type) {
		error_log("in seed with " . $name . " " . $type);
	
		$seed = new Models_Seeder();
		$artifactIds = array();
		$groups = "";
		$contacts = "";
		
		if ($type=="mendeley_profile") {
			$artifactIds = $seed->getMendeleyProfileArtifacts($name);
		} elseif ($type=="mendeley_group") {
			$artifactIds = $seed->getMendeleyGroupArtifacts($name);		
		} elseif ($type=="slideshare_profile") {
			$artifactIds = $seed->getSlideshareProfileArtifacts($name);		
		} elseif ($type=="dryad_profile") {
			$artifactIds = $seed->getDryadProfileArtifacts($name);		
		} elseif ($type=="pubmed_grant") {
			$artifactIds = $seed->getPubMedGrantArtifacts($name);		
		} elseif ($type=="mendeley_profile_quick") {
			$groups = $seed->getMendeleyProfileGroupsDisplay($name);
			$contacts = $seed->getMendeleyProfileContactsDisplay($name);
		}

		$artifactIdsString = implode("\n", $artifactIds); # \n has to be in DOUBLE 	 quotes not single quotes
		error_log("returning " . count($artifactIds) . " artifacts");
		$asarray = array("artifactIds"=>$artifactIdsString, "groups"=>$groups, "contacts"=>$contacts);
		return($asarray);
	}
	
	echo json_encode(seed(trim($_REQUEST['name']), trim($_REQUEST['type'])));
	
?>

