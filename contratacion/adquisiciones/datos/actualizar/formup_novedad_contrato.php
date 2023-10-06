<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
$data = isset($_POST['datos']) ? explode('|', $_POST['datos']) : exit('Acción no permitida ');
$id_novedad = $data[0];
$opcion = $data[1];
//API URL
$url = $api . 'terceros/datos/res/listar/tipo_novedad';
$ch = curl_init($url);
//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
$tip_novedad = json_decode($result, true);
if ($tip_novedad == 0) {
    echo 'Error al intentar obetener tipos de novedad';
    exit();
}
//API URL
$url = $api . 'terceros/datos/res/listar/detalles_novedad/' . $_POST['datos'];
$ch = curl_init($url);
//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
$detalles_novedad = json_decode($result, true);
switch ($opcion) {
    case 1:
    case 2:
    case 3:
?>
        <div class="px-0">
            <div class="shadow">
                <div class="card-header" style="background-color: #16a085 !important;">
                    <h5 style="color: white;">ACTUALIZAR/MODIFICAR ADICIÓN Y/O PRORROGA DE CONTRATO</h5>
                </div>
                <form id="formUpNovContrato">
                    <input type="hidden" name="id_novendad" value="<?php echo $id_novedad ?>">
                    <div class="form-row px-4 pt-2">
                        <div class="form-group col-md-12">
                            <label for="slcTipoNovedad" class="small">TIPO DE NOVEDAD</label>
                            <select id="slcTipoNovedad" name="slcTipoNovedad" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                <?php
                                $ver1 = $ver2 = 'none';
                                foreach ($tip_novedad as $tn) {
                                    $slc = $opcion == $tn['id_novedad'] ? 'selected' : '';
                                    echo '<option ' . $slc . ' value="' . $tn['id_novedad'] . '">' . $tn['descripcion'] . '</option>';
                                }
                                if ($opcion == 3) {
                                    $ver1 = $ver2 = true;
                                } else if ($opcion == 2) {
                                    $ver2 = true;
                                } else {
                                    $ver1 = true;
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row px-4" id="divAdicion" style="display: <?php echo $ver1 ?>;">
                        <div class="form-group col-md-6">
                            <label for="numValAdicion" class="small">VALOR</label>
                            <input type="number" name="numValAdicion" id="numValAdicion" class="form-control form-control-sm" value="<?php echo $detalles_novedad['val_adicion'] ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="datFecAdicion" class="small">FECHA DE ADICIÓN</label>
                            <input type="date" name="datFecAdicion" id="datFecAdicion" class="form-control form-control-sm" value="<?php echo $detalles_novedad['fec_adcion'] ?>">
                        </div>
                    </div>
                    <div class="form-row px-4" id="divCDPadicion" style="display: <?php echo $ver1 ?>;">
                        <div class="form-group col-md-12">
                            <label for="slcCDP" class="small">CDP</label>
                            <select id="slcCDP" name="slcCDP" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                <option value="1">CDP-001</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row px-4" id="divProrroga" style="display: <?php echo $ver2 ?>;">
                        <div class="form-group col-md-6">
                            <label for="datFecIniProrroga" class="small">FECHA INICIAL</label>
                            <input type="date" name="datFecIniProrroga" id="datFecIniProrroga" class="form-control form-control-sm" value="<?php echo $detalles_novedad['fec_ini_prorroga'] ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="datFecFinProrroga" class="small">FECHA FINAL</label>
                            <input type="date" name="datFecFinProrroga" id="datFecFinProrroga" class="form-control form-control-sm" value="<?php echo $detalles_novedad['fec_fin_prorroga'] ?>">
                        </div>
                    </div>
                    <div class="form-row px-4" id="divObservaNov">
                        <div class="form-group col-md-12">
                            <label for="txtAObservaNov" class="small">OBSERVACIONES</label>
                            <textarea class="form-control" id="txtAObservaNov" name="txtAObservaNov" rows="3"><?php echo $detalles_novedad['observacion'] ?></textarea>
                        </div>
                    </div>
                    <div class="text-center pb-3">
                        <button class="btn btn-primary btn-sm" id="btnUpNovContrato">Actualizar</button>
                        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    <?php
        break;
    case 4:
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            $sql = "SELECT
                        `seg_terceros`.`id_tercero`, `seg_terceros`.`no_doc`
                    FROM
                        `rel_tipo_tercero`
                        INNER JOIN `seg_terceros` 
                            ON (`rel_tipo_tercero`.`id_tercero_api` = `seg_terceros`.`id_tercero_api`)
                    WHERE `seg_terceros`.`estado` = 1 AND `rel_tipo_tercero`.`id_tipo_tercero` = 2";
            $rs = $cmd->query($sql);
            $terceros = $rs->fetchAll();
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
        if (!empty($terceros)) {
            $ced = '0';
            foreach ($terceros as $tE) {
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
            $terceros_api = json_decode($result, true);
        } else {
            echo "No se ha registrado ningun tercero" . '<br><br><a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>';
        }
    ?>
        <div class="px-0">
            <div class="shadow">
                <div class="card-header" style="background-color: #16a085 !important;">
                    <h5 style="color: white;">ACTUALIZAR O MODIFICAR CESIÓN DE CONTRATO</h5>
                </div>
                <form id="formUpNovContrato">
                    <input type="hidden" name="id_novendad" value="<?php echo $id_novedad ?>">
                    <input type="hidden" name="id_contrato" value="<?php echo $detalles_novedad['id_contrato'] ?>">
                    <input type="hidden" name="slcTipoNovedad" id="slcTipoNovedad" value="4">
                    <div class="form-row px-4 pt-2">
                        <div class="form-group col-md-4">
                            <label for="datFecCesion" class="small">FECHA CESIÓN</label>
                            <input type="date" name="datFecCesion" id="datFecCesion" class="form-control form-control-sm" value="<?php echo $detalles_novedad['fec_cesion'] ?>">
                        </div>
                        <div class="form-group col-md-8">
                            <label for="slcTerceroCesion" class="small">TERCERO CESIONARIO</label>
                            <select id="slcTerceroCesion" name="slcTerceroCesion" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                <?php
                                foreach ($terceros_api as $tc) {
                                    $slc = $detalles_novedad['id_tercero'] == $tc['id_tercero'] ? 'selected' : '';
                                    $razsoc = $tc['razon_social'] != '' ? ' - ' . $tc['razon_social'] : '';
                                    echo '<option ' . $slc . ' value="' . $tc['id_tercero'] . '">' . mb_strtoupper($tc['apellido1'] . ' ' . $tc['apellido2'] . ' ' . $tc['nombre1'] . ' ' . $tc['nombre2'] . $razsoc) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row px-4">
                        <div class="form-group col-md-12">
                            <label for="txtAObservaNov" class="small">OBSERVACIONES</label>
                            <textarea class="form-control" id="txtAObservaNov" name="txtAObservaNov" rows="3"><?php echo $detalles_novedad['observacion'] ?></textarea>
                        </div>
                    </div>
                    <div class="text-center pb-3">
                        <button class="btn btn-primary btn-sm" id="btnUpNovContrato">Actualizar</button>
                        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    <?php
        break;
    case 5:
    ?>
        <div class="px-0">
            <div class="shadow">
                <div class="card-header" style="background-color: #16a085 !important;">
                    <h5 style="color: white;">ACTUALIZAR O MODIFICAR SUSPENCIÓN DE CONTRATO</h5>
                </div>
                <form id="formUpNovContrato">
                    <input type="hidden" name="id_novendad" value="<?php echo $id_novedad ?>">
                    <input type="hidden" name="id_contrato" value="<?php echo $detalles_novedad['id_contrato'] ?>">
                    <input type="hidden" name="slcTipoNovedad" id="slcTipoNovedad" value="5">
                    <div class="form-row px-4 pt-2">
                        <div class="form-group col-md-6">
                            <label for="datFecIniSuspencion" class="small">FECHA INICIAL</label>
                            <input type="date" name="datFecIniSuspencion" id="datFecIniSuspencion" class="form-control form-control-sm" value="<?php echo $detalles_novedad['fec_inicia'] ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="datFecFinSuspencion" class="small">FECHA FINAL</label>
                            <input type="date" name="datFecFinSuspencion" id="datFecFinSuspencion" class="form-control form-control-sm" value="<?php echo $detalles_novedad['fec_fin'] ?>">
                        </div>
                    </div>
                    <div class="form-row px-4">
                        <div class="form-group col-md-12">
                            <label for="txtAObservaNov" class="small">OBSERVACIONES</label>
                            <textarea class="form-control" id="txtAObservaNov" name="txtAObservaNov" rows="3"><?php echo $detalles_novedad['observacion'] ?></textarea>
                        </div>
                    </div>
                    <div class="text-center pb-3">
                        <button class="btn btn-primary btn-sm" id="btnUpNovContrato">Actualizar</button>
                        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    <?php
        break;
    case 6:
        //API URL
        $url = $api . 'terceros/datos/res/listar/suspension2/' . $id_novedad;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        $suspensiones = json_decode($result, true);
    ?>
        <div class="px-0">
            <div class="shadow">
                <div class="card-header" style="background-color: #16a085 !important;">
                    <h5 style="color: white;">ACTUALIZAR O MODIFICAR REINICIO DE CONTRATO</h5>
                </div>
                <br>
                <div class="px-4">
                    <?php
                    if ($suspensiones['id_suspension'] == '') {
                    ?>
                        <div class="alert alert-danger" role="alert">
                            PRIMERO DEBE REGISTAR UNA SUSPENCIÓN DE CONTRATO!
                        </div>
                        <div class="text-center pb-3">
                            <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                        </div>
                    <?php
                    } else {
                    ?>
                        <table class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Fecha Inicial</th>
                                    <th>Fecha Final</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?php echo $suspensiones['id_suspension'] ?></td>
                                    <td><?php echo $suspensiones['fec_inicia'] ?></td>
                                    <td><?php echo $suspensiones['fec_fin'] ?></td>
                                </tr>
                            </tbody>
                        </table>
                </div>
                <form id="formUpNovContrato">
                    <input type="hidden" name="id_novendad" value="<?php echo $id_novedad ?>">
                    <input type="hidden" id="fecIniSus" value="<?php echo $suspensiones['fec_inicia'] ?>">
                    <input type="hidden" id="fecFinSus" value="<?php echo $suspensiones['fec_fin'] ?>">
                    <input type="hidden" id="id_suspension" name="id_suspension" value="<?php echo $suspensiones['id_suspension'] ?>">
                    <input type="hidden" name="slcTipoNovedad" id="slcTipoNovedad" value="6">
                    <div class="form-row px-4 pt-2">
                        <div class="form-group col-md-12">
                            <label for="datFecReinicio" class="small">FECHA APROBADA REINICIO</label>
                            <input type="date" name="datFecReinicio" id="datFecReinicio" class="form-control form-control-sm" value="<?php echo $detalles_novedad['fec_reinicia'] ?>">
                        </div>
                    </div>
                    <div class="form-row px-4">
                        <div class="form-group col-md-12">
                            <label for="txtAObservaNov" class="small">OBSERVACIONES</label>
                            <textarea class="form-control" id="txtAObservaNov" name="txtAObservaNov" rows="3"><?php echo $detalles_novedad['observacion'] ?></textarea>
                        </div>
                    </div>
                    <div class="text-center pb-3">
                        <button class="btn btn-primary btn-sm" id="btnUpNovContrato">Actualizar</button>
                        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                    </div>
                </form>
            <?php } ?>
            </div>
        </div>
    <?php
        break;
    case 7:
        //API URL
        $url = $api . 'terceros/datos/res/listar/tipos_terminacion_contrato';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        $tip_terminacion = json_decode($result, true);
    ?>
        <div class="px-0">
            <div class="shadow">
                <div class="card-header" style="background-color: #16a085 !important;">
                    <h5 style="color: white;">ACTUALIZAR O MODIFICAR TERMINACIÓN DE CONTRATO</h5>
                </div>
                <form id="formUpNovContrato">
                    <input type="hidden" name="id_novendad" value="<?php echo $id_novedad ?>">
                    <input type="hidden" name="slcTipoNovedad" id="slcTipoNovedad" value="7">
                    <div class="form-row px-4 pt-2">
                        <div class="form-group col-md-12">
                            <label for="slcTipTerminacion" class="small">TIPO DE TERMINACIÓN DE CONTRATO</label>
                            <select id="slcTipTerminacion" name="slcTipTerminacion" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                <?php
                                foreach ($tip_terminacion as $tt) {
                                    $slc = $tt['id_tipo_term'] == $detalles_novedad['id_t_terminacion'] ? 'selected' : '';
                                    echo '<option ' . $slc . ' value="' . $tt['id_tipo_term'] . '">' . $tt['descripcion'] . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row px-4">
                        <div class="form-group col-md-12">
                            <label for="txtAObservaNov" class="small">OBSERVACIONES</label>
                            <textarea class="form-control" id="txtAObservaNov" name="txtAObservaNov" rows="3"><?php echo $detalles_novedad['observacion'] ?></textarea>
                        </div>
                    </div>
                    <div class="text-center pb-3">
                        <button class="btn btn-primary btn-sm" id="btnUpNovContrato">Actualizar</button>
                        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    <?php
        break;
    case 8:
    ?>
        <div class="px-0">
            <div class="shadow">
                <div class="card-header" style="background-color: #16a085 !important;">
                    <h5 style="color: white;">ACTUALIZAR O MODIFICAR LIQUIDACIÓN DE CONTRATO</h5>
                </div>
                <form id="formUpNovContrato">
                    <input type="hidden" name="id_novendad" value="<?php echo $id_novedad ?>">
                    <input type="hidden" name="slcTipoNovedad" id="slcTipoNovedad" value="8">
                    <div class="form-row px-4 pt-2">
                        <div class="form-group col-md-4">
                            <label for="datFecLiq" class="small">FECHA LIQUIDACIÓN</label>
                            <input type="date" name="datFecLiq" id="datFecLiq" class="form-control form-control-sm" value="<?php echo $detalles_novedad['fec_liq'] ?>">
                        </div>
                        <div class="form-group col-md-8">
                            <label for="slcTipLiquidacion" class="small">TIPO DE LIQUIDACIÓN DE CONTRATO</label>
                            <select id="slcTipLiquidacion" name="slcTipLiquidacion" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                <?php
                                $slc1 = $detalles_novedad['id_t_liq'] == 1 ? 'selected' : '';
                                $slc2 = $detalles_novedad['id_t_liq'] == 2 ? 'selected' : '';
                                ?>
                                <option <?php echo $slc1 ?> value="1">UNILATERAL</option>
                                <option <?php echo $slc2 ?> value="2">MUTUO ACUERDO</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row px-4">
                        <div class="form-group col-md-6">
                            <label for="numValFavorCtrate" class="small">VALOR A FAVOR CONTRATANTE</label>
                            <input type="number" name="numValFavorCtrate" id="numValFavorCtrate" class="form-control form-control-sm" value="<?php echo $detalles_novedad['val_cte'] ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="numValFavorCtrista" class="small">VALOR A FAVOR CONTRATISTA</label>
                            <input type="number" name="numValFavorCtrista" id="numValFavorCtrista" class="form-control form-control-sm" value="<?php echo $detalles_novedad['val_cta'] ?>">
                        </div>
                    </div>
                    <div class="form-row px-4">
                        <div class="form-group col-md-12">
                            <label for="txtAObservaNov" class="small">OBSERVACIONES</label>
                            <textarea class="form-control" id="txtAObservaNov" name="txtAObservaNov" rows="3"><?php echo $detalles_novedad['observacion'] ?></textarea>
                        </div>
                    </div>
                    <div class="text-center pb-3">
                        <button class="btn btn-primary btn-sm" id="btnUpNovContrato">Actualizar</button>
                        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
<?php
        break;
}
?>