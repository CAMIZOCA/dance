# ADR-012 — Media/contenido versionado y no destructivo

- Estado: Aceptado
- Fecha: 2026-09-07

## Contexto

Reemplazar demostraciones no debe borrar historia ni impedir restaurar una versión previa.

## Decisión

Separar asset lógico, versiones numeradas y variantes físicas. Una versión es actual; reemplazo archiva la anterior; restauración conserva trazabilidad y auditoría. Soft delete/archivo cuando aplique.

## Consecuencias

Más storage y reglas de concurrencia, mitigados con índice único parcial, transacciones y políticas de retención. Los enlaces apuntan al asset o versión explícita. Borrado legal/físico requiere workflow especializado, no overwrite.
