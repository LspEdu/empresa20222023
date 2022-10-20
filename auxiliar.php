<?php

function conectar()
{
    return new PDO('pgsql:host=localhost;dbname=empresa', 'empresa', 'empresa');
}

/**
 * Vuelve al index.php
 */
function volver()
{
    header("Location: index.php");
}

function obtener_get($par)
{
    return obtener_parametro($par, $_GET);
}

function obtener_post($par)
{
    return obtener_parametro($par, $_POST);
}

function obtener_parametro($par, $array)
{
    return isset($array[$par]) ? trim($array[$par]) : null;
}

function filtrar_codigo($codigo, &$error)
{
    $long = mb_strlen($codigo);

    if ($long < 1 || $long > 2) {
        insertar_error(
            'codigo',
            'La longitud del código es incorrecta',
            $error
        );
    }
    if (!ctype_digit($codigo)) {
        insertar_error(
            'codigo',
            'Los caracteres del código no son válidos',
            $error
        );
    }

    filter_var($codigo, FILTER_VALIDATE_INT, [
        'options' => [
            'min_range' => 0,
            'max_range' => 99,
        ]
    ])

    if (!isset($error['codigo'])) {
        $pdo = conectar();
        $sent = $pdo->prepare("SELECT COUNT(*)
                                 FROM departamentos
                                WHERE codigo = :codigo");
        $sent->execute([':codigo' => $codigo]);
        $cuantos = $sent->fetchColumn();
        if ($cuantos !== 0) {
            insertar_error('codigo', 'El código ya existe', $error);
        }
    }
}

function filtrar_denominacion($denominacion, &$error)
{
    $long = mb_strlen($denominacion);
    if ($long < 1 || $long > 255) {
        insertar_error(
            'denominacion',
            'La longitud de la denominación es incorrecta',
            $error
        );
    }
}

function insertar_error($campo, $mensaje, &$error)
{
    if (!isset($error[$campo])) {
        $error[$campo] = [];
    }
    $error[$campo][] = $mensaje;
}

function insertar_departamento($codigo, $denominacion)
{
    $pdo = conectar();
    $sent = $pdo->prepare("INSERT
                             INTO departamentos (codigo, denominacion)
                           VALUES (:codigo, :denominacion)");
    $sent->execute([
        ':codigo' => $codigo,
        ':denominacion' => $denominacion,
    ]);
}

function mostrar_errores($campo, $error)
{
    if (isset($error[$campo])) {
        foreach ($error[$campo] as $mensaje) { ?>
        <ul <?= css_error($campo, $error) ?>>
            <li><?= $mensaje ?></li>
        </ul><?php
    }
}
}

function comprobar_parametros($codigo, $denominacion)
{
    if (!isset($codigo, $denominacion)) {
        throw new Exception();
    }
}

function comprobar_errores($error)
{
    if (!empty($error)) {
        throw new Exception();
    }
}

function css_error($campo, $error)
{
    return isset($error[$campo]) ? 'class="error"' : '';
}

function css_campo_error($campo, $error)
{
    return isset($error[$campo]) ? 'class="campo-error"' : '';
}
