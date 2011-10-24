<?php require_once './bootstrap.php'; 

	function runseed($name, $type) {
		breadcrumb("in seed with " . $name . " " . $type);
	
		$seed = new Models_Seeder();
		$artifactIds = array();
		$groups = "";
		$contacts = "";
		
		if ($type=="mendeley_profile") {
			$detailList = $seed->getMendeleyProfileArtifacts($name);
			$detailString = implode("\n", $detailList); # \n has to be in DOUBLE quotes not single quotes
			$response = array("artifactIds"=>$detailString, "artifactCount"=>count($detailList));
		} elseif ($type=="mendeley_group") {
			$detailList = $seed->getMendeleyGroupArtifacts($name);
			$detailString = implode("\n", $detailList); # \n has to be in DOUBLE quotes not single quotes
			$response = array("artifactIds"=>$detailString, "artifactCount"=>count($detailList));
		} elseif ($type=="slideshare_profile") {
			$detailList = $seed->getSlideshareProfileArtifacts($name);
			$detailString = implode("\n", $detailList); # \n has to be in DOUBLE quotes not single quotes
			$response = array("artifactIds"=>$detailString, "artifactCount"=>count($detailList));
		} elseif ($type=="dryad_profile") {
			$detailList = $seed->getDryadProfileArtifacts($name);
			$detailString = implode("\n", $detailList); # \n has to be in DOUBLE quotes not single quotes
			$response = array("artifactIds"=>$detailString, "artifactCount"=>count($detailList));
		} elseif ($type=="pubmed_grant") {
			$detailList = $seed->getPubMedGrantArtifacts($name);
			$detailString = implode("\n", $detailList); # \n has to be in DOUBLE quotes not single quotes
			$response = array("artifactIds"=>$detailString, "artifactCount"=>count($detailList));
		} elseif ($type=="quick_report_contacts") {
			$contacts = $seed->getMendeleyProfileContactsDisplay($name);
			$response = array("contacts"=>$contacts);
		} elseif ($type=="quick_report_groups") {
			$groups = $seed->getMendeleyProfileGroupsDisplay($name);
			$response = array("groups"=>$groups);
		}

                return($response);
                
	}
	
	breadcrumb("finished seed.php");
	echo json_encode(runseed(trim($_REQUEST['name']), trim($_REQUEST['type'])));
	
?>

