<?php

class GetDataDetailAction extends CAction {
/**
* Dashboard Organization
*/
    public function run($type, $id, $dataName) { 
    	//$controller=$this->getController();

    	$contextMap = array();
		$element = Element::getByTypeAndId($type, $id);

		if($dataName == "follows"){
			if(isset($element["links"]["follows"])){
				foreach ($element["links"]["follows"] as $keyFollow => $value){
					//$need = Need::getSimpleNeedById($keyFollow);
					$follow = Element::getByTypeAndId($value["type"], $keyFollow);
					$follow["type"] = $value["type"];
	           		$contextMap[$keyFollow] = $follow;
				}
			}
		}

		if($dataName == "followers"){
			if(isset($element["links"]["followers"])){
				foreach ($element["links"]["followers"] as $keyFollow => $value){
					//$need = Need::getSimpleNeedById($keyFollow);
					$follow = Element::getByTypeAndId($value["type"], $keyFollow);
					$follow["type"] = $value["type"];
	           		$contextMap[$keyFollow] = $follow;
				}
			}
		}

		if($dataName == "links"){
			$links=@$element["links"];
			$contextMap = Element::getAllLinks($links,$type, $id);
		}

		if($dataName == "events"){ //var_dump($element["links"]); exit;
			if(isset($element["links"]["events"])){
				foreach ($element["links"]["events"] as $keyEv => $valueEv) {
					 $event = Event::getSimpleEventById($keyEv);
					 //var_dump($event); exit;
					 if(!empty($event)){
					 	$event["typeEvent"] = @$event["type"];
						$event["type"] = "events";
						$event["typeSig"] = Event::COLLECTION;
						$contextMap[$keyEv] = $event;
					 }
				}
			}
		}

		if($dataName == "projects"){
			foreach ($element["links"]["projects"] as $keyProj => $valueProj) {
				$project = Project::getPublicData($keyProj);
				$project["type"] = "projects";
				$project["typeSig"] = Project::COLLECTION;
           		$contextMap[$keyProj] = $project;
			}
		}
		if($dataName == "organizations"){
			foreach ($element["links"]["memberOf"] as $keyOrga => $valueOrga) {
				$orga = Organization::getPublicData($keyOrga);
				//$orga["type"] = "organization";
				$orga["typeSig"] = Organization::COLLECTION;
           		$contextMap[$keyOrga] = $orga;
			}
		}

		if($dataName == "classified"){
			$contextMap = Classified::getClassifiedByCreator($id);
		}


		if($dataName == "poi"){
			$contextMap = Poi::getPoiByIdAndTypeOfParent($id, $type);

		}





		if($dataName == "liveNow"){

			$date = date('Y-m-d H:i:s');
			$dDate = strtotime($date);//+" +7 day");
			$sDate = strtotime($date+" +7 day");
			
			//EVENTS
			$events = PHDB::findAndSortAndLimitAndIndex( Event::COLLECTION,
							array("startDate" => array( '$gte' => new MongoDate( time() ) )),
							array("startDate"=>1), 5);
			foreach ($events as $key => $value) {
				$events[$key]["type"] = "events";
				$events[$key]["typeSig"] = "events";
				if(@$value["startDate"]) {
					//var_dump(@$value["startDate"]);
					$events[$key]["updatedLbl"] = Translate::pastTime(@$value["startDate"]->sec,"timestamp");
		  		}
		  	}
		  	$contextMap = array_merge($contextMap, $events); //Poi::getPoiByIdAndTypeOfParent($id, $type);
			

			//CLASSIFIED
			$classified = PHDB::findAndSortAndLimitAndIndex( Classified::COLLECTION,
							array(),
							array("updated"=>-1), 5);
			foreach ($classified as $key => $value) {
				$classified[$key]["type"] = "classified";
				$classified[$key]["typeSig"] = "classified";
				if(@$value["updated"]) {
					//var_dump(@$value["startDate"]);
					$classified[$key]["updatedLbl"] = Translate::pastTime(@$value["updated"],"timestamp");
		  		}
		  	}
		  	$contextMap = array_merge($contextMap, $classified); //Poi::getPoiByIdAndTypeOfParent($id, $type);
			




			echo $this->getController()->renderPartial($_POST['tpl'], array("result"=>$contextMap));
			Yii::app()->end();
		}



		return Rest::json($contextMap);
		Yii::app()->end();


		
	}
}



?>