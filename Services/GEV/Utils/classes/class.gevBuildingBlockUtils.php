<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Utilities for generali users.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/
class gevBuildingBlockUtils {
	static protected $instances = array();
	const TABLE_NAME = "dct_building_blocks";

	protected $block_unit_id = "";
	protected $title = "";
	protected $content = "";
	protected $learning_dest = "";
	protected $is_wp_relevant = false;
	protected $is_active = false;

	protected function __construct($a_block_unit_id) {
		global $ilDB, $ilUser;
				
		$this->block_unit_id = $a_block_unit_id;
		$this->db = $ilDB;
		$this->ilUser = $ilUser;
	}

	public function getInstance($a_block_unit_id) {
		if (array_key_exists($a_block_unit_id, self::$instances)) {
			return self::$instances[$a_block_unit_id];
		}
		
		self::$instances[$a_block_unit_id] = new gevBuildingBlockUtils($a_block_unit_id);
		return self::$instances[$a_block_unit_id];
	}

	public function getId() {
		return $this->block_unit_id;
	}

	public function getTitle() {
		return $this->title;
	}

	public function setTitle($a_title) {
		$this->title = $a_title;
	}

	public function getContent() {
		return $this->content;
	}

	public function setContent($a_content) {
		$this->content = $a_content;
	}

	public function getLearningDestination() {
		return $this->learning_dest;
	}

	public function setLearningDestination($a_learning_destination) {
		$this->learning_dest = $a_learning_destination;
	}

	public function isWPRelevant() {
		return $this->is_wp_relevant;
	}

	public function setIsWPRelevant($a_is_wp_relevant) {
		$this->is_wp_relevant = $a_is_wp_relevant;
	}

	public function isActive() {
		return $this->is_active;
	}

	public function setIsActice($a_is_active) {
		$this->is_active = $a_is_active;
	}

	public function loadData() {
		$sql = "SELECT obj_id, title, content, learning_dest, is_wp_relevant, is_active FROM ".self::TABLE_NAME." WHERE obj_id = ".$this->getId();

		$res = $this->db->query($sql);
		
		if($this->db->numRows($res) > 0) {
			$row = $this->db->fetchAssoc($res);

			$this->title = $row["title"];
			$this->content = $row["content"];
			$this->learning_dest = $row["learning_dest"];
			$this->is_wp_relevant = $row["is_wp_relevant"];
			$this->is_active = $row["is_active"];
		}
	}

	public function update() {
		$sql = "UPDATE ".self::TABLE_NAME." SET title = '".$this->getTitle()."'"
									   .", content = '".$this->getContent()."'"
									   .", learning_dest = '".$this->getLearningDestination()."'"
									   .", is_wp_relevant = ".$this->isWPRelevant().""
									   .", is_active = ".$this->isActive().""
									   .", last_change_user = ".$this->ilUser->getId().""
									   .", last_change_date = NOW()"
									   ." WHERE obj_id = ".$this->getId();

		$this->db->manipulate($sql);

		return;
	}

	public function save() {
		
		$isWPRelevant = ($this->isWPRelevant() === "") ? "0" : "1";
		$isActive = ($this->isActive() === "") ? "0" : "1";

		$sql = "INSERT INTO ".self::TABLE_NAME.""
				." (obj_id, title, content, learning_dest, is_wp_relevant, is_active, last_change_user, last_change_date, is_deleted)"
				." VALUES ("
					.$this->getId()
					.",'".$this->getTitle()."'"
					.",'".$this->getContent()."'"
					.",'".$this->getLearningDestination()."'"
					.",".$isWPRelevant
					.",".$isActive
					.",".$this->ilUser->getId()
					.", NOW()"
					.", 0)";

		$this->db->manipulate($sql);

		return;
	}

	static public function getAllBuildingBlocks($a_search_opts,$a_order, $a_order_direction) {
		global $ilDB;

		$add_where = self::createAdditionalWhere($a_search_opts);
		$sql = "SELECT obj_id, title, content, learning_dest, is_wp_relevant, is_active FROM ".self::TABLE_NAME." WHERE is_deleted = 0";
		$sql .= $add_where;

		if($a_order !== null) {
			$sql .= " ORDER BY ".$a_order." ".$a_order_direction;
		}

		$ret = array();
		$res = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($res)) {
			$ret[] = $row;
		}

		return $ret;
	}

	static private function createAdditionalWhere($a_search_opts) {
		$ret = "";

			foreach ($a_search_opts as $key => $value) {
				switch($key) {
					case "title":
					case "content":
					case "learning_dest":
						$ret .= " AND ".$key." LIKE '%".$value."%'";
						break;
					case "is_wp_relevant":
					case "is_active":
						if($value != -1) {
							if($value == "ja") {
								$ret .= " AND ".$key." = 1";
							} elseif($value == "nein"){
								$ret .= " AND ".$key." = 0";
							}
						}
						break;
				}
				
			}

		return $ret;
	}

	static public function deleteBuildingBlock($a_obj_id) {
		global $ilDB;

		$query = "UPDATE ".self::TABLE_NAME." SET is_deleted = 1 WHERE obj_id = ".$ilDB->quote($a_obj_id,"integer");
		$ilDB->manipulate($query);

		return;
	}

	static public function getDeleteLink($a_obj_id) {
		global $ilCtrl,$ilUser;

		$ilCtrl->setParameterByClass("gevDecentralTrainingBuildingBlockAdminGUI", "obj_id", $a_obj_id);
		$lnk = $ilCtrl->getLinkTargetByClass("gevDecentralTrainingBuildingBlockAdminGUI", "delete");
		$ilCtrl->clearParametersByClass("gevDecentralTrainingBuildingBlockAdminGUI");
		return $lnk;
	}

	static public function getEditLink($a_obj_id) {
		global $ilCtrl,$ilUser;

		$ilCtrl->setParameterByClass("gevDecentralTrainingBuildingBlockAdminGUI", "obj_id", $a_obj_id);
		$lnk = $ilCtrl->getLinkTargetByClass("gevDecentralTrainingBuildingBlockAdminGUI", "edit");
		$ilCtrl->clearParametersByClass("gevDecentralTrainingBuildingBlockAdminGUI");
		return $lnk;
	}
}

?>
