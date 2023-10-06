<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
$id_cc = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida ');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_contrato_compra`
                , `id_compra`
                , `fec_fin`
                , `fec_ini`
                , `val_contrato`
                , `id_forma_pago`
                , `id_supervisor`
            FROM
                `seg_contrato_compra`
            WHERE `id_contrato_compra` = '$id_cc'";
    $rs = $cmd->query($sql);
    $contrato = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$id_contra = isset($contrato) ? $contrato['id_contrato_compra'] : 0;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_garantia`
                , `id_contrato_compra`
                , `id_poliza`
            FROM
                `seg_garantias_contrato_compra`
            WHERE `id_contrato_compra`  = '$id_contra'";
    $rs = $cmd->query($sql);
    $garantias = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_form_pago`
                , `descripcion`
            FROM
                `seg_forma_pago_compras` ORDER BY `descripcion` ASC ";
    $rs = $cmd->query($sql);
    $forma_pago = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_terceros`.`id_tercero`, `seg_terceros`.`no_doc`
            FROM
                `rel_tipo_tercero`
                INNER JOIN `seg_terceros` 
                    ON (`rel_tipo_tercero`.`id_tercero_api` = `seg_terceros`.`id_tercero_api`)
            WHERE `seg_terceros`.`estado` = 1 AND `rel_tipo_tercero`.`id_tipo_tercero` = 3";
    $rs = $cmd->query($sql);
    $terceros_sup = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if (!empty($terceros_sup)) {
    $ced = '0';
    foreach ($terceros_sup as $tE) {
        $ced .= ',' . $tE['no_doc'];
    }
    //API URL
    $url = $api . 'terceros/datos/res/lista/' . $ced;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    $supervisor = json_decode($result, true);
} else {
    echo "No se ha registrado ningun tercero" . '<br><br><a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>';
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
            `id_poliza`
            , `descripcion`
            , `porcentaje`
        FROM
            `seg_polizas` ORDER BY `descripcion` ASC";
    $rs = $cmd->query($sql);
    $polizas = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR CONTRATO</h5>
        </div>
        <form id="formUpContraCompra">
            <input type="hidden" name="id_cc" value="<?php echo $id_cc ?>">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-4">
                    <label for="datFecIniEjec" class="small">FECHA INICIAL CONTRATO</label>
                    <input type="date" name="datFecIniEjec" id="datFecIniEjec" class="form-control form-control-sm" value="<?php echo $contrato['fec_ini'] ?>">
                </div>
                <div class="form-group col-md-4">
                    <label for="datFecFinEjec" class="small">FECHA FINAL CONTRATO</label>
                    <input type="date" name="datFecFinEjec" id="datFecFinEjec" class="form-control form-control-sm" value="<?php echo $contrato['fec_fin'] ?>">
                </div>
                <?php
                $fini = new DateTime($contrato['fec_ini']);
                $ffin = new DateTime($contrato['fec_fin']);
                $diferencia = $fini->diff($ffin);
                $dias = intval($diferencia->format('%d')) + 1;
                $meses = intval($diferencia->format('%m')) > 0 ? intval($diferencia->format('%m')) . ' mes(es) ' : '';
                ?>
                <div class="form-group col-md-4">
                    <label for="numDuracionContrato" class="small">DURACIÓN DEL CONTRATO</label>
                    <div id="divDuraContrato" class="form-control form-control-sm">
                        <?php echo $meses . $dias . ' día(s)' ?>
                    </div>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-4">
                    <label for="numValContrata" class="small">Valor Contrato</label>
                    <input type="number" name="numValContrata" id="numValContrata" class="form-control form-control-sm" value="<?php echo $contrato['val_contrato'] ?>">
                </div>
                <div class="form-group col-md-4">
                    <label for="slcFormPago" class="small">FORMA DE PAGO</label>
                    <select id="slcFormPago" name="slcFormPago" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                        <?php
                        foreach ($forma_pago as $fp) {
                            $selecionada = '';
                            if ($fp['id_form_pago'] == $contrato['id_forma_pago']) {
                                $selecionada = 'selected';
                            }
                            echo '<option ' . $selecionada . ' value="' . $fp['id_form_pago'] . '">' . $fp['descripcion'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="slcSupervisor" class="small">SUPERVISOR</label>
                    <select id="slcSupervisor" name="slcSupervisor" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                        <?php
                        foreach ($supervisor as $s) {
                            $selecionada = '';
                            if ($s['id_tercero'] == $contrato['id_supervisor']) {
                                $selecionada = 'selected';
                            }
                            echo '<option ' . $selecionada . ' value="' . $s['id_tercero'] . '">' . $s['apellido1'] . ' ' . $s['apellido2'] . ' ' . $s['nombre1'] . ' ' . $s['nombre2'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            <label for="slcSupervisor" class="small">PÓLIZAS</label>
            <div class="form-row px-4">

                <?php
                $cant = 1;
                foreach ($polizas as $pz) {
                    $chequeado = '';
                    $idp = $pz['id_poliza'];
                    $key = array_search($idp, array_column($garantias, 'id_poliza'));
                    if (false !== $key) {
                        $chequeado = 'checked';
                    }
                ?>
                    <div class="form-group col-md-4">
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <input type="checkbox" aria-label="Checkbox for following text input" id="check_<?php echo $cant;
                                                                                                                    $cant++ ?>" name="check[]" value="<?php echo $pz['id_poliza'] ?>" <?php echo $chequeado ?>>
                                </div>
                            </div>
                            <div class="form-control form-control-sm text-left" aria-label="Text input with checkbox" style="font-size: 55%;"><?php echo $pz['descripcion'] . ' ' . $pz['porcentaje'] ?> </div>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>
            <div class="text-center pb-3">
                <button class="btn btn-primary btn-sm" id="btnUpContratoCompra">Actualizar</button>
                <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
            </div>
        </form>
    </div>
</div>