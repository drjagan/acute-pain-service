<?php
/**
 * Master Data Form View
 * Generic add/edit form for all master data types
 * 
 * @version 1.2.0
 */

$isEdit = ($action === 'edit' && !empty($item));
$formAction = $isEdit 
    ? BASE_URL . '/masterdata/update/' . $type . '/' . $item['id']
    : BASE_URL . '/masterdata/store/' . $type;
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2">
            <i class="<?= $config['icon'] ?>"></i> 
            <?= $isEdit ? 'Edit' : 'Add New' ?> <?= e($config['singular'] ?? rtrim($config['label'], 'ies') . 'y') ?>
        </h1>
        <p class="text-muted mb-0"><?= e($config['description']) ?></p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= BASE_URL ?>/masterdata/list/<?= $type ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card shadow-sm">
            <div class="card-header bg-<?= $config['color'] ?> text-white">
                <h5 class="mb-0">
                    <i class="bi bi-<?= $isEdit ? 'pencil' : 'plus-circle' ?>"></i>
                    <?= $isEdit ? 'Edit Item' : 'New Item' ?>
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= $formAction ?>" id="masterDataForm">
                    <input type="hidden" name="csrf_token" value="<?= \Helpers\CSRF::token() ?>">
                    
                    <?php foreach ($config['fields'] as $fieldName => $fieldConfig): ?>
                    <div class="mb-3">
                        <label for="<?= $fieldName ?>" class="form-label">
                            <?= e($fieldConfig['label'] ?? ucfirst(str_replace('_', ' ', $fieldName))) ?>
                            <?php if (!empty($fieldConfig['required'])): ?>
                            <span class="text-danger">*</span>
                            <?php endif; ?>
                        </label>
                        
                        <?php
                        $value = $item[$fieldName] ?? ($fieldConfig['default'] ?? '');
                        $fieldType = $fieldConfig['type'];
                        ?>
                        
                        <?php if ($fieldType === 'text'): ?>
                        <input type="text" 
                               class="form-control" 
                               id="<?= $fieldName ?>" 
                               name="<?= $fieldName ?>"
                               value="<?= e($value) ?>"
                               placeholder="<?= e($fieldConfig['placeholder'] ?? '') ?>"
                               maxlength="<?= $fieldConfig['maxlength'] ?? 255 ?>"
                               <?= !empty($fieldConfig['pattern']) ? 'pattern="' . e($fieldConfig['pattern']) . '"' : '' ?>
                               <?= !empty($fieldConfig['style']) ? 'style="' . e($fieldConfig['style']) . '"' : '' ?>
                               <?= !empty($fieldConfig['required']) ? 'required' : '' ?>>
                        
                        <?php elseif ($fieldType === 'textarea'): ?>
                        <textarea class="form-control" 
                                  id="<?= $fieldName ?>" 
                                  name="<?= $fieldName ?>"
                                  rows="<?= $fieldConfig['rows'] ?? 3 ?>"
                                  placeholder="<?= e($fieldConfig['placeholder'] ?? '') ?>"
                                  <?= !empty($fieldConfig['required']) ? 'required' : '' ?>><?= e($value) ?></textarea>
                        
                        <?php elseif ($fieldType === 'number'): ?>
                        <input type="number" 
                               class="form-control" 
                               id="<?= $fieldName ?>" 
                               name="<?= $fieldName ?>"
                               value="<?= e($value) ?>"
                               step="<?= $fieldConfig['step'] ?? 1 ?>"
                               min="<?= $fieldConfig['min'] ?? '' ?>"
                               max="<?= $fieldConfig['max'] ?? '' ?>"
                               placeholder="<?= e($fieldConfig['placeholder'] ?? '') ?>"
                               <?= !empty($fieldConfig['required']) ? 'required' : '' ?>>
                        
                        <?php elseif ($fieldType === 'select'): ?>
                        <select class="form-select" 
                                id="<?= $fieldName ?>" 
                                name="<?= $fieldName ?>"
                                <?= !empty($fieldConfig['required']) ? 'required' : '' ?>>
                            <option value="">-- Select --</option>
                            
                            <?php if (!empty($fieldConfig['foreign'])): ?>
                                <?php 
                                // Foreign key dropdown
                                $options = $foreignOptions[$fieldName] ?? [];
                                foreach ($options as $optionValue => $optionLabel): 
                                ?>
                                <option value="<?= $optionValue ?>" 
                                        <?= $value == $optionValue ? 'selected' : '' ?>>
                                    <?= e($optionLabel) ?>
                                </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <?php 
                                // Static options
                                foreach ($fieldConfig['options'] as $optionValue => $optionLabel): 
                                ?>
                                <option value="<?= $optionValue ?>" 
                                        <?= $value == $optionValue ? 'selected' : '' ?>>
                                    <?= e($optionLabel) ?>
                                </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        
                        <?php elseif ($fieldType === 'checkbox'): ?>
                        <div class="form-check form-switch">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="<?= $fieldName ?>" 
                                   name="<?= $fieldName ?>"
                                   value="1"
                                   <?= $value ? 'checked' : '' ?>>
                            <label class="form-check-label" for="<?= $fieldName ?>">
                                <?= e($fieldConfig['label'] ?? ucfirst(str_replace('_', ' ', $fieldName))) ?>
                            </label>
                        </div>
                        
                        <?php endif; ?>
                        
                        <?php if (!empty($fieldConfig['help'])): ?>
                        <div class="form-text"><?= e($fieldConfig['help']) ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    
                    <hr class="my-4">
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?= BASE_URL ?>/masterdata/list/<?= $type ?>" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-<?= $config['color'] ?>">
                            <i class="bi bi-<?= $isEdit ? 'check-circle' : 'plus-circle' ?>"></i>
                            <?= $isEdit ? 'Update' : 'Create' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <?php if ($isEdit): ?>
        <div class="card shadow-sm mt-3 border-danger">
            <div class="card-header bg-danger text-white">
                <h6 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Danger Zone</h6>
            </div>
            <div class="card-body">
                <p class="mb-2">
                    <strong>Delete this item?</strong> This action cannot be undone.
                    <?php if ($type === 'specialties'): ?>
                    <br><small class="text-muted">Note: You cannot delete specialties that have associated surgeries.</small>
                    <?php endif; ?>
                </p>
                <form method="POST" 
                      action="<?= BASE_URL ?>/masterdata/delete/<?= $type ?>/<?= $item['id'] ?>"
                      onsubmit="return confirm('Are you absolutely sure you want to delete this item? This action cannot be undone.');">
                    <input type="hidden" name="csrf_token" value="<?= \Helpers\CSRF::token() ?>">
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bi bi-trash"></i> Delete Item
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('masterDataForm');
    
    // Form validation
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Custom validation logic
        <?php if ($type === 'removal_indications'): ?>
        const code = document.getElementById('code');
        if (code && !/^[a-z_]+$/.test(code.value)) {
            alert('Code must contain only lowercase letters and underscores');
            code.focus();
            isValid = false;
        }
        <?php endif; ?>
        
        <?php if ($type === 'specialties'): ?>
        const code = document.getElementById('code');
        if (code) {
            code.value = code.value.toUpperCase();
            if (!/^[A-Z]+$/.test(code.value)) {
                alert('Code must contain only uppercase letters');
                code.focus();
                isValid = false;
            }
        }
        <?php endif; ?>
        
        if (!isValid) {
            e.preventDefault();
        }
    });
    
    // Specialty-based surgery filtering (for surgeries form)
    <?php if ($type === 'surgeries'): ?>
    const specialtySelect = document.getElementById('specialty_id');
    if (specialtySelect) {
        specialtySelect.addEventListener('change', function() {
            // Optionally fetch and display surgery count for selected specialty
            console.log('Specialty changed to:', this.value);
        });
    }
    <?php endif; ?>
});
</script>
