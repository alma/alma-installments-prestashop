Twig Template documentation
===================================

## Context
The form builder is used to create forms in the back office.
It is defined in the `AdminController` class of the module. And how we use the form builder to create forms in the back office.

## Link to the documentation
### Legacy System
[Fields of Form builder](https://devdocs.prestashop-project.org/1.7/development/components/helpers/helperform/#fields)

## Create a form
To create a form, you need to add a new Class in the `src/Infrastructure/Form` folder who contains TITLE and FIELDS_FORM constants and extends AbstractAdminForm.
And you need to add this class in the `FormCollection` file in the `SETTINGS_FORMS_CLASSES` constant to register it in the form collection.
To display the form in the controller back office, you need to inject the new class of the form and build the form with the `build` method.

```php
$newAdminForm = $this->get('alma.new_admin_form');

$forms = [
    [...]
    $newAdminForm->build()
];
```
Then add the form class in the services.yml file to be able to inject it in the controller.

```yaml
  alma.new_admin_form:
    class: 'PrestaShop\Module\Alma\Infrastructure\Form\NewAdminForm'
    arguments:
      - '@alma.form_builder'
```

### How does the FIELDS_FORM constant works ?
You need to add key with the keyName of the input value, in the value of this key you need to add an array with the type of the input, the label, the name, and other parameters depending on the type of the input.
- `type` is the type of the input, it can be text, select, checkbox, radio, etc.
- `label` is the label of the input, it will be displayed in the form.
- `required` is a boolean that indicates if the input is required or not.
- `form` is the name of the form, it will be used to group inputs in the same form.
- `options` is an array that contains additional options for the input, it can contain a description, a class, a size, etc. depending on the type of the input.

## Example of types of fields
### Text
```php
'KEY_INPUT_TEXT' => [
    'type' => 'text',
    'label' => 'Label name',
    'required' => false,
    'form' => 'form_name',
    'options' => [
        'desc' => 'Help text for this input',
        'size' => 20,
    ],
]
```

### Select
```php
'KEY_INPUT_SELECT' => [
    'type' => 'select',
    'label' => 'Label name',
    'required' => true,
    'form' => 'form_name',
    'options' => [
        'desc' => 'Help text for this input',
        'options' => [
            'query' => [
                ['id' => 'value_option_1', 'name' => 'Name Option 1'],
                ['id' => 'value_option_2', 'name' => 'Name Option 2'],
            ],
            'id' => 'id',
            'name' => 'name',
        ],
    ]
]
```

### Checkbox
```php
'KEY_INPUT_CHECKBOX' => [
    'type' => 'checkbox',
    'label' => 'Label name',
    'required' => true,
    'form' => 'form_name',
    'options' => [
        'desc' => 'Help text for this input',
        'values' => [
            'query' => [
                ['id' => 'value_option_1', 'name' => 'Name Option 1'],
                ['id' => 'value_option_2', 'name' => 'Name Option 2'],
            ],
            'id' => 'id',
            'name' => 'name',
        ],
    ]
]
```

### Radio
```php
'KEY_INPUT_RADIO' => [
    'type' => 'radio',
    'label' => 'Label name',
    'required' => true,
    'form' => 'form_name',
    'options' => [
        'desc' => 'Help text for this input',
        'class' => 'class_of_the_input',
        'is_bool' => true, // if true, the radio buttons will be displayed as yes/no options
        'values' => [
            [
                'id' => 'value_option_1',
                'value' => 1,
                'label' => 'Name Option 1',
            ],
            [
                'id' => 'value_option_2',
                'value' => 0,
                'label' => 'Name Option 2',
            ],
        ],
    ]
]
```
