<?php
// Realiza la suma del valor total asignado a un CDP
include '../../../conexion.php';
$_post = json_decode(file_get_contents('php://input'), true);
$valor_pago = str_replace(",", "", $_post['valor']);
// Buscamos si hay registros posteriores a la fecha recibida
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
    `seg_destino_contrato`.`id_adquisicion`
    , `seg_centros_costo`.`id_centro`
    , `seg_destino_contrato`.`horas_mes`
    , `seg_centro_costo_x_sede`.`id_sede`
    , `seg_destino_contrato`.`horas_mes` / 192 as participacion
FROM
    `seg_centro_costo_x_sede`
    INNER JOIN `seg_centros_costo` 
        ON (`seg_centro_costo_x_sede`.`id_centro_c` = `seg_centros_costo`.`id_centro`)
    INNER JOIN `seg_destino_contrato` 
        ON (`seg_destino_contrato`.`id_centro_costo` = `seg_centro_costo_x_sede`.`id_x_sede`)
WHERE (`seg_destino_contrato`.`id_adquisicion` =73);";
    $rs = $cmd->query($sql);
    $centros = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

foreach ($centros as $sed) {
    $valor = $valor_pago  * $sed['participacion'];
    $response[] = array("value" => "ok", "valor" => $valor);
}
echo json_encode($response);
exit;
