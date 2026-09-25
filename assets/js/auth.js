// assets/js/auth.js
document.addEventListener('DOMContentLoaded', () => {
  verificarSesion();
  mostrarMensajeSiVieneDeRegistro();

  const formLogin = document.getElementById('formLogin');
  if (formLogin) {
    formLogin.addEventListener('submit', manejarSubmitLogin);
  }

  const formRegistro = document.getElementById('formRegistro');
  if (formRegistro) {
    formRegistro.addEventListener('submit', manejarSubmitRegistro);
  }

  const btnCerrarSesion = document.getElementById('btnCerrarSesion');
  if (btnCerrarSesion) {
    btnCerrarSesion.addEventListener('click', manejarLogout);
  }
});

// Pregunta al servidor si hay sesión activa y ajusta el navbar según la
// respuesta. Un 401 aquí es el caso normal de un visitante: no es un error,
// solo significa "muestra la zona de invitado".
async function verificarSesion() {
  // api/auth.php?accion=verificar-sesion → app/controladores/AuthController.php
  const resultado = await llamarApi('auth.php?accion=verificar-sesion', 'POST');

  const zonaInvitado = document.getElementById('zonaSesionInvitado');
  const zonaUsuario = document.getElementById('zonaSesionUsuario');
  const nombreUsuario = document.getElementById('nombreUsuario');
  const enlaceAdmin = document.getElementById('enlaceAdmin');

  if (resultado.exito) {
    zonaInvitado.classList.add('d-none');
    zonaUsuario.classList.remove('d-none');
    nombreUsuario.textContent = resultado.datos.nombre;
    enlaceAdmin.classList.toggle('d-none', resultado.datos.tipo_usuario !== 'administrador');
  } else {
    zonaUsuario.classList.add('d-none');
    zonaInvitado.classList.remove('d-none');
  }
}

// Tras un registro exitoso redirigimos a login.php?registrado=1. Esta
// función solo lee ese parámetro de la URL para mostrar el mensaje una vez;
// no guarda nada en el navegador (ni localStorage ni sessionStorage).
function mostrarMensajeSiVieneDeRegistro() {
  const parametros = new URLSearchParams(window.location.search);
  if (parametros.get('registrado') === '1') {
    mostrarMensaje('success', 'Cuenta creada correctamente. Ya puedes iniciar sesión.');
  }
}

async function manejarSubmitLogin(evento) {
  evento.preventDefault();
  const formulario = evento.target;

  const correo = formulario.correo.value.trim();
  const password = formulario.password.value;

  // api/auth.php?accion=login → app/controladores/AuthController.php
  const resultado = await llamarApi('auth.php?accion=login', 'POST', { correo, password });

  if (!resultado.exito) {
    // Mensaje genérico tal cual lo manda el servidor: nunca decimos aquí
    // si falló el correo o la contraseña.
    mostrarMensaje('danger', resultado.mensaje);
    return;
  }

  // La sesión ya quedó abierta en el servidor (cookie de PHP). No se
  // guarda el usuario en el cliente; simplemente navegamos al inicio.
  window.location.href = '../index.php';
}

async function manejarSubmitRegistro(evento) {
  evento.preventDefault();
  const formulario = evento.target;

  const datos = {
    nombre: formulario.nombre.value.trim(),
    apellido: formulario.apellido.value.trim(),
    correo: formulario.correo.value.trim(),
    password: formulario.password.value,
    // Campos opcionales: si están vacíos se envía null, nunca "".
    telefono: formulario.telefono.value.trim() || null,
    direccion: formulario.direccion.value.trim() || null
  };

  // api/auth.php?accion=registro → app/controladores/AuthController.php
  const resultado = await llamarApi('auth.php?accion=registro', 'POST', datos);

  if (!resultado.exito) {
    mostrarMensaje('danger', resultado.mensaje);
    return;
  }

  // El registro NO inicia sesión: se redirige a login con el aviso de éxito.
  window.location.href = 'login.php?registrado=1';
}

async function manejarLogout() {
  // api/auth.php?accion=logout → app/controladores/AuthController.php
  await llamarApi('auth.php?accion=logout', 'POST');
  await verificarSesion();
}

// Pinta una alerta de Bootstrap en #zonaMensajes. El texto SIEMPRE se
// asigna con textContent (nunca innerHTML), porque ese texto puede venir
// de la API y no debemos confiar en que no traiga HTML/JS malicioso.
function mostrarMensaje(tipo, texto) {
  const zonaMensajes = document.getElementById('zonaMensajes');
  zonaMensajes.innerHTML = ''; // limpiamos el contenedor, no el texto del usuario

  const alerta = document.createElement('div');
  alerta.className = `alert alert-${tipo} alert-dismissible fade show`;
  alerta.setAttribute('role', 'alert');

  const mensaje = document.createElement('span');
  mensaje.textContent = texto;
  alerta.appendChild(mensaje);

  const botonCerrar = document.createElement('button');
  botonCerrar.type = 'button';
  botonCerrar.className = 'btn-close';
  botonCerrar.setAttribute('data-bs-dismiss', 'alert');
  botonCerrar.setAttribute('aria-label', 'Cerrar');
  alerta.appendChild(botonCerrar);

  zonaMensajes.appendChild(alerta);
}