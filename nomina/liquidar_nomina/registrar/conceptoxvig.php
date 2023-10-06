<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
$key = array_search('1', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
$vigencia = $_SESSION['vigencia'];
$concepto = $_POST['concepto'];
$valor = $_POST['valor'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_valxvigencia`.`id_valxvig`
            FROM
                `seg_valxvigencia`
                INNER JOIN `seg_conceptosxvigencia` 
                    ON (`seg_valxvigencia`.`id_concepto` = `seg_conceptosxvigencia`.`id_concp`)
                INNER JOIN `con_vigencias` 
                    ON (`seg_valxvigencia`.`id_vigencia` = `con_vigencias`.`id_vigencia`)
            WHERE (`con_vigencias`.`anio` = '$vigencia' AND `seg_valxvigencia`.`id_concepto` = '$concepto')";
    $rs = $cmd->query($sql);
    $resultado = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_vigencia`, `anio`
            FROM
                `con_vigencias`
            WHERE (`anio` = '$vigencia') LIMIT 1";
    $rs = $cmd->query($sql);
    $idvig = $rs->fetch(PDO::FETCH_ASSOC);
    $id_vigencia = $idvig['id_vigencia'];
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if (empty($resultado)) {
    $date = new DateTime('now', new DateTimeZone('America/Bogota'));
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "INSERT INTO `seg_valxvigencia` (`id_vigencia`, `id_concepto`, `valor`, `fec_reg`)
                VALUES (?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_vigencia, PDO::PARAM_INT);
        $sql->bindParam(2, $concepto, PDO::PARAM_INT);
        $sql->bindParam(3, $valor, PDO::PARAM_STR);
        $sql->bindValue(4, $date->format('Y-m-d H:i:s'));
        $sql->execute();
        if ($cmd->lastInsertId() > 0) {
            echo 'ok';
        } else {
            echo $sql->errorInfo()[2];
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
} else {
    echo 'Concepto ya registrado para la vigencia ' . $vigencia;
    exit();
}
