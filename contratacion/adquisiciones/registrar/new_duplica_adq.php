<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$id_compra = isset($_POST['id_compra']) ? $_POST['id_compra'] : exit('Acción no permitida');
$modalidad = $_POST['slcModalidad'];
$id_empresa = '2';
$id_sede = '1';
$fec_adq = $_POST['datFecAdq'];
$val_cont = $_POST['numTotalContrato'];
$vig = $_POST['datFecVigencia'];
$area = $_POST['slcAreaSolicita'];
$tbnsv = $_POST['slcTipoBnSv'];
$obligaciones = '';
$objeto = mb_strtoupper($_POST['txtObjeto']);
$estado = '6';
$fec_ini =  date('Y-m-d', strtotime($_POST['datFecIniEjec']));
$fec_fin = date('Y-m-d', strtotime($_POST['datFecFinEjec']));
$val_contrato = $_POST['numValContrata'];
$forma_pago = $_POST['slcFormPago'];
$supervisor = $_POST['slcSupervisor'] == 'A' ? NULL : $_POST['slcSupervisor'];
$DescNec = $_POST['txtDescNec'];
$ActEspecificas = $_POST['txtActEspecificas'];
$ProdEntrega = $_POST['txtProdEntrega'];
$ObligContratista = $_POST['txtObligContratista'];
$FormPago = $_POST['txtFormPago'];
$numDS = $_POST['numDS'];
$requisitos = $_POST['txtReqMinHab'];
$garantia = $_POST['txtGarantias'];
$describe_valor = $_POST['txtDescValor'];
$centros = $_POST['slcCentroCosto'];
$cantidades = $_POST['numHorasMes'];
$tercero_api = $_POST['id_tercero'];

$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_bn_sv`
                , `cantidad`
                , `val_estimado_unid`
                , `id_adquisicion`
            FROM
                `seg_detalle_adquisicion`
            WHERE (`id_adquisicion` = $id_compra)";
    $rs = $cmd->query($sql);
    $detalles = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_tercero` FROM `seg_terceros` WHERE `id_tercero_api` = $tercero_api";
    $rs = $cmd->query($sql);
    $tercero = $rs->fetch()['id_tercero'];
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$cont = 0;
$cant = 0;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO `seg_adquisiciones` (`id_modalidad`, `id_empresa`, `id_sede`, `id_area`, `fecha_adquisicion`, `val_contrato`, `vigencia`, `id_tipo_bn_sv`, `obligaciones`, `objeto`, `estado`, `id_user_reg`, `fec_reg`, `id_tercero`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $modalidad, PDO::PARAM_INT);
    $sql->bindParam(2, $id_empresa, PDO::PARAM_INT);
    $sql->bindParam(3, $id_sede, PDO::PARAM_INT);
    $sql->bindParam(4, $area, PDO::PARAM_INT);
    $sql->bindParam(5, $fec_adq, PDO::PARAM_STR);
    $sql->bindParam(6, $val_cont, PDO::PARAM_STR);
    $sql->bindParam(7, $vig, PDO::PARAM_STR);
    $sql->bindParam(8, $tbnsv, PDO::PARAM_INT);
    $sql->bindParam(9, $obligaciones, PDO::PARAM_STR);
    $sql->bindParam(10, $objeto, PDO::PARAM_STR);
    $sql->bindParam(11, $estado, PDO::PARAM_STR);
    $sql->bindParam(12, $iduser, PDO::PARAM_INT);
    $sql->bindValue(13, $date->format('Y-m-d H:i:s'));
    $sql->bindParam(14, $tercero, PDO::PARAM_INT);
    $sql->execute();
    if ($cmd->lastInsertId() > 0) {
        $id_adquisicion = $cmd->lastInsertId();
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "INSERT INTO `seg_detalle_adquisicion` (`id_adquisicion`, `id_bn_sv`, `cantidad`, `val_estimado_unid`, `id_user_reg`, `fec_reg`) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id_adquisicion, PDO::PARAM_INT);
            $sql->bindParam(2, $idBS, PDO::PARAM_INT);
            $sql->bindParam(3, $cantidad, PDO::PARAM_INT);
            $sql->bindParam(4, $valEs, PDO::PARAM_STR);
            $sql->bindParam(5, $iduser, PDO::PARAM_INT);
            $sql->bindValue(6, $date->format('Y-m-d H:i:s'));
            foreach ($detalles as $dt) {
                $idBS = $dt['id_bn_sv'];
                $cantidad = $dt['cantidad'];
                $valEs = $dt['val_estimado_unid'];
                $sql->execute();
                if ($cmd->lastInsertId() > 0) {
                    $cont++;
                } else {
                    echo $sql->errorInfo()[2];
                }
            }
            if ($cont > 0) {
                $sql = "INSERT INTO `seg_destino_contrato`
                            (`id_adquisicion`, `id_centro_costo`, `horas_mes`, `id_user_reg`, `fec_reg`)
                        VALUES (?, ?, ?, ?, ?)";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $id_adquisicion, PDO::PARAM_INT);
                $sql->bindParam(2, $id_cc, PDO::PARAM_INT);
                $sql->bindParam(3, $numhoras, PDO::PARAM_INT);
                $sql->bindParam(4, $id_user, PDO::PARAM_INT);
                $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
                foreach ($centros as $key => $value) {
                    $id_cc = $value;
                    $numhoras = $cantidades[$key];
                    $sql->execute();
                    if (!($cmd->lastInsertId() > 0)) {
                        echo $cmd->errorInfo()[2];
                    }
                }
                try {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                    $sql = "INSERT INTO `seg_estudios_previos`(`id_compra`,`fec_ini_ejec`,`fec_fin_ejec`, `val_contrata`,`id_forma_pago`,`id_supervisor`,`necesidad`,`act_especificas`,`prod_entrega`,`obligaciones`,`forma_pago`, `num_ds`,`requisitos`,`garantia`, `describe_valor`,`id_user_reg`,`fec_reg`) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $sql = $cmd->prepare($sql);
                    $sql->bindParam(1, $id_adquisicion, PDO::PARAM_INT);
                    $sql->bindParam(2, $fec_ini, PDO::PARAM_STR);
                    $sql->bindParam(3, $fec_fin, PDO::PARAM_STR);
                    $sql->bindParam(4, $val_contrato, PDO::PARAM_STR);
                    $sql->bindParam(5, $forma_pago, PDO::PARAM_INT);
                    $sql->bindParam(6, $supervisor, PDO::PARAM_INT);
                    $sql->bindParam(7, $DescNec, PDO::PARAM_STR);
                    $sql->bindParam(8, $ActEspecificas, PDO::PARAM_STR);
                    $sql->bindParam(9, $ProdEntrega, PDO::PARAM_STR);
                    $sql->bindParam(10, $ObligContratista, PDO::PARAM_STR);
                    $sql->bindParam(11, $FormPago, PDO::PARAM_STR);
                    $sql->bindParam(12, $numDS, PDO::PARAM_STR);
                    $sql->bindParam(13, $requisitos, PDO::PARAM_STR);
                    $sql->bindParam(14, $garantia, PDO::PARAM_STR);
                    $sql->bindParam(15, $describe_valor, PDO::PARAM_STR);
                    $sql->bindParam(16, $iduser, PDO::PARAM_INT);
                    $sql->bindValue(17, $date->format('Y-m-d H:i:s'));
                    $sql->execute();
                    $id_estudio = $cmd->lastInsertId();
                    if ($id_estudio > 0) {
                        $polizas = isset($_POST['check']) ? $_POST['check'] : '';
                        if ($polizas == '') {
                            $cant = 1;
                        } else {
                            try {
                                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                                $sql = "INSERT INTO `seg_garantias_compra`(`id_est_prev`,`id_poliza`,`id_user_reg`,`fec_reg`) VALUES (?, ?, ?, ?)";
                                $sql = $cmd->prepare($sql);
                                $sql->bindParam(1, $id_estudio, PDO::PARAM_INT);
                                $sql->bindParam(2, $id_pol, PDO::PARAM_INT);
                                $sql->bindParam(3, $iduser, PDO::PARAM_INT);
                                $sql->bindValue(4, $date->format('Y-m-d H:i:s'));
                                foreach ($polizas as $p) {
                                    $id_pol = $p;
                                    $sql->execute();
                                    if ($cmd->lastInsertId() > 0) {
                                        $cant++;
                                    } else {
                                        echo $sql->errorInfo()[2];
                                    }
                                }
                                $cmd = null;
                            } catch (PDOException $e) {
                                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                            }
                        }
                    } else {
                        echo $sql->errorInfo()[2];
                    }
                    $cmd = null;
                } catch (PDOException $e) {
                    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                }
            }
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
    } else {
        echo $sql->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if ($cant > 0) {
    echo 'ok';
} else {
    echo 'Ha ocurrido un error';
}
