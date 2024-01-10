<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$busca = isset($_POST['term']) ? $_POST['term'] : '';
$tipo = $_POST['tipo'];
$pto = $_POST['pto'];

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    switch ($tipo) {
        case 1:
            $sql = "SELECT
                        `id_cod` AS `id`
                        , `codigo`
                        , `nombre`
                    FROM `seg_pto_codigo_cgr`
                    WHERE `presupuesto` = $pto AND `tipo` = 'D' AND (`codigo` LIKE '%$busca%' OR `nombre` LIKE '%$busca%')";
            break;
        case 2:
            $sql = "SELECT
                        `id_cpc` AS `id` 
                        , `codigo`
                        , `producto` AS `nombre` 
                    FROM `seg_pto_cpc`
                    WHERE  `producto` LIKE '%$busca%' OR `codigo` LIKE '%$busca%'";
            break;
        case 3:
            $sql = "SELECT
                        `id_fuente` AS `id`
                        , `codigo`
                        , `fuente` AS `nombre`
                    FROM
                        `seg_pto_fuente`
                    WHERE `codigo` LIKE '%$busca%'OR `fuente` LIKE '%$busca%'";
            break;
        case 4:
            $sql = "SELECT
                        `id_tercero` AS `id`
                        , `codigo`
                        , `entidad` AS `nombre`
                    FROM
                        `seg_pto_terceros`
                    WHERE `codigo` LIKE '%$busca%' OR `entidad` LIKE '%$busca%'";
            break;
        case 5:
            $sql = "SELECT
                        `id_politica` AS `id`
                        , `codigo`
                        , `politica` AS `nombre`
                    FROM
                        `seg_pto_politica`
                    WHERE `codigo` LIKE '%$busca%' OR `politica` LIKE '%$busca%'";
            break;
    }
    $rs = $cmd->query($sql);
    $lista = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if (!empty($lista)) {
    foreach ($lista as $l) {
        $data[] = [
            'id' => $l['id'],
            'label' => $l['codigo'] . ' -> ' . mb_strtoupper($l['nombre']),
        ];
    }
} else {
    $data[] = [
        'id' => '0',
        'label' => 'No hay coincidencias...',
    ];
}
echo json_encode($data);
