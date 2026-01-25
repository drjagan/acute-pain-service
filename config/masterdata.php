<?php

/**
 * Master Data Configuration
 * 
 * Defines all master data types, their tables, models, and field configurations
 * for the generic master data management system.
 * 
 * @version 1.2.0
 * @author Acute Pain Service System
 */

return [
    'catheter_indications' => [
        'label' => 'Catheter Insertion Indications',
        'singular' => 'Catheter Insertion Indication',
        'description' => 'Reasons for catheter insertion',
        'icon' => 'bi-clipboard-pulse',
        'table' => 'lookup_catheter_indications',
        'model' => 'LookupCatheterIndication',
        'color' => 'primary',
        'fields' => [
            'name' => [
                'type' => 'text', 
                'required' => true, 
                'label' => 'Indication Name',
                'placeholder' => 'e.g., Post-operative pain management',
                'maxlength' => 200
            ],
            'description' => [
                'type' => 'textarea', 
                'label' => 'Description',
                'rows' => 3,
                'placeholder' => 'Additional details about this indication'
            ],
            'is_common' => [
                'type' => 'checkbox', 
                'label' => 'Frequently Used',
                'help' => 'Mark as commonly used for quick selection',
                'default' => false
            ],
            'active' => [
                'type' => 'checkbox', 
                'label' => 'Active', 
                'default' => true,
                'help' => 'Only active items appear in dropdowns'
            ],
            'sort_order' => [
                'type' => 'number', 
                'label' => 'Sort Order', 
                'default' => 0,
                'help' => 'Lower numbers appear first',
                'min' => 0
            ]
        ],
        'list_columns' => ['name', 'is_common', 'active', 'created_at'],
        'searchable' => ['name', 'description'],
        'sortable' => true,
        'soft_delete' => true,
        'export' => true
    ],
    
    'removal_indications' => [
        'label' => 'Catheter Removal Indications',
        'singular' => 'Catheter Removal Indication',
        'description' => 'Reasons for catheter removal',
        'icon' => 'bi-x-circle',
        'table' => 'lookup_removal_indications',
        'model' => 'LookupRemovalIndication',
        'color' => 'warning',
        'fields' => [
            'name' => [
                'type' => 'text', 
                'required' => true,
                'label' => 'Indication Name',
                'maxlength' => 200,
                'placeholder' => 'e.g., Adequate analgesia achieved'
            ],
            'code' => [
                'type' => 'text', 
                'required' => true, 
                'unique' => true,
                'label' => 'Code',
                'help' => 'Unique code (e.g., adequate_analgesia)',
                'maxlength' => 50,
                'pattern' => '[a-z_]+',
                'placeholder' => 'lowercase_with_underscores'
            ],
            'description' => [
                'type' => 'textarea', 
                'label' => 'Description',
                'rows' => 3
            ],
            'requires_notes' => [
                'type' => 'checkbox', 
                'label' => 'Requires Additional Notes',
                'default' => false,
                'help' => 'Force user to enter notes when this indication is selected'
            ],
            'is_planned' => [
                'type' => 'checkbox', 
                'label' => 'Planned Removal',
                'default' => true,
                'help' => 'Was this a planned removal vs unplanned/emergency?'
            ],
            'active' => [
                'type' => 'checkbox', 
                'label' => 'Active',
                'default' => true
            ],
            'sort_order' => [
                'type' => 'number', 
                'label' => 'Sort Order', 
                'default' => 0,
                'min' => 0
            ]
        ],
        'list_columns' => ['name', 'code', 'requires_notes', 'is_planned', 'active'],
        'searchable' => ['name', 'code', 'description'],
        'sortable' => true,
        'soft_delete' => true,
        'export' => true
    ],
    
    'sentinel_events' => [
        'label' => 'Sentinel Events',
        'singular' => 'Sentinel Event',
        'description' => 'Adverse events and complications',
        'icon' => 'bi-exclamation-triangle',
        'table' => 'lookup_sentinel_events',
        'model' => 'LookupSentinelEvent',
        'color' => 'danger',
        'fields' => [
            'name' => [
                'type' => 'text', 
                'required' => true,
                'label' => 'Event Name',
                'maxlength' => 200,
                'placeholder' => 'e.g., Catheter-related bloodstream infection'
            ],
            'category' => [
                'type' => 'select', 
                'label' => 'Category',
                'required' => true,
                'options' => [
                    'infection' => 'Infection',
                    'neurological' => 'Neurological',
                    'cardiovascular' => 'Cardiovascular',
                    'respiratory' => 'Respiratory',
                    'mechanical' => 'Mechanical',
                    'other' => 'Other'
                ]
            ],
            'severity' => [
                'type' => 'select', 
                'label' => 'Severity',
                'required' => true,
                'options' => [
                    'mild' => 'Mild',
                    'moderate' => 'Moderate',
                    'severe' => 'Severe',
                    'critical' => 'Critical'
                ]
            ],
            'requires_immediate_action' => [
                'type' => 'checkbox',
                'label' => 'Requires Immediate Action',
                'default' => false,
                'help' => 'Mark if this event requires immediate medical intervention'
            ],
            'description' => [
                'type' => 'textarea', 
                'label' => 'Description',
                'rows' => 3,
                'placeholder' => 'Clinical description and management guidelines'
            ],
            'active' => [
                'type' => 'checkbox', 
                'label' => 'Active',
                'default' => true
            ],
            'sort_order' => [
                'type' => 'number', 
                'label' => 'Sort Order', 
                'default' => 0,
                'min' => 0
            ]
        ],
        'list_columns' => ['name', 'category', 'severity', 'requires_immediate_action', 'active'],
        'searchable' => ['name', 'description'],
        'sortable' => true,
        'soft_delete' => true,
        'export' => true
    ],
    
    'specialties' => [
        'label' => 'Medical Specialties',
        'singular' => 'Medical Specialty',
        'description' => 'Surgical and medical specialties',
        'icon' => 'bi-hospital',
        'table' => 'lookup_specialties',
        'model' => 'LookupSpecialty',
        'color' => 'info',
        'fields' => [
            'name' => [
                'type' => 'text', 
                'required' => true,
                'label' => 'Specialty Name',
                'placeholder' => 'e.g., General Surgery',
                'maxlength' => 100
            ],
            'code' => [
                'type' => 'text', 
                'required' => true, 
                'unique' => true,
                'label' => 'Code',
                'maxlength' => 20,
                'placeholder' => 'e.g., GEN',
                'help' => 'Short code for this specialty',
                'pattern' => '[A-Z]+',
                'style' => 'text-transform: uppercase;'
            ],
            'description' => [
                'type' => 'textarea', 
                'label' => 'Description',
                'rows' => 2,
                'placeholder' => 'Brief description of this specialty'
            ],
            'active' => [
                'type' => 'checkbox', 
                'label' => 'Active',
                'default' => true
            ],
            'sort_order' => [
                'type' => 'number', 
                'label' => 'Sort Order', 
                'default' => 0,
                'min' => 0
            ]
        ],
        'list_columns' => ['name', 'code', 'surgery_count', 'active', 'created_at'],
        'searchable' => ['name', 'code', 'description'],
        'sortable' => true,
        'soft_delete' => true,
        'export' => true,
        'has_children' => ['table' => 'lookup_surgeries', 'label' => 'Surgeries', 'foreign_key' => 'specialty_id']
    ],
    
    'surgeries' => [
        'label' => 'Surgical Procedures',
        'singular' => 'Surgical Procedure',
        'description' => 'Types of surgical procedures',
        'icon' => 'bi-bandaid',
        'table' => 'lookup_surgeries',
        'model' => 'LookupSurgery',
        'color' => 'success',
        'fields' => [
            'name' => [
                'type' => 'text', 
                'required' => true,
                'label' => 'Procedure Name',
                'maxlength' => 200,
                'placeholder' => 'e.g., Total Hip Replacement'
            ],
            'specialty_id' => [
                'type' => 'select', 
                'label' => 'Specialty',
                'required' => true,
                'foreign' => 'lookup_specialties',
                'foreign_key' => 'id',
                'foreign_label' => 'name',
                'help' => 'Medical specialty this procedure belongs to'
            ],
            'active' => [
                'type' => 'checkbox', 
                'label' => 'Active',
                'default' => true
            ],
            'sort_order' => [
                'type' => 'number', 
                'label' => 'Sort Order', 
                'default' => 0,
                'min' => 0
            ]
        ],
        'list_columns' => ['name', 'specialty', 'active', 'created_at'],
        'searchable' => ['name'],
        'sortable' => true,
        'soft_delete' => true,
        'export' => true,
        'group_by' => 'specialty_id',
        'parent' => ['table' => 'lookup_specialties', 'key' => 'specialty_id', 'label' => 'Specialty']
    ],
    
    'comorbidities' => [
        'label' => 'Comorbidities',
        'singular' => 'Comorbidity',
        'description' => 'Patient medical conditions',
        'icon' => 'bi-heart-pulse',
        'table' => 'lookup_comorbidities',
        'model' => 'LookupComorbidity',
        'color' => 'secondary',
        'fields' => [
            'name' => [
                'type' => 'text', 
                'required' => true,
                'label' => 'Comorbidity Name',
                'maxlength' => 100,
                'placeholder' => 'e.g., Diabetes Mellitus'
            ],
            'description' => [
                'type' => 'textarea', 
                'label' => 'Description',
                'rows' => 2,
                'placeholder' => 'Additional clinical information'
            ],
            'active' => [
                'type' => 'checkbox', 
                'label' => 'Active',
                'default' => true
            ],
            'sort_order' => [
                'type' => 'number', 
                'label' => 'Sort Order', 
                'default' => 0,
                'min' => 0
            ]
        ],
        'list_columns' => ['name', 'active', 'created_at'],
        'searchable' => ['name', 'description'],
        'sortable' => true,
        'soft_delete' => true,
        'export' => true
    ],
    
    'drugs' => [
        'label' => 'Drugs',
        'singular' => 'Drug',
        'description' => 'Medications and drugs',
        'icon' => 'bi-capsule',
        'table' => 'lookup_drugs',
        'model' => 'LookupDrug',
        'color' => 'primary',
        'fields' => [
            'name' => [
                'type' => 'text', 
                'required' => true, 
                'label' => 'Brand Name',
                'maxlength' => 100,
                'placeholder' => 'e.g., Ropivacaine'
            ],
            'generic_name' => [
                'type' => 'text', 
                'label' => 'Generic Name',
                'maxlength' => 100,
                'placeholder' => 'Generic/chemical name'
            ],
            'typical_concentration' => [
                'type' => 'number', 
                'step' => '0.01',
                'label' => 'Typical Concentration',
                'min' => 0,
                'placeholder' => '0.2'
            ],
            'max_dose' => [
                'type' => 'number', 
                'step' => '0.01',
                'label' => 'Maximum Dose',
                'min' => 0,
                'help' => 'Maximum safe dose per administration'
            ],
            'unit' => [
                'type' => 'text', 
                'maxlength' => 20,
                'label' => 'Unit',
                'placeholder' => 'e.g., mg, mcg, %'
            ],
            'active' => [
                'type' => 'checkbox', 
                'label' => 'Active',
                'default' => true
            ]
        ],
        'list_columns' => ['name', 'generic_name', 'typical_concentration', 'max_dose', 'unit', 'active'],
        'searchable' => ['name', 'generic_name'],
        'sortable' => false,
        'soft_delete' => true,
        'export' => true
    ],
    
    'adjuvants' => [
        'label' => 'Adjuvants',
        'singular' => 'Adjuvant',
        'description' => 'Drug adjuvants and additives',
        'icon' => 'bi-plus-circle',
        'table' => 'lookup_adjuvants',
        'model' => 'LookupAdjuvant',
        'color' => 'info',
        'fields' => [
            'name' => [
                'type' => 'text', 
                'required' => true,
                'label' => 'Adjuvant Name',
                'maxlength' => 100,
                'placeholder' => 'e.g., Epinephrine'
            ],
            'typical_dose' => [
                'type' => 'number', 
                'step' => '0.01',
                'label' => 'Typical Dose',
                'min' => 0
            ],
            'unit' => [
                'type' => 'text', 
                'maxlength' => 20,
                'label' => 'Unit',
                'placeholder' => 'e.g., mcg, mg'
            ],
            'active' => [
                'type' => 'checkbox', 
                'label' => 'Active',
                'default' => true
            ]
        ],
        'list_columns' => ['name', 'typical_dose', 'unit', 'active'],
        'searchable' => ['name'],
        'sortable' => false,
        'soft_delete' => true,
        'export' => true
    ],
    
    'red_flags' => [
        'label' => 'Red Flags',
        'singular' => 'Red Flag',
        'description' => 'Insertion complications',
        'icon' => 'bi-flag',
        'table' => 'lookup_red_flags',
        'model' => 'LookupRedFlag',
        'color' => 'danger',
        'fields' => [
            'name' => [
                'type' => 'text', 
                'required' => true,
                'label' => 'Red Flag Name',
                'maxlength' => 200,
                'placeholder' => 'e.g., Blood aspiration'
            ],
            'severity' => [
                'type' => 'select', 
                'label' => 'Severity',
                'required' => true,
                'options' => [
                    'mild' => 'Mild',
                    'moderate' => 'Moderate',
                    'severe' => 'Severe'
                ]
            ],
            'requires_immediate_action' => [
                'type' => 'checkbox',
                'label' => 'Requires Immediate Action',
                'default' => false,
                'help' => 'Mark if this complication requires immediate intervention'
            ],
            'active' => [
                'type' => 'checkbox', 
                'label' => 'Active',
                'default' => true
            ]
        ],
        'list_columns' => ['name', 'severity', 'requires_immediate_action', 'active'],
        'searchable' => ['name'],
        'sortable' => false,
        'soft_delete' => true,
        'export' => true
    ]
];
