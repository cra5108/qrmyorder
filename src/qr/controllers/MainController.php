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

			$addonsQueryCSV = "'" . implode("','", explode(',', $addonsCSV)) . "'";

			$query = $this->db->prepare(
				'INSERT INTO ordered_items (order_id, menu_item_detail_id, menu_item_type_id, price)'.
				'VALUES(:uniqueId, :addonsCSV, :menuItemId, :totalPrice)'
				);
			$query->execute(
				array(
					':uniqueId' => $uniqueId,
					':addonsCSV' => $addonsQueryCSV,
					':menuItemId' => $menuItemId,
					':totalPrice' => $price
				)
			);
		}
		// insert all menu items

	}
}