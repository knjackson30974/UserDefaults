<?php
require_once('./Customizing/global/plugins/Services/EventHandling/EventHook/UserDefaults/classes/UserSetting/class.ilUserSettingsFormGUI.php');
require_once('./Customizing/global/plugins/Services/EventHandling/EventHook/UserDefaults/classes/UserSetting/class.ilUserSettingsTableGUI.php');
require_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');

/**
 * Class ilUserSettingsGUI
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 * @version           1.0.0
 *
 * @ilCtrl_IsCalledBy ilUserSettingsGUI : ilUserDefaultsConfigGUI
 */
class ilUserSettingsGUI {

	const CMD_INDEX = 'configure';
	const CMD_SEARCH_COURSES = 'searchContainer';
	const CMD_CANCEL = 'cancel';
	const CMD_CREATE = 'create';
	const CMD_UPDATE = 'update';
	const CMD_ADD = 'add';
	const CMD_EDIT = 'edit';
	const CMD_CONFIRM_DELETE = 'confirmDelete';
	const CMD_DEACTIVATE = 'deactivate';
	const CMD_ACTIVATE = 'activate';
	const CMD_DELETE = 'delete';
	const IDENTIFIER = 'set_id';
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var HTML_Template_ITX|ilTemplate
	 */
	protected $tpl;


	/**
	 * @param $parent_gui
	 */
	public function __construct($parent_gui) {
		global $ilCtrl, $tpl;
		$this->ctrl = $ilCtrl;
		$this->tpl = $tpl;
		$this->pl = ilUserDefaultsPlugin::getInstance();
//		$this->pl->updateLanguageFiles();
		$this->ctrl->saveParameter($this, self::IDENTIFIER);
	}


	public function executeCommand() {
		$cmd = $this->ctrl->getCmd(self::CMD_INDEX);
		switch ($cmd) {
			case self::CMD_INDEX:
				$this->index();
				break;
			case self::CMD_SEARCH_COURSES:
			case self::CMD_CANCEL:
			case self::CMD_CREATE:
			case self::CMD_UPDATE:
			case self::CMD_ADD:
			case self::CMD_EDIT:
			case self::CMD_ACTIVATE:
			case self::CMD_DEACTIVATE:
			case self::CMD_CONFIRM_DELETE:
			case self::CMD_DELETE:
				$this->{$cmd}();
				break;
		}

		return true;
	}


	protected function activate() {
		$ilUserSetting = ilUserSetting::find($_GET[self::IDENTIFIER]);
		$ilUserSetting->setStatus(ilUserSetting::STATUS_ACTIVE);
		$ilUserSetting->update();
		$this->cancel();
	}


	protected function deactivate() {
		$ilUserSetting = ilUserSetting::find($_GET[self::IDENTIFIER]);
		$ilUserSetting->setStatus(ilUserSetting::STATUS_INACTIVE);
		$ilUserSetting->update();
		$this->cancel();
	}


	protected function index() {
		$ilUserSettingsTableGUI = new ilUserSettingsTableGUI($this);
		$this->tpl->setContent($ilUserSettingsTableGUI->getHTML());
	}


	protected function add() {
		$ilUserSettingsFormGUI = new ilUserSettingsFormGUI($this, new ilUserSetting());
		$this->tpl->setContent($ilUserSettingsFormGUI->getHTML());
	}


	protected function create() {
		$ilUserSettingsFormGUI = new ilUserSettingsFormGUI($this, new ilUserSetting());
		$ilUserSettingsFormGUI->setValuesByPost();
		if ($ilUserSettingsFormGUI->saveObject()) {
			ilUtil::sendSuccess($this->pl->txt('msg_entry_added'), true);
			$this->ctrl->redirect($this, self::CMD_INDEX);
		}
		$this->tpl->setContent($ilUserSettingsFormGUI->getHTML());
	}


	protected function edit() {
		$ilUserSettingsFormGUI = new ilUserSettingsFormGUI($this, ilUserSetting::find($_GET[self::IDENTIFIER]));
		$ilUserSettingsFormGUI->fillForm();
		$this->tpl->setContent($ilUserSettingsFormGUI->getHTML());
	}


	protected function update() {
		$ilUserSettingsFormGUI = new ilUserSettingsFormGUI($this, ilUserSetting::find($_GET[self::IDENTIFIER]));
		$ilUserSettingsFormGUI->setValuesByPost();
		if ($ilUserSettingsFormGUI->saveObject()) {
			ilUtil::sendSuccess($this->pl->txt('msg_entry_added'), true);
			$this->cancel();
		}
		$this->tpl->setContent($ilUserSettingsFormGUI->getHTML());
	}


	public function confirmDelete() {
		$conf = new ilConfirmationGUI();
		$conf->setFormAction($this->ctrl->getFormAction($this));
		$conf->setHeaderText($this->pl->txt('msg_confirm_delete'));
		$conf->setConfirm($this->pl->txt('set_delete'), self::CMD_DELETE);
		$conf->setCancel($this->pl->txt('set_cancel'), self::CMD_INDEX);
		$this->tpl->setContent($conf->getHTML());
	}


	public function delete() {
		$ilUserSetting = ilUserSetting::find($_GET[self::IDENTIFIER]);
		$ilUserSetting->delete();
		$this->cancel();
	}


	public function cancel() {
		$this->ctrl->setParameter($this, self::IDENTIFIER, NULL);
		$this->ctrl->redirect($this, self::CMD_INDEX);
	}


	protected function searchContainer() {
		global $ilDB;
		/**
		 * @var ilDB $ilDB
		 */

		$term = $ilDB->quote('%' . $_GET['term'] . '%', 'text');
		$type = $ilDB->quote($_GET['container_type'], 'text');

		$query = "SELECT obj.obj_id, obj.title
				FROM object_data obj
				 LEFT JOIN object_translation trans ON trans.obj_id = obj.obj_id
				 JOIN object_reference ref ON obj.obj_id = ref.obj_id
			 WHERE obj.type = $type AND
				 (obj.title LIKE $term OR trans.title LIKE $term)
				 AND ref.deleted IS NULL
			 ORDER BY  obj.title";

		$res = $ilDB->query($query);
		$result = array();
		while ($row = $ilDB->fetchAssoc($res)) {
			if($row['title'] != "__OrgUnitAdministration")
				$result[] = array( "id" => $row['obj_id'], "text" => $row['title'] );
		}
		echo json_encode($result);
		exit;
	}


	protected function applyFilter() {
		$tableGui = new ilUserSettingsTableGUI($this, self::CMD_INDEX);
		$tableGui->resetOffset(true);
		$tableGui->writeFilterToSession();
		$this->ctrl->redirect($this, self::CMD_INDEX);
	}


	protected function resetFilter() {
		$tableGui = new ilUserSettingsTableGUI($this, self::CMD_INDEX);
		$tableGui->resetOffset();
		$tableGui->resetFilter();
		$this->ctrl->redirect($this, self::CMD_INDEX);
	}
}

?>
