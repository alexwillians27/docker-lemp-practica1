-- =====================================================================
--  mi_aventurero.sql · Alta de mi aventurero en el Gremio
-- =====================================================================
--  Impórtalo DESPUÉS de gremio.sql: usa la tabla `aventureros` que crea.
--
--  Credenciales de mi aventurero:
--    usuario    / contraseña
--    alex       / Gremio2026
--
--  La contraseña NUNCA se guarda en claro: solo su hash bcrypt, generado
--  con password_hash('Gremio2026', PASSWORD_DEFAULT).
-- =====================================================================

INSERT INTO aventureros (usuario, nombre, clase, password_hash) VALUES
('alex', 'Alex <el Errante>', 'Explorador', '$2y$10$QC5zIkFInKIYusr9ioyAx.SB5VmGtLotk.IY4fyodgJnCM61PDvty');
