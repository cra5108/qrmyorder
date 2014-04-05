<?php
class MenuItems {
	private $db;

	public function __construct($db) {
		$this->db = $db;
	}

	public function get() {
		$returnArray = array();

		$menuItems = $this->db->query('SELECT mit.id, mit.name, mit.price, mit.description, mit.addons FROM menu_item_type AS mit')->fetchAll(PDO::FETCH_OBJ);


		foreach ($menuItems as $menuItem) {
			$addonsCSV = $menuItem->addons;
			$addonsQueryCSV = "'" . implode("','", explode(',', $addonsCSV)) . "'";

			$addons = $this->db->query(
				'SELECT mid.id AS addonId, mid.name AS addonName, mid.price, mid.menu_item_detail_type_id, midt.name AS addonCategoryName
					FROM menu_item_detail AS mid LEFT OUTER JOIN menu_item_detail_type AS midt ON mid.menu_item_detail_type_id=midt.id
					WHERE mid.menu_item_detail_type_id IN (' . $addonsQueryCSV . ')
					ORDER BY menu_item_detail_type_id, precedence ASC')->fetchAll(PDO::FETCH_OBJ);

			$menuItem->addons = array();

			foreach ($addons as $addon) {
				if (!isset($menuItem->addons[$addon->addonCategoryName])) {
					$menuItem->addons[$addon->addonCategoryName] = array();
				}

				array_push($menuItem->addons[$addon->addonCategoryName], $addon);
			}

			array_push($returnArray, $menuItem);
		}
		//echo'<pre>';print_r($returnArray);echo'</pre>';

		return $returnArray;
	}

	public function getPrice($menuItemId, $addonIds) {

		$addonsQueryCSV = "'" . implode("','", explode(',', $addonIds)) . "'";

		$queryString = 'SELECT sum(mit.price';

		if (strlen($addonIds) > 0) {
			$queryString .= '+ (SELECT sum(mid.price) FROM menu_item_detail AS mid WHERE id IN (' . $addonsQueryCSV .'))';
		}
		$queryString .= ') FROM menu_item_type AS mit WHERE mit.id=' . $menuItemId;

		$price = $this->db->query($queryString)->fetch();

		return $price[0];
	}
}