<?php
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';
include '../../permisos.php';
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_contrato_compra`.`id_contrato_compra`
                , `seg_contrato_compra`.`id_compra`
                , `seg_contrato_compra`.`fec_ini`
                , `seg_contrato_compra`.`fec_fin`
                , `seg_contrato_compra`.`val_contrato`
                , `seg_forma_pago_compras`.`descripcion`
                , `seg_contrato_compra`.`id_supervisor`
            FROM
                `seg_contrato_compra`
            INNER JOIN `seg_forma_pago_compras` 
                ON (`seg_contrato_compra`.`id_forma_pago` = `seg_forma_pago_compras`.`id_form_pago`)
            WHERE `id_compra` = '$id_adq'";
    $rs = $cmd->query($sql);
    $contrato = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$id_ter_sup = $contrato['id_supervisor'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `no_doc` FROM `seg_terceros` WHERE `id_tercero_api` = '$id_ter_sup'";
    $rs = $cmd->query($sql);
    $terceros_sup = $rs->fetch();
    //API URL
    $url = $api . 'terceros/datos/res/lista/' . $terceros_sup['no_doc'];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    $supervisor = json_decode($result, true);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$contrata = isset($contrato) ? $contrato['id_contrato_compra'] : 0;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_garantias_contrato_compra`.`id_contrato_compra`
                ,`seg_garantias_contrato_compra`.`id_poliza`
                , `seg_polizas`.`descripcion`
                , `seg_polizas`.`porcentaje`
            FROM
                `seg_garantias_contrato_compra`
            INNER JOIN `seg_polizas` 
                ON (`seg_garantias_contrato_compra`.`id_poliza` = `seg_polizas`.`id_poliza`)
            WHERE `seg_garantias_contrato_compra`.`id_contrato_compra` = '$contrata'";
    $rs = $cmd->query($sql);
    $garantias = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

?>
<div class="overflow">
    <table class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
        <thead>
            <tr>
                <th rowspan="2" class="text-center centro-vertical">Fecha Inicial</th>
                <th rowspan="2" class="text-center centro-vertical">Fecha Final</th>
                <th rowspan="2" class="text-center centro-vertical">Duración</th>
                <th rowspan="2" class="text-center centro-vertical">Forma de Pago</th>
                <th rowspan="2" class="text-center centro-vertical">Valor Contrato</th>
                <th rowspan="2" class="text-center centro-vertical">Garantías / Pólizas</th>
                <th colspan="2" class="text-center centro-vertical">Supervisor</th>
                <th rowspan="2" class="text-center centro-vertical">Acciones</th>
            </tr>
            <tr>
                <th class="text-center centro-vertical">No. Documento</th>
                <th class="text-center centro-vertical">Nombre</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="centro-vertical"><?php echo $contrato['fec_ini'] ?></td>
                <td class="centro-vertical"><?php echo $contrato['fec_fin'] ?></td>
                <td class="centro-vertical">
                    <?php
                    $fini = new DateTime($contrato['fec_ini']);
                    $ffin = new DateTime($contrato['fec_fin']);
                    $diferencia = $fini->diff($ffin);
                    $dias = intval($diferencia->format('%d')) + 1;
                    $meses = intval($diferencia->format('%m')) > 0 ? intval($diferencia->format('%m')) . ' mes(es) ' : '';
                    echo $meses . $dias . ' día(s)'
                    ?>
                </td>
                <td class="centro-vertical"><?php echo $contrato['descripcion'] ?></td>
                <td class="text-right"><?php echo pesos($contrato['val_contrato']) ?></td>
                <td class="centro-vertical">
                    <?php
                    foreach ($garantias as $g) {
                        echo '<li>' . $g['descripcion'] . ' ' . $g['porcentaje'] . '%</li>';
                    }
                    ?>
                </td>
                <td class="centro-vertical">
                    <?php echo $supervisor[0]['cc_nit'] ?>
                    <input type="hidden" id="id_sup_desig" value="<?php echo $contrato['id_supervisor'] ?>">
                </td>
                <td class="centro-vertical"><?php echo $supervisor[0]['apellido1'] . ' ' . $supervisor[0]['apellido2'] . ' ' . $supervisor[0]['nombre1'] . ' ' . $supervisor[0]['nombre2'] ?></td>
                <td class="centro-vertical" id="modificarContraCompra">
                    <?php
                    $editar = $borrar = $superv = null;
                    if ($adquisicion['estado'] <= 7) {
                        if ($permisos['editar'] == 1) {
                            $editar = '<a value="' . $contrata . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
                        }
                        if ($permisos['borrar'] == 1) {
                            $borrar = '<a value="' . $contrata . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
                        }
                    } else if ($adquisicion['estado'] == 8) {
                        $superv = '<a value="' . $contrata . '" class="btn btn-outline-info btn-sm btn-circle shadow-gb supervisor" title="Designar Supervisor"><span class="fas fa-user-secret fa-lg"></span></a>';
                    }
                    ?>
                    <div class="text-center">
                        <?php echo $editar . $borrar . $superv ?>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>