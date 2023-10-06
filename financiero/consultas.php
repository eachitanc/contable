<?php
// Función para consuiltar fecha de cierre por modulo
function fechaCierre($vigencia, $modulo, $cx)
{
    try {
        $sql = "SELECT fecha_cierre FROM seg_fin_periodos WHERE id_modulo = $modulo AND vigencia = $vigencia";
        $rs = $cx->query($sql);
        $cierre = $rs->fetch();
        $fecha_cierre = date('Y-m-d', strtotime($cierre['fecha_cierre']));
        $cx = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    return $fecha_cierre;
}
// Funcion para convertir numero a letras
function numeroLetras($numero)
{
    if (!is_numeric($numero)) {
        return false;
    }
    $numero_letras = '';
    $pesos = 'PESOS';
    $centavos = 'CENTAVOS';
    $parte = explode(".", $numero);
    $entero = $parte[0];
    // obtener modulo de un numero
    $modulo = $entero % 1000000;
    if ($modulo == 0) {
        $pesos = 'de pesos';
    }
    if (isset($parte[1])) {
        $decimos = strlen($parte[1]) == 1 ? $parte[1] . '0' : $parte[1];
    }
    $fmt = new \NumberFormatter('es', \NumberFormatter::SPELLOUT);
    if (is_array($parte)) {
        $numero_letras = $fmt->format($entero) . ' ' . $pesos;
        if (isset($decimos) && $decimos > 0) {
            if ($parte[1] < 2) {
                $centavos = 'CENTAVO';
            }
            $numero_letras .= ' con ' . $fmt->format($decimos) . ' ' . $centavos;
        }
    }
    $numero_letras = str_replace("uno", "un", $numero_letras);
    $numero_letras = mb_strtoupper($numero_letras . ' M/CTE.');
    return $numero_letras;
}

// Función para consultar fecha de sesión del usuario
function fechaSesion($vigencia, $usuario, $cx)
{
    try {
        $sql = "SELECT fecha FROM seg_fin_fecha WHERE vigencia = $vigencia AND id_usuario = '$usuario'";
        $rs = $cx->query($sql);
        $fecha_sesion = $rs->fetch();
        if ($fecha_sesion) {
            $fecha = date('Y-m-d', strtotime($fecha_sesion['fecha']));
        } else {
            $fecha = date('Y-m-d');
        }
        $cx = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    return $fecha;
}

// Funcion para convertir a fecha larga
function fechaLarga($fecha, $tipo)
{
    $meses = array(
        '01' => 'Enero',
        '02' => 'Febrero',
        '03' => 'Marzo',
        '04' => 'Abril',
        '05' => 'Mayo',
        '06' => 'Junio',
        '07' => 'Julio',
        '08' => 'Agosto',
        '09' => 'Septiembre',
        '10' => 'Octubre',
        '11' => 'Noviembre',
        '12' => 'Diciembre'
    );
    $prefijo = "A LOS";
    $dia = 'días';
    $objFecha = new DateTime($fecha, new DateTimeZone('America/Mexico_City'));
    $mes = $objFecha->format('m');
    $dia_letras = numeroLetras($objFecha->format('d'));
    $numero_letras = str_replace("PESOS M/CTE.", "", $dia_letras);
    if ($objFecha->format('d') == '01') {
        $numero_letras = str_replace("UN", "PRIMER", $numero_letras);
        $prefijo = 'AL';
        $dia = 'DÍA';
    }
    if ($tipo == 0) {
        $fecha_larga = $meses[$mes] . ' ' . $objFecha->format('d') . ' de ' . $objFecha->format('Y');
    } else {
        $fecha_larga = mb_strtolower($prefijo . ' ' . $numero_letras . '(' . $objFecha->format('d') . ')' . ' ' . $dia . ' del mes de ' . $meses[$mes] . ' de ' . $objFecha->format('Y'));
    }
    return $fecha_larga;
}

// función para establecer el saldo de un rubro de gastos a cierta fecha de una vigencia
function saldoRubroGastos($vigencia, $rubro, $fecha, $tipo, $estado, $cx)
{
    $fecha_ini = date('Y-m-d', strtotime($vigencia . '/01/01'));
    try {
        $sql = "SELECT
        `seg_pto_mvto`.`rubro`
        , `seg_pto_mvto`.`tipo_mov`
        , SUM(`seg_pto_mvto`.`valor`) as total
        FROM
        `seg_pto_mvto`
        INNER JOIN `seg_pto_documento` 
            ON (`seg_pto_mvto`.`id_pto_doc` = `seg_pto_documento`.`id_pto_doc`)
        WHERE `seg_pto_mvto`.`rubro` ='$rubro' AND (`seg_pto_documento`.`fecha` BETWEEN '$fecha_ini' AND '$fecha') AND `seg_pto_mvto`.`tipo_mov` ='$tipo'
        AND `seg_pto_mvto`.`estado` ='$estado' AND `seg_pto_documento`.`estado` =0;";
        $rs = $cx->query($sql);
        $saldo = $rs->fetch_assoc();
        $cx = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    return $saldo['total'];
}

// Funcion para determinar el saldo que tiene un cdp para registrar
function saldoCdp($cdp, $rubro, $cx)
{
    try {
        $sql = "SELECT sum(valor) as total FROM seg_pto_mvto WHERE id_pto_doc = $cdp AND rubro = '$rubro'";
        $rs = $cx->query($sql);
        $saldo = $rs->fetch();
        $valor_cdp = $saldo['total'];
        //$sql = "SELECT sum(valor) as registrado FROM seg_pto_mvto WHERE id_auto_dep = $cdp AND rubro = '$rubro' AND tipo_mov='CRP'";
        $sql = "SELECT
                    SUM(seg_pto_mvto.valor) AS registrado
                FROM
                    seg_pto_mvto
                INNER JOIN seg_pto_documento ON (seg_pto_mvto.id_pto_doc = seg_pto_documento.id_pto_doc)
                WHERE seg_pto_mvto.rubro ='$rubro' AND (seg_pto_mvto.tipo_mov ='CRP' OR seg_pto_mvto.tipo_mov ='LRP') AND seg_pto_mvto.id_auto_dep =$cdp AND seg_pto_documento.estado=0;;";
        $rs = $cx->query($sql);
        $saldo = $rs->fetch();

        $valor_registrado = $saldo['registrado'];
        $saldo = $valor_cdp - $valor_registrado;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    return $saldo;
}
