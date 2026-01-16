-- Master Data Seeder
-- Version: 1.2.0
-- Description: Comprehensive seed data for all lookup tables

-- ============================================================================
-- COMORBIDITIES
-- ============================================================================
INSERT IGNORE INTO lookup_comorbidities (name, description, sort_order) VALUES
('Diabetes Mellitus', 'Type 1 or Type 2 diabetes', 1),
('Hypertension', 'High blood pressure', 2),
('Coronary Artery Disease', 'Heart disease', 3),
('Chronic Obstructive Pulmonary Disease (COPD)', 'Chronic lung disease', 4),
('Asthma', 'Reactive airway disease', 5),
('Chronic Kidney Disease', 'Impaired renal function', 6),
('Liver Disease', 'Hepatic insufficiency', 7),
('Obesity', 'BMI > 30', 8),
('Sleep Apnea', 'Obstructive sleep apnea', 9),
('Gastroesophageal Reflux Disease (GERD)', 'Chronic acid reflux', 10),
('Stroke/CVA', 'Previous cerebrovascular accident', 11),
('Peripheral Vascular Disease', 'Impaired peripheral circulation', 12),
('Atrial Fibrillation', 'Irregular heart rhythm', 13),
('Heart Failure', 'Congestive heart failure', 14),
('Epilepsy', 'Seizure disorder', 15),
('Depression', 'Major depressive disorder', 16),
('Anxiety Disorder', 'Generalized anxiety or panic disorder', 17),
('Rheumatoid Arthritis', 'Autoimmune joint disease', 18),
('Osteoarthritis', 'Degenerative joint disease', 19),
('Cancer', 'Active or history of malignancy', 20),
('Immunosuppression', 'Weakened immune system', 21),
('Thyroid Disease', 'Hyper or hypothyroidism', 22),
('Bleeding Disorder', 'Coagulopathy or platelet disorder', 23),
('Anticoagulation Therapy', 'On blood thinners', 24),
('Previous Spinal Surgery', 'History of back surgery', 25),
('Dementia', 'Cognitive impairment', 26),
('HIV/AIDS', 'Human immunodeficiency virus', 27),
('Tuberculosis', 'Active or latent TB', 28),
('Substance Abuse', 'Drug or alcohol dependency', 29),
('None', 'No significant comorbidities', 99);

-- ============================================================================
-- DRUGS
-- ============================================================================
INSERT IGNORE INTO lookup_drugs (name, generic_name, typical_concentration, max_dose, unit) VALUES
-- Local Anesthetics
('Ropivacaine', 'Ropivacaine HCl', 0.20, 400, 'mg'),
('Bupivacaine', 'Bupivacaine HCl', 0.25, 225, 'mg'),
('Levobupivacaine', 'Levobupivacaine HCl', 0.25, 150, 'mg'),
('Lidocaine', 'Lidocaine HCl', 1.00, 300, 'mg'),
('Mepivacaine', 'Mepivacaine HCl', 1.00, 400, 'mg'),
('Chloroprocaine', '2-Chloroprocaine HCl', 2.00, 800, 'mg'),

-- Opioids
('Fentanyl', 'Fentanyl Citrate', 0.002, 100, 'mcg/ml'),
('Morphine', 'Morphine Sulfate', 0.05, 10, 'mg/ml'),
('Hydromorphone', 'Hydromorphone HCl', 0.02, 2, 'mg/ml'),
('Sufentanil', 'Sufentanil Citrate', 0.0005, 1, 'mcg/ml'),
('Diamorphine', 'Diamorphine HCl', 0.10, 5, 'mg/ml'),

-- Alpha-2 Agonists
('Clonidine', 'Clonidine HCl', 0.15, 150, 'mcg'),
('Dexmedetomidine', 'Dexmedetomidine HCl', 0.50, 2, 'mcg/kg/hr'),

-- Other
('Ketamine', 'Ketamine HCl', 5.00, 50, 'mg'),
('Neostigmine', 'Neostigmine Methylsulfate', 0.50, 2, 'mg');

-- ============================================================================
-- ADJUVANTS
-- ============================================================================
INSERT IGNORE INTO lookup_adjuvants (name, typical_dose, unit) VALUES
('Epinephrine', 5, 'mcg/ml'),
('Sodium Bicarbonate', 1, 'mEq/10ml'),
('Dexamethasone', 4, 'mg'),
('Methylprednisolone', 40, 'mg'),
('Magnesium Sulfate', 50, 'mg'),
('Midazolam', 2, 'mg'),
('Ketorolac', 30, 'mg'),
('Normal Saline', 10, 'ml');

-- ============================================================================
-- RED FLAGS (Additional entries beyond migration seed)
-- ============================================================================
INSERT IGNORE INTO lookup_red_flags (name, severity, requires_immediate_action) VALUES
('Blood aspiration', 'severe', TRUE),
('Paresthesia during insertion', 'moderate', FALSE),
('Persistent paresthesia', 'severe', TRUE),
('Motor weakness', 'severe', TRUE),
('Inadvertent dural puncture', 'moderate', TRUE),
('Difficulty advancing catheter', 'mild', FALSE),
('Multiple insertion attempts (>3)', 'moderate', FALSE),
('Patient extreme discomfort', 'moderate', FALSE),
('Hemodynamic instability', 'severe', TRUE),
('Loss of resistance not obtained', 'moderate', FALSE),
('Catheter kinking', 'mild', FALSE),
('Air embolism suspected', 'critical', TRUE);

-- ============================================================================
-- SURGICAL PROCEDURES (Organized by Specialty)
-- ============================================================================

-- General Surgery
INSERT IGNORE INTO lookup_surgeries (name, specialty_id, sort_order)
SELECT 'Laparotomy', id, 1 FROM lookup_specialties WHERE code = 'GEN'
UNION ALL SELECT 'Appendectomy', id, 2 FROM lookup_specialties WHERE code = 'GEN'
UNION ALL SELECT 'Cholecystectomy', id, 3 FROM lookup_specialties WHERE code = 'GEN'
UNION ALL SELECT 'Hernia Repair', id, 4 FROM lookup_specialties WHERE code = 'GEN'
UNION ALL SELECT 'Bowel Resection', id, 5 FROM lookup_specialties WHERE code = 'GEN'
UNION ALL SELECT 'Colectomy', id, 6 FROM lookup_specialties WHERE code = 'GEN'
UNION ALL SELECT 'Gastrectomy', id, 7 FROM lookup_specialties WHERE code = 'GEN'
UNION ALL SELECT 'Whipple Procedure', id, 8 FROM lookup_specialties WHERE code = 'GEN'
UNION ALL SELECT 'Mastectomy', id, 9 FROM lookup_specialties WHERE code = 'GEN'
UNION ALL SELECT 'Thyroidectomy', id, 10 FROM lookup_specialties WHERE code = 'GEN';

-- Orthopedic Surgery
INSERT IGNORE INTO lookup_surgeries (name, specialty_id, sort_order)
SELECT 'Total Hip Replacement', id, 1 FROM lookup_specialties WHERE code = 'ORTHO'
UNION ALL SELECT 'Total Knee Replacement', id, 2 FROM lookup_specialties WHERE code = 'ORTHO'
UNION ALL SELECT 'Hip Fracture Repair', id, 3 FROM lookup_specialties WHERE code = 'ORTHO'
UNION ALL SELECT 'Femur Fracture Fixation', id, 4 FROM lookup_specialties WHERE code = 'ORTHO'
UNION ALL SELECT 'Spinal Fusion', id, 5 FROM lookup_specialties WHERE code = 'ORTHO'
UNION ALL SELECT 'Laminectomy', id, 6 FROM lookup_specialties WHERE code = 'ORTHO'
UNION ALL SELECT 'Shoulder Arthroplasty', id, 7 FROM lookup_specialties WHERE code = 'ORTHO'
UNION ALL SELECT 'ACL Reconstruction', id, 8 FROM lookup_specialties WHERE code = 'ORTHO'
UNION ALL SELECT 'Rotator Cuff Repair', id, 9 FROM lookup_specialties WHERE code = 'ORTHO'
UNION ALL SELECT 'Ankle Fracture Fixation', id, 10 FROM lookup_specialties WHERE code = 'ORTHO';

-- Vascular Surgery
INSERT IGNORE INTO lookup_surgeries (name, specialty_id, sort_order)
SELECT 'Abdominal Aortic Aneurysm Repair', id, 1 FROM lookup_specialties WHERE code = 'VASC'
UNION ALL SELECT 'Carotid Endarterectomy', id, 2 FROM lookup_specialties WHERE code = 'VASC'
UNION ALL SELECT 'Peripheral Bypass', id, 3 FROM lookup_specialties WHERE code = 'VASC'
UNION ALL SELECT 'AV Fistula Creation', id, 4 FROM lookup_specialties WHERE code = 'VASC'
UNION ALL SELECT 'Lower Limb Amputation', id, 5 FROM lookup_specialties WHERE code = 'VASC'
UNION ALL SELECT 'Varicose Vein Stripping', id, 6 FROM lookup_specialties WHERE code = 'VASC';

-- Thoracic Surgery
INSERT IGNORE INTO lookup_surgeries (name, specialty_id, sort_order)
SELECT 'Lobectomy', id, 1 FROM lookup_specialties WHERE code = 'THOR'
UNION ALL SELECT 'Pneumonectomy', id, 2 FROM lookup_specialties WHERE code = 'THOR'
UNION ALL SELECT 'Esophagectomy', id, 3 FROM lookup_specialties WHERE code = 'THOR'
UNION ALL SELECT 'Thoracotomy', id, 4 FROM lookup_specialties WHERE code = 'THOR'
UNION ALL SELECT 'VATS', id, 5 FROM lookup_specialties WHERE code = 'THOR'
UNION ALL SELECT 'Mediastinoscopy', id, 6 FROM lookup_specialties WHERE code = 'THOR';

-- Cardiac Surgery
INSERT IGNORE INTO lookup_surgeries (name, specialty_id, sort_order)
SELECT 'CABG (Coronary Artery Bypass Graft)', id, 1 FROM lookup_specialties WHERE code = 'CARD'
UNION ALL SELECT 'Valve Replacement', id, 2 FROM lookup_specialties WHERE code = 'CARD'
UNION ALL SELECT 'Valve Repair', id, 3 FROM lookup_specialties WHERE code = 'CARD'
UNION ALL SELECT 'Heart Transplant', id, 4 FROM lookup_specialties WHERE code = 'CARD'
UNION ALL SELECT 'Aortic Root Replacement', id, 5 FROM lookup_specialties WHERE code = 'CARD'
UNION ALL SELECT 'Pacemaker Insertion', id, 6 FROM lookup_specialties WHERE code = 'CARD';

-- Neurosurgery
INSERT IGNORE INTO lookup_surgeries (name, specialty_id, sort_order)
SELECT 'Craniotomy', id, 1 FROM lookup_specialties WHERE code = 'NEURO'
UNION ALL SELECT 'Brain Tumor Resection', id, 2 FROM lookup_specialties WHERE code = 'NEURO'
UNION ALL SELECT 'Ventriculoperitoneal Shunt', id, 3 FROM lookup_specialties WHERE code = 'NEURO'
UNION ALL SELECT 'Spinal Decompression', id, 4 FROM lookup_specialties WHERE code = 'NEURO'
UNION ALL SELECT 'Aneurysm Clipping', id, 5 FROM lookup_specialties WHERE code = 'NEURO';

-- Urology
INSERT IGNORE INTO lookup_surgeries (name, specialty_id, sort_order)
SELECT 'Nephrectomy', id, 1 FROM lookup_specialties WHERE code = 'URO'
UNION ALL SELECT 'Cystectomy', id, 2 FROM lookup_specialties WHERE code = 'URO'
UNION ALL SELECT 'Prostatectomy', id, 3 FROM lookup_specialties WHERE code = 'URO'
UNION ALL SELECT 'TURP', id, 4 FROM lookup_specialties WHERE code = 'URO'
UNION ALL SELECT 'Kidney Transplant', id, 5 FROM lookup_specialties WHERE code = 'URO'
UNION ALL SELECT 'Ureteroscopy', id, 6 FROM lookup_specialties WHERE code = 'URO';

-- Gynecology
INSERT IGNORE INTO lookup_surgeries (name, specialty_id, sort_order)
SELECT 'Hysterectomy', id, 1 FROM lookup_specialties WHERE code = 'GYN'
UNION ALL SELECT 'Myomectomy', id, 2 FROM lookup_specialties WHERE code = 'GYN'
UNION ALL SELECT 'Ovarian Cystectomy', id, 3 FROM lookup_specialties WHERE code = 'GYN'
UNION ALL SELECT 'Salpingo-oophorectomy', id, 4 FROM lookup_specialties WHERE code = 'GYN'
UNION ALL SELECT 'Pelvic Exenteration', id, 5 FROM lookup_specialties WHERE code = 'GYN';

-- Obstetrics
INSERT IGNORE INTO lookup_surgeries (name, specialty_id, sort_order)
SELECT 'Cesarean Section', id, 1 FROM lookup_specialties WHERE code = 'OBS'
UNION ALL SELECT 'Cervical Cerclage', id, 2 FROM lookup_specialties WHERE code = 'OBS'
UNION ALL SELECT 'Postpartum Hemorrhage Surgery', id, 3 FROM lookup_specialties WHERE code = 'OBS';

-- Bariatric Surgery
INSERT IGNORE INTO lookup_surgeries (name, specialty_id, sort_order)
SELECT 'Gastric Bypass', id, 1 FROM lookup_specialties WHERE code = 'BARI'
UNION ALL SELECT 'Sleeve Gastrectomy', id, 2 FROM lookup_specialties WHERE code = 'BARI'
UNION ALL SELECT 'Gastric Band', id, 3 FROM lookup_specialties WHERE code = 'BARI';
