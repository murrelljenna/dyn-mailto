<?php

$tags = ['if', 'for'];
$filters = [];
$methods = [];
$properties = [];
$functions = ['random'];

return new \Twig\Sandbox\SecurityPolicy($tags, $filters, $methods, $properties, $functions);

?>
