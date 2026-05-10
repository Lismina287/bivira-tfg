# BiViR@ — Biblioteca Virtual y Recursos Académicos para FP

> Proyecto Integrador ASIR · IES Doctor Fleming · 2026  
> Azucena Beltrán Arrebola

Repositorio colaborativo de materiales de estudio para estudiantes  
de Formación Profesional de Educastur. Basado en Moodle 4.3,  
desplegado con Docker sobre Ubuntu Server 22.04 LTS.

---

## Stack tecnológico

| Componente | Tecnología |
|------------|-----------|
| Sistema operativo | Ubuntu Server 22.04 LTS |
| Contenerización | Docker Engine 29 + Compose v5 |
| Plataforma LMS | Moodle 4.3 (MOODLE_403_STABLE) |
| Servidor web | Apache 2.4 + PHP 8.2 |
| Base de datos | MariaDB 10.6 |
| Seguridad | OpenSSL + UFW |

## Estructura del repositorio

```
bivira-tfg/
│
├── README.md
├── .gitignore
│
├── moodle/
│   ├── Dockerfile
│   ├── docker-compose.yml
│   ├── config.php.example
│   ├── bivira-ssl.conf
│   ├── ssl-entrypoint.sh
│   │
│   └── bivira_modulos/
│       ├── block_bivira_modulos.php
│       ├── action.php
│       ├── request.php
│       ├── version.php
│       ├── db/
│       │   ├── install.xml
│       │   └── upgrade.php
│       └── lang/
│           ├── en/
│           │   └── block_bivira_modulos.php
│           └── es/
│               └── block_bivira_modulos.php
│
├── scripts/
│   └── backup-db.sh
│
└── docs/
    └── BiViR@_TFG_Azucena_Beltran.pdf


## Cómo desplegar

1. Clonar el repositorio
2. Copiar config.php.example → config.php y rellenar credenciales
3. Copiar docker-compose.yml.example → docker-compose.yml
4. docker compose up -d --build
5. Acceder a http://localhost:8080 para completar la instalación

## Documentación

El TFG completo está disponible en /docs/

## Licencia

GNU GPL v3 — igual que Moodle
