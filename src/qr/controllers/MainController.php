<?php

class MainController {
	private $twig;

	public function __construct($twig) {
		$this->twig = $twig;
	}

	public function render() {
		echo $this->twig->render('index.html', array('name' => 'abc'));
	}
}