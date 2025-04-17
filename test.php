<?php
$output = [];
exec("python --version 2>&1", $output);
print_r($output);
?>