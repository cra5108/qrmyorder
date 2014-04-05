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
}