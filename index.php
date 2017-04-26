<?php
include_once 'query.class.php';
$status = new MinecraftServerQuery();

$response = $status->getStatus('mc.pixelroad.net');

echo "<pre>" . print_r($response,true) . "</pre>";
?>
