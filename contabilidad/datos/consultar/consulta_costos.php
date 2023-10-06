<?php
// Realiza la suma del valor total asignado a un CDP
include '../../../conexion.php';
$_post = json_decode(file_get_contents('php://input'), true);
// Buscamos si hay registros posteriores a la fecha recibida
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
    `seg_centro_costo_x_sede`.`id_sede`
    ,`seg_centros_costo`.`descripcion`
    ,`seg_centros_costo`.`id_centro`
    FROM
    `seg_centro_costo_x_sede`
    INNER JOIN `seg_centros_costo` 
        ON (`seg_centro_costo_x_sede`.`id_centro_c` = `seg_centros_costo`.`id_centro`)
    WHERE (`seg_centro_costo_x_sede`.`id_sede` =$_post[id])";
    $rs = $cmd->query($sql);
    $centros = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$response = '
<select class="form-control form-control-sm py-0 sm" id="id_cc" name="id_cc" >
<option value="">-- Seleccionar --</option>';
foreach ($centros as $sed) {
    $response .= '<option value="' . $sed['id_centro'] . '">' . $sed['descripcion'] .  '</option>';
}
$response .= "</select>";
echo $response;
exit;
