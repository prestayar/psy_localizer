<?php
/**
 * PrestaSDK Multilingual Fields Usage Example
 * 
 * This example demonstrates how to use the new multilingual support
 * features in PrestaSDK for both form initialization and configuration saving.
 */

// Example 1: Using HelperForm with multilingual fields
use PrestaSDK\V071\Utility\HelperForm;
use PrestaSDK\V071\Utility\Config;

class ExampleController extends ModuleAdminController
{
    public function renderForm()
    {
        // Initialize helper form
        $helper = new HelperForm($this->module);
        
        // Method 1: Set fields with multilingual specification
        $fields = ['REGULAR_FIELD', 'MULTILINGUAL_FIELD', 'ANOTHER_FIELD'];
        $multilingualFields = ['MULTILINGUAL_FIELD'];
        $helper->setFieldsByArray($fields, $multilingualFields);
        
        // Method 2: Set individual multilingual field with default value
        $helper->setMultilingualField('CUSTOM_LABEL', $this->l('Default Label'));
        
        // Method 3: Set multiple multilingual fields at once
        $multilingualFieldsWithDefaults = [
            'WELCOME_MESSAGE' => $this->l('Welcome'),
            'THANK_YOU_MESSAGE' => $this->l('Thank you'),
            'ERROR_MESSAGE' // No default value
        ];
        $helper->setMultilingualFields($multilingualFieldsWithDefaults);
        
        // Form configuration
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Configuration'),
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('Regular Field'),
                        'name' => 'REGULAR_FIELD',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Multilingual Field'),
                        'name' => 'MULTILINGUAL_FIELD',
                        'lang' => true, // This marks the field as multilingual
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Custom Label'),
                        'name' => 'CUSTOM_LABEL',
                        'lang' => true,
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ]
            ]
        ];
        
        return $helper->generateForm([$fields_form]);
    }
    
    public function postProcess()
    {
        if (Tools::isSubmit('submitConfiguration')) {
            // Method 1: Specify multilingual fields explicitly
            $configs = ['REGULAR_FIELD', 'MULTILINGUAL_FIELD', 'CUSTOM_LABEL'];
            $multilingualFields = ['MULTILINGUAL_FIELD', 'CUSTOM_LABEL'];
            Config::updateConfigs($configs, true, false, $multilingualFields);
            
            // Method 2: Let the system auto-detect multilingual fields
            // This works by checking if language-specific keys exist in POST data
            $allConfigs = ['REGULAR_FIELD', 'MULTILINGUAL_FIELD', 'CUSTOM_LABEL', 'WELCOME_MESSAGE'];
            Config::updateConfigs($allConfigs, true, false); // Auto-detection enabled
            
            $this->confirmations[] = $this->l('Settings updated successfully');
        }
    }
}

/**
 * Example form field configuration for multilingual support:
 * 
 * [
 *     'type' => 'text',
 *     'label' => $this->l('Field Label'),
 *     'name' => 'FIELD_NAME',
 *     'lang' => true, // This is crucial for multilingual fields
 * ]
 * 
 * The system will automatically:
 * 1. Generate language-specific input fields (FIELD_NAME_1, FIELD_NAME_2, etc.)
 * 2. Initialize each field with the appropriate language value
 * 3. Save each language value separately when the form is submitted
 */