<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$nomCod = $_POST['nomCod'];
$id_empresa = '2';
$id_sede = '1';
$tipoDato = $_POST['tipoDato'];
$vigencia = $_SESSION['vigencia'];
if ($tipoDato === '0') $nomRubro = $nomRubro = strtoupper($_POST['nomRubro']);
else $nomRubro = $_POST['nomRubro'];
if (isset($_POST['valorAprob'])) $valorAprob = $_POST['valorAprob'];
else $valorAprob = 0;
// quitar separador de mailes
$valorAprob = str_replace(',', '', $valorAprob);
if (isset($_POST['tipoRecurso'])) $tipoRecurso = $_POST['tipoRecurso'];
else $tipoRecurso = '';
$tipoPto = $_POST['tipoPresupuesto'];
if (isset($_POST['situaFondos'])) $situaFondos = $_POST['situaFondos'];
else $situaFondos = '0';
$id_pto = $_POST['id_pto'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO `seg_pto_cargue` (`id_pto_presupuestos`,`vigencia`, `id_sede`, `cod_pptal`, `nom_rubro`, `tipo_dato`, `ppto_aprob`, `id_tipo_recurso`, `situacion_fondos`, `tipo_gasto`,id_user_reg, fec_reg) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_pto, PDO::PARAM_INT);
    $sql->bindParam(2, $vigencia, PDO::PARAM_INT);
    $sql->bindParam(3, $id_sede, PDO::PARAM_INT);
    $sql->bindParam(4, $nomCod, PDO::PARAM_STR);
    $sql->bindParam(5, $nomRubro, PDO::PARAM_STR);
    $sql->bindParam(6, $tipoDato, PDO::PARAM_STR);
    $sql->bindParam(7, $valorAprob, PDO::PARAM_INT);
    $sql->bindParam(8, $tipoRecurso, PDO::PARAM_INT);
    $sql->bindParam(9, $situaFondos, PDO::PARAM_INT);
    $sql->bindParam(10, $tipoPto, PDO::PARAM_INT);
    $sql->bindParam(11, $iduser, PDO::PARAM_INT);
    $sql->bindValue(12, $date->format('Y-m-d H:i:s'));
    $sql->execute();
    if ($cmd->lastInsertId() > 0) {
        echo '1';
    } else {
        print_r($sql->errorInfo()[2]);
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
