<?php
session_start();

// 1. SEGURIDAD: Si no es empleado, expulsar al index.
if (!isset($_SESSION['user']) || !isset($_SESSION['rol']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: index.php");
    exit();
}

// Lógica de logout específica para la intranet
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <title>Intranet Empleados</title>
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">EcoPower | Panel de Empleado</span>
            <span class="navbar-text text-white">
                Hola, <?php echo htmlspecialchars($_SESSION['user']); ?> 
                <a href="dffourfiun.php?action=logout" class="btn btn-sm btn-danger ms-3">Cerrar Sesión</a>
            </span>
        </div>
    </nav>

    <div class="container">
        <div class="alert alert-info">
            <h4>Bienvenido a la Intranet</h4>
            <p>Aquí puedes gestionar las incidencias y ver el estado de las baterías.</p>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-header">Incidencias Activas</div>
                    <div class="card-body">
                        <h5 class="card-title">3 Pendientes</h5>
                        <p class="card-text">Revisar estación UPNA.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-success mb-3">
                    <div class="card-header">Baterías en Circulación</div>
                    <div class="card-body">
                        <h5 class="card-title">150 Unidades</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-5 col-md-4 mb-5">
    
    <h4 class="mb-3">Acceso a los servicios internos de la empresa:</h4>

    <div class="card shadow-sm overflow-hidden" style="max-width: 600px; border-radius: 10px;">
        <table class="table mb-0"> <tbody>
                
                <tr>
                    <td class="align-middle ps-4">Almacenamiento en la nube</td>
                    <td class="text-end pe-3 py-3">
                        <a href="http://localhost:8081" class="btn btn-secondary btn-outline-primary px-4" style="background-color: #c0c0c0; border: none; color: black;">
                            NextCloud
                        </a>
                    </td>
                </tr>

                <tr class="align-middle ps-4">
                    <td class="align-middle ps-4">Sistema de incidencias</td>
                    <td class="text-end pe-3 py-3">
                        <a href="http://localhost:3000" class="btn btn-secondary px-4 btn-outline-primary" style="background-color: #c0c0c0; border: none; color: black;">
                            Peppermint
                        </a>
                    </td>
                </tr>

                <tr>
                    <td class="align-middle ps-4">Sistema de Gestión de Contenidos.</td>
                    <td class="text-end pe-3 py-3">
                        <a href="http://localhost:8082" class="btn btn-secondary px-4" style="background-color: #c0c0c0; border: none; color: black;">
                            Wordpress
                        </a>
                    </td>
                </tr>

                <tr>
                    <td class="align-middle ps-4">Plataforma de Colaboración</td>
                    <td class="text-end pe-3 py-3">
                        <a href="http://localhost:8083" class="btn btn-secondary px-4" style="background-color: #c0c0c0; border: none; color: black;">
                            MediaWiki 
                        </a>
                    </td>
                </tr>

            </tbody>
        </table>
    </div>

</div>
 <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="./assets/js/jquery.js"></script>
</body>
</html>