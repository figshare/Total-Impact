<?php require_once './bootstrap.php'; 

	function seed($name, $type) {
		error_log("in seed with " . $name . " " . $type);
	
		$seed = new Models_Seeder();
		$artifactIds = "";
		$groups = "";
		$contacts = "";
		
		if ($type=="mendeley_profile") {
			$artifactIds = $seed->getMendeleyProfileArtifacts($name);
			$artifactIdsString = implode("\n", $artifactIds); # \n has to be in DOUBLE quotes not single quotes
		} elseif ($type=="mendeley_group") {
			$artifactIds = $seed->getMendeleyGroupArtifacts($name);		
			$artifactIdsString = implode("\n", $artifactIds); # \n has to be in DOUBLE quotes not single quotes
		} elseif ($type=="slideshare_profile") {
			$artifactIds = $seed->getSlideshareProfileArtifacts($name);		
			$artifactIdsString = implode("\n", $artifactIds); # \n has to be in DOUBLE quotes not single quotes
		} elseif ($type=="dryad_profile") {
			$artifactIds = $seed->getDryadProfileArtifacts($name);		
			$artifactIdsString = implode("\n", $artifactIds); # \n has to be in DOUBLE quotes not single quotes
		} elseif ($type=="mendeley_profile_quick") {
			$groups = $seed->getMendeleyProfileGroupsDisplay($name);
			$contacts = $seed->getMendeleyProfileContactsDisplay($name);
		}
		$asarray = array("artifactIds"=>$artifactIdsString, "groups"=>$groups, "contacts"=>$contacts);
		return($asarray);
	}
	
	echo json_encode(seed(trim($_REQUEST['name']), trim($_REQUEST['type'])));
	
?>

