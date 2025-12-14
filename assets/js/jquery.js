/**
 * @title: Proyecto integrador Ev01 - Lógica SPA con jQuery
 * @description: Maneja la navegación sin recarga de página (SPA) y el envío asíncrono
 * de formularios de Login/Signup/Logout.
 *
 * @author  ecopower@gmail.com
 */

$(document).ready(function () {
  // -----------------------------------------------------------
  // FUNCIONALIDAD BÁSICA DE NAVEGACIÓN SPA
  // -----------------------------------------------------------

  // Ocultar todas las secciones al inicio para asegurar que solo se muestre la deseada
  $('.mostrar').hide();

  // Función para mostrar la sección objetivo y manejar el historial del navegador
  function navigate(targetId) {
    $('.mostrar').hide(); // Ocultar todas las secciones
    $(targetId).show(); // Mostrar la sección objetivo

    // Actualizar la URL sin recargar la página (para permitir la navegación con hash)
    if (history.pushState) {
      // El primer argumento (null) es para el estado, el segundo (null) para el título, y el tercero para la URL
      history.pushState(null, null, targetId);
    }
  }

  // Inicializar la vista: Mostrar el index o la sección definida en el hash de la URL
  const initialHash = window.location.hash;
  if (initialHash) {
    navigate(initialHash);
  } else {
    $('#mostrar-index').show(); // Mostrar el index por defecto
  }

  // 1. Manejo de la Navegación (Botones en la barra de navegación y el index)
  // Se aplica a elementos con clase 'boton-nav' o con el atributo 'data-target'
  $(document).on('click', '.boton-nav, [data-target]', function (e) {
    e.preventDefault();
    const targetId = $(this).data('target');
    if (targetId) {
      navigate(targetId);
    }
  });

  // Manejar la navegación del historial (flechas atrás/adelante del navegador)
  window.onpopstate = function (event) {
    const targetId = window.location.hash || '#mostrar-index';
    navigate(targetId);
  };
  
  $("#buscarEvento").on("keyup", function () {
    const texto = $(this).val().toLowerCase();

    $(".evento").each(function () {
      const contenido = $(this).text().toLowerCase();
      $(this).toggle(contenido.includes(texto));
    });
  });

  // -----------------------------------------------------------
  // MANEJO DE SESIONES ASÍNCRONAS (AJAX)
  // -----------------------------------------------------------

  // 2. Manejo de la sesión de SALIR (Logout) vía AJAX
  $(document).on('click', '#logout-btn', function (e) {
    e.preventDefault();
    $.ajax({
      url: 'index.php?action=logout', // Se usa GET para la acción de salir
      type: 'GET',
      success: function (response) {
        // Éxito: El servidor ha destruido la sesión. 
        // Recargar la página para que el PHP reconstruya la barra de navegación 
        // con las opciones de "Invitado".
        window.location.reload();
      },
      error: function () {
        alert('Error al cerrar la sesión.');
      }
    });
  });

  // 3. Manejo del formulario de INICIAR SESIÓN (Login) vía AJAX
  $('#login-form').on('submit', function (e) {
    e.preventDefault();
    const form = $(this);
    const errorDisplay = form.find('.login-error');
    errorDisplay.html(''); // Limpiar errores anteriores

    $.ajax({
      url: form.attr('action'),
      type: 'POST',
      data: form.serialize(),
      dataType: 'json',
      beforeSend: function () {
        // Opcional: Deshabilitar el botón para evitar múltiples envíos
        form.find('button[type="submit"]').prop('disabled', true);
      },
      success: function (response) {
        if (response.success) {
          // Éxito: El servidor ha gestionado la sesión. Recargar la interfaz completa.
          window.location.reload();
        } else {
          // Fallo: Mostrar el mensaje de error devuelto por PHP
          errorDisplay.html('<i class="fa fa-close"></i> ' + response.message);
        }
      },
      error: function () {
        errorDisplay.html('<i class="fa fa-close"></i> Error de comunicación con el servidor.');
      },
      complete: function () {
        // Habilitar el botón de nuevo
        form.find('button[type="submit"]').prop('disabled', false);
      }
    });
  });

  // 4. Manejo del formulario de REGISTRARSE (Signup) vía AJAX
  $('#signup-form').on('submit', function (e) {
      e.preventDefault();
      const form = $(this);
      const emailInput = form.find('#signup_email'); // Obtener el campo de email
      const errorDisplay = form.find('.signup-error');
      const modalBody = $('#modalBody');
      errorDisplay.html(''); // Limpiar errores anteriores
      
      // --- VALIDACIÓN DE FORMATO DE EMAIL ---
      const email = emailInput.val();
      if (email.indexOf('@') === -1) {
          const validationMessage = 'El campo de Email debe contener al menos un "@".';
          errorDisplay.html('<i class="fa fa-close"></i> ' + validationMessage);
          emailInput.focus();
          return; // Detiene el envío del formulario AJAX
      }
      // ----------------------------------------------

      $.ajax({
          url: form.attr('action'),
          type: 'POST',
          data: form.serialize(),
          dataType: 'json',
          beforeSend: function() {
              form.find('button[type="submit"]').prop('disabled', true);
          },
          success: function (response) {
              if (response.success) {
                  // Éxito en el registro: Mostrar modal y recargar. 
                  // Esto logra el objetivo 1: Registrar y loguear.
                  modalBody.text(response.message);
                  const successModal = new bootstrap.Modal(document.getElementById('modalGracias'));
                  successModal.show();
                  
                  // Recargar la página al cerrar el modal para actualizar la barra de navegación
                  document.getElementById('modalGracias').addEventListener('hidden.bs.modal', function () {
                      window.location.reload();
                  });

              } else {
                  // Fallo o Usuario Existente
                  if (response.redirect === '#mostrar-iniciosesion') {
                      // Si el usuario existe, navegar al Inicio de Sesión (Objetivo 2)
                      alert(response.message); // Mostrar el mensaje de que ya existe
                      navigate(response.redirect); // Navegar a la sección de Login
                  } else {
                      // Mostrar error de validación normal (campos vacíos, etc.)
                      errorDisplay.html('<i class="fa fa-close"></i> ' + response.message);
                  }
              }
          },
          error: function () {
              errorDisplay.html('<i class="fa fa-close"></i> Error de comunicación con el servidor.');
          },
          complete: function() {
              form.find('button[type="submit"]').prop('disabled', false);
          }
      });
  });
});