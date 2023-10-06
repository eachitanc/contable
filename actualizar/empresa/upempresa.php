<?php

session_start();
include '../../conexion.php';
function calcularDV($nit)
{
    if (!is_numeric($nit)) {
        return false;
    }

    $arr = array(
        1 => 3, 4 => 17, 7 => 29, 10 => 43, 13 => 59, 2 => 7, 5 => 19,
        8 => 37, 11 => 47, 14 => 67, 3 => 13, 6 => 23, 9 => 41, 12 => 53, 15 => 71
    );
    $x = 0;
    $y = 0;
    $z = strlen($nit);
    $dv = '';

    for ($i = 0; $i < $z; $i++) {
        $y = substr($nit, $i, 1);
        $x += ($y * $arr[$z - $i]);
    }

    $y = $x % 11;

    if ($y > 1) {
        $dv = 11 - $y;
        return $dv;
    } else {
        $dv = $y;
        return $dv;
    }
}
$idempresa = isset($_POST['idUpEmpresa']) ? $_POST['idUpEmpresa'] : exit('Acción no permitida');
$nit = $_POST['txtNitEmpresa'];
$digver = calcularDV($nit);
$nombre = $_POST['txtUpNomEmpresa'];
$correo = $_POST['mailUpEmpresa'];
$tel = $_POST['txtUpTel'];
$dpto = $_POST['slcDptoEmp'];
$municipio = $_POST['slcMunicipioEmp'];
$direccion = $_POST['txtUpDireccion'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE seg_empresas SET  nit = ?, dig_ver = ?, correo = ?, telefono = ?, nombre = ?, id_dpto = ?, id_ciudad = ?, direccion = ? WHERE id_empresa = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $nit, PDO::PARAM_STR);
    $sql->bindParam(2, $digver, PDO::PARAM_STR);
    $sql->bindParam(3, $correo, PDO::PARAM_STR);
    $sql->bindParam(4, $tel, PDO::PARAM_STR);
    $sql->bindParam(5, $nombre, PDO::PARAM_STR);
    $sql->bindParam(6, $dpto, PDO::PARAM_INT);
    $sql->bindParam(7, $municipio, PDO::PARAM_INT);
    $sql->bindParam(8, $direccion, PDO::PARAM_STR);
    $sql->bindParam(9, $idempresa, PDO::PARAM_INT);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        $updata = 1;
    } else {
        $updata = 0;
    }
    if (!($sql->execute())) {
        print_r($sql->errorInfo()[2]);
        exit();
    }
    if ($updata > 0) {
        echo '1';
    } else {
        print_r($cmd->errorInfo()[2]);
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
