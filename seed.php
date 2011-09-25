<?php require_once './bootstrap.php'; 

function seed($name, $type) {
	error_log("in seed with " . $name . $type);
	
	$seed = new Models_Seed();
	
	if ($type=="mendeley_profile") {
		$artifactIds = $seed->getMendeleyProfileArtifacts($name);
	} elseif ($type=="mendeley_group") {
		$artifactIds = $seed->getMendeleyGroupArtifacts($name);		
	} elseif ($type=="mendeley_group") {
		$artifactIds = $seed->getMendeleyGroupArtifacts($name);		
	} elseif ($type=="slideshare_profile") {
		$artifactIds = $seed->getSlideshareProfileArtifacts($name);		
	} elseif ($type=="dryad_profile") {
		$artifactIds = $seed->getDryadProfileArtifacts($name);		
	}
	$artifactIdsString = implode("\n", $artifactIds); # \n has to be in DOUBLE quotes not single quotes
	return($artifactIdsString);
}
	echo seed(trim($_REQUEST['name']), trim($_REQUEST['type']));
	
?>

