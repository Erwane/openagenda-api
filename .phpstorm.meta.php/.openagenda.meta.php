<?php
// @link https://confluence.jetbrains.com/display/PhpStorm/PhpStorm+Advanced+Metadata
namespace PHPSTORM_META {

    override(
		\OpenAgenda\Endpoint\EndpointFactory::make(0),
		map([
			'/agenda' => \OpenAgenda\Endpoint\Agenda::class,
			'/location' => \OpenAgenda\Endpoint\Location::class,
			'/event' => \OpenAgenda\Endpoint\Event::class,
		]),
	);
}
