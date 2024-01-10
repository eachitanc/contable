<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$codCgrs = $_POST['codCgr'];
$codCpc = $_POST['cpc'];
$codFuente = $_POST['fuente'];
$codTercero = $_POST['tercero'];
$codPolitica = $_POST['polPub'];
$codSiho = $_POST['siho'];
$codSituacion = $_POST['situacion'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$suma = 0;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO `seg_pto_homologa_ingresos`
                (`id_pto`, `id_cgr`, `id_cpc`, `id_fuente`, `id_tercero`, `id_politica`, `id_siho`, `id_situacion`, `id_user_reg`, `fec_reg`)
            VALUES (?, ?, ?, ? , ?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_ppto, PDO::PARAM_INT);
    $sql->bindParam(2, $cgr, PDO::PARAM_STR);
    $sql->bindParam(3, $cpc, PDO::PARAM_STR);
    $sql->bindParam(4, $fte, PDO::PARAM_STR);
    $sql->bindParam(5, $tercer, PDO::PARAM_STR);
    $sql->bindParam(6, $polit, PDO::PARAM_STR);
    $sql->bindParam(7, $siho, PDO::PARAM_STR);
    $sql->bindParam(8, $situa, PDO::PARAM_STR);
    $sql->bindParam(9, $iduser, PDO::PARAM_INT);
    $sql->bindValue(10, $date->format('Y-m-d H:i:s'));
    foreach ($codCgrs as $key => $value) {
        $id_ppto = $key;
        $cgr = $value;
        $cpc = $codCpc[$key];
        $fte = $codFuente[$key];
        $tercer = $codTercero[$key];
        $polit = $codPolitica[$key];
        $siho = $codSiho[$key];
        $situa = $codSituacion[$key];
        $sql->execute();
        if ($sql->rowCount() > 0) {
            $suma++;
        } else {
            echo $sql->errorInfo()[2];
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if ($suma > 0) {
    echo 'ok';
} else {
    echo 'No se realizó ninguna modificación';
}
