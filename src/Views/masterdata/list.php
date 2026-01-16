<?php
/**
 * Master Data List View
 * Generic list page for all master data types
 * 
 * @version 1.2.0
 */
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2">
            <i class="<?= $config['icon'] ?>"></i> 
            <?= e($config['label']) ?>
        </h1>
        <p class="text-muted mb-0"><?= e($config['description']) ?></p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="<?= BASE_URL ?>/masterdata/create/<?= $type ?>" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-circle"></i> Add New
            </a>
            <?php if ($config['export']): ?>
            <a href="<?= BASE_URL ?>/masterdata/export/<?= $type ?>" class="btn btn-sm btn-success">
                <i class="bi bi-download"></i> Export CSV
            </a>
            <?php endif; ?>
        </div>
        <a href="<?= BASE_URL ?>/masterdata/index" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<?php if (!empty($search)): ?>
<div class="alert alert-info alert-dismissible fade show">
    <i class="bi bi-search"></i>
    Showing results for: <strong>"<?= e($search) ?>"</strong>
    <a href="<?= BASE_URL ?>/masterdata/list/<?= $type ?>" class="btn btn-sm btn-outline-info ms-2">
        Clear Search
    </a>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Search and Filter -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="<?= BASE_URL ?>/masterdata/list/<?= $type ?>" class="row g-3">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" 
                           class="form-control" 
                           name="search" 
                           placeholder="Search <?= strtolower($config['label']) ?>..." 
                           value="<?= e($search) ?>">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </div>
            <div class="col-md-3">
                <select name="per_page" class="form-select" onchange="this.form.submit()">
                    <option value="25" <?= ($pagination['perPage'] == 25) ? 'selected' : '' ?>>25 per page</option>
                    <option value="50" <?= ($pagination['perPage'] == 50) ? 'selected' : '' ?>>50 per page</option>
                    <option value="100" <?= ($pagination['perPage'] == 100) ? 'selected' : '' ?>>100 per page</option>
                </select>
            </div>
            <div class="col-md-3 text-end">
                <span class="text-muted">
                    Total: <strong><?= $pagination['total'] ?></strong> items
                </span>
            </div>
        </form>
    </div>
</div>

<!-- Data Table -->
<div class="card shadow-sm">
    <div class="card-body">
        <?php if (empty($items)): ?>
        <div class="text-center py-5">
            <i class="bi bi-inbox display-1 text-muted"></i>
            <h4 class="mt-3">No Items Found</h4>
            <p class="text-muted">Get started by adding your first <?= strtolower(rtrim($config['label'], 's')) ?>.</p>
            <a href="<?= BASE_URL ?>/masterdata/create/<?= $type ?>" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add New
            </a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="masterDataTable">
                <thead class="table-light">
                    <tr>
                        <?php if ($config['sortable']): ?>
                        <th width="50" class="text-center">
                            <i class="bi bi-arrows-move" title="Drag to reorder"></i>
                        </th>
                        <?php endif; ?>
                        
                        <?php foreach ($config['list_columns'] as $column): ?>
                        <th><?= ucfirst(str_replace('_', ' ', $column)) ?></th>
                        <?php endforeach; ?>
                        
                        <th width="200" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody <?= $config['sortable'] ? 'id="sortable-tbody"' : '' ?>>
                    <?php foreach ($items as $item): ?>
                    <tr data-id="<?= $item['id'] ?>">
                        <?php if ($config['sortable']): ?>
                        <td class="text-center">
                            <i class="bi bi-grip-vertical text-muted sortable-handle" style="cursor: move;"></i>
                        </td>
                        <?php endif; ?>
                        
                        <?php foreach ($config['list_columns'] as $column): ?>
                        <td>
                            <?php
                            $value = $item[$column] ?? '';
                            
                            // Format based on column type
                            if ($column === 'active' || $column === 'is_common' || $column === 'requires_notes' || 
                                $column === 'is_planned' || $column === 'requires_immediate_action') {
                                echo $value ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>';
                            } elseif ($column === 'created_at' || $column === 'updated_at') {
                                echo $value ? date('Y-m-d H:i', strtotime($value)) : 'N/A';
                            } elseif ($column === 'severity') {
                                $colors = ['mild' => 'success', 'moderate' => 'warning', 'severe' => 'danger', 'critical' => 'dark'];
                                $color = $colors[$value] ?? 'secondary';
                                echo '<span class="badge bg-' . $color . '">' . ucfirst($value) . '</span>';
                            } elseif ($column === 'category') {
                                echo '<span class="badge bg-info">' . ucfirst($value) . '</span>';
                            } elseif ($column === 'surgery_count') {
                                echo '<span class="badge bg-primary">' . $value . ' surgeries</span>';
                            } else {
                                echo e($value);
                            }
                            ?>
                        </td>
                        <?php endforeach; ?>
                        
                        <td class="text-center">
                            <div class="btn-group btn-group-sm" role="group">
                                <!-- Toggle Active -->
                                <button type="button" 
                                        class="btn btn-outline-<?= $item['active'] ? 'success' : 'secondary' ?> toggle-active"
                                        data-id="<?= $item['id'] ?>"
                                        data-active="<?= $item['active'] ?>"
                                        title="<?= $item['active'] ? 'Active' : 'Inactive' ?>">
                                    <i class="bi bi-<?= $item['active'] ? 'check-circle' : 'x-circle' ?>"></i>
                                </button>
                                
                                <!-- Manage Children (for parent tables like specialties) -->
                                <?php if (isset($config['has_children'])): ?>
                                <a href="<?= BASE_URL ?>/masterdata/manageChildren/<?= $type ?>/<?= $item['id'] ?>" 
                                   class="btn btn-outline-info"
                                   title="Manage <?= $config['has_children']['label'] ?>">
                                    <i class="bi bi-list-ul"></i>
                                </a>
                                <?php endif; ?>
                                
                                <!-- Edit -->
                                <a href="<?= BASE_URL ?>/masterdata/edit/<?= $type ?>/<?= $item['id'] ?>" 
                                   class="btn btn-outline-primary"
                                   title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                
                                <!-- Delete -->
                                <form method="POST" 
                                      action="<?= BASE_URL ?>/masterdata/delete/<?= $type ?>/<?= $item['id'] ?>" 
                                      style="display: inline;"
                                      onsubmit="return confirm('Are you sure you want to delete this item?');">
                                    <input type="hidden" name="csrf_token" value="<?= \Helpers\CSRF::token() ?>">
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
        
        <!-- Pagination -->
        <?php if ($pagination['totalPages'] > 1): ?>
        <nav aria-label="Page navigation" class="mt-3">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= BASE_URL ?>/masterdata/list/<?= $type ?>?page=<?= $pagination['page'] - 1 ?>&search=<?= urlencode($search) ?>&per_page=<?= $pagination['perPage'] ?>">
                        Previous
                    </a>
                </li>
                
                <?php for ($i = 1; $i <= $pagination['totalPages']; $i++): ?>
                    <?php if ($i == 1 || $i == $pagination['totalPages'] || abs($i - $pagination['page']) <= 2): ?>
                    <li class="page-item <?= $i == $pagination['page'] ? 'active' : '' ?>">
                        <a class="page-link" href="<?= BASE_URL ?>/masterdata/list/<?= $type ?>?page=<?= $i ?>&search=<?= urlencode($search) ?>&per_page=<?= $pagination['perPage'] ?>">
                            <?= $i ?>
                        </a>
                    </li>
                    <?php elseif (abs($i - $pagination['page']) == 3): ?>
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <li class="page-item <?= $pagination['page'] >= $pagination['totalPages'] ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= BASE_URL ?>/masterdata/list/<?= $type ?>?page=<?= $pagination['page'] + 1 ?>&search=<?= urlencode($search) ?>&per_page=<?= $pagination['perPage'] ?>">
                        Next
                    </a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- JavaScript for AJAX operations -->
<script>
// Suppress Chrome extension errors
const originalError = console.error;
console.error = function(...args) {
    if (args[0]?.includes?.('message port closed')) return;
    originalError.apply(console, args);
};

document.addEventListener('DOMContentLoaded', function() {
    const type = '<?= $type ?>';
    
    // Toggle Active Status
    document.querySelectorAll('.toggle-active').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            const currentStatus = this.dataset.active === '1';
            
            if (confirm('Change active status for this item?')) {
                fetch('<?= BASE_URL ?>/masterdata/toggleActive/' + type + '/' + id, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Failed to update status: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating status');
                });
            }
        });
    });
    
    <?php if ($config['sortable']): ?>
    // Drag and Drop Reordering
    const tbody = document.getElementById('sortable-tbody');
    if (tbody) {
        let draggedRow = null;
        let draggedIndex = -1;
        let placeholder = null;
        
        // Create placeholder element
        function createPlaceholder() {
            const tr = document.createElement('tr');
            tr.style.height = '3px';
            tr.style.background = '#007bff';
            tr.innerHTML = '<td colspan="100"></td>';
            return tr;
        }
        
        // Make only handles draggable, not the whole row
        tbody.querySelectorAll('tr').forEach(row => {
            const handle = row.querySelector('.sortable-handle');
            if (handle) {
                handle.style.cursor = 'move';
                handle.setAttribute('draggable', 'true');
                
                // Drag events on handle
                handle.addEventListener('dragstart', function(e) {
                    draggedRow = this.closest('tr');
                    draggedIndex = Array.from(tbody.children).indexOf(draggedRow);
                    
                    // Set drag image to the whole row
                    e.dataTransfer.effectAllowed = 'move';
                    e.dataTransfer.setData('text/html', draggedRow.innerHTML);
                    
                    // Add visual feedback
                    setTimeout(() => {
                        draggedRow.style.opacity = '0.4';
                        draggedRow.style.background = '#f8f9fa';
                    }, 0);
                    
                    // Create and insert placeholder
                    placeholder = createPlaceholder();
                });
                
                handle.addEventListener('dragend', function(e) {
                    if (!draggedRow) return;
                    
                    // Store reference before nulling
                    const currentRow = draggedRow;
                    
                    // Remove visual feedback
                    currentRow.style.opacity = '1';
                    currentRow.style.background = '';
                    
                    // Remove placeholder
                    if (placeholder && placeholder.parentNode) {
                        placeholder.remove();
                    }
                    
                    // Check if order changed
                    const newIndex = Array.from(tbody.children).indexOf(currentRow);
                    if (newIndex === draggedIndex) {
                        draggedRow = null;
                        return;
                    }
                    
                    // Collect new order
                    const order = {};
                    tbody.querySelectorAll('tr').forEach((row, index) => {
                        order[row.dataset.id] = index;
                    });
                    
                    console.log('Sending order:', order);
                    
                    // Clear drag state immediately
                    draggedRow = null;
                    draggedIndex = -1;
                    
                    // Send to server
                    fetch('<?= BASE_URL ?>/masterdata/reorder/' + type, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ order: order })
                    })
                    .then(response => {
                        console.log('Response status:', response.status);
                        return response.json();
                    })
                    .then(data => {
                        console.log('Response data:', data);
                        if (data && data.success) {
                            // Show success indicator on the row that was dragged
                            const handle = currentRow.querySelector('.sortable-handle');
                            if (handle) {
                                const originalHTML = handle.innerHTML;
                                handle.innerHTML = '<i class="bi bi-check-circle text-success"></i>';
                                setTimeout(() => {
                                    handle.innerHTML = originalHTML;
                                }, 1500);
                            }
                        } else {
                            console.error('Failed to update order:', data);
                            alert('Failed to update order: ' + (data ? data.message : 'Unknown error'));
                            location.reload();
                        }
                    })
                    .catch(error => {
                        // Only show alert for real network errors, not browser navigation
                        if (error.name !== 'AbortError') {
                            console.error('Error:', error);
                            // Don't alert - just log it, as the order was likely saved
                            // alert('An error occurred while updating order');
                        }
                    });
                });
            }
        });
        
        // Dragover on tbody
        tbody.addEventListener('dragover', function(e) {
            e.preventDefault();
            if (!draggedRow) return;
            
            const afterElement = getDragAfterElement(tbody, e.clientY);
            
            if (afterElement == null) {
                tbody.appendChild(draggedRow);
            } else {
                tbody.insertBefore(draggedRow, afterElement);
            }
        });
        
        // Helper function to determine drop position
        function getDragAfterElement(container, y) {
            const draggableElements = [...container.querySelectorAll('tr:not([style*="opacity: 0.4"])')];
            
            return draggableElements.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;
                
                if (offset < 0 && offset > closest.offset) {
                    return { offset: offset, element: child };
                } else {
                    return closest;
                }
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        }
    }
    <?php endif; ?>
});
</script>
