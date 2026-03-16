Twig Template documentation
===================================

## Context
The form builder is used to create forms in the back office.
It is defined in the `AdminController` class of the module. And how we use the form builder to create forms in the back office.

## Link to the documentation
### Legacy System
[Fields of Form builder](https://devdocs.prestashop-project.org/1.7/development/components/helpers/helperform/#fields)

## Create a form
To create a form, you need to add a new Class in the `src/Infrastructure/Form` folder who contains `title` and `fieldForm` static function and extends AbstractAdminForm.
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
            'id' => 'id', // The value of the 'id' key must be the same as the key for the 'value' attribute of the <option> tag in each $options sub-array.
            'name' => 'name', // The value of the 'name' key must be the same as the key for the text content of the <option> tag in each $options sub-array.
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
            'id' => 'id', // The value of the 'id' key must be the same as the key for the 'value' attribute of the <option> tag in each $options sub-array.
            'name' => 'name', // The value of the 'name' key must be the same as the key for the text content of the <option> tag in each $options sub-array.
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

### Switch (Two radio inputs)
```php
'KEY_INPUT_SWITCH' => [
    'type' => 'switch',
    'label' => 'Label name',
    'required' => true,
    'form' => 'form_name',
    'options' => [
        [
            'id' => 'id_option_1',
            'value' => 1,
            'label' => 'Label Option 1'
        ],
        [
            'id' => 'id_option_2',
            'value' => 0,
            'label' => 'Label Option 2'
        ]
    ]
]
```

## Multi-language fields
To create a multi-language field, you need to add the `lang` key in `options` with the value `true`, and the form builder will automatically create an input for each language available in the back office.

```php
'KEY_INPUT_MULTI_LANGUAGE' => [
    'type' => 'text',
    'label' => 'Label name',
    'required' => false,
    'form' => 'form_name',
    'options' => [
        'desc' => 'Help text for this input',
        'lang' => true,
    ],
```

### How does it work ?
If you enable the `lang` option for a field, the configuration saved in the database add the language id to the key of the field, `KEY_FIELS_NAME_{LANG_ID}`
To get the fields_value for display the value in the form, we need to get the key configuration value in array with the language id like

```php
'KEY_FIELDS_NAME' => [
    {LANG_ID_EN} => 'text EN',
    {LANG_ID_FR} => 'text FR',
]
```

So we had to create a function to get the value with the multi-language to split it with the language id to validate the field value with the validator.
And in the getValueFields, handle the multi-language option to return the value in array with the language id to display the value in the form.

Currently, we define the default value in a const, but we will need to get the default value from the .xlf file.
