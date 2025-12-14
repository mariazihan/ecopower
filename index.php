<?php
/**
 * @title: Proyecto integrador Ev01 - Single Page Application (SPA).
 * @description: Archivo unificado que contiene la lógica de header, index, login, signup y logout.
 *
 * @version    0.1
 * @author ecopower@gmail.com
 */

// --- Lógica de header.php (Incluye functions.php desde utils) ---
session_start([
    'cookie_lifetime' => 86400,
]);

// Asegúrate de que la ruta sea correcta según la estructura de tu proyecto
require_once 'utils/functions.php';

$userstr = ' (Invitado)';
$loggedin = FALSE;

// 1. Gestión de la inactividad de la sesión
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    // La última solicitud fue hace más de 30 minutos
    session_unset();     // Limpiar las variables $_SESSION
    session_destroy();   // Destruir los datos de sesión en el almacenamiento
}
$_SESSION['LAST_ACTIVITY'] = time(); // Actualizar la marca de tiempo de la última actividad

if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
    $loggedin = TRUE;
    $userstr = " ($user)";
}

// Inicializar variables de error para los formularios
$error_login = $user_login = $pass_login = "";
$error_signup = $user_signup = $pass_signup = "";


// -------------------------------------------------------------------------------------
// 2. Lógica de Manejo de Solicitudes (POST y AJAX)
// -------------------------------------------------------------------------------------

// --- Lógica de logout.php (Manejo de la acción de salir) ---
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    if ($loggedin) {
        destroySession();
        $loggedin = FALSE; // Actualizar el estado para la visualización inmediata
        $userstr = ' (Invitado)';
        // No hay header('Location'), ya que estamos en una SPA
    }
}

// --- Lógica de login.php (Manejo del formulario de acceso) ---
if (isset($_POST['action']) && $_POST['action'] == 'login') {
    $user_login = $_POST['user'] ?? '';
    $pass_login = $_POST['pass'] ?? '';
    $response = ['success' => false, 'message' => ''];

    if ($user_login == "" || $pass_login == "") {
        $error_login = "Debes completar todos los campos";
        $response['message'] = $error_login;
    } else {
        // La consulta original es vulnerable a inyección SQL. Mantenemos la lógica
        // del archivo original por requisitos del ejercicio, pero en producción se debería usar sentencias preparadas.
        $result = queryMySQL("SELECT * FROM members WHERE user='$user_login' AND pass='$pass_login'");

        if ($result->num_rows == 0) {
            $error_login = "<span class='error'>Email/Contraseña inválida</span>";
            $response['message'] = $error_login;
        } else {
            $_SESSION["user"] = $user_login;
            $loggedin = TRUE;
            $userstr = " ($user_login)";

            // Gestión de vida de la sesión (regeneración de ID)
            if (!isset($_SESSION['CREATED'])) {
                $_SESSION['CREATED'] = time();
            } else if (time() - $_SESSION['CREATED'] > 1800) {
                session_regenerate_id(true);
                $_SESSION['CREATED'] = time();
            }

            $response['success'] = true;
            $response['message'] = "Acceso exitoso. Redireccionando...";
        }
    }
    // Si la solicitud es AJAX (desde el código jQuery), enviamos la respuesta JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        echo json_encode($response);
        exit;
    }
    // Si no es AJAX, el flujo normal de PHP continúa (se mostrará el formulario con el error)
}

// --- Lógica de signup.php (Manejo del formulario de registro) ---
if (isset($_POST['action']) && $_POST['action'] == 'signup') {
    $user_signup = $_POST["email"] ?? '';
    $pass_signup = $_POST["password"] ?? '';
    // Cambiamos el 'redirect' en la respuesta para indicar a dónde debe ir el cliente.
    $response = ['success' => false, 'message' => '', 'redirect' => ''];

    if ($user_signup == "" || $pass_signup == "") {
        $error_signup = "Debes completar todos los campos";
        $response['message'] = $error_signup;
    } else {
        // Consultar si el usuario ya existe
        $result = queryMysql("SELECT * FROM members WHERE user='$user_signup'");

        if ($result->num_rows) {
            // EL USUARIO YA EXISTE: Redirigir al inicio de sesión
            $error_signup = "El usuario ya existe. Por favor, inicia sesión.";
            $response['message'] = $error_signup;
            $response['redirect'] = '#mostrar-iniciosesion'; // <--- Nuevo campo de redirección
        } else {
            // El usuario no existe: Proceder al registro
            queryMysql("INSERT INTO members(user,pass) VALUES('$user_signup', '$pass_signup')");

            $_SESSION["user"] = $user_signup;
            $loggedin = TRUE;
            $userstr = " ($user_signup)";

            $response['success'] = true;
            $response['message'] = "Registro exitoso. ¡Bienvenido!";
            $response['redirect'] = '#mostrar-index'; // Redirección al inicio tras éxito
        }
    }
    // Si la solicitud es AJAX, enviamos la respuesta JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        echo json_encode($response);
        exit;
    }
}

// -------------------------------------------------------------------------------------
// 3. Renderizado de la página HTML
// -------------------------------------------------------------------------------------
?>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="icon" href="./assets/img/Logo.png">
    <title>EcoPower</title>

    <style>
        .event-card {
            transition: transform 0.3s ease;
        }

        .event-card:hover {
            transform: translateY(-8px);
        }

        .event-card img {
            filter: grayscale(100%) brightness(80%);
            height: 200px;
            transition: all 0.3s;
        }

        .event-card:hover img {
            filter: grayscale(0%) brightness(100%);
            transition: all 0.3s;
        }

        /* Ocultar todas las secciones por defecto para que jQuery controle la visualización */
        .mostrar {
            display: none;
        }
    </style>
</head>

<body class="bg-light min-vh-100 d-flex flex-column">

    <?php if ($loggedin): ?>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark text-primary" id="inicio">
            <div class="container text-primary bg-dark">
                <a class="navbar-brand text-warning" href="#" data-target="#mostrar-index">EcoPower</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarBasic"
                    aria-controls="navbarBasic" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse show " id="navbarBasic">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <button data-target="#mostrar-index" class="boton-nav btn-outline-primary me-2">Inicio</button>
                        </li>
                        <li class="nav-item">
                            <button data-target="#mostrar-eventos"
                                class="boton-nav btn-outline-primary me-2">Estaciones</button>
                        </li>
                        <li class="nav-item">
                            <button data-target="#mostrar-contactos"
                                class="boton-nav btn-outline-primary me-2">Contactos</button>
                        </li>
                        <li class="nav-item">
                            <a href="#" id="logout-btn" class="btn btn-sm boton-nav btn-outline-danger me-2 ">
                                Salir (<?php echo htmlspecialchars($user); ?>)
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    <?php else: ?>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark text-primary" id="inicio">
            <div class="container text-primary bg-dark">
                <a class="navbar-brand text-warning" href="#" data-target="#mostrar-index">EcoPower</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarBasic"
                    aria-controls="navbarBasic" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse show " id="navbarBasic">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <button data-target="#mostrar-index" class="boton-nav btn-outline-primary me-2">Inicio</button>
                        </li>
                        <li class="nav-item">
                            <button data-target="#mostrar-eventos"
                                class="boton-nav btn-outline-primary me-2">Estaciones</button>
                        </li>
                        <li class="nav-item">
                            <button data-target="#mostrar-contactos"
                                class="boton-nav btn-outline-primary me-2">Contactos</button>
                        </li>
                        <li class="nav-item">
                            <button data-target="#mostrar-registrarse"
                                class="boton-nav btn-outline-primary me-2">Registrarse</button>
                        </li>
                        <li class="nav-item">
                            <button data-target="#mostrar-iniciosesion" class="boton-nav btn-outline-primary me-2">Inicio
                                Sesión</button>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    <?php endif; ?>

    <main>
        <section id="mostrar-index" class="mostrar">
            <div class="container mt-5 ">
                <h1 class="display-10 text-center text-primary mb-5">¡Bienvenido a EcoPower!</h1>
                <p class="lead mt-3">
                    La empresa dedicada al alquiler de baterías portátiles que se cargan en estaciones de carga con
                    paneles solares.
                </p>
                <p class="lead mb-3">
                    Nuestro servicio permite a los usuarios alquilar batería portátiles mediante una aplicación móvil,
                    cargar su dispositivo en movimiento y devolverla en cualquier punto de nuestra red de estaciones de
                    carga. Nuestra misión es que los usuarios no tengan la preocupación por falta de batería en sus
                    dispositivos móviles, permitiendo que tengan conectividad continua.
                </p>

                <?php if (!$loggedin): // Mostrar solo si no está logueado ?>
                    <button data-target="#mostrar-registrarse" id="boton-registrarse"
                        class="btn btn-primary text-warning"><i class="bi bi-person-plus-fill me-2"></i> Únete a
                        EcoPower</button>
                <?php endif; ?>

                <h1 class="display-8 text-primary mt-4">Descubre las estaciones cerca de ti</h1>
                <p class="lead mt-3">Carga en movimiento, disfruta el momento.</p>
                <button data-target="#mostrar-eventos" class="btn btn-primary btn-lg text-warning">Ver
                    Estaciones</button>
            </div>


            <div id="map" class="container py-5">
                <h2 class="mb-4 text-center text-primary">Mapa de Estaciones</h2>
                <div class="ratio ratio-21x9 rounded shadow">
                    <iframe class=""
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3036.617388971936!2d-3.70379!3d40.41678!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xd42287d61b5b123%3A0x1234567890abcdef!2sCoffee%20Shop!5e0!3m2!1ses!2ses!4v1695480000000!5m2!1ses!2ses"
                        allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </section>

        <section id="mostrar-eventos" class="mostrar">
            <header class="bg-light py-5 ">
                <div class="container text-center">
                    <h1 class="display-4">Entidades con nuestro servicio</h1>
                    <p class="lead">Descubre donde coger y devolver las baterías cerca de tí.</p>
                </div>
                <div class="w-25 py-5 ms-5">
                    <h4 class="display-10 text-primary">Encuentra estaciones rápidamente:</h4>
                    <input type="text" class="form-control text-primary" id="buscarEvento"
                        placeholder="Buscar estaciones...">
                </div>
            </header>

            <section class="container py-5">
                <div class="eventos row g-4">

                    <div class="evento col-md-6 col-lg-4">
                        <div class="card event-card shadow-sm h-100">
                            <img src="./assets/img/Pamplona.webp" class="card-img-top" alt="Degustación de cafés">
                            <div class="card-body">
                                <h5 class="card-title">Ayuntamiento de Pamplona</h5>
                                <p class="card-text text-muted">Plaza consistorial, Pamplona</p>
                                <p class="card-text">Punto de recogida y devolución de baterías portátiles. -50
                                    dispositivos</p>
                            </div>
                        </div>
                    </div>

                    <div class="evento col-md-6 col-lg-4" id="">
                        <div class="card event-card shadow-sm h-100">
                            <img src="./assets/img/UPNA.jpg" class="card-img-top" alt="Taller de Latte Art">
                            <div class="card-body">
                                <h5 class="card-title">Estación campus UPNA</h5>
                                <p class="card-text text-muted">Campus Arrosadia, 31006, Pamplona (Navarra)</p>
                                <p class="card-text">Punto de recogida y devolución de baterías portátiles. -200
                                    dispositivos</p>
                            </div>
                        </div>
                    </div>

                    <div class="evento col-md-6 col-lg-4">
                        <div class="card event-card shadow-sm h-100">
                            <img src="./assets/img/Estacion.webp" class="card-img-top" alt="Música en vivo y café">
                            <div class="card-body">
                                <h5 class="card-title">Estación de Autobuses</h5>
                                <p class="card-text text-muted">Calle Yanguas y Miranda 2, 31003, Pamplona (Navarra)</p>
                                <p class="card-text">Disfruta de una carga nada más llegar a la ciudad. -25 Dispositivos
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="evento col-md-6 col-lg-4">
                        <div class="card event-card shadow-sm h-100">
                            <img src="./assets/img/Hospital.webp" alt="Concurso de baristas">
                            <div class="card-body">
                                <h5 class="card-title">Complejo Hospitalario de Pamplona</h5>
                                <p class="card-text text-muted">C/ Irunlarrea, 3, 31008, Pamplona (Navarra)</p>
                                <p class="card-text">Punto de recogida y devolución de baterías portátiles. -30
                                    dispositivos</p>
                            </div>
                        </div>
                    </div>

                    <div class="evento col-md-6 col-lg-4">
                        <div class="card event-card shadow-sm h-100">
                            <img src="./assets/img/Toros.webp" class="card-img-top" alt="Concurso de baristas">
                            <div class="card-body">
                                <h5 class="card-title">Plaza de Toros</h5>
                                <p class="card-text text-muted">Paseo de Hemingway, S/N, 31002, Pamplona (Navarra)
                                </p>
                                <p class="card-text">Punto de recogida y devolución de baterías portátiles. -40
                                    dispositivos</p>
                            </div>
                        </div>
                    </div>

                </div>
            </section>
        </section>

        <section class="mostrar" id="mostrar-contactos">
            <section class="container py-5">
                <h1 class="text-center mb-4">Quiénes Somos</h1>
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <p>
                            EcoPower es una plataforma dedicada al **alquiler de baterías portátiles** y estaciones de
                            carga solar.
                            Nuestro objetivo es ofrecer energía móvil sostenible y accesible para que nuestros usuarios
                            nunca se queden sin conexión.
                        </p>
                    </div>
                    <div class="col-md-6 text-center">
                        <img src="./assets/img/Logo.png" alt="logo EcoPower" class="img-fluid rounded w-25">
                        <img src="./assets/img/Logo_Realista.png" alt="logo EcoPower" class="img-fluid rounded w-25">
                    </div>
                </div>
            </section>

            <section class="py-5">
                <div class="container">
                    <h2 class="text-center mb-4">Contacto</h2>
                    <p class="text-center mb-4">
                        ¿Tienes dudas, sugerencias o quieres colaborar? Escríbenos a <a
                            href="mailto:info@ecopower.com">info@ecopower.com</a> o llámanos al +34 656 122 327.
                    </p>
                    <div class="row justify-content-center">
                        <div class="col-md-6 text-center">
                            <p>También puedes seguirnos en nuestras redes sociales:</p>
                            <a href="#" class="btn btn-outline-primary me-2">Facebook</a>
                            <a href="#" class="btn btn-outline-danger me-2">Instagram</a>
                            <a href="#" class="btn btn-outline-info">X / Twitter</a>
                        </div>
                    </div>
            </section>

            <section class="py-5">
                <h2 class="text-center mb-4">Alguno de Nuestros Colaboradores...</h2>
                <div class="row g-4">

                    <div class="col-md-4">
                        <div class="card h-100 w-75 shadow-sm">
                            <img src="./assets/img/Pamplona.webp" class="card-img-top" alt="Café Aromático"
                                style="height:200px; object-fit:cover;">
                            <div class="card-body text-center">
                                <h5 class="card-title">Ayuntamiento de Pamplona</h5>
                                <p class="card-text">Estación Nº1 más utilizada en la ciudad.</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card h-100 w-75 shadow-sm">
                            <img src="./assets/img/UPNA.jpg" class="card-img-top" alt="Latte Lovers"
                                style="height:200px; object-fit:cover;">
                            <div class="card-body text-center">
                                <h5 class="card-title">Campus UPNA</h5>
                                <p class="card-text">Estación con más baterías en zona.</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card h-100 w-75 shadow-sm">
                            <img src="./assets/img/Estacion.webp" class="card-img-top" alt="Espresso Express"
                                style="height:200px; object-fit:cover;">
                            <div class="card-body text-center">
                                <h5 class="card-title">Estación de Autobuses</h5>
                                <p class="card-text">Estación de carga rápida, perfecta para la llegada de turistas a la
                                    ciudad.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </section>

        <section class="container py5 mostrar" id="mostrar-registrarse">
            <?php if (!$loggedin): // Mostrar solo si no está logueado ?>
                <div class="container">
                    <div class="row justify-content-center py-5">
                        <div class="col-lg-6 bg-primary py-4">
                            <div class="card shadow-sm border-0 ">
                                <div class="card-body p-4 ">
                                    <h4 class="mb-4 text-center">Registrarse como Usuario</h4>
                                    <form id="signup-form" class="form-horizontal" role="form" method="POST"
                                        action="index.php">
                                        <input type="hidden" name="action" value="signup">
                                        <div class="row">
                                            <div class="col-md-3"></div>

                                            <div class="form-group has-danger">
                                                <label class="sr-only" for="signup_email">Email:</label>
                                                <div class="input-group mb-2 mr-sm-2 mb-sm-0">
                                                    <div class="input-group-addon" style="width: 2.6rem"><i
                                                            class="fa fa-at"></i></div>
                                                    <input type="text" name="email" class="form-control" id="signup_email"
                                                        placeholder="usuario@correo.com" required autofocus>
                                                </div>

                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-control-feedback">
                                                    <span class="text-danger align-middle signup-error">
                                                        <i class="fa fa-close"></i> <?php echo $error_signup; ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="md-3"></div>

                                            <div class="form-group">
                                                <label class="sr-only" for="signup_password">Contraseña:</label>
                                                <div class="input-group mb-2 mr-sm-2 mb-sm-0">
                                                    <div class="input-group-addon" style="width: 2.6rem"><i
                                                            class="fa fa-key"></i></div>
                                                    <input type="password" name="password" class="form-control"
                                                        id="signup_password" placeholder="Password" required>
                                                </div>

                                            </div>
                                            <div class="md-3">
                                                <div class="form-control-feedback">
                                                    <span class="text-danger align-middle">
                                                        <?php //  TODO: Muestra mensaje de error      } ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row" style="padding-top: 1rem text-primary">
                                            <div class="md-3 mb-3"></div>
                                            <div class="md-6">
                                                <button type="submit" class="btn btn-success bg-warning"><i
                                                        class="fa fa-sign-in"></i> Registrarse
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row justify-content-center py-5">
                        <div class="col-lg-6 bg-warning py-4">
                            <div class="card shadow-sm border-0 ">
                                <div class="card-body p-4 ">
                                    <h4 class="mb-4 text-center">Registrarse como Empresa</h4>
                                    <form class="form-horizontal" role="form" method="POST" action="index.php">
                                        <div class="row">
                                            <p class="text-center">Por favor, contacte con **info@ecopower.com** para el
                                                registro de empresas.</p>
                                        </div>

                                        <div class="row" style="padding-top: 1rem">
                                            <div class="md-3 mb-3"></div>
                                            <div class="md-6">
                                                <button type="button" class="btn btn-success bg-primary" disabled><i
                                                        class="fa fa-sign-in"></i> Registrarse
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal fade" id="modalGracias" tabindex="-1" aria-labelledby="modalGraciasLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalGraciasLabel">¡Registro completado!</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body" id="modalBody">
                                Gracias por registrarte.
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-success bg-primary"
                                    data-bs-dismiss="modal">Aceptar</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: // Si está logueado ?>
                <div class="container py-5 text-center">
                    <h2 class="text-primary">Ya estás registrado.</h2>
                    <p class="lead">Bienvenido de nuevo, <?php echo htmlspecialchars($_SESSION['user']); ?>.</p>
                </div>
            <?php endif; ?>
        </section>

        <section class="container py5 mostrar" id="mostrar-iniciosesion">
            <?php if (!$loggedin): // Mostrar solo si no está logueado ?>
                <div class="container my-5">
                    <form id="login-form" class="form-horizontal" role="form" method="POST" action="index.php">
                        <input type="hidden" name="action" value="login">
                        <div class="row">
                            <div class="col-md-3 mb-2"></div>
                            <div class="col-md-6">
                                <h2 class="text-primary">Introduzca detalles del acceso</h2>
                                <hr>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3"></div>
                            <div class="col-md-6">
                                <div class="form-group has-danger">
                                    <label class="sr-only" for="login_user">Email:</label>
                                    <div class="input-group mb-2 mr-sm-2 mb-sm-0">
                                        <div class="input-group-addon" style="width: 2.6rem"><i class="fa fa-at"></i></div>
                                        <input type="text" name="user" class="form-control" id="login_user"
                                            placeholder="usuario@ejemplo.com" required autofocus>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-control-feedback">
                                    <span class="text-danger align-middle login-error">
                                        <i class="fa fa-close"></i> <?php echo $error_login; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3"></div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="sr-only" for="login_pass">Contraseña:</label>
                                    <div class="input-group mb-2 mr-sm-2 mb-sm-0">
                                        <div class="input-group-addon" style="width: 2.6rem"><i class="fa fa-key"></i></div>
                                        <input type="password" name="pass" class="form-control" id="login_pass"
                                            placeholder="Contraseña" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-control-feedback">
                                    <span class="text-danger align-middle">
                                        <?php // TODO Muestra el mensaje de error ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="row" style="padding-top: 1rem">
                            <div class="col-md-3"></div>
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-success bg-warning"><i class="fa fa-sign-in"></i>
                                    Acceder</button>
                            </div>
                        </div>
                    </form>
                </div>
            <?php else: // Si está logueado ?>
                <div class="container py-5 text-center">
                    <h2 class="text-primary">Has iniciado sesión.</h2>
                    <p class="lead">Ya puedes navegar por nuestra web.</p>
                </div>
            <?php endif; ?>
        </section>

    </main>
    <footer class="bg-dark text-light text-center py-3 mt-auto">
        <h3 class="mb-2 text-primary" style="font-family:'Montserrat',sans-serif; font-weight:700">
            EcoPower
        </h3>
        <p class="mb-3 small text-muted">Carga en movimiento, disfruta el momento.</p>
        <div>
            <a href="#" class="text-light me-3"><i class="bi bi-facebook"></i></a>
            <a href="#" class="text-light me-3"><i class="bi bi-instagram"></i></a>
            <a href="#" class="text-light"><i class="bi bi-twitter"></i></a>
        </div>
        <p class="mt-3 mb-0 small">© 2025 EcoPower</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="./assets/js/jquery.js"></script>
</body>

</html>