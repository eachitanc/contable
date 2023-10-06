<?php
$_post = json_decode(file_get_contents('php://input'), true);
$dato = $_post['dato'];
include '../../../conexion.php';
// Inicio conexion a la base de datos
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
} catch (Exception $e) {
    die("No se pudo conectar: " . $e->getMessage());
}
// Inicio transaccion 
try {
    $query = $cmd->prepare("DELETE FROM seg_pto_mvto WHERE id_pto_mvto =?");
    $query->bindParam(1, $dato);
    $query->execute();
    $response[] = array("value" => 'ok', "id" => $dato);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
echo json_encode($response);
