# ADR-002 — Laravel para backend

- Estado: Aceptado
- Fecha: 2026-09-07

## Contexto

Se requiere API productiva, autenticación/autorización, colas, jobs, migraciones, validación y ecosistema PHP maduro.

## Decisión

Usar Laravel en una release estable de producción, fijada por lockfile al integrar, como monolito modular/API. Casos de uso y contratos separan dominio de framework/proveedores.

## Consecuencias

Entrega inicial simple y convenciones comunes; exige disciplina para evitar controladores/modelos acoplados. Upgrade de major se planifica y prueba. La versión exacta se documenta tras scaffold validado.
