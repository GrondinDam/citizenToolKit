<?php
/**
 * [actionAddWatcher 
 * create or update a user account
 * if the email doesn't exist creates a new citizens with corresponding data 
 * else simply adds the watcher app the users profile ]
 * @return [json] 
 */
class SaveActionAction extends CAction
{
    public function run()
    {
        error_log("saveSession");
        $res = array();
        if( Yii::app()->session["userId"] )
        {
            $email = $_POST["email"];
            $name  = $_POST['name'];

            //Organizer of the action
            if ($_POST['organizer'] == "currentUser") {
                $organizerId = Yii::app()->session["userId"];
                $organizerType = Person::COLLECTION;
            } else {
                $organizerId = $_POST['organizer'];
                $organizerType = Organization::COLLECTION;
            }

            //if exists login else create the new user
            //TODO Tib : do not use the email to retrieve a person : prefere use the getById
            if(PHDB::findOne (Person::COLLECTION, array( "email" => $email ) ))
            {
                //udate the new app specific fields
                $entryInfos = array();
                $entryInfos['email'] = (string)$email;
                $entryInfos['name'] = (string)$name;
                $entryInfos['organizerId'] = $organizerId;
                $entryInfos['organizerType'] = $organizerType;
                if( isset($_POST['room']) )
                {
                    $entryInfos['room'] = $_POST['room'];
                    $res['parentId'] = $_POST['room'];
                    //this might not be necessary , since the information is on the parent action
                    $room = PHDB::findOne (ActionRoom::COLLECTION, array( "_id" => new MongoId($_POST['room']) ) );
                    if( isset( $room["parentType"] ) ) 
                        $entryInfos['parentType'] = $room['parentType'];
                    if( isset( $room["parentId"] )  ) 
                        $entryInfos['parentId'] = $room['parentId'];
                }
                if( isset($_POST['message']) )
                    $entryInfos['message'] = (string)$_POST['message'];
                if( isset($_POST['type']) )
                    $entryInfos['type'] = $_POST['type'];
                if( isset($_POST['tags']) && count($_POST['tags'])>0 )
                    $entryInfos['tags'] = $_POST['tags'];
                if( isset($_POST['cp']) )
                    $entryInfos['cp'] = explode(",",$_POST['cp']);
                if( isset($_POST['urls']) && count($_POST['urls'])>0 )
                    $entryInfos['urls'] = $_POST['urls'];
                if( isset($_POST['dateEnd']) && $_POST['dateEnd'] != "" )
                    $entryInfos['dateEnd'] = round(strtotime( str_replace("/", "-", $_POST['dateEnd']) ));
                if( isset($_POST['startDate']) && $_POST['startDate'] != "" )
                    $entryInfos['startDate'] = round(strtotime( str_replace("/", "-", $_POST['startDate']) ));

                $entryInfos['created'] = time();
                

                $where = array();
                if( isset( $_POST['id'] ) ){
                    $where["_id"] = new MongoId($_POST['id']);
                    $result = PHDB::update( ActionRoom::COLLECTION_ACTIONS,  $where, 
                                                   array('$set' => $entryInfos ));
                    $actionId = $_POST['id'];
                } else {
                    $actionId = new MongoId();
                    $entryInfos["_id"] = $actionId;
                    $result = PHDB::insert( ActionRoom::COLLECTION_ACTIONS,$entryInfos );
                }
                

                $res['result'] = true;
                $res['msg'] = "actionSaved";
                $res['actionId'] = $actionId;

                //Notify Element participants 
                Notification::actionOnPerson ( ActStr::VERB_ADD_ACTION, ActStr::ICON_ADD, "", array( "type" => ActionRoom::COLLECTION_ACTIONS , "id" => $actionId ));
                
            } else
                $res = array('result' => false , 'msg'=>"user doen't exist");
        } else
            $res = array('result' => false , 'msg'=>'something somewhere went terribly wrong');
            
        Rest::json($res);  
        Yii::app()->end();
    }
}