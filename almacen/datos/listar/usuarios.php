<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$busca = isset($_POST['term']) ? $_POST['term'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `t`.`id_usuario`, `t`.`documento`, `t`.`nombre` 
            FROM 
                (SELECT
                    `id_usuario`
                    , `documento`
                    , CONCAT_WS(' ', `nombre1`, `nombre2`, `apellido1`, `apellido2`) AS `nombre`
                FROM
                    `seg_usuarios`
                WHERE `id_usuario` <> 1) AS `t`
            WHERE `nombre` LIKE '%$busca%' OR `documento` LIKE '%$busca%'";
    $rs = $cmd->query($sql);
    $user = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
foreach ($user as $u) {
    $data[] = [
        'id' => $u['id_usuario'],
        'label' => $u['documento'] . ' - ' . $u['nombre'],
    ];
}
if (empty($data)) {
    $data[] = [
        'id' => '0',
        'label' => 'No hay coincidencias...',
    ];
}
echo json_encode($data);
