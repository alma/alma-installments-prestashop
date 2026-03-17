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
And Extend the Controller class with `FrameworkBundleAdminController` to use the `render` function to render the template.
And return the rendered template with the `render` function, and pass the template path and the variables to the template.
And create the template in the `views/templates/admin` folder of your module, and name it like `template.html.twig`.
Then, you need to define the route in the `routes.yml` file.
#### Route
```yaml
alma_settings:
  path: /alma/settings
  methods: [GET, POST]
  defaults:
    _controller: 'PrestaShop\Module\Alma\Infrastructure\Controller\SettingsController::indexAction'
    _legacy_controller: 'AdminAlmaSettings'
    _legacy_link: 'AdminAlmaSettings'
```

#### Controller
```php
class SampleController extends FrameworkBundleAdminController

return $this->render(
    '@Modules/alma/views/templates/admin/template.html.twig',
    [
        'title' => 'My title',
        'key' => 'Value',
    ]
);
```

#### Twig
Create twig file with layout
```html
{% extends '@PrestaShop/Admin/layout.html.twig' %}

{% block content %}
    {% if errors is defined and errors|length > 0 %}
        <div class="alert alert-danger">
            <ul>
                {% for error in errors %}
                    <li>{{ error }}</li>
                {% endfor %}
            </ul>
        </div>
    {% endif %}

    <div class="card">
        <h3 class="card-header">
            {{ title }}
        </h3>

        <div class="card-body">
            <p>Welcode to the new controller 🚀</p>
        </div>
    </div>
{% endblock %}
```

#### In the tabs array
Add the `route_name`
```php
[
    [...]
    'route_name' => 'alma_settings',
    [...]
]
```
