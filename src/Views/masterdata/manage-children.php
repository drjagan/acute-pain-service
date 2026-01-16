<?php
/**
 * Manage Children View
 * For managing child records (e.g., surgeries under a specialty)
 * 
 * @version 1.2.0
 */
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/masterdata/index">Master Data</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/masterdata/list/<?= $parentType ?>"><?= e($parentConfig['label']) ?></a></li>
                <li class="breadcrumb-item active"><?= e($parent['name']) ?></li>
            </ol>
        </nav>
        
        <h1 class="h2">
            <i class="<?= $parentConfig['icon'] ?>"></i> 
            <?= e($parent['name']) ?>
            <?php if (!empty($parent['code'])): ?>
            <span class="badge bg-secondary"><?= e($parent['code']) ?></span>
            <?php endif; ?>
        </h1>
        
        <?php if (!empty($parent['description'])): ?>
        <p class="text-muted mb-0"><?= e($parent['description']) ?></p>
        <?php endif; ?>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= BASE_URL ?>/masterdata/list/<?= $parentType ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to <?= $parentConfig['label'] ?>
        </a>
    </div>
</div>

<!-- Stats Card -->
<div class="row mb-3">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi bi-list-ul"></i> <?= $childConfig['label'] ?>
                </h5>
                <h2 class="mb-0"><?= count($children) ?></h2>
                <small>Total <?= strtolower($childConfig['label']) ?></small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi bi-check-circle"></i> Active
                </h5>
                <h2 class="mb-0"><?= count(array_filter($children, fn($c) => $c['active'])) ?></h2>
                <small>Active records</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-secondary text-white">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi bi-x-circle"></i> Inactive
                </h5>
                <h2 class="mb-0"><?= count(array_filter($children, fn($c) => !$c['active'])) ?></h2>
                <small>Inactive records</small>
            </div>
        </div>
    </div>
</div>

<!-- Add New Child Form -->
<div class="card mb-3" id="add-child-form">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">
            <i class="bi bi-plus-circle"></i> Add New <?= rtrim($childConfig['label'], 's') ?>
        </h5>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/masterdata/storeChild/<?= $parentType ?>/<?= $parent['id'] ?>" id="childForm">
            <?= \Helpers\CSRF::field() ?>
            <input type="hidden" name="<?= $foreignKey ?>" value="<?= $parent['id'] ?>">
            
            <div class="row">
                <?php foreach ($childConfig['fields'] as $fieldName => $fieldConfig): ?>
                    <?php if ($fieldName === $childConfig['parent']['key']): continue; endif; ?>
                    <?php if ($fieldName === 'sort_order'): continue; endif; ?>
                    <?php if ($fieldName === 'active'): continue; endif; ?>
                    
                    <div class="col-md-<?= $fieldConfig['type'] === 'textarea' ? '12' : '6' ?> mb-3">
                        <label for="<?= $fieldName ?>" class="form-label">
                            <?= $fieldConfig['label'] ?>
                            <?php if ($fieldConfig['required'] ?? false): ?>
                            <span class="text-danger">*</span>
                            <?php endif; ?>
                        </label>
                        
                        <?php if ($fieldConfig['type'] === 'text'): ?>
                            <input type="text" 
                                   class="form-control" 
                                   id="<?= $fieldName ?>" 
                                   name="<?= $fieldName ?>"
                                   placeholder="<?= $fieldConfig['placeholder'] ?? '' ?>"
                                   maxlength="<?= $fieldConfig['maxlength'] ?? '' ?>"
                                   <?= ($fieldConfig['required'] ?? false) ? 'required' : '' ?>>
                        
                        <?php elseif ($fieldConfig['type'] === 'textarea'): ?>
                            <textarea class="form-control" 
                                      id="<?= $fieldName ?>" 
                                      name="<?= $fieldName ?>"
                                      rows="<?= $fieldConfig['rows'] ?? 3 ?>"
                                      placeholder="<?= $fieldConfig['placeholder'] ?? '' ?>"></textarea>
                        
                        <?php elseif ($fieldConfig['type'] === 'number'): ?>
                            <input type="number" 
                                   class="form-control" 
                                   id="<?= $fieldName ?>" 
                                   name="<?= $fieldName ?>"
                                   step="<?= $fieldConfig['step'] ?? '1' ?>"
                                   min="<?= $fieldConfig['min'] ?? '' ?>"
                                   placeholder="<?= $fieldConfig['placeholder'] ?? '' ?>"
                                   <?= ($fieldConfig['required'] ?? false) ? 'required' : '' ?>>
                        
                        <?php elseif ($fieldConfig['type'] === 'select'): ?>
                            <select class="form-select" 
                                    id="<?= $fieldName ?>" 
                                    name="<?= $fieldName ?>"
                                    <?= ($fieldConfig['required'] ?? false) ? 'required' : '' ?>>
                                <option value="">Select...</option>
                                <?php foreach ($fieldConfig['options'] as $optValue => $optLabel): ?>
                                <option value="<?= $optValue ?>"><?= $optLabel ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                        
                        <?php if (!empty($fieldConfig['help'])): ?>
                        <div class="form-text"><?= $fieldConfig['help'] ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="d-flex justify-content-end gap-2">
                <button type="reset" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Clear
                </button>
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-plus-circle"></i> Add <?= rtrim($childConfig['label'], 's') ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Children List -->
<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-list-ul"></i> <?= $childConfig['label'] ?> List
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($children)): ?>
        <div class="text-center py-5">
            <i class="bi bi-inbox display-1 text-muted"></i>
            <h4 class="mt-3">No <?= $childConfig['label'] ?> Yet</h4>
            <p class="text-muted">Get started by adding your first <?= strtolower(rtrim($childConfig['label'], 's')) ?> above.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="childrenTable">
                <thead class="table-light">
                    <tr>
                        <?php if ($childConfig['sortable']): ?>
                        <th width="50" class="text-center">
                            <i class="bi bi-arrows-move" title="Drag to reorder"></i>
                        </th>
                        <?php endif; ?>
                        
                        <th>Name</th>
                        <th width="100" class="text-center">Status</th>
                        <th width="200" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody <?= $childConfig['sortable'] ? 'id="sortable-children"' : '' ?>>
                    <?php foreach ($children as $child): ?>
                    <tr data-id="<?= $child['id'] ?>">
                        <?php if ($childConfig['sortable']): ?>
                        <td class="text-center">
                            <i class="bi bi-grip-vertical text-muted sortable-handle" style="cursor: move;"></i>
                        </td>
                        <?php endif; ?>
                        
                        <td>
                            <strong><?= e($child['name']) ?></strong>
                            <?php if (!empty($child['description'])): ?>
                            <br><small class="text-muted"><?= e($child['description']) ?></small>
                            <?php endif; ?>
                        </td>
                        
                        <td class="text-center">
                            <button type="button" 
                                    class="btn btn-sm btn-<?= $child['active'] ? 'success' : 'secondary' ?> toggle-child-active"
                                    data-id="<?= $child['id'] ?>"
                                    data-active="<?= $child['active'] ?>"
                                    title="<?= $child['active'] ? 'Active' : 'Inactive' ?>">
                                <i class="bi bi-<?= $child['active'] ? 'check-circle' : 'x-circle' ?>"></i>
                                <?= $child['active'] ? 'Active' : 'Inactive' ?>
                            </button>
                        </td>
                        
                        <td class="text-center">
                            <div class="btn-group btn-group-sm" role="group">
                                <!-- Edit (inline modal) -->
                                <button type="button" 
                                        class="btn btn-outline-primary edit-child-btn"
                                        data-id="<?= $child['id'] ?>"
                                        data-name="<?= e($child['name']) ?>"
                                        data-description="<?= e($child['description'] ?? '') ?>"
                                        title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                
                                <!-- Delete -->
                                <form method="POST" 
                                      action="<?= BASE_URL ?>/masterdata/deleteChild/<?= $childType ?>/<?= $child['id'] ?>" 
                                      style="display: inline;"
                                      onsubmit="return confirm('Are you sure you want to delete this item?');">
                                    <?= \Helpers\CSRF::field() ?>
                                    <button type="submit" 
                                            class="btn btn-outline-danger"
                                            title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Child Modal -->
<div class="modal fade" id="editChildModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="editChildForm">
                <?= \Helpers\CSRF::field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Edit <?= rtrim($childConfig['label'], 's') ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_child_id">
                    
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle child active status (AJAX)
    document.querySelectorAll('.toggle-child-active').forEach(btn => {
        btn.addEventListener('click', function() {
            const childId = this.dataset.id;
            const isActive = this.dataset.active === '1';
            
            fetch('<?= BASE_URL ?>/masterdata/toggleChildActive/<?= $childType ?>/' + childId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': '<?= \Helpers\CSRF::token() ?>'
                },
                body: JSON.stringify({ active: !isActive })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to update status'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating status');
            });
        });
    });
    
    // Edit child modal
    const editModal = new bootstrap.Modal(document.getElementById('editChildModal'));
    const editForm = document.getElementById('editChildForm');
    
    document.querySelectorAll('.edit-child-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const childId = this.dataset.id;
            const name = this.dataset.name;
            const description = this.dataset.description;
            
            document.getElementById('edit_child_id').value = childId;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_description').value = description;
            
            editForm.action = '<?= BASE_URL ?>/masterdata/updateChild/<?= $childType ?>/' + childId;
            editModal.show();
        });
    });
    
    // Sortable functionality (if enabled)
    <?php if ($childConfig['sortable']): ?>
    const sortableList = document.getElementById('sortable-children');
    if (sortableList) {
        let draggedElement = null;
        let draggedRow = null;
        
        sortableList.querySelectorAll('tr').forEach(row => {
            const handle = row.querySelector('.sortable-handle');
            if (handle) {
                handle.addEventListener('mousedown', function() {
                    row.draggable = true;
                });
                
                handle.addEventListener('mouseup', function() {
                    row.draggable = false;
                });
            }
            
            row.addEventListener('dragstart', function(e) {
                draggedElement = this;
                draggedRow = this;
                this.style.opacity = '0.5';
                e.dataTransfer.effectAllowed = 'move';
            });
            
            row.addEventListener('dragend', function() {
                this.style.opacity = '';
                row.draggable = false;
            });
            
            row.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                
                if (draggedElement !== this) {
                    const rect = this.getBoundingClientRect();
                    const midpoint = rect.top + rect.height / 2;
                    
                    if (e.clientY < midpoint) {
                        this.parentNode.insertBefore(draggedElement, this);
                    } else {
                        this.parentNode.insertBefore(draggedElement, this.nextSibling);
                    }
                }
            });
            
            row.addEventListener('drop', function(e) {
                e.preventDefault();
                saveOrder();
            });
        });
        
        function saveOrder() {
            const rows = sortableList.querySelectorAll('tr');
            const order = {};
            
            rows.forEach((row, index) => {
                const id = row.dataset.id;
                if (id) {
                    order[id] = index;
                }
            });
            
            console.log('Sending order:', order);
            
            fetch('<?= BASE_URL ?>/masterdata/reorderChildren/<?= $childType ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': '<?= \Helpers\CSRF::token() ?>'
                },
                body: JSON.stringify({ order: order })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Response:', data);
                if (data.success) {
                    // Show success feedback - maybe highlight the row briefly
                    console.log('Order saved successfully');
                } else {
                    alert('Error: ' + (data.message || 'Failed to save order'));
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while saving order');
                location.reload();
            });
        }
    }
    <?php endif; ?>
});
</script>
