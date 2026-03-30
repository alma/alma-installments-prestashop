---
applyTo: "**/*.php"
---
# PrestaShop PHP Rules

- Always inherit from ModuleFrontController or ModuleAdminController
- Validate inputs with $this->toolsProxy->getValue() and inject its dependency into the class constructor as well as the service.yml file
- Sanitize with pSQL() before any SQL query
- Use $this->trans() for all translatable strings
