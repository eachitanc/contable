<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
$id_b_s = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
$tipo = $_POST['tipo'];
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_bien_servicio`.`id_b_s`, `seg_escala_honorarios`.`val_honorarios`,`seg_escala_honorarios`.`val_hora`, `seg_escala_honorarios`.`vigencia`
            FROM
                `seg_escala_honorarios`
                INNER JOIN `seg_tipo_bien_servicio` 
                    ON (`seg_escala_honorarios`.`id_tipo_b_s` = `seg_tipo_bien_servicio`.`id_tipo_b_s`)
                INNER JOIN `seg_bien_servicio` 
                    ON (`seg_bien_servicio`.`id_tipo_bn_sv` = `seg_tipo_bien_servicio`.`id_tipo_b_s`)
            WHERE `seg_bien_servicio`.`id_b_s` = '$id_b_s' AND `seg_escala_honorarios`.`vigencia` = '$vigencia' LIMIT 1";
    $rs = $cmd->query($sql);
    $honorario = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if (!empty($honorario)) {
    if ($tipo == 'H') {
        echo $honorario['val_hora'];
    } else {
        echo $honorario['val_honorarios'];
    }
} else {
    echo '0';
}
