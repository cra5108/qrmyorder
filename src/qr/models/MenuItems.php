<?php
class MenuItems {
	private $db;

	public function __construct($db) {
		$this->db = $db;
	}

	public function getMenuCategories() {
		echo 'getmenucates';
	}
}