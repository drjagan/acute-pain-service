-- Lookup Data Seeds for Medical Information

-- Comorbidities lookup data
INSERT INTO lookup_comorbidities (name, description, active, sort_order) VALUES
('Diabetes Mellitus', 'Type 1 or Type 2 Diabetes', 1, 1),
('Hypertension', 'High blood pressure', 1, 2),
('Coronary Artery Disease', 'CAD/IHD', 1, 3),
('Chronic Kidney Disease', 'CKD', 1, 4),
('COPD', 'Chronic Obstructive Pulmonary Disease', 1, 5),
('Asthma', 'Bronchial Asthma', 1, 6),
('Obesity', 'BMI >30', 1, 7),
('Sleep Apnea', 'OSA', 1, 8),
('Hypothyroidism', 'Thyroid disorder', 1, 9),
('None', 'No comorbidities', 1, 10);

-- Drugs lookup data
INSERT INTO lookup_drugs (name, generic_name, typical_concentration, max_dose, unit, active) VALUES
('Bupivacaine', 'Bupivacaine HCl', 0.125, 400, 'mg', 1),
('Ropivacaine', 'Ropivacaine HCl', 0.2, 300, 'mg', 1),
('Levobupivacaine', 'Levobupivacaine HCl', 0.125, 150, 'mg', 1),
('Lignocaine', 'Lidocaine HCl', 0.5, 300, 'mg', 1);

-- Adjuvants lookup data
INSERT INTO lookup_adjuvants (name, typical_dose, unit, active) VALUES
('Fentanyl', 2, 'mcg/ml', 1),
('Morphine', 0.05, 'mg/ml', 1),
('Clonidine', 1, 'mcg/ml', 1),
('Dexmedetomidine', 0.5, 'mcg/ml', 1);

-- Red Flags lookup data
INSERT INTO lookup_red_flags (name, severity, requires_immediate_action, active) VALUES
('Hypotension during insertion', 'moderate', 0, 1),
('Bradycardia during insertion', 'moderate', 0, 1),
('Paresthesia', 'mild', 0, 1),
('Blood in catheter', 'severe', 1, 1),
('Dural puncture', 'severe', 1, 1),
('Failed block', 'mild', 0, 1),
('Patient discomfort/anxiety', 'mild', 0, 1);

-- Surgeries lookup data (sample)
INSERT INTO lookup_surgeries (name, speciality, active, sort_order) VALUES
('Total Knee Replacement', 'orthopaedics', 1, 1),
('Total Hip Replacement', 'orthopaedics', 1, 2),
('Cesarean Section', 'obg', 1, 3),
('Laparotomy', 'general_surgery', 1, 4),
('Thoracotomy', 'cardiothoracic', 1, 5),
('Mastectomy', 'oncosurgery', 1, 6),
('Nephrectomy', 'urology', 1, 7),
('CABG', 'cardiothoracic', 1, 8),
('Spinal Fusion', 'orthopaedics', 1, 9),
('Abdominal Hysterectomy', 'obg', 1, 10),
('Whipple Procedure', 'oncosurgery', 1, 11),
('Radical Prostatectomy', 'urology', 1, 12),
('Cholecystectomy', 'general_surgery', 1, 13),
('Appendectomy', 'general_surgery', 1, 14),
('Hernia Repair', 'general_surgery', 1, 15);
