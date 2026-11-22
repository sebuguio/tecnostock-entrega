/**
 * Validaciones del lado del cliente para TecnoStock.
 * Complementa (sin reemplazar) las validaciones del servidor en PHP.
 */
document.addEventListener('DOMContentLoaded', () => {
    const formularios = document.querySelectorAll('form[data-validar="true"]');

    formularios.forEach((form) => {
        form.addEventListener('submit', (evento) => {
            let valido = true;
            const mensajes = [];

            // Limpiar marcas previas
            form.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));

            // 1. Campos obligatorios de texto/select
            form.querySelectorAll('[required]').forEach((campo) => {
                if (campo.value.trim() === '') {
                    campo.classList.add('is-invalid');
                    valido = false;
                    mensajes.push(`El campo "${campo.dataset.etiqueta || campo.name}" es obligatorio.`);
                }
            });

            // 2. Validar código de producto mínimo 3 caracteres sin espacios internos
            const campoCodigo = form.querySelector('input[name="codigo"]');
            if (campoCodigo && !campoCodigo.readOnly) {
                const codigo = campoCodigo.value.trim();
                if (codigo.length < 3 || /\s/.test(codigo)) {
                    campoCodigo.classList.add('is-invalid');
                    valido = false;
                    mensajes.push('El código debe tener al menos 3 caracteres y sin espacios.');
                }
            }

            // 3. Validar números no negativos (o >= min especificado)
            form.querySelectorAll('input[type="number"]').forEach((campoNum) => {
                const valor = Number(campoNum.value);
                const minimo = campoNum.hasAttribute('min') ? Number(campoNum.getAttribute('min')) : 0;

                if (campoNum.value.trim() === '' || Number.isNaN(valor) || valor < minimo) {
                    campoNum.classList.add('is-invalid');
                    valido = false;
                    mensajes.push(`"${campoNum.dataset.etiqueta || campoNum.name}" debe ser mayor o igual a ${minimo}.`);
                }
            });

            // 4. Validar contraseña mínima en formulario de usuarios
            const campoClave = form.querySelector('input[name="clave_nueva"]');
            if (campoClave && campoClave.value.length > 0 && campoClave.value.length < 6) {
                campoClave.classList.add('is-invalid');
                valido = false;
                mensajes.push('La contraseña debe tener al menos 6 caracteres.');
            }

            if (!valido) {
                evento.preventDefault();
                evento.stopPropagation();

                let alertaBox = form.querySelector('.alerta-js');
                if (!alertaBox) {
                    alertaBox = document.createElement('div');
                    alertaBox.className = 'alert alert-danger alerta-js small fw-bold';
                    form.prepend(alertaBox);
                }
                alertaBox.innerHTML = '<i class="bi bi-exclamation-octagon-fill me-1"></i> Corrige los siguientes campos:<ul class="mb-0 mt-1">' +
                    mensajes.map((m) => `<li>${m}</li>`).join('') + '</ul>';
            }
        });
    });
});
