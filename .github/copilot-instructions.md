# PrestaShop Module - Alma

## Project Context
PrestaShop module compatible with version 1.7.8.x and later, developed in PHP 7.4+. Native PrestaShop MVC architecture.

Never use an external framework (no standalone Symfony).

## PHP Standards
- PSR-12 strict
- No direct SQL queries: always use Db::getInstance() or ObjectModel
- Prefix all tables: `{$this->table_prefix}my_table_`

## PrestaShop Hooks
- Always use hookExec() and not hookDispatcher directly
- Declare hooks in ModuleInstallerService const HOOK_LIST and add the function hookXxx() in the module main class
- Name hook methods: hookActionXxx / hookDisplayXxx

## File Structure
- Admin controllers: src/Infrastructure/Controller/
- Smarty or Twig views: views/templates/
- Service classes: src/Application/Service/
- Repository classes: src/Infrastructure/Repository/
