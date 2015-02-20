<?php
$container->loadFromExtension('framework', array(
	'templating'      => array(
//		'engines' => array('twig'),
		'assets_version' => exec('git rev-parse --short HEAD'),
//		'assets_base_urls' => [
//            'http' => ['/compiled']
//		],
	),
));