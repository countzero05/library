<?php
$container->loadFromExtension('framework', array(
	'assets'      => array(
		'version' => exec('git rev-parse --short HEAD'),
	),
));