Twig Template documentation
===================================

## Context
The admin route is used to define the URL of the page in the back office.
It is defined in the `AdminController` class of the module.
And how we use the twig template to display the content of the page.

## Link to the documentation
### Symfony System
[Admin route](https://devdocs.prestashop-project.org/1.7/modules/concepts/controllers/admin-controllers/)

### Legacy System
[Admin route](https://devdocs.prestashop-project.org/1.7/modules/concepts/controllers/admin-controllers/tabs/)

## Add route
### Symfony System
To create a route for the admin page, you need to create a new class in the Infrastructure\Controller\Admin namespace of your module, and extend the `FrameworkBundleAdminController` class.
And add a function with suffix Action like `indexAction` to define the content of the page.
And return the rendered template with the `render` function, and pass the template path and the variables to the template.
And create the template in the `views/templates/admin` folder of your module, and name it like `template.html.twig`.
```php
return $this->render(
    '@Modules/alma/views/templates/admin/template.html.twig',
    [
        'title' => 'My title',
        'key' => 'Value',
    ]
);
```
Then, you need to define the route in the `routes.yml` file.
