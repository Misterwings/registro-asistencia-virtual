# Manual de usuario

## 1. Presentación

Attendance Record permite crear eventos, compartir un enlace o código QR para que los asistentes registren sus datos y firma, y descargar reportes de asistencia.

El sistema tiene dos experiencias principales:

- **Panel administrativo:** para administradores y organizadores.
- **Formulario público:** para las personas que asisten a un evento.

## 2. Accesos principales

| Función | Dirección de referencia |
| --- | --- |
| Página inicial | `http://localhost:8080/` en local o la URL pública configurada |
| Panel administrativo | `/admin` |
| Registro de un evento | `/evento/{slug}`; normalmente se abre desde el QR |
| Reportes | `/admin/reportes` |

La URL real depende de `APP_URL`. En un ambiente publicado, use el dominio institucional y no una dirección `localhost`.

## 3. Perfiles y permisos

### Administrador/organizador

Puede:

- iniciar sesión en el panel;
- crear y administrar sus eventos;
- ver las asistencias de sus eventos;
- consultar firmas;
- descargar reportes;
- administrar cargos, sedes y motivos;
- activar o desactivar elementos de los catálogos.

Los eventos y asistencias están separados por usuario. Un organizador no debería ver desde el panel los eventos creados por otro administrador.

### Asistente

No necesita cuenta. Solo requiere el enlace o el código QR vigente del evento y un navegador con JavaScript habilitado para capturar la firma.

## 4. Inicio de sesión

1. Abra la página `/admin`.
2. Escriba el correo y la contraseña asignados.
3. Seleccione el botón de ingreso.
4. Al entrar verá el panel `Asistencia` y sus recursos disponibles.

La cuenta debe tener el campo administrativo habilitado. Si aparece un rechazo de acceso aunque la contraseña sea correcta, consulte la sección de solución de problemas.

### Cuenta inicial de instalación

En una instalación recién sembrada, la cuenta inicial es:

```text
Correo:     admin@example.com
Contraseña: admin123
```

Es una credencial de instalación. Cámbiela inmediatamente en cualquier ambiente distinto de desarrollo local.

## 5. Configuración inicial recomendada

Antes de crear el primer evento, revise los catálogos:

1. Abra **Cargos** y confirme que existan las posiciones que podrán seleccionar los asistentes.
2. Abra **Sedes** y confirme las ubicaciones disponibles.
3. Abra **Motivos** y agregue las opciones que quiera sugerir al crear eventos.
4. Desactive las opciones que ya no deban aparecer en nuevos registros.

Desactivar es preferible a eliminar. Así se conserva la referencia de los registros históricos que ya usaron ese cargo o sede.

## 6. Administrar cargos

### Crear un cargo

1. En el menú, abra **Cargos**.
2. Pulse **Crear cargo**.
3. Escriba el nombre del cargo.
4. Mantenga activado **Activo** si debe aparecer en futuros formularios.
5. Guarde el registro.

### Editar o desactivar

1. En la tabla de cargos, localice el registro.
2. Pulse **Editar**.
3. Cambie el nombre o el interruptor **Activo**.
4. Guarde.

Un cargo inactivo no aparece en el formulario público ni en las selecciones administrativas de nuevos registros. Las asistencias históricas pueden seguir mostrando el cargo si el registro no fue eliminado.

### Eliminar

La eliminación es permanente desde la aplicación. Si un cargo ya fue utilizado, desactívelo en vez de borrarlo.


## 7. Administrar sedes

El flujo es equivalente al de cargos:


1. Abra **Sedes**.
2. Use **Crear sede** para agregar una ubicación.
3. Escriba el nombre.
4. Mantenga **Activo** habilitado si debe aparecer en formularios nuevos.
5. Use **Editar** para cambiar el nombre o desactivarla.


## 8. Administrar motivos

### Crear un motivo

1. Abra **Motivos**.
2. Pulse **Crear motivo**.
3. Escriba el nombre del motivo.
4. Mantenga **Activo** habilitado.
5. Guarde.

El nombre de un motivo no puede repetirse. Los motivos activos aparecen como sugerencias al escribir el motivo de un evento, pero el organizador también puede escribir un texto personalizado.

## 9. Crear un evento

### Paso 1: abrir el formulario

1. Entre al panel administrativo.
2. Abra **Eventos**.
3. Pulse **Crear evento**.

### Paso 2: completar la información básica


| Campo | Instrucción |
| --- | --- |
| **Fecha** | Día en que se realiza el evento. |
| **Tema** | Nombre o asunto que identificará el evento. Se mostrará a los asistentes. |
| **Hora de inicio** | Hora programada de comienzo. |
| **Hora final** | Hora programada de finalización. |
| **Lugar** | Salón, sede, plataforma o ubicación del evento. |
| **Motivo** | Seleccione una sugerencia del listado o escriba un motivo personalizado. |
| **Cargo de quien dirige** | Seleccione una sugerencia o escriba el cargo/área correspondiente. |
| **Archivo adjunto** | Opcional. Puede cargar PDF, JPG, JPEG, PNG, GIF o WEBP de hasta 10 MB. |

### Paso 3: definir vencimiento

El interruptor **Activar vencimiento del enlace público** controla cuánto tiempo se puede usar el QR o el enlace:

- Desactivado: el enlace permanece disponible.



- Activado: aparece **Fecha de vencimiento**, que se vuelve obligatoria.
- El enlace funciona durante todo el día indicado. Deja de funcionar al comenzar el día siguiente en la zona horaria de Bogotá.



### Paso 4: guardar

1. Revise los datos.
2. Pulse el botón de guardar del formulario.
3. El sistema genera automáticamente un enlace único.
4. El usuario que creó el evento queda registrado como quien lo dirige.



## 10. Ver y compartir un evento

1. En **Eventos**, pulse **Ver** en el evento correspondiente.
2. Revise fecha, horario, lugar, motivo, vencimiento y cantidad de asistencias.
3. En la sección **Código QR**, visualice el código.
4. Use el enlace mostrado para abrir o copiar el formulario público.
5. Comparta el QR o el enlace con los asistentes.





## 11. Editar un evento

1. En **Eventos**, pulse **Editar**.
2. Modifique los datos necesarios.
3. Para ampliar o cerrar el acceso, cambie el interruptor o la fecha de vencimiento.
4. Guarde los cambios.

El enlace existente conserva el mismo slug. No es necesario distribuir un QR nuevo por cambiar el tema, el lugar o el vencimiento.

### Antes de cerrar un evento

Para impedir nuevos registros:

1. Edite el evento.
2. Active el vencimiento.




3. Establezca una fecha que ya haya pasado, o utilice una fecha de cierre adecuada y espere a que termine el día.
4. Guarde.





## 12. Registro de un asistente

### Abrir el formulario

El asistente puede:

- escanear el código QR del evento; o
- abrir la URL compartida por el organizador.


### Completar datos

1. Revise la información del evento.
2. Escriba sus **Nombres**.
3. Escriba sus **Apellidos**.
4. Escriba el **Número de Identificación**.
5. Seleccione el **Cargo**.
6. Seleccione la **Sede**.

La identificación debe contener solo números. Se aceptan puntos, guiones o espacios entre los dígitos, pero el sistema los elimina antes de guardar. Por ejemplo, `1.149.303.038` se almacena como `1149303038`.

No use letras. Una identificación con letras será rechazada.

### Registrar la firma

Hay dos alternativas:

**Firmar con el dedo o el mouse**

1. Dibuje la firma dentro del recuadro.

2. Compruebe que aparezca el mensaje de firma dibujada correctamente.








**Generar una firma con el nombre**

1. Complete primero nombres y apellidos.
2. Pulse **Usar nombre como firma**.
3. El sistema dibuja una representación visual del nombre.

Para borrar y empezar de nuevo, pulse **Limpiar firma**. La firma es obligatoria; el botón no enviará el formulario si el recuadro está vacío.

### Enviar

1. Pulse **Registrar Asistencia**.
2. Espere mientras el botón muestra `Registrando...`.



3. Si todo es correcto, aparecerá `¡Asistencia registrada exitosamente!`.





## 13. Adjuntos del evento

Si el organizador cargó un PDF o una imagen válida, el formulario público mostrará el enlace **Ver archivo adjunto**.

Si no aparece:

- el evento puede no tener adjunto;
- el archivo puede tener una extensión no visualizable;
- puede faltar el enlace de almacenamiento del servidor;
- el archivo puede haber sido eliminado del almacenamiento.



## 14. Consultar asistencias

1. Entre al panel.
2. Abra **Asistencias**.
3. Revise las columnas:
   - Evento.
   - Nombre.
   - Identificación.
   - Cargo.
   - Sede.
   - Fecha de registro.
   - Vista previa de firma.
4. Utilice las búsquedas disponibles por evento, nombre o identificación.
5. Pulse **Ver detalles** para abrir la información completa y ampliar la firma.



### Eliminar asistencias

1. Seleccione uno o varios registros.
2. Abra **Acciones masivas**.
3. Pulse **Eliminar seleccionados**.
4. Confirme la eliminación.

La acción es permanente en la aplicación. No hay papelera ni recuperación integrada.

## 15. Descargar reportes

### Abrir el formulario

Puede abrirlo de dos formas:

1. Desde **Asistencias**, pulse **Descargar Reporte**.
2. Abra directamente la ruta `/admin/reportes`.

### Seleccionar evento

En **Evento**, seleccione uno de sus eventos. El listado muestra tema y fecha.









### Filtrar por sedes

El filtro **Sedes** es opcional:

- marque una sede para incluir solo sus asistencias;
- marque varias para combinar sedes;
- pulse **Seleccionar todas** para marcar todas las opciones;
- pulse **Limpiar selección** para quitar todas las marcas;
- si no marca ninguna, el reporte incluirá todas las sedes.




La pantalla puede listar sedes inactivas. La selección filtra por los registros existentes, independientemente de que la sede esté actualmente activa.

### Elegir formato

Seleccione una opción:

- **Hoja de cálculo**: archivo `.xlsx`, útil para análisis y filtros posteriores.
- **Documento PDF**: formato institucional para imprimir o archivar.

Pulse **Descargar reporte**.

### Contenido XLSX

Incluye:

- nombre completo;
- número de identificación;
- cargo;
- sede;
- fecha de registro.

No incluye la imagen de la firma.

### Contenido PDF

Incluye:

- encabezado institucional;
- información del evento;
- número consecutivo;
- nombre completo;
- identificación;
- cargo;
- sede;
- firma manuscrita o generada;
- aviso de privacidad.

Los registros se ordenan por fecha de registro. El formato puede recortar textos muy largos para conservar el diseño de la tabla.










## 16. Gestión de usuarios para soporte técnico

No hay pantalla de usuarios. Las operaciones se hacen desde el contenedor `app`.

### Crear un usuario

Desde la raíz del proyecto:

```bash
docker compose run --rm app php artisan db:seed --class=UserSeeder
```


El comando solicita:

1. nombre;
2. correo electrónico;
3. contraseña de mínimo 8 caracteres;
4. confirmación de si es administrador.

Responder `Sí` en la última pregunta habilita el acceso al panel.

### Crear el administrador inicial

```bash
docker compose run --rm app php artisan db:seed --class=AdminUserSeeder
```



### Activar una cuenta existente

```bash
docker compose run --rm app php artisan tinker --execute='App\Models\User::where("email", "usuario@example.com")->update(["is_admin" => true]);'
```



### Restablecer contraseña y activar acceso

Usar una contraseña temporal robusta y comunicarla por un canal seguro:

```bash
docker compose run --rm app php artisan tinker --execute='App\Models\User::where("email", "usuario@example.com")->firstOrFail()->forceFill(["is_admin" => true, "password" => "ReemplazarConUnaClaveSegura"])->save();'
```



El modelo aplica el hash configurado al guardar la contraseña. Cambiar la contraseña temporal después del primer acceso si existe un mecanismo de cambio disponible en el entorno.

## 17. Solución de problemas

| Situación | Causa probable | Acción |
| --- | --- | --- |
| `/admin` rechaza el acceso | El usuario no existe, la contraseña es incorrecta o `is_admin` es `false`. | Verificar la cuenta con `UserSeeder`/Tinker y usar una contraseña de al menos 8 caracteres. |
| El panel carga sin estilos | Assets no compilados o manifest ausente. | Ejecutar `pnpm run build` en `src` y limpiar caché de configuración si es necesario. |
| El QR abre `localhost` en otro teléfono | `APP_URL` apunta a localhost o a una dirección no accesible. | Configurar el dominio/IP público en `src/.env`, limpiar caché y volver a probar. |
| El evento muestra “Enlace vencido” | La fecha de vencimiento pasó o está activado sin fecha. | Editar el evento y revisar `Activar vencimiento del enlace público` y `Fecha de vencimiento`. |
| El evento devuelve 404 | El slug está incompleto, fue alterado o la URL no corresponde al evento. | Abrir el enlace desde la vista del evento y copiarlo de nuevo. |
| No aparecen cargos o sedes | No hay datos sembrados o todos están inactivos. | Revisar los catálogos y activar las opciones necesarias. |
| La identificación es rechazada | Contiene letras o separadores que no están entre dígitos. | Escribir solo números; los puntos, guiones y espacios internos se normalizan automáticamente. |
| El sistema dice que ya existe la asistencia | La identificación ya fue registrada en ese evento, incluso con otro formato de separadores. | Confirmar el evento y consultar la lista de asistencias. |
| No se puede enviar la firma | El lienzo está vacío o el navegador no ejecutó JavaScript. | Dibujar la firma o usar `Usar nombre como firma`; revisar que JavaScript esté habilitado. |
| El adjunto no abre | Falta `storage:link`, el volumen no está montado o el archivo no existe. | Verificar `php artisan storage:link`, permisos y el volumen de almacenamiento. |
| No aparece un evento en reportes | El evento pertenece a otro usuario o no se ha creado. | Ingresar con el administrador propietario del evento. |
| Un reporte no descarga | Faltan evento/formato o se seleccionó una sede inexistente. | Completar los campos y revisar los mensajes de validación. |
| Al borrar un evento desaparecen asistencias | Es el comportamiento de la relación `cascadeOnDelete`. | Restaurar desde una copia de seguridad si fue accidental. |

## 18. Lista de comprobación para un evento

### Antes de compartir

- [ ] El evento tiene fecha, horario, tema, lugar, motivo y cargo de quien dirige.
- [ ] Los cargos y sedes necesarios están activos.
- [ ] El adjunto, si existe, se puede abrir.
- [ ] La fecha de vencimiento es correcta o el vencimiento está desactivado intencionalmente.
- [ ] El enlace no contiene `localhost` cuando se usará desde otros dispositivos.
- [ ] Se probó el QR desde un teléfono.
- [ ] Se completó un registro de prueba y se confirmó que aparece en Asistencias.

### Durante el evento

- [ ] El asistente usa el QR o enlace correspondiente.
- [ ] Se verifica el mensaje de registro exitoso.
- [ ] Si una persona recibe aviso de duplicado, se consulta primero la lista de asistencias.
- [ ] Se evita borrar o desactivar catálogos mientras los asistentes están registrándose, salvo necesidad operativa.

### Después del evento

- [ ] Se revisa el total de asistencias.
- [ ] Se inspeccionan firmas o registros con errores.
- [ ] Se descarga el XLSX para análisis.
- [ ] Se descarga el PDF para archivo institucional.
- [ ] Se define la fecha de cierre del enlace si no debe quedar abierto.
- [ ] Se respalda la información según la política institucional.

## 19. Privacidad y uso responsable

El formulario informa que los datos se utilizarán para fines laborales y administrativos y que serán tratados de forma confidencial conforme a la normativa indicada por la organización.

Los administradores deben:

- compartir el enlace solo con las personas convocadas;
- no publicar reportes con identificaciones en canales abiertos;
- restringir el acceso a PDF, XLSX, firmas y copias de seguridad;
- conservar los datos solo durante el periodo definido por la organización;
- reportar cualquier pérdida de acceso, archivo o credencial.

## 20. Resumen rápido

```text
1. Ingresar a /admin.
2. Revisar Cargos, Sedes y Motivos.
3. Crear un Evento.
4. Abrir el evento y compartir el QR/enlace.
5. Los asistentes completan datos y firma en /evento/{slug}.
6. Revisar Asistencias.
7. Descargar Reporte en XLSX o PDF.
8. Cerrar el enlace mediante vencimiento si corresponde.
```
