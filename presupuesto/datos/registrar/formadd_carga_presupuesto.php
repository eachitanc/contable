<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$error = "Debe diligenciar este campo";
$id_cpto = $_POST['id_cpto'];
$id_ppto = $_POST['id_ppto'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    // consulta select tipo de recursos
    $sql = "SELECT * FROM seg_pto_tiporecursos WHERE id_pto_tipo=1 ORDER BY nombre_tipo ASC";
    $rs = $cmd->query($sql);
    $tiporecurso = $rs->fetchAll();
    // Consulta el tipo de id_pto_tipo de seg_pto_presupuestos para saber si es de ingresos o gastos
    $sql = "SELECT id_pto_tipo FROM seg_pto_presupuestos WHERE id_pto_presupuestos=$id_ppto";
    $rs = $cmd->query($sql);
    $tipo = $rs->fetch();
    $id_pto_tipo = $tipo['id_pto_tipo'];
    // consulta select tipo de gastos
    $sql = "SELECT * FROM seg_pto_tipogasto WHERE id_pto_tipo=$id_pto_tipo ORDER BY nombre_tipo_gasto ASC";
    $rs = $cmd->query($sql);
    $tipogasto = $rs->fetchAll();
    $sql = "SELECT
                  MAX(`seg_pto_cargue`.`id_pto_cargue`)
                , MAX(`seg_pto_cargue`.`cod_pptal`) as codigo

            FROM
                `seg_pto_cargue`
                INNER JOIN `seg_pto_presupuestos` 
                    ON (`seg_pto_cargue`.`id_pto_presupuestos` = `seg_pto_presupuestos`.`id_pto_presupuestos`)
            WHERE    
                (`seg_pto_presupuestos`.`id_pto_presupuestos` =$id_cpto );";
    $rs = $cmd->query($sql);
    $ultimo_codptal = $rs->fetch();
    $ultimo = $ultimo_codptal['codigo'];
    $sql = "select tipo_dato, tipo_gasto, id_tipo_recurso from seg_pto_cargue where id_pto_presupuestos=$id_cpto and cod_pptal='$ultimo'";
    $rs = $cmd->query($sql);
    $detalles = $rs->fetch();
    //$cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if ($detalles['tipo_dato'] == '1') {
    // Tomo el ultimo nivel separado por punto y sumo 1
    $ultimo_nivel = explode('.', $ultimo);
    $ultimo_nivel = $ultimo_nivel[count($ultimo_nivel) - 1];
    $niveles = strlen($ultimo_nivel) * -1;
    $ultimo_nivel = $ultimo_nivel + 1;
    $ultimo_nivel = str_pad($ultimo_nivel, abs($niveles), "0", STR_PAD_LEFT);
    $ultimo = substr($ultimo, 0, $niveles);
    $ultimo_nivel = $ultimo . $ultimo_nivel;
} else {
    $ultimo_nivel = $ultimo;
}

?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">CREAR NUEVO RUBRO</h5>
        </div>
        <form id="formAddCargaPresupuesto">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-3">
                    <label for="nomCod" class="small">CODIGO PRESUPUESTAL</label>
                    <input type="text" name="nomCod" id="nomCod" class="form-control form-control-sm" value="<?php echo $ultimo_nivel; ?>">
                    <input type="hidden" name="id_pto" id="id_pto" value="<?php echo $_POST['id_cpto']; ?>">
                </div>
                <input type="hidden" name="datFecVigencia" value="<?php echo $_SESSION['vigencia'] ?>">
                <div class="form-group col-md-7">
                    <label for="nomRubro" class="small">NOMBRE RUBRO</label>
                    <input type="text" name="nomRubro" id="nomRubro" class="form-control form-control-sm">
                </div>
                <div class="form-group col-md-2">
                    <label for="tipoDato" class="small">TIPO DATO</label>
                    <select id="tipoDato" name="tipoDato" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                        <option value="">-- Seleccionar --</option>
                        <option value="0">M - Mayor</option>
                        <option value="1">D - Detalle</option>
                    </select>
                </div>
            </div>
            <div class="form-row px-4  ">
                <div class="form-group col-md-3">
                    <label for="valorAprob" class="small">VALOR APROBADO</label>
                    <input type="text" name="valorAprob" id="valorAprob" class="form-control form-control-sm" style='text-align: right;'>

                </div>
                <input type="hidden" name="datFecVigencia" value="<?php echo $_SESSION['vigencia'] ?>">
                <div class="form-group col-md-3">
                    <label for="tipoRecurso" class="small">TIPO RECURSOS</label>
                    <select id="tipoRecurso" name="tipoRecurso" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                        <option value="">-- Seleccionar --</option>
                        <?php
                        foreach ($tiporecurso as $mo) {
                            echo '<option value="' . $mo['id_pto_tiporecurso'] . '">' . $mo['nombre_tipo'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label for="sin_fondos" class="small">SIN SITUACION DE FONDOS</label>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="situaFondos" name="situaFondos">
                        <label class="custom-control-label" for="situaFondos"></label>
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <label for="tipoPresupuesto" class="small">TIPO PRESUPUESTO</label>
                    <select id="tipoPresupuesto" name="tipoPresupuesto" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                        <option value="">-- Seleccionar --</option>
                        <?php
                        foreach ($tipogasto as $mo) {
                            echo '<option value="' . $mo['id_tipo_gasto'] . '">' . $mo['nombre_tipo_gasto'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-row px-2 ">
                <div class="text-center pb-3">
                    <button class="btn btn-primary btn-sm" id="btnAddCargaPresupuesto">Agregar</button>
                    <a type="button" class="btn btn-danger btn-sm" data-dismiss="modal"> Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>