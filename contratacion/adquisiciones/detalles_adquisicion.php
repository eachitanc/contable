<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';
include '../../permisos.php';
$key = array_search('3', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
$id_adq = isset($_POST['detalles']) ? $_POST['detalles'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_adquisiciones`.`id_adquisicion`
                , `seg_tipo_compra`.`id_tipo`
                , `seg_tipo_compra`.`tipo_compra`
            FROM
                `seg_tipo_contrata`
                INNER JOIN `seg_tipo_compra` 
                    ON (`seg_tipo_contrata`.`id_tipo_compra` = `seg_tipo_compra`.`id_tipo`)
                INNER JOIN `seg_tipo_bien_servicio` 
                    ON (`seg_tipo_bien_servicio`.`id_tipo_cotrato` = `seg_tipo_contrata`.`id_tipo`)
                INNER JOIN `seg_adquisiciones` 
                    ON (`seg_adquisiciones`.`id_tipo_bn_sv` = `seg_tipo_bien_servicio`.`id_tipo_b_s`)
            WHERE `seg_adquisiciones`.`id_adquisicion` = '$id_adq'";
    $rs = $cmd->query($sql);
    $tipo_adq = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_sede`, `nombre` FROM `seg_sedes_empresa`";
    $rs = $cmd->query($sql);
    $sedes = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_centro_costo_x_sede`.`id_x_sede`,`seg_centro_costo_x_sede`.`id_sede`, `seg_centros_costo`.`descripcion`
            FROM
                `seg_centro_costo_x_sede`
                INNER JOIN `seg_centros_costo` 
                    ON (`seg_centro_costo_x_sede`.`id_centro_c` = `seg_centros_costo`.`id_centro`) 
            ORDER BY `seg_centros_costo`.`descripcion` ASC";
    $rs = $cmd->query($sql);
    $centros_costo = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id`, `descripcion` FROM `seg_estado_adq`";
    $rs = $cmd->query($sql);
    $estado_adq = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_destino_contrato`.`id_destino`, `seg_centro_costo_x_sede`.`id_sede`, `seg_destino_contrato`.`id_centro_costo`, `seg_destino_contrato`.`horas_mes`
            FROM
                `seg_destino_contrato`
                INNER JOIN `seg_centro_costo_x_sede` 
                    ON (`seg_destino_contrato`.`id_centro_costo` = `seg_centro_costo_x_sede`.`id_x_sede`)
            WHERE `seg_destino_contrato`.`id_adquisicion` = '$id_adq' ORDER BY `seg_destino_contrato`.`id_destino` ASC";
    $rs = $cmd->query($sql);
    $destinos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                *
            FROM
                seg_adquisiciones
            INNER JOIN seg_modalidad_contrata 
                ON (seg_adquisiciones.id_modalidad = seg_modalidad_contrata.id_modalidad) 
            WHERE id_adquisicion = '$id_adq'";
    $rs = $cmd->query($sql);
    $adquisicion = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_adquisiciones`.`id_adquisicion`
                , `seg_tipo_contrata`.`id_tipo_compra`
            FROM
                `seg_adquisiciones`
                INNER JOIN `seg_tipo_bien_servicio` 
                    ON (`seg_adquisiciones`.`id_tipo_bn_sv` = `seg_tipo_bien_servicio`.`id_tipo_b_s`)
                INNER JOIN `seg_tipo_contrata` 
                    ON (`seg_tipo_bien_servicio`.`id_tipo_cotrato` = `seg_tipo_contrata`.`id_tipo`)
            WHERE  `seg_adquisiciones`.`id_adquisicion` = '$id_adq'";
    $rs = $cmd->query($sql);
    $tipo_compra = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_terceros`.`no_doc`
                , `seg_terceros`.`id_tercero_api`
                , `seg_adquisiciones`.`id_adquisicion`
                , `seg_adquisiciones`.`estado`
            FROM
                `seg_adquisiciones`
            INNER JOIN `seg_terceros` 
                ON (`seg_adquisiciones`.`id_tercero` = `seg_terceros`.`id_tercero`)
            WHERE  `seg_adquisiciones`.`id_adquisicion` =  '$id_adq' AND `seg_adquisiciones`.`estado` >= '5' LIMIT 1";
    $rs = $cmd->query($sql);
    $seleccionada = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_est_prev` , `id_compra`, `fec_ini_ejec`, `fec_fin_ejec`, `id_forma_pago`, `id_supervisor`, `id_user_reg`
            FROM
                `seg_estudios_previos`
            WHERE `id_compra` = '$id_adq' LIMIT 1";
    $rs = $cmd->query($sql);
    $estudios = $rs->fetch();

    $id_estudio = !empty($estudios['id_est_prev']) ? $estudios['id_est_prev'] : '';
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_adquisiciones`.`id_adquisicion`
                , `seg_pto_documento`.`id_manu`
                , `seg_pto_documento`.`objeto`
                , `seg_pto_documento`.`fecha`
                , `seg_pto_mvto`.`valor`
            FROM
                `seg_pto_documento`
                INNER JOIN `seg_adquisiciones` 
                    ON (`seg_pto_documento`.`id_pto_doc` = `seg_adquisiciones`.`id_cdp`)
                INNER JOIN `seg_pto_mvto`
                ON (`seg_pto_mvto`.`id_pto_doc` = `seg_pto_documento`.`id_pto_doc`)
            WHERE `seg_adquisiciones`.`id_adquisicion` = '$id_adq' LIMIT 1";
    $rs = $cmd->query($sql);
    $cdp = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_pto_documento`.`id_manu`
                , `seg_pto_documento`.`fecha`
                , `seg_pto_documento`.`objeto`
                , `seg_pto_documento`.`fecha`
                , `seg_pto_mvto`.`valor`
            FROM
                `seg_adquisiciones`
                INNER JOIN `seg_pto_documento` 
                    ON (`seg_adquisiciones`.`id_cdp` = `seg_pto_documento`.`id_auto`)
                INNER JOIN `seg_pto_mvto`
                ON (`seg_pto_mvto`.`id_pto_doc` = `seg_pto_documento`.`id_pto_doc`)
            WHERE `seg_adquisiciones`.`id_adquisicion` = '$id_adq' AND `seg_pto_documento`.`tipo_doc` ='CRP' LIMIT 1";
    $rs = $cmd->query($sql);
    $crp = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_contrato_compra`
                , `id_compra`
                , `fec_ini`
                , `fec_fin`
                , `val_contrato`
                , `id_forma_pago`
                , `id_supervisor`
                , `id_secop`
                , `num_contrato`
            FROM
                `seg_contrato_compra`
            WHERE (`id_compra` = $id_adq) LIMIT 1";
    $rs = $cmd->query($sql);
    $contrato = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
function pesos($valor)
{
    if ($valor >= 0) {
        return '$' . number_format($valor, 2, ",", ".");
    } else {
        return '-$' . number_format($valor * (-1), 2);
    }
}
if (!empty($adquisicion)) {
    $idtbnsv = $adquisicion['id_tipo_bn_sv'];
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "SELECT 
                    `id_b_s`, `tipo_compra`,`seg_tipo_contrata`.`id_tipo`, `tipo_contrato`, `tipo_bn_sv`, `bien_servicio`
                FROM
                    `seg_tipo_contrata`
                INNER JOIN `seg_tipo_compra` 
                    ON (`seg_tipo_contrata`.`id_tipo_compra` = `seg_tipo_compra`.`id_tipo`)
                INNER JOIN seg_tipo_bien_servicio 
                    ON (`seg_tipo_bien_servicio`.`id_tipo_cotrato` = `seg_tipo_contrata`.`id_tipo`)
                INNER JOIN seg_bien_servicio 
                    ON (`seg_bien_servicio`.`id_tipo_bn_sv` = `seg_tipo_bien_servicio`.`id_tipo_b_s`)
                WHERE `id_tipo_b_s` = '$idtbnsv'
                ORDER BY `tipo_compra`,`tipo_contrato`, `tipo_bn_sv`, `bien_servicio`";
        $rs = $cmd->query($sql);
        $bnsv = $rs->fetchAll();
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    $j = 0;
?>
    <!DOCTYPE html>
    <html lang="es">
    <?php include '../../head.php' ?>

    <body class="sb-nav-fixed <?php if ($_SESSION['navarlat'] == '1') {
                                    echo 'sb-sidenav-toggled';
                                } ?>">
        <?php include '../../navsuperior.php' ?>
        <div id="layoutSidenav">
            <?php include '../../navlateral.php' ?>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid p-2">
                        <div class="card mb-4">
                            <div class="card-header" id="divTituloPag">
                                <div class="row">
                                    <div class="col-md-11">
                                        <i class="fas fa-copy fa-lg" style="color:#1D80F7"></i>
                                        DETALLES DE ADQUISICIÓN
                                    </div>
                                </div>
                            </div>
                            <div class="card-body" id="divCuerpoPag">
                                <div id="accordion">
                                    <div class="card">
                                        <div class="card-header card-header-detalles py-0 headings" id="headingOne">
                                            <h5 class="mb-0">
                                                <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#datosperson" aria-expanded="true" aria-controls="collapseOne">
                                                    <div class="form-row">
                                                        <div class="div-icono">
                                                            <span class="fas fa-clipboard-list fa-lg" style="color: #3498DB;"></span>
                                                        </div>
                                                        <div>
                                                            <?php $j++;
                                                            echo $j ?>. DETALLES DE CONTRATACIÓN
                                                        </div>
                                                    </div>
                                                </a>
                                            </h5>
                                        </div>
                                        <div id="datosperson" class="collapse show" aria-labelledby="headingOne">
                                            <div class="card-body">
                                                <div class="shadow detalles-empleado">
                                                    <div class="row">
                                                        <div class="div-mostrar bor-top-left col-md-4">
                                                            <label class="lbl-mostrar pb-2">MODALIDAD CONTRATACIÓN</label>
                                                            <div class="div-cont pb-2"><?php echo $adquisicion['modalidad'] ?></div>
                                                        </div>
                                                        <div class="div-mostrar col-md-2">
                                                            <label class="lbl-mostrar pb-2">ADQUISICIÓN</label>
                                                            <input type="hidden" id="id_compra" value="<?php echo $id_adq ?>">
                                                            <input type="hidden" id="id_contrato_compra" value="<?php echo isset($contrato['id_contrato_compra']) ? $contrato['id_contrato_compra'] : '' ?>">
                                                            <div class="div-cont pb-2">ADQ-<?php echo mb_strtoupper($adquisicion['id_adquisicion']) ?></div>
                                                        </div>
                                                        <div class="div-mostrar col-md-3">
                                                            <label class="lbl-mostrar pb-2">FECHA</label>
                                                            <div class="div-cont pb-2"><?php echo $adquisicion['fecha_adquisicion'] ?></div>
                                                        </div>
                                                        <div class="div-mostrar bor-top-right col-md-3">
                                                            <label class="lbl-mostrar pb-2">ESTADO</label>
                                                            <?php
                                                            $estad = $adquisicion['estado'];
                                                            $key = array_search($estad, array_column($estado_adq, 'id'));
                                                            ?>
                                                            <div class="div-cont pb-2"><?php echo $estado_adq[$key]['descripcion'] ?></div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="div-mostrar bor-down-right bor-down-left col-md-12">
                                                            <label class="lbl-mostrar pb-2">OBJETO</label>
                                                            <div class="div-cont text-left pb-2"><?php echo $adquisicion['objeto'] ?></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--parte-->
                                    <div class="card">
                                        <div class="card-header card-header-detalles py-0 headings" id="headingBnSv">
                                            <h5 class="mb-0">
                                                <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseBnSv" aria-expanded="true" aria-controls="collapseBnSv">
                                                    <div class="form-row">
                                                        <div class="div-icono">
                                                            <span class="fas fa-swatchbook fa-lg" style="color: #EC7063;"></span>
                                                        </div>
                                                        <div>
                                                            <?php $j++;
                                                            echo $j ?>. ORDEN DE BIEN O SERVICIOS
                                                        </div>
                                                    </div>
                                                </a>
                                            </h5>
                                        </div>
                                        <div id="collapseBnSv" class="collapse" aria-labelledby="headingBnSv">
                                            <div class="card-body">
                                                <?php
                                                $tipo_contrato = '0';
                                                foreach ($bnsv as $bs) {
                                                    if ($bs['id_tipo'] == '10') {
                                                        $tipo_contrato = '1';
                                                    }
                                                }
                                                ?>
                                                <div id="divEstadoBnSv">
                                                    <?php
                                                    if ($adquisicion['estado'] == 1) {
                                                    ?>
                                                        <form id="formDetallesAdq">
                                                            <input type="hidden" name="idAdq" value="<?php echo $id_adq ?>">
                                                            <table id="tableAdqBnSv" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Seleccionar</th>
                                                                        <?php echo $tipo_contrato == '1' ? '<th>Pago</th>' : '' ?>
                                                                        <th>Bien o Servicio</th>
                                                                        <th>Cantidad</th>
                                                                        <th>Valor Unitario</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php
                                                                    foreach ($bnsv as $bs) {
                                                                    ?>
                                                                        <tr>
                                                                            <td>
                                                                                <div class="text-center listado">
                                                                                    <input type="checkbox" name="check[]" value="<?php echo $bs['id_b_s'] ?>">
                                                                                </div>
                                                                            </td>
                                                                            <?php if ($tipo_contrato == '1') { ?>
                                                                                <td>
                                                                                    <select class="form-control form-control-sm altura py-0" id="tipo_<?php echo $bs['id_b_s'] ?>">
                                                                                        <option value="H">Horas</option>
                                                                                        <option value="M">Mensual</option>
                                                                                    </select>
                                                                                </td>
                                                                            <?php } ?>
                                                                            <td class="text-left"><i><?php echo $bs['bien_servicio'] ?></i></td>
                                                                            <td><input type="number" name="bnsv_<?php echo $bs['id_b_s'] ?>" id="bnsv_<?php echo $bs['id_b_s'] ?>" class="form-control altura cantidad"></td>
                                                                            <td><input type="number" name="val_bnsv_<?php echo $bs['id_b_s'] ?>" id="val_bnsv_<?php echo $bs['id_b_s'] ?>" class="form-control altura" value="0"></td>
                                                                        </tr>
                                                                    <?php
                                                                    }
                                                                    ?>
                                                                </tbody>
                                                                <tfoot>
                                                                    <tr>
                                                                        <th>Seleccionar</th>
                                                                        <?php echo $tipo_contrato == '1' ? '<th>Pago</th>' : '' ?>
                                                                        <th>Bien o Servicio</th>
                                                                        <th>Cantidad</th>
                                                                        <th>Valor Unitario</th>
                                                                    </tr>
                                                                </tfoot>
                                                            </table>
                                                        </form>
                                                    <?php
                                                    } else {
                                                        echo '<div class="p-3 mb-2 bg-success text-white">ORDEN AGREGADA CORRECTAMENTE</div>';
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" id="tipo_contrato" value="<?php echo $tipo_contrato ?>">
                                    <?php
                                    if ($tipo_contrato == '1' && $adquisicion['estado'] >= 1) { ?>
                                        <!--parte-->
                                        <div class="card">
                                            <div class="card-header card-header-detalles py-0 headings" id="headingDestContrato">
                                                <h5 class="mb-0">
                                                    <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseDestContrato" aria-expanded="true" aria-controls="collapseDestContrato">
                                                        <div class="form-row">
                                                            <div class="div-icono">
                                                                <span class="fas fa-people-arrows fa-lg" style="color: #1ABC9C;"></span>
                                                            </div>
                                                            <div>
                                                                <?php $j++;
                                                                echo $j ?>. DESTINACIÓN DEL CONTRATO
                                                            </div>
                                                        </div>
                                                    </a>
                                                </h5>
                                            </div>
                                            <div id="collapseDestContrato" class="collapse" aria-labelledby="headingDestContrato">
                                                <?php
                                                $accion = empty($destinos) ? 'Guardar' : 'Actualizar';
                                                $value = empty($destinos) ? '0' : '1';
                                                ?>
                                                <div class="card-body">
                                                    <form id="formDestContra">
                                                        <fieldset class="border p-2 bg-light">
                                                            <div id="contenedor">
                                                                <?php
                                                                if ($value == '0') {
                                                                ?>
                                                                    <div class="form-row px-4 pt-2">
                                                                        <div class="form-group col-md-4 mb-2">
                                                                            <label class="small">SEDE</label>
                                                                            <select name="slcSedeAC[]" class="form-control form-control-sm slcSedeAC">
                                                                                <option value="0">--Seleccione--</option>
                                                                                <?php
                                                                                foreach ($sedes as $s) {
                                                                                    echo '<option value="' . $s['id_sede'] . '">' . $s['nombre'] . '</option>';
                                                                                }
                                                                                ?>
                                                                            </select>
                                                                        </div>
                                                                        <div class="form-group col-md-4 mb-2">
                                                                            <label class="small">CENTRO DE COSTO</label>
                                                                            <select name="slcCentroCosto[]" class="form-control form-control-sm slcCentroCosto">
                                                                                <option value="0">--Seleccionar Sede--</option>
                                                                            </select>
                                                                        </div>
                                                                        <div class="form-group col-md-4 mb-2">
                                                                            <label for="numHorasMes" class="small">Horas asignadas / mes</label>
                                                                            <div class="input-group input-group-sm">
                                                                                <input type="number" name="numHorasMes[]" class="form-control">
                                                                                <div class="input-group-append">
                                                                                    <button class="btn btn-outline-success" type="button" id="addRowSedes"><i class="fas fa-plus"></i></button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <?php
                                                                } else {
                                                                    $control = 0;
                                                                    $disabled = $adquisicion['estado'] <= 5 ? '' : 'disabled';
                                                                    foreach ($destinos as $d) {
                                                                    ?>
                                                                        <div class="form-row px-4 pt-2">
                                                                            <div class="form-group col-md-4 mb-2">
                                                                                <?php echo $control == 0 ? '<label class="small">SEDE</label>' : '' ?>
                                                                                <select name="slcSedeAC[]" class="form-control form-control-sm slcSedeAC" <?php echo $disabled ?>>
                                                                                    <?php
                                                                                    foreach ($sedes as $s) {
                                                                                        if ($s['id_sede'] == $d['id_sede']) {
                                                                                            echo '<option value="' . $s['id_sede'] . '" selected>' . $s['nombre'] . '</option>';
                                                                                        } else {
                                                                                            echo '<option value="' . $s['id_sede'] . '">' . $s['nombre'] . '</option>';
                                                                                        }
                                                                                    }
                                                                                    ?>
                                                                                </select>
                                                                            </div>
                                                                            <div class="form-group col-md-4 mb-2">
                                                                                <?php echo $control == 0 ? '<label class="small">CENTRO DE COSTO</label>' : '' ?>
                                                                                <select name="slcCentroCosto[]" class="form-control form-control-sm slcCentroCosto" <?php echo $disabled ?>>
                                                                                    <?php
                                                                                    foreach ($centros_costo as $cc) {
                                                                                        if ($cc['id_sede'] == $d['id_sede']) {
                                                                                            if ($cc['id_x_sede'] == $d['id_centro_costo']) {
                                                                                                echo '<option value="' . $cc['id_x_sede'] . '" selected>' . $cc['descripcion'] . '</option>';
                                                                                            } else {
                                                                                                echo '<option value="' . $cc['id_x_sede'] . '">' . $cc['descripcion'] . '</option>';
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                    ?>
                                                                                </select>
                                                                            </div>
                                                                            <div class="form-group col-md-4 mb-2">
                                                                                <?php echo $control == 0 ? '<label for="numHorasMes" class="small">Horas asignadas / mes</label>' : '' ?>
                                                                                <div class="input-group input-group-sm">
                                                                                    <input type="number" name="numHorasMes[]" class="form-control" value="<?php echo $d['horas_mes'] ?>" <?php echo $disabled ?>>
                                                                                    <div class="input-group-append">
                                                                                        <?php
                                                                                        if ($adquisicion['estado'] <= 5) {
                                                                                            if ($control == 0) {
                                                                                                echo '<button class="btn btn-outline-success" type="button" id="addRowSedes"><i class="fas fa-plus"></i></button>';
                                                                                            } else {
                                                                                                echo '<button class="btn btn-outline-danger delRowSedes" type="button"><i class="fas fa-minus"></i></button>';
                                                                                            }
                                                                                        }
                                                                                        ?>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                <?php
                                                                        $control++;
                                                                    }
                                                                }
                                                                ?>
                                                            </div>
                                                        </fieldset>
                                                    </form>
                                                    <?php if ($adquisicion['estado'] <= 5) {  ?>
                                                        <div class="text-center pt-3">
                                                            <button type="button" class="btn btn-success btn-sm" id="btnDestContra" value="<?php echo $value ?>"><?php echo $accion ?></button>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php
                                    }
                                    ?>
                                    <!--parte-->
                                    <?php if ($adquisicion['estado'] >= 4) { ?>
                                        <div class="card">
                                            <div class="card-header card-header-detalles py-0 headings" id="headingCotRec">
                                                <h5 class="mb-0">
                                                    <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseCotRec" aria-expanded="true" aria-controls="collapseCotRec">
                                                        <div class="form-row">
                                                            <div class="div-icono">
                                                                <span class="fas fa-clipboard-check fa-lg" style="color: #2ECC71;"></span>
                                                            </div>
                                                            <div>
                                                                <?php $j++;
                                                                echo $j ?>. COTIZACIONES RECIBIDAS.
                                                            </div>
                                                        </div>
                                                    </a>
                                                </h5>
                                            </div>
                                            <div id="collapseCotRec" class="collapse" aria-labelledby="headingCotRec">
                                                <div class="card-body">
                                                    <div id="accordion">
                                                        <?php
                                                        $id_cot_rec = $id_adq . '|' . $_SESSION['nit_emp'];
                                                        $url = $api . 'terceros/datos/res/listar/cot_recibidas/' . $id_cot_rec;
                                                        $ch = curl_init($url);
                                                        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                                                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                                                        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                                        $result = curl_exec($ch);
                                                        curl_close($ch);
                                                        $recibe =  json_decode($result, true);
                                                        $cstv = 0;
                                                        if ($recibe != 0) {
                                                            foreach ($recibe as $rc) {
                                                                $ter_slc = $cot_slc = '';
                                                                if ($rc['cc_nit'] == $seleccionada['no_doc']) {
                                                                    $ter_slc = '<i class="far fa-check-circle fa-lg" style="color:#8E44AD"></i>';
                                                                    $cot_slc = 'bg-slc';
                                                                }
                                                        ?>
                                                                <!-- parte-->
                                                                <div class="card">
                                                                    <div class="card-header <?php echo $cot_slc != '' ? $cot_slc : 'card-header-detalles' ?> py-0 headings" id="<?php echo 'cotRec' . $cstv ?>" title="<?php echo $cot_slc != '' ? 'COTIZACIÓN SELECCIONADA' : '' ?>">
                                                                        <h5 class="mb-0">
                                                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapsecotRec<?php echo $cstv ?>" aria-expanded="true" aria-controls="collapsecotRec<?php echo $cstv ?>">
                                                                                <div class="form-row">
                                                                                    <div class="div-icono">
                                                                                        <span class="fas fa-list-ul fa-lg" style="color: #F1C40F;"></span>
                                                                                    </div>
                                                                                    <div>
                                                                                        <?php echo mb_strtoupper($rc['apellido1'] . ' ' . $rc['apellido2'] . ' ' . $rc['nombre1'] . ' ' . $rc['nombre2'] . ' ' . $rc['razon_social'] . ' ' . $rc['cc_nit']) ?>
                                                                                    </div>
                                                                                    <div class="ml-auto mr-0 mr-md-3 my-2 my-md-0 con-icon">
                                                                                        <?php echo $ter_slc ?>
                                                                                    </div>
                                                                                </div>
                                                                            </a>
                                                                        </h5>
                                                                    </div>
                                                                    <div id="collapsecotRec<?php echo $cstv ?>" class="collapse" aria-labelledby="cotRec<?php echo $cstv ?>">
                                                                        <div class="card-body">
                                                                            <?php
                                                                            $dat_cot_rec = $id_adq . '|' . $_SESSION['nit_emp'] . '|' . $rc['id_tercero'];
                                                                            $url = $api . 'terceros/datos/res/listar/datos_cotiz_recibidas/' . $dat_cot_rec;
                                                                            $ch = curl_init($url);
                                                                            //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                                                                            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                                                                            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                                                                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                                                            $result = curl_exec($ch);
                                                                            curl_close($ch);
                                                                            $datos_cotiza =  json_decode($result, true);
                                                                            if ($datos_cotiza != 0) {
                                                                                if ($estad == 4) {
                                                                                    echo '<div class="text-center"><button value="' . $id_adq . '|' . $rc['cc_nit'] . '" class="btn btn-info btn-sm btnSlcCot"><i class="far fa-check-square">&nbsp&nbsp;</i>SELECCIONAR COTIZACIÓN</button></div>';
                                                                                }
                                                                            ?>
                                                                                <table class="table table-striped table-bordered table-sm nowrap table-hover shadow tableCotRecibidas" style="width:100%">
                                                                                    <thead>
                                                                                        <tr>
                                                                                            <th>Bien o Servicio</th>
                                                                                            <th>Cantidad</th>
                                                                                            <th>Val. Estimado Unidad</th>
                                                                                            <th>Valor Cotizado Unidad</th>
                                                                                            <th>Diferencia</th>
                                                                                        </tr>
                                                                                    </thead>
                                                                                    <tbody class="modificarCotizaciones">
                                                                                        <?php
                                                                                        foreach ($datos_cotiza as $dc) {
                                                                                        ?>
                                                                                            <tr>
                                                                                                <td><?php echo $dc['bien_servicio'] ?></td>
                                                                                                <td><?php echo $dc['cantidad'] ?></td>
                                                                                                <td class="text-right"><?php echo pesos($dc['val_estimado_unid']) ?></td>
                                                                                                <td class="text-right"><?php echo pesos($dc['valor']) ?></td>
                                                                                                <?php
                                                                                                $dif =  $dc['valor'] - $dc['val_estimado_unid'];
                                                                                                $signo = '';
                                                                                                if ($dif == 0) {
                                                                                                    $text_clas = 'text-gray';
                                                                                                } else if ($dif < 0) {
                                                                                                    $text_clas = 'text-green';
                                                                                                } else {
                                                                                                    $text_clas = 'text-red';
                                                                                                    $signo = '+';
                                                                                                }
                                                                                                ?>
                                                                                                <td class="<?php echo $text_clas ?> text-right"><?php echo $signo . pesos($dif) ?></td>
                                                                                            </tr>
                                                                                        <?php
                                                                                        }
                                                                                        ?>
                                                                                    </tbody>
                                                                                </table>
                                                                            <?php
                                                                            } else {
                                                                            ?>
                                                                                <div class="p-3 mb-2 bg-warning text-white">NO HAY COTIZACIONES RECIBIDAS</div>
                                                                            <?php
                                                                            }
                                                                            ?>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                        <?php
                                                                $cstv++;
                                                            }
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!--parte-->
                                        <?php if ($adquisicion['estado'] >= 5) { ?>
                                            <div class="card">
                                                <div class="card-header card-header-detalles py-0 headings" id="headingCDP">
                                                    <h5 class="mb-0">
                                                        <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseCDP" aria-expanded="true" aria-controls="collapseCDP">
                                                            <div class="form-row">
                                                                <div class="div-icono">
                                                                    <span class="fas fa-file-invoice-dollar fa-lg" style="color: #7D3C98;"></span>
                                                                </div>
                                                                <div>
                                                                    <?php $j++;
                                                                    echo $j ?>. CERTIFICADO DE DISPONIBILIDAD PRESUPUESTAL (CDP).
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </h5>
                                                </div>
                                                <div id="collapseCDP" class="collapse" aria-labelledby="headingCDP">
                                                    <div class="card-body">
                                                        <?php
                                                        if (!empty($cdp)) {
                                                        ?>
                                                            <table class="table table-striped table-bordered table-sm nowrap table-hover shadow tableCDP" style="width:100%">
                                                                <thead class="text-center">
                                                                    <tr>
                                                                        <th>Número</th>
                                                                        <th>Fecha</th>
                                                                        <th>Objeto</th>
                                                                        <th>Valor</th>
                                                                        <!--<th>Acción</th>-->
                                                                    </tr>
                                                                </thead>
                                                                <tbody class="modificarCDP">
                                                                    <tr>
                                                                        <td><?php echo $cdp['id_manu'] ?></td>
                                                                        <td><?php echo $cdp['fecha'] ?></td>
                                                                        <td><?php echo $cdp['objeto'] ?></td>
                                                                        <td class="text-right"><?php echo pesos($cdp['valor']) ?></td>
                                                                        <!--<td class="text-center">
                                                                            <a class="btn btn-outline-warning btn-sm btn-circle shadow-gb" title="Descargar CDP" onclick="generarFormatoCdp(<?php echo $cdp['id_pto_doc'] ?>)"><span class="fas fa-download fa-lg"></span></a>
                                                                        </td>-->
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        <?php
                                                        } else {
                                                            echo '<div class="p-3 mb-2 bg-warning text-white">AÚN <b>NO</b> SE HA ASIGNADO UN CDP</div>';
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <!--parte-->
                                            <div class="card">
                                                <div class="card-header card-header-detalles py-0 headings" id="headingEstPrev">
                                                    <h5 class="mb-0">
                                                        <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseEstPrev" aria-expanded="true" aria-controls="collapseEstPrev">
                                                            <div class="form-row">
                                                                <div class="div-icono">
                                                                    <span class="fas fa-folder-open fa-lg" style="color: #DC7633;"></span>
                                                                </div>
                                                                <div>
                                                                    <?php $j++;
                                                                    echo $j ?>. ESTUDIOS PREVIOS.
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </h5>
                                                </div>
                                                <div id="collapseEstPrev" class="collapse" aria-labelledby="headingEstPrev">
                                                    <div class="card-body">
                                                        <?php if ($id_estudio == '') { ?>
                                                            <button type="button" class="btn btn-success btn-sm" id='btnAddEstudioPrevio' value="<?php echo $id_adq ?>">INICIAR ESTUDIOS PREVIOS</button>
                                                            <?php } else {
                                                            include 'datos/listar/datos_estudio_previo.php';
                                                            if ($adquisicion['estado'] <= 7) {
                                                            ?>
                                                                <a type="button" class="btn btn-warning btn-sm" id="btnFormatoEstudioPrevio" style="color:white">DESCARGAR FORMATO&nbsp&nbsp;<span class="fas fa-file-download fa-lg"></span></a>
                                                                <a type="button" class="btn btn-info btn-sm" id="btnMatrizRiesgo" style="color:white">MATRIZ DE RIESGOS&nbsp&nbsp;<span class="fas fa-download fa-lg"></span></a>
                                                                <a type="button" class="btn btn-primary btn-sm" id="btnAnexos" style="color:white">ANEXOS&nbsp&nbsp;<span class="far fa-copy fa-lg"></span></a>
                                                        <?php }
                                                        } ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <!--parte-->
                                            <div class="card">
                                                <div class="card-header card-header-detalles py-0 headings" id="headingContrata">
                                                    <h5 class="mb-0">
                                                        <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseContrata" aria-expanded="true" aria-controls="collapseContrata">
                                                            <div class="form-row">
                                                                <div class="div-icono">
                                                                    <span class="fas fa-file-contract fa-lg" style="color: #26C6DA;"></span>
                                                                </div>
                                                                <div>
                                                                    <?php $j++;
                                                                    echo $j ?>. CONTRATACION.
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </h5>
                                                </div>
                                                <div id="collapseContrata" class="collapse" aria-labelledby="headingContrata">
                                                    <div class="card-body">
                                                        <?php if ($id_estudio == '') { ?>
                                                            <div class="alert alert-warning" role="alert">
                                                                AUN NO SE HA REGISTRADO ESTUDIOS PREVIOS
                                                            </div>
                                                            <?php } else {
                                                            if ($adquisicion['estado'] == 6) {
                                                            ?>
                                                                <button type="button" class="btn btn-success btn-sm" id='btnAddContrato' value="<?php echo $id_estudio ?>">INICIAR CONTRATACIÓN</button>
                                                                <?php } else if ($adquisicion['estado'] >= 7) {
                                                                include 'datos/listar/datos_contrato_compra.php';
                                                                if ($adquisicion['estado'] == 7) {
                                                                    if ($tipo_compra['id_tipo_compra'] != '2') {
                                                                ?>
                                                                        <a type="button" class="btn btn-warning btn-sm" id="btnFormatoCompraVenta" style="color:white">DESCARGAR FORMATO COMPRAVENTA&nbsp&nbsp;<span class="fas fa-file-download fa-lg"></span></a>
                                                                    <?php } else { ?>
                                                                        <a type="button" class="btn btn-warning btn-sm" id="btnFormatoServicios" style="color:white">DESCARGAR FORMATO SERVICIOS&nbsp&nbsp;<span class="fas fa-file-download fa-lg"></span></a>
                                                                    <?php } ?>
                                                                    <a type="button" class="btn btn-success btn-sm" id="btnEnviarContrato" style="color:white">ENVIAR CONTRATO&nbsp&nbsp;<span class="fas fa-file-upload fa-lg"></span></a>
                                                            <?php }
                                                            }
                                                        }
                                                        if ($adquisicion['estado'] == 9) { ?>
                                                            <a type="button" class="btn btn-warning btn-sm" id="btnFormatoDesigSuper" style="color:white">DESCARGAR FORMATO DESIGNACIÓN DE SUPERVISIÓN&nbsp&nbsp;<span class="fas fa-file-download fa-lg"></span></a>
                                                            <a type="button" class="btn btn-success btn-sm" id="btnEnviarActaSupervision" value="<?php echo $adquisicion['id_supervision'] ?>" style="color:white">ENVIAR SUPERVISIÓN&nbsp&nbsp;<span class="fas fa-file-upload fa-lg"></span></a>
                                                        <?php
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <!--parte-->
                                            <div class="card">
                                                <div class="card-header card-header-detalles py-0 headings" id="headingDocSoporte">
                                                    <h5 class="mb-0">
                                                        <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseDocSoporte" aria-expanded="true" aria-controls="collapseDocSoporte">
                                                            <div class="form-row">
                                                                <div class="div-icono">
                                                                    <span class="fas fa-file-invoice fa-lg" style="color: #AFB42B;"></span>
                                                                </div>
                                                                <div>
                                                                    <?php $j++;
                                                                    echo $j ?>. DOCUMENTOS DE SOPORTE.
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </h5>
                                                </div>
                                                <div id="collapseDocSoporte" class="collapse" aria-labelledby="headingDocSoporte">
                                                    <div class="card-body">
                                                        <table id="tableDocSopContrato" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                            <thead>
                                                                <tr class="text-center">
                                                                    <th>#</th>
                                                                    <th>Nombre Documento</th>
                                                                    <th>Archivo</th>
                                                                    <th>Aprobado</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="DocsSoportContrato">
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            <!--parte-->
                                            <div class="card">
                                                <div class="card-header card-header-detalles py-0 headings" id="headingCRP">
                                                    <h5 class="mb-0">
                                                        <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseCRP" aria-expanded="true" aria-controls="collapseCRP">
                                                            <div class="form-row">
                                                                <div class="div-icono">
                                                                    <span class="fas fa-file-prescription fa-lg" style="color: #795548;"></span>
                                                                </div>
                                                                <div>
                                                                    <?php $j++;
                                                                    echo $j ?>. REGISTRO PRESUPUESTAL (CRP).
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </h5>
                                                </div>
                                                <div id="collapseCRP" class="collapse" aria-labelledby="headingCRP">
                                                    <div class="card-body">
                                                        <?php
                                                        if (!empty($crp)) {
                                                        ?>
                                                            <table class="table table-striped table-bordered table-sm nowrap table-hover shadow tableCDP" style="width:100%">
                                                                <thead class="text-center">
                                                                    <tr>
                                                                        <th>Número</th>
                                                                        <th>Fecha</th>
                                                                        <th>Objeto</th>
                                                                        <th>Valor</th>
                                                                        <!--<th>Acción</th>-->
                                                                    </tr>
                                                                </thead>
                                                                <tbody class="modificarCDP">
                                                                    <tr>
                                                                        <td><?php echo $crp['id_manu'] ?></td>
                                                                        <td><?php echo $crp['fecha'] ?></td>
                                                                        <td><?php echo $crp['objeto'] ?></td>
                                                                        <td class="text-right"><?php echo pesos($crp['valor']) ?></td>
                                                                        <!--<td class="text-center">
                                                                            <a class="btn btn-outline-warning btn-sm btn-circle shadow-gb" title="Descargar CDP" onclick="generarFormatoCdp(<?php echo $cdp['id_pto_doc'] ?>)"><span class="fas fa-download fa-lg"></span></a>
                                                                        </td>-->
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        <?php
                                                        } else {
                                                            echo '<div class="p-3 mb-2 bg-warning text-white">AÚN <b>NO</b> SE HA REGISTRADO UN CRP</div>';
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <!--parte-->
                                            <div class="card">
                                                <div class="card-header card-header-detalles py-0 headings" id="headingActIni">
                                                    <h5 class="mb-0">
                                                        <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseActIni" aria-expanded="true" aria-controls="collapseActIni">
                                                            <div class="form-row">
                                                                <div class="div-icono">
                                                                    <span class="fas fa-map-pin fa-lg" style="color: #2471A3;"></span>
                                                                </div>
                                                                <div>
                                                                    <?php $j++;
                                                                    echo $j ?>. ACTA DE INICIO.
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </h5>
                                                </div>
                                                <div id="collapseActIni" class="collapse" aria-labelledby="headingActIni">
                                                    <div class="card-body">
                                                        <?php
                                                        if ($adquisicion['estado'] >= 9) {
                                                        ?>
                                                            <a type="button" class="btn btn-warning btn-sm" id="btnFormActaInicio" style="color:white">DESCARGAR FORMATO ACTA DE INICIO&nbsp&nbsp;<span class="fas fa-file-download fa-lg"></span></a>
                                                        <?php
                                                        } else { ?>
                                                            <div class="alert alert-warning" role="alert">
                                                                SE DEBE ASIGNAR UN SUPERVISOR PARA GENERAR ACTA DE INICIO.
                                                            </div>
                                                        <?php
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <!--parte-->
                                            <div class="card">
                                                <div class="card-header card-header-detalles py-0 headings" id="headingNovedad">
                                                    <h5 class="mb-0">
                                                        <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseNovedad" aria-expanded="true" aria-controls="collapseNovedad">
                                                            <div class="form-row">
                                                                <div class="div-icono">
                                                                    <span class="fas fa-bullhorn fa-lg" style="color: #F1C40F;"></span>
                                                                </div>
                                                                <div>
                                                                    <?php $j++;
                                                                    echo $j ?>. NOVEDADES.
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </h5>
                                                </div>
                                                <?php
                                                //API URL
                                                $url = $api . 'terceros/datos/res/listar/novedades_contrato/' . $adquisicion['id_cont_api'];
                                                $ch = curl_init($url);
                                                //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                                                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                                $result = curl_exec($ch);
                                                curl_close($ch);
                                                $nvdds = json_decode($result, true);
                                                $t_novdad = [];
                                                if (isset($nvdds)) {
                                                    while (current($nvdds)) {
                                                        $t_novdad[] =  key($nvdds);
                                                        next($nvdds);
                                                    }
                                                }
                                                $keyliq = array_search('liquidacion', $t_novdad);
                                                $keyter = array_search('liquidacion', $t_novdad);
                                                $inactivo = '';
                                                $activar = 'novedadC';
                                                if (false !== $keyliq || false !== $keyter) {
                                                    $inactivo = 'disabled';
                                                    $activar = '';
                                                }
                                                ?>
                                                <div id="collapseNovedad" class="collapse" aria-labelledby="headingNovedad">
                                                    <div class="card-body">
                                                        <div class="form-row pb-3">
                                                            <div class=" col-md-2">
                                                                <button value="1" type="button" <?php echo $inactivo ?> class="btn btn-outline-info w-100 btn-sm <?php echo $activar ?>">Adición o Prorroga</button>
                                                            </div>
                                                            <div class=" col-md-2">
                                                                <button value="2" type="button" <?php echo $inactivo ?> class="btn btn-outline-info w-100 btn-sm <?php echo $activar ?>">Cesión</button>
                                                            </div>
                                                            <div class=" col-md-2">
                                                                <button value="3" type="button" <?php echo $inactivo ?> class="btn btn-outline-info w-100 btn-sm <?php echo $activar ?>">Suspención</button>
                                                            </div>
                                                            <div class=" col-md-2">
                                                                <button value="4" type="button" <?php echo $inactivo ?> class="btn btn-outline-info w-100 btn-sm <?php echo $activar ?>">Reinicio</button>
                                                            </div>
                                                            <div class=" col-md-2">
                                                                <button value="5" type="button" <?php echo $inactivo ?> class="btn btn-outline-info w-100 btn-sm <?php echo $activar ?>">Terminación</button>
                                                            </div>
                                                            <div class=" col-md-2">
                                                                <button value="6" type="button" <?php echo $inactivo ?> class="btn btn-outline-info w-100 btn-sm <?php echo $activar ?>">Liquidación</button>
                                                            </div>
                                                        </div>
                                                        <table id="tableNovedadesContrato" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                            <thead>
                                                                <tr class="text-center">
                                                                    <th>Novedad</th>
                                                                    <th>Valor adición</th>
                                                                    <th>Fecha</th>
                                                                    <th>tipo</th>
                                                                    <th>CDP</th>
                                                                    <th>Fecha Inicia</th>
                                                                    <th>Fecha Fin</th>
                                                                    <th>Valor Contratante</th>
                                                                    <th>Valor Contratista</th>
                                                                    <th>Tercero</th>
                                                                    <th>Observación</th>
                                                                    <th>Acciones</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="modificarNovContrato">
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            <!--parte-->
                                            <div class="card">
                                                <div class="card-header card-header-detalles py-0 headings" id="headingInfoActv">
                                                    <h5 class="mb-0">
                                                        <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseInfoActv" aria-expanded="true" aria-controls="collapseInfoActv">
                                                            <div class="form-row">
                                                                <div class="div-icono">
                                                                    <span class="fas fa-info-circle fa-lg" style="color: #29B6F6;"></span>
                                                                </div>
                                                                <div>
                                                                    <?php $j++;
                                                                    echo $j ?>. INFORME DE ACTIVIDADES.
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </h5>
                                                </div>
                                                <div id="collapseInfoActv" class="collapse" aria-labelledby="headingInfoActv">
                                                    <div class="card-body">
                                                        <?php
                                                        $id_c = $seleccionada['id_tercero_api'] . '|' . $_SESSION['nit_emp'] . '|' . $id_adq;
                                                        //API URL
                                                        $url = $api . 'terceros/datos/res/lista/compra_entregado/' . $id_c;
                                                        $ch = curl_init($url);
                                                        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                                                        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                                        $result = curl_exec($ch);
                                                        curl_close($ch);
                                                        $separar = explode('|', $id_c);
                                                        $compra_entregada = json_decode($result, true);
                                                        if ($compra_entregada != '0') {
                                                            //API URL
                                                            /*$id_empresa = $compra_entregada['nit'];
                                                            $url = $api . 'terceros/datos/res/listar/empresas/' . $id_empresa;
                                                            $ch = curl_init($url);
                                                            //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                                                            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                                                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                                            $result = curl_exec($ch);
                                                            curl_close($ch);
                                                            $datos_empresa = json_decode($result, true);*/
                                                        ?>
                                                            <div id="contTablaEntrega">
                                                                <form id="formCantEntrega">
                                                                    <input type="hidden" name="id_cnt" value="<?php echo $compra_entregada['id_c'] ?>">
                                                                    <table id="tableListProdRecibidos" class="table table-striped table-bordered table-sm nowrap table-hover shadow" width="100%">
                                                                        <thead class="alinear-head">
                                                                            <tr>
                                                                                <th>Bien o servicio</th>
                                                                                <th>Cant. Contratada</th>
                                                                                <th>Entrega # 1</th>
                                                                                <?php
                                                                                for ($i = 2; $i <= $compra_entregada['num_entregas']['entregas']; $i++) {
                                                                                    echo '<th>Entrega # ' . $i . ' </th>';
                                                                                }
                                                                                ?>
                                                                                <th>Cant. Pendiente</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody id="modificarCompraRec">
                                                                            <?php
                                                                            $total_entrega = 0;
                                                                            foreach ($compra_entregada['listado'] as $ce) {
                                                                            ?>
                                                                                <tr class="text-center">
                                                                                    <td class="text-left"><?php echo $ce['bien_servicio'] ?></td>
                                                                                    <td><?php echo $ce['cantid'] ?></td>
                                                                                    <?php
                                                                                    $array_entregado = $compra_entregada['entregas'];
                                                                                    $c_entregado = 0;
                                                                                    foreach ($array_entregado as $ae) {
                                                                                        if ($ae['id_val_cot'] == $ce['id_val_cot']) {
                                                                                            $c_entregado += $ae['cantidad_entrega'];
                                                                                            echo '<td>' . $ae['cantidad_entrega'] . '</td>';
                                                                                            $maxim =  $ae['cantidad_entrega'];
                                                                                            $id_ent = $ae['id_entrega'];
                                                                                            $estado_ent = $ae['cantidad_entrega'] > 0 ? $ae['estado'] : 4;
                                                                                        }
                                                                                    }
                                                                                    ?>
                                                                                    <td>
                                                                                        <?php
                                                                                        if ($ce['cantid'] > $c_entregado) {
                                                                                            $pendiente = $ce['cantid'] - $c_entregado;
                                                                                            $total_entrega += $pendiente;
                                                                                            echo $pendiente;
                                                                                        }
                                                                                        ?>
                                                                                    </td>
                                                                                </tr>
                                                                            <?php
                                                                            }
                                                                            ?>
                                                                        </tbody>
                                                                    </table>
                                                                </form>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <!--parte-->
                                            <?php
                                            if ($tipo_adq['id_tipo'] == '1') {
                                            ?>
                                                <div class="card">
                                                    <div class="card-header card-header-detalles py-0 headings" id="headingActaEntrada">
                                                        <h5 class="mb-0">
                                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseActaEntrada" aria-expanded="true" aria-controls="collapseActaEntrada">
                                                                <div class="form-row">
                                                                    <div class="div-icono">
                                                                        <span class="fas fa-file-signature fa-lg" style="color: #F0B27A;"></span>
                                                                    </div>
                                                                    <div>
                                                                        <?php $j++;
                                                                        echo $j ?>. ACTA DE ENTRADA (ALMACÉN).
                                                                    </div>
                                                                </div>
                                                            </a>
                                                        </h5>
                                                    </div>
                                                    <div id="collapseActaEntrada" class="collapse" aria-labelledby="headingActaEntrada">
                                                        <div class="card-body">
                                                            <table class="table-striped table-bordered table-sm nowrap table-hover shadow" width="100%">
                                                                <thead>
                                                                    <tr>
                                                                        <th>#</th>
                                                                        <th>DESCRIPCIÓN</th>
                                                                        <th>ACCIÓN</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="detallesXEntrega">
                                                                    <?php
                                                                    for ($i = 1; $i <= $compra_entregada['num_entregas']['entregas']; $i++) {
                                                                        echo '<tr>';
                                                                        echo '<td>' . $i . ' </td>';
                                                                        echo '<td>Entrega #' . $i . ' </td>';
                                                                        echo '<td><div clasS="text-center"><a value="' . $id_c . '&' . $i . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb details" title="Detalles"><span class="fas fa-eye fa-lg"></span></a></div></td>';
                                                                        echo '</tr>';
                                                                    }
                                                                    ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php
                                            }
                                            ?>
                                            <!--parte-->
                                            <div class="card">
                                                <div class="card-header card-header-detalles py-0 headings" id="headingXXXX">
                                                    <h5 class="mb-0">
                                                        <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseXXXX" aria-expanded="true" aria-controls="collapseXXXX">
                                                            <div class="form-row">
                                                                <div class="div-icono">
                                                                    <span class="fas fa-search-dollar fa-lg" style="color: #F48FB1;"></span>
                                                                </div>
                                                                <div>
                                                                    <?php $j++;
                                                                    echo $j ?>. SUPERVISIÓN O INTERVENTORIA.
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </h5>
                                                </div>
                                                <div id="collapseXXXX" class="collapse" aria-labelledby="headingXXXX">
                                                    <div class="card-body">

                                                    </div>
                                                </div>
                                            </div>
                                            <!--parte-->
                                            <div class="card">
                                                <div class="card-header card-header-detalles py-0 headings" id="headingXXXX">
                                                    <h5 class="mb-0">
                                                        <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseXXXX" aria-expanded="true" aria-controls="collapseXXXX">
                                                            <div class="form-row">
                                                                <div class="div-icono">
                                                                    <span class="fas fa-money-check-alt fa-lg" style="color: #663399;"></span>
                                                                </div>
                                                                <div>
                                                                    <?php $j++;
                                                                    echo $j ?>. CAUSACIÓN CONTABLE.
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </h5>
                                                </div>
                                                <div id="collapseXXXX" class="collapse" aria-labelledby="headingXXXX">
                                                    <div class="card-body">

                                                    </div>
                                                </div>
                                            </div>
                                            <!--parte-->
                                            <div class="card">
                                                <div class="card-header card-header-detalles py-0 headings" id="headingXXXX">
                                                    <h5 class="mb-0">
                                                        <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseXXXX" aria-expanded="true" aria-controls="collapseXXXX">
                                                            <div class="form-row">
                                                                <div class="div-icono">
                                                                    <span class="fas fa-sign-out-alt fa-lg" style="color: #1ABC9C;"></span>
                                                                </div>
                                                                <div>
                                                                    <?php $j++;
                                                                    echo $j ?>. EGRESO TESORERIA.
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </h5>
                                                </div>
                                                <div id="collapseXXXX" class="collapse" aria-labelledby="headingXXXX">
                                                    <div class="card-body">

                                                    </div>
                                                </div>
                                            </div>
                                    <?php }
                                    } ?>
                                    <div class="text-center pt-3">
                                        <a type="button" class="btn btn-secondary  btn-sm" href="lista_adquisiciones.php">Regresar</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
                <?php include '../../footer.php' ?>
            </div>
            <!-- Modal -->
            <div class="modal fade" id="divModalError" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header" id="divModalHeader">
                            <h5 class="modal-title" id="exampleModalLongTitle">
                                <i class="fas fa-exclamation-circle fa-lg" style="color:red"></i>
                                ¡Error!
                            </h5>
                        </div>
                        <div class="modal-body text-center" id="divMsgError">

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success btn-sm" data-dismiss="modal">Aceptar</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal -->
            <!-- Modal -->
            <div class="modal fade" id="divModalConfDel" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header" id="divModalHeaderConfir">
                            <h5 class="modal-title" id="exampleModalLongTitle">
                                <i class="fas fa-exclamation-triangle fa-lg" style="color: #E67E22;"></i>
                                ¡Confirmar!
                            </h5>
                        </div>
                        <div class="modal-body" id="divMsgConfdel">

                        </div>
                        <div class="modal-footer" id="divBtnsModalDel">
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal -->
            <div class="modal fade" id="divModalDone" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header" id="divDone">
                            <h5 class="modal-title" id="exampleModalLongTitle">
                                <i class="fas fa-check-circle fa-lg" style="color:#2FDA49"></i>
                                ¡Correcto!
                            </h5>
                        </div>
                        <div class="modal-body text-center" id="divMsgDone">

                        </div>
                        <div class="modal-footer">
                            <a type="button" class="btn btn-primary btn-sm" data-dismiss="modal"> Aceptar</a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal -->
            <!-- Modal -->
            <div class="modal fade" id="divModalForms" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                <div id="divTamModalForms" class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-body text-center" id="divForms">

                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal -->
        </div>
        <?php include '../../scripts.php' ?>
    </body>

    </html>
<?php
} else {
    echo 'Error al intentar obtener datos';
} ?>