<?php

class MainController {
	private $twig;
	private $db;
	private $MenuItemsModel;

	public function __construct($app) {
		$this->twig = $app->twig;
		$this->db = $app->db;
		$this->MenuItemsModel = $app->MenuItemsModel;
	}

	// Home page
	public function index() {
		echo $this->twig->render('index.html', array('title' => 'QRMyOrder' ,'name' => 'abc'));
	}

	// Create order page
	public function createOrder() {
		echo $this->twig->render(
			'create_order.html',
			array(
				'title' => 'Create Order',
				'menu_items' => $this->MenuItemsModel->get()
			)
		);
	}

	public function submitOrder($csvs) {
		$uniqueId = uniqid();
		// Insert a new unique order
		$query = $this->db->prepare('INSERT INTO orders SET id=:uniqueid');
		$query->execute(array(':uniqueid' => $uniqueId));

		foreach ($csvs as $csv) {

			$csvArray = explode(',', $csv);
			$menuItemId = $csvArray[0];
			$addonsCSV = '';

			for ($i = 1; $i < count($csvArray); $i++) {
				if ($i == count ($csvArray) - 1)  {
					$addonsCSV .= $csvArray[$i];
				} else {

					$addonsCSV .= $csvArray[$i].',';
				}
			}

			$price = $this->MenuItemsModel->getPrice($menuItemId, $addonsCSV);

			//$addonsQueryCSV = "'" . implode("','", explode(',', $addonsCSV)) . "'";

			$query = $this->db->prepare(
				'INSERT INTO ordered_items (order_id, menu_item_detail_id, menu_item_type_id, price)'.
				'VALUES(:uniqueId, :addonsCSV, :menuItemId, :totalPrice)'
				);
			$query->execute(
				array(
					':uniqueId' => $uniqueId,
					':addonsCSV' => $addonsCSV,
					':menuItemId' => $menuItemId,
					':totalPrice' => $price
				)
			);
		}
		// insert all menu items

		//return 'https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl=http://qrmyorder.com/order/' . $uniqueId;
		return 'https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl=192.168.56.101/order/' . $uniqueId;

	}

	public function getOrder($orderId) {
		$retOrderedItems = array();

		$orderedItems = $this->db->query(
			"SELECT oi.menu_item_detail_id AS csvs, oi.menu_item_type_id, oi.price, menu_item_type.name AS menuItemName
			FROM ordered_items AS oi INNER JOIN menu_item_type ON oi.menu_item_type_id=menu_item_type.id
			WHERE order_id='". $orderId. "'")->fetchAll(PDO::FETCH_OBJ);

		foreach ($orderedItems as $orderedItem) {
			//print_r($orderedItem);
			$orderedItem->addons = array();

			$addons = $this->db->query(
				"select mid.id AS midID, mid.name AS midName,
					midt.id AS midtID, midt.name AS midtName
					FROM menu_item_detail AS mid JOIN menu_item_detail_type AS midt ON mid.menu_item_detail_type_id=midt.id
					WHERE mid.id IN (". $orderedItem->csvs . ")"
				)->fetchAll(PDO::FETCH_OBJ);

			foreach ($addons as $addon) {
				if (!isset($orderedItem->addons[$addon->midtName])) {
					$orderedItem->addons[$addon->midtName] = array();
				}

				array_push($orderedItem->addons[$addon->midtName], $addon);
			}

			array_push($retOrderedItems, $orderedItem);
		}

		echo $this->twig->render(
			'order.html',
			array(
				'title' => 'Order',
				'ordered_items' => $retOrderedItems
			)
		);
	}
}