<?php
// Realiza la suma del valor total asignado a un CDP
include '../../../conexion.php';
$_post = json_decode(file_get_contents('php://input'), true);
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
   `seg_tes_cuentas`.`id_tes_cuenta`
   , `seg_tes_cuentas`.`nombre`
   , `seg_tes_cuentas`.`id_banco`
    FROM
   `seg_tes_cuentas`
   INNER JOIN `seg_bancos` 
       ON (`seg_tes_cuentas`.`id_banco` = `seg_bancos`.`id_banco`)
    WHERE (`seg_tes_cuentas`.`id_banco` ={$_post['id']} AND `seg_tes_cuentas`.`estado` = 0)
    ORDER BY `seg_tes_cuentas`.`nombre` ASC;";
    $rs = $cmd->query($sql);
    $retenciones = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$response = '
<select class="form-control form-control-sm py-0 sm" id="cuentas" name="cuentas"  required>
<option value="0">-- Seleccionar --</option>';
foreach ($retenciones as $ret) {
    $response .= '<option value="' . $ret['id_tes_cuenta'] . '">' . $ret['nombre'] .  '</option>';
}
$response .= "</select>";
echo $response;
exit;
