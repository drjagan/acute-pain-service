<div class="mb-4">
    <h1 class="h2">Edit Patient</h1>
    <p class="text-muted">Update patient registration & demographics</p>
</div>

<form id="patient-edit-form" method="POST" action="<?= BASE_URL ?>/patients/update/<?= $patient['id'] ?>">
    <?= \Helpers\CSRF::field() ?>
    
    <!-- Section 1: Patient Identification -->
    <div class="card mb-3">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-person-badge"></i> Patient Identification</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="patient_name" class="form-label">Patient Name <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control" 
                           id="patient_name" 
                           name="patient_name" 
                           value="<?= e($patient['patient_name']) ?>"
                           required 
                           autofocus
                           placeholder="Full name">
                    <div class="invalid-feedback">Please provide patient name</div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="hospital_number" class="form-label">Hospital Number <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control" 
                           id="hospital_number" 
                           name="hospital_number"
                           value="<?= e($patient['hospital_number']) ?>"
                           required
                           placeholder="Unique hospital ID">
                    <div class="invalid-feedback">Please provide hospital number</div>
                    <div class="form-text" id="hospital-number-feedback"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Section 2: Demographics -->
    <div class="card mb-3">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="bi bi-people"></i> Demographics</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="age" class="form-label">Age (years) <span class="text-danger">*</span></label>
                    <input type="number" 
                           class="form-control" 
                           id="age" 
                           name="age"
                           value="<?= $patient['age'] ?>"
                           min="0" 
                           max="120" 
                           required>
                    <div class="invalid-feedback">Age must be between 0 and 120</div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                    <select class="form-select" id="gender" name="gender" required>
                        <option value="">Select...</option>
                        <option value="male" <?= $patient['gender'] === 'male' ? 'selected' : '' ?>>Male</option>
                        <option value="female" <?= $patient['gender'] === 'female' ? 'selected' : '' ?>>Female</option>
                        <option value="transgender" <?= $patient['gender'] === 'transgender' ? 'selected' : '' ?>>Transgender</option>
                    </select>
                    <div class="invalid-feedback">Please select gender</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Section 3: Anthropometric Data -->
    <div class="card mb-3">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="bi bi-rulers"></i> Anthropometric Data</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="height" class="form-label">Height <span class="text-danger">*</span></label>
                    <input type="number" 
                           class="form-control" 
                           id="height" 
                           name="height"
                           value="<?= $patient['height'] ?>"
                           step="0.01" 
                           min="50" 
                           max="250" 
                           required>
                    <div class="invalid-feedback">Height must be 50-250 cm</div>
                </div>
                
                <div class="col-md-2 mb-3">
                    <label for="height_unit" class="form-label">Unit</label>
                    <select class="form-select" id="height_unit" name="height_unit">
                        <option value="cm" <?= $patient['height_unit'] === 'cm' ? 'selected' : '' ?>>cm</option>
                        <option value="feet" <?= $patient['height_unit'] === 'feet' ? 'selected' : '' ?>>feet</option>
                    </select>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="weight" class="form-label">Weight (kg) <span class="text-danger">*</span></label>
                    <input type="number" 
                           class="form-control" 
                           id="weight" 
                           name="weight"
                           value="<?= $patient['weight'] ?>"
                           step="0.01" 
                           min="20" 
                           max="300" 
                           required>
                    <div class="invalid-feedback">Weight must be 20-300 kg</div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="bmi" class="form-label">BMI (auto-calculated)</label>
                    <input type="text" 
                           class="form-control bg-light" 
                           id="bmi" 
                           name="bmi"
                           value="<?= number_format($patient['bmi'], 2) ?>"
                           readonly 
                           placeholder="0.00">
                    <div class="form-text">Automatically calculated from height and weight</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Section 4: Clinical Information -->
    <div class="card mb-3">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="bi bi-clipboard2-pulse"></i> Clinical Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="comorbid_illness" class="form-label">Comorbid Illness</label>
                    <select class="form-select" 
                            id="comorbid_illness" 
                            name="comorbid_illness[]" 
                            multiple 
                            size="5">
                        <?php 
                        $selectedComorbidities = $patient['comorbid_illness'] ?? [];
                        foreach ($comorbidities as $comorbidity): 
                        ?>
                        <option value="<?= $comorbidity['id'] ?>"
                                <?= in_array($comorbidity['id'], $selectedComorbidities) ? 'selected' : '' ?>>
                            <?= e($comorbidity['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Hold Ctrl/Cmd to select multiple</div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="speciality" class="form-label">Speciality <span class="text-danger">*</span></label>
                    <select class="form-select" id="speciality" name="speciality" required>
                        <option value="">Select...</option>
                        <?php foreach ($specialities as $key => $value): ?>
                        <option value="<?= $key ?>" <?= $patient['speciality'] === $key ? 'selected' : '' ?>>
                            <?= $value ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Please select speciality</div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="diagnosis" class="form-label">Diagnosis <span class="text-danger">*</span></label>
                    <textarea class="form-control" 
                              id="diagnosis" 
                              name="diagnosis" 
                              rows="2" 
                              required
                              placeholder="Primary diagnosis"><?= e($patient['diagnosis']) ?></textarea>
                    <div class="invalid-feedback">Please provide diagnosis</div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-9 mb-3">
                    <label for="surgery" class="form-label">Surgery</label>
                    <select class="form-select" 
                            id="surgery" 
                            name="surgery[]" 
                            multiple 
                            size="4">
                        <?php 
                        $selectedSurgeries = $patient['surgery'] ?? [];
                        foreach ($surgeries as $surgery): 
                        ?>
                        <option value="<?= $surgery['id'] ?>"
                                <?= in_array($surgery['id'], $selectedSurgeries) ? 'selected' : '' ?>>
                            <?= e($surgery['name']) ?>
                            <?php if ($surgery['speciality']): ?>
                                <small>(<?= e(ucwords(str_replace('_', ' ', $surgery['speciality']))) ?>)</small>
                            <?php endif; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Hold Ctrl/Cmd to select multiple procedures</div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="asa_status" class="form-label">ASA Status <span class="text-danger">*</span></label>
                    <select class="form-select" id="asa_status" name="asa_status" required>
                        <option value="">Select...</option>
                        <option value="1" <?= $patient['asa_status'] == 1 ? 'selected' : '' ?>>ASA I - Healthy</option>
                        <option value="2" <?= $patient['asa_status'] == 2 ? 'selected' : '' ?>>ASA II - Mild systemic disease</option>
                        <option value="3" <?= $patient['asa_status'] == 3 ? 'selected' : '' ?>>ASA III - Severe systemic disease</option>
                        <option value="4" <?= $patient['asa_status'] == 4 ? 'selected' : '' ?>>ASA IV - Life-threatening</option>
                        <option value="5" <?= $patient['asa_status'] == 5 ? 'selected' : '' ?>>ASA V - Moribund</option>
                    </select>
                    <div class="invalid-feedback">Please select ASA status</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Section 6: Assign Physicians (v1.1) -->
    <div class="card mb-3">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="bi bi-people"></i> Assign Physicians</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="attending_physicians" class="form-label">
                        <i class="bi bi-person-badge"></i> Attending Physicians
                    </label>
                    <select class="form-select" 
                            id="attending_physicians" 
                            name="attending_physicians[]" 
                            multiple="multiple"
                            style="width: 100%">
                        <?php if (isset($attendings) && !empty($attendings)): ?>
                            <?php 
                            $selectedAttendingIds = array_column($patient['attending_physicians'] ?? [], 'user_id');
                            ?>
                            <?php foreach ($attendings as $attending): ?>
                                <option value="<?= $attending['id'] ?>"
                                        <?= in_array($attending['id'], $selectedAttendingIds) ? 'selected' : '' ?>>
                                    <?= e($attending['display_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <small class="form-text text-muted">
                        Select attending physicians or admins (admins have attending privileges). First selected will be primary.
                    </small>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="residents" class="form-label">
                        <i class="bi bi-person"></i> Residents
                    </label>
                    <select class="form-select" 
                            id="residents" 
                            name="residents[]" 
                            multiple="multiple"
                            style="width: 100%">
                        <?php if (isset($residents) && !empty($residents)): ?>
                            <?php 
                            $selectedResidentIds = array_column($patient['residents'] ?? [], 'user_id');
                            ?>
                            <?php foreach ($residents as $resident): ?>
                                <option value="<?= $resident['id'] ?>"
                                        <?= in_array($resident['id'], $selectedResidentIds) ? 'selected' : '' ?>>
                                    <?= e($resident['display_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <small class="form-text text-muted">
                        Select one or more residents. First selected will be primary.
                    </small>
                </div>
            </div>
            
            <div class="alert alert-info mb-0">
                <i class="bi bi-info-circle"></i>
                <strong>Note:</strong> Assigned physicians will receive notifications when this patient's status changes.
            </div>
        </div>
    </div>
    
    <!-- Form Actions -->
    <div class="d-flex justify-content-between">
        <a href="<?= BASE_URL ?>/patients/viewPatient/<?= $patient['id'] ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Cancel
        </a>
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="bi bi-save"></i> Update Patient
        </button>
    </div>
</form>

<script>
// BMI Auto-calculation
document.addEventListener('DOMContentLoaded', function() {
    const heightInput = document.getElementById('height');
    const weightInput = document.getElementById('weight');
    const bmiInput = document.getElementById('bmi');
    
    function calculateBMI() {
        const height = parseFloat(heightInput.value);
        const weight = parseFloat(weightInput.value);
        
        if (height > 0 && weight > 0) {
            const heightM = height / 100; // convert cm to meters
            const bmi = (weight / (heightM * heightM)).toFixed(2);
            bmiInput.value = bmi;
            
            // Add color coding
            bmiInput.classList.remove('bg-success', 'bg-warning', 'bg-danger', 'text-white');
            if (bmi < 18.5 || (bmi >= 25 && bmi < 30)) {
                bmiInput.classList.add('bg-warning');
            } else if (bmi >= 30) {
                bmiInput.classList.add('bg-danger', 'text-white');
            } else {
                bmiInput.classList.add('bg-success', 'text-white');
            }
        }
    }
    
    // Calculate on page load
    calculateBMI();
    
    heightInput.addEventListener('input', calculateBMI);
    weightInput.addEventListener('input', calculateBMI);
});

// AJAX Hospital Number Validation
document.addEventListener('DOMContentLoaded', function() {
    const hospitalNumberInput = document.getElementById('hospital_number');
    const feedback = document.getElementById('hospital-number-feedback');
    const originalHospitalNumber = '<?= e($patient['hospital_number']) ?>';
    let timeoutId;
    
    hospitalNumberInput.addEventListener('input', function() {
        clearTimeout(timeoutId);
        const value = this.value.trim();
        
        // If same as original, skip validation
        if (value === originalHospitalNumber) {
            feedback.textContent = '';
            this.classList.remove('is-valid', 'is-invalid');
            return;
        }
        
        if (value.length < 3) {
            feedback.textContent = '';
            this.classList.remove('is-valid', 'is-invalid');
            return;
        }
        
        timeoutId = setTimeout(async function() {
            try {
                const response = await fetch('<?= BASE_URL ?>/patients/check-hospital-number', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'hospital_number=' + encodeURIComponent(value) + '&exclude_id=<?= $patient['id'] ?>'
                });
                
                const result = await response.json();
                
                if (result.available) {
                    hospitalNumberInput.classList.remove('is-invalid');
                    hospitalNumberInput.classList.add('is-valid');
                    feedback.className = 'form-text text-success';
                    feedback.textContent = '✓ ' + result.message;
                } else {
                    hospitalNumberInput.classList.remove('is-valid');
                    hospitalNumberInput.classList.add('is-invalid');
                    feedback.className = 'form-text text-danger';
                    feedback.textContent = '✗ ' + result.message;
                }
            } catch (error) {
                console.error('Validation error:', error);
            }
        }, 500);
    });
});

// Form validation
(function() {
    'use strict';
    const form = document.getElementById('patient-edit-form');
    
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        form.classList.add('was-validated');
    }, false);
})();

// Initialize Select2 for physician dropdowns (v1.1)
jQuery(document).ready(function($) {
    $('#attending_physicians').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select attending physicians',
        allowClear: true,
        width: '100%'
    });
    
    $('#residents').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select residents',
        allowClear: true,
        width: '100%'
    });
});
</script>
