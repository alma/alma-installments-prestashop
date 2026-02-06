Twig Template documentation
===================================

## Context
The form builder is used to create forms in the back office.
It is defined in the `AdminController` class of the module. And how we use the form builder to create forms in the back office.

## Link to the documentation
### Legacy System
[Fields of Form builder](https://devdocs.prestashop-project.org/1.7/development/components/helpers/helperform/#fields)

## Types of fields
### Text
```php
array(
  'type'     => 'text',                             // This is a regular <input> tag.
  'label'    => $this->l('Name'),                   // The <label> for this <input> tag.
  'name'     => 'name',                             // The content of the 'id' attribute of the <input> tag.
  'class'    => 'lg',                                // The content of the 'class' attribute of the <input> tag. To set the size of the element, use these: sm, md, lg, xl, or xxl.
  'required' => true,                               // If set to true, this option must be set.
  'desc'     => $this->l('Please enter your name.') // A help text, displayed right next to the <input> tag.
),
```

### Select
```php
$options = array(
  array(
    'id_option' => 1,       // The value of the 'value' attribute of the <option> tag.
    'name' => 'Method 1'    // The value of the text content of the  <option> tag.
  ),
  array(
    'id_option' => 2,
    'name' => 'Method 2'
  ),
);

array(
  'type' => 'select',                              // This is a <select> tag.
  'label' => $this->l('Shipping method:'),         // The <label> for this <select> tag.
  'desc' => $this->l('Choose a shipping method'),  // A help text, displayed right next to the <select> tag.
  'name' => 'shipping_method',                     // The content of the 'id' attribute of the <select> tag.
  'required' => true,                              // If set to true, this option must be set.
  'options' => array(
    'query' => $options,                           // $options contains the data itself.
    'id' => 'id_option',                           // The value of the 'id' key must be the same as the key for 'value' attribute of the <option> tag in each $options sub-array.
    'name' => 'name'                               // The value of the 'name' key must be the same as the key for the text content of the <option> tag in each $options sub-array.
  )
),
```

### Checkbox
```php
array(
  'type'    => 'checkbox',                   // This is an <input type="checkbox"> tag.
  'label'   => $this->l('Options'),          // The <label> for this <input> tag.
  'desc'    => $this->l('Choose options.'),  // A help text, displayed right next to the <input> tag.
  'name'    => 'options',                    // The content of the 'id' attribute of the <input> tag.
  'values'  => array(
    'query' => $options,                     // $options contains the data itself.
    'id'    => 'id_option',                  // The value of the 'id' key must be the same as the key
                                             // for the 'value' attribute of the <option> tag in each $options sub-array.
    'name'  => 'name'                        // The value of the 'name' key must be the same as the key
                                             // for the text content of the <option> tag in each $options sub-array.
  'expand' => array(                         // You can hide the checkboxes when there are too many.
    'print_total' => count($options),        // A button appears with the number of options it hides.
    'default' => 'show',                     // 'show' will show by default, whereas 'hide' will do the opposite.
    'show' => array('text' => $this->l('show'), 'icon' => 'plus-sign-alt'),
    'hide' => array('text' => $this->l('hide'), 'icon' => 'minus-sign-alt')
  ),
),
```

### Radio
```php
array(
  'type'      => 'radio',                               // This is an <input type="checkbox"> tag.
  'label'     => $this->l('Enable this option'),        // The <label> for this <input> tag.
  'desc'      => $this->l('Are you a customer too?'),   // A help text, displayed right next to the <input> tag.
  'name'      => 'active',                              // The content of the 'id' attribute of the <input> tag.
  'required'  => true,                                  // If set to true, this option must be set.
  'class'     => 't',                                   // The content of the 'class' attribute of the <label> tag for the <input> tag.
  'is_bool'   => true,                                  // If set to true, this means you want to display a yes/no or true/false option.
                                                        // The CSS styling will therefore use green mark for the option value '1', and a red mark for value '2'.
                                                        // If set to false, this means there can be more than two radio buttons,
                                                        // and the option label text will be displayed instead of marks.
  'values'    => array(                                 // $values contains the data itself.
    array(
      'id'    => 'active_on',                           // The content of the 'id' attribute of the <input> tag, and of the 'for' attribute for the <label> tag.
      'value' => 1,                                     // The content of the 'value' attribute of the <input> tag.
      'label' => $this->l('Enabled')                    // The <label> for this radio button.
    ),
    array(
      'id'    => 'active_off',
      'value' => 0,
      'label' => $this->l('Disabled')
    )
  ),
),
```
