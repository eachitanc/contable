<?php

session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';

$idempleado = $_POST['ie'];
$tipoembar = $_POST['te'];
$up = $_POST['up'];
$vigencia = $_SESSION['vigencia'];
$res = "";

function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT *
            FROM
                seg_valxvigencia
            INNER JOIN seg_conceptosxvigencia 
                ON (seg_valxvigencia.id_concepto = seg_conceptosxvigencia.id_concp)
            INNER JOIN con_vigencias 
                ON (seg_valxvigencia.id_vigencia = con_vigencias.id_vigencia)
            WHERE anio = '$vigencia' AND id_concp = '1'";
    $rs = $cmd->query($sql);
    $valxvig = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                * 
            FROM seg_tipo_embargo
            WHERE id_tipo_emb = '$tipoembar'";
    $rs = $cmd->query($sql);
    $tipemb = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT  `id_empleado`, `vigencia`, `salario_basico`
            FROM
                `seg_salarios_basico`
            WHERE `id_salario` 
                IN( SELECT MAX(`id_salario`) 
                    FROM  `seg_salarios_basico`
                    WHERE `vigencia` <= '$vigencia' AND  `id_empleado` = '$idempleado')";
    $rs = $cmd->query($sql);
    $salemp = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if ($tipoembar === '1') {
    $valmax = $tipemb['porcentaje'] * ($salemp['salario_basico'] - $valxvig['valor']);
} else {
    $valmax = $tipemb['porcentaje'] * $salemp['salario_basico'];
}
if ($up !== '') {
    $res .= pesos($valmax) . '<input type="number" id="num' . $up . 'DctoAprox" name="num' . $up . 'DctoAprox" value="' . $valmax . '" hidden>'
        . '        <input type="number" name="num' . $up . 'TipoEmbargo" value="' . $tipoembar . '" hidden>';
} else {
    $res .= pesos($valmax) . '<input type="number" id="numDctoAprox" name="numDctoAprox" value="' . $valmax . '" hidden>'
        . '        <input type="number" name="numTipoEmbargo" value="' . $tipoembar . '" hidden>';
}
echo $res;
