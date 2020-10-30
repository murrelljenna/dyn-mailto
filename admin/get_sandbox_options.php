<?php

$tags = ['if', 'for', 'apply'];
$filters = ['abs', 'capitalize', 'date', 'upper', 'title', 'split', 'round', 'lower'];
$methods = [];
$properties = [];
$functions = ['random', 'range', 'date', 'min', 'max'];

return new \Twig\Sandbox\SecurityPolicy($tags, $filters, $methods, $properties, $functions);

?>
