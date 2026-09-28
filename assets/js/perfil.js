// assets/js/perfil.js
document.addEventListener('DOMContentLoaded', () => {
  cargarPerfil();
  document.getElementById('formPerfil').addEventListener('submit', manejarSubmitPerfil);
});

// Carga el perfil del usuario.
async function cargarPerfil() {
  const resultado = await llamarApi('usuarios.php', 'GET');

  if (!resultado.exito) {
    window.location.href = 'login.php';
    return;
  }

  const usuario = resultado.datos;
  document.getElementById('nombreCompletoPerfil').textContent = `${usuario.nombre} ${usuario.apellido}`;
  document.getElementById('correoPerfil').textContent = usuario.correo;
  document.getElementById('nombrePerfil').value = usuario.nombre;
  document.getElementById('apellidoPerfil').value = usuario.apellido;
  document.getElementById('telefonoPerfil').value = usuario.telefono || '';
  document.getElementById('direccionPerfil').value = usuario.direccion || '';
}

// Guarda cambios del perfil.
async function manejarSubmitPerfil(evento) {
  evento.preventDefault();
  const formulario = evento.target;

  const datos = {
    nombre: formulario.nombre.value.trim(),
    apellido: formulario.apellido.value.trim(),
    telefono: formulario.telefono.value.trim() || null,
    direccion: formulario.direccion.value.trim() || null
  };

  const resultado = await llamarApi('usuarios.php', 'PUT', datos);

  const zonaMensaje = document.getElementById('mensajePerfil');
  zonaMensaje.innerHTML = '';
  const alerta = document.createElement('div');
  alerta.className = `alert ${resultado.exito ? 'alert-success' : 'alert-danger'} mb-0`;
  alerta.textContent = resultado.mensaje;
  zonaMensaje.appendChild(alerta);

  if (resultado.exito) {
    document.getElementById('nombreCompletoPerfil').textContent = `${datos.nombre} ${datos.apellido}`;
  }
}