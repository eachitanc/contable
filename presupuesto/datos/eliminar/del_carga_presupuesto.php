<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';

$idpto = $_SESSION['del']; // id del rubro
$pto = $_POST['pto']; // id del presupuesto

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    // Valido que el rubro no tenga cuentas asociadas
    $sql = "SELECT cod_pptal FROM `seg_pto_cargue` WHERE `id_pto_cargue` = '$idpto'";
    $rs = $cmd->query($sql);
    $codigo = $rs->fetch();
    // consulta codigo asociado
    $sql = "SELECT cod_pptal FROM `seg_pto_cargue` WHERE `cod_pptal` LIKE '$codigo[cod_pptal]%' AND `id_pto_presupuestos` = '$pto'";
    $rs = $cmd->query($sql);
    $fil = $rs->rowCount();
    // consulto que el rubro no tenga registros en la tabla seg_pto_mvto
    $sql = "SELECT id_pto_mvto FROM `seg_pto_mvto` WHERE `rubro` = '$codigo[cod_pptal]'";
    $rs = $cmd->query($sql);
    $fil2 = $rs->rowCount();
    if ($fil > 1) {
        echo 'No se puede eliminar el registro, tiene cuentas asociadas';
    } elseif ($fil2 > 1) {
        echo 'El rubro ya fue utilizado en movimientos presupuestales';
    } else {
        $sql = "DELETE FROM seg_pto_cargue  WHERE id_pto_cargue = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $idpto, PDO::PARAM_INT);
        $sql->execute();
        if ($sql->rowCount() > 0) {
            echo '1';
        } else {
            print_r($sql->errorInfo()[2]);
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
