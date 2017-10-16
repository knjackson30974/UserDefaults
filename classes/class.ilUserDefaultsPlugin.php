<?php
require_once('./Services/EventHandling/classes/class.ilEventHookPlugin.php');
require_once('./Customizing/global/plugins/Services/EventHandling/EventHook/UserDefaults/classes/UserSetting/class.ilUserSetting.php');

/**
 * Class ilUserDefaultsPlugin
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilUserDefaultsPlugin extends ilEventHookPlugin {

	const PLUGIN_NAME = 'UserDefaults';
	/**
	 * @var
	 */
	protected static $instance;


	/**
	 * @return ilUserDefaultsPlugin
	 */
	public static function getInstance() {
		if (!isset(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * Handle the event
	 *
	 * @param    string        component, e.g. "Services/User"
	 * @param    event         event, e.g. "afterUpdate"
	 * @param    array         array of event specific parameters
	 */
	public function handleEvent($a_component, $a_event, $a_parameter) {

		if ( $a_event == 'afterLogin' OR ($a_component == 'Services/User' AND ($a_event == 'savAsNew' OR $a_event == 'afterCreate'))) {

		    /**
			 * @var $ilUser ilObjUser
			 */
            if ( is_null($a_parameter['user_obj']) && $a_parameter['username'] !== 'anonymous' ) {
                $ilUser = new ilObjUser( ilObjUser::getUserIdByLogin( $a_parameter['username'] ) );
            } else {
                $ilUser = $a_parameter['user_obj'];
            }

			if ($ilUser instanceof ilObjUser) {
				// Do Stuff
				/**
				 * @var $ilUserSetting ilUserSetting
				 */
				foreach (ilUserSetting::where(array( 'status' => ilUserSetting::STATUS_ACTIVE ))->get() as $ilUserSetting) {
					$ilUserSetting->doAssignements($ilUser);
				}
			}
		}
	}


//		/**
//		 * @param $key
//		 * @return mixed|string
//		 * @throws \ilException
//		 */
//		public function txt($key) {
//			require_once('./Customizing/global/plugins/Libraries/PluginTranslator/class.sragPluginTranslator.php');
//
//			return sragPluginTranslator::getInstance($this)->active()->write()->txt($key);
//		}

	/**
	 * @return string
	 */
	public function getPluginName() {
		return self::PLUGIN_NAME;
	}


	/**
	 * @return bool
	 */
	public static function is50() {
		$version = explode('.', ILIAS_VERSION_NUMERIC);

		return $version[0] >= 5;
	}


	/**
	 * @return bool
	 */
	public static function is51() {
		$version = explode('.', ILIAS_VERSION_NUMERIC);

		return $version[0] >= 5 && $version[1] >= 1;
	}
}
