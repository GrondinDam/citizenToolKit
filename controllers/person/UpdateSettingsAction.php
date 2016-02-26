<?php
/**
   * Register a new user for the application
   * Data expected in the post : name, email, postalCode and pwd
   * @return Array as json with result => boolean and msg => String
   */
class UpdateSettingsAction extends CAction
{
    public function run()
    {
        $controller=$this->getController();
        $res=Preference::updatePreferences(Yii::app()->session["userId"],Person::COLLECTION);
		Rest::json($res);
		exit;
    }
}