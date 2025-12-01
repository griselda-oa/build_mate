/**
 * Modern KYC Application Form - Interactive JavaScript
 * Handles step navigation, validation, file uploads, and form submission
 */

(function() {
    'use strict';

    let currentStep = 1;
    const totalSteps = 4;
    const formData = {
        files: {}
    };

    // Initialize on DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        initStepNavigation();
        initFormValidation();
        initFileUploads();
        initReviewSummary();
        updateProgressIndicator();
    });

    /**
     * Step Navigation
     */
    function initStepNavigation() {
        // Prevent form submission on Enter key
        document.getElementById('kycForm').addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
                e.preventDefault();
            }
        });
    }

    function nextStep() {
        if (validateCurrentStep()) {
            if (currentStep < totalSteps) {
                currentStep++;
                showStep(currentStep);
                updateProgressIndicator();
                updateReviewSummary();
            }
        }
    }

    function prevStep() {
        if (currentStep > 1) {
            currentStep--;
            showStep(currentStep);
            updateProgressIndicator();
        }
    }

    function showStep(step) {
        // Hide all steps
        document.querySelectorAll('.form-step').forEach(s => {
            s.classList.remove('active');
        });

        // Show current step
        const stepElement = document.getElementById(`step${step}`);
        if (stepElement) {
            stepElement.classList.add('active');
            stepElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function updateProgressIndicator() {
        document.getElementById('currentStep').textContent = currentStep;
        
        document.querySelectorAll('.progress-dots .dot').forEach((dot, index) => {
            dot.classList.remove('active', 'completed');
            if (index + 1 < currentStep) {
                dot.classList.add('completed');
            } else if (index + 1 === currentStep) {
                dot.classList.add('active');
            }
        });
    }

    // Make functions global for onclick handlers
    window.nextStep = nextStep;
    window.prevStep = prevStep;

    /**
     * Form Validation
     */
    function initFormValidation() {
        // Real-time validation for business name
        const businessName = document.getElementById('business_name');
        if (businessName) {
            businessName.addEventListener('blur', function() {
                validateBusinessName(this.value);
            });
        }

        // Real-time validation for registration number
        const regNumber = document.getElementById('reg_number');
        if (regNumber) {
            regNumber.addEventListener('input', function() {
                formatRegistrationNumber(this);
                validateRegistrationNumber(this.value);
            });
        }

        // Real-time validation for email
        const contactEmail = document.getElementById('contact_email');
        if (contactEmail) {
            contactEmail.addEventListener('blur', function() {
                validateEmail(this.value);
            });
        }

        // Real-time validation for phone
        const contactPhone = document.getElementById('contact_phone');
        if (contactPhone) {
            contactPhone.addEventListener('input', function() {
                formatPhoneNumber(this);
            });
            contactPhone.addEventListener('blur', function() {
                validatePhone(this.value);
            });
        }
    }

    function validateCurrentStep() {
        const stepElement = document.getElementById(`step${currentStep}`);
        if (!stepElement) {
            console.error('Step element not found for step', currentStep);
            return false;
        }

        const inputs = stepElement.querySelectorAll('input[required], select[required], textarea[required]');
        let isValid = true;

        inputs.forEach(input => {
            if (input.type === 'file') {
                if (!formData.files[input.name] || formData.files[input.name].length === 0) {
                    showError(input.name, 'This file is required');
                    isValid = false;
                }
            } else if (input.type === 'checkbox') {
                if (!input.checked) {
                    showError(input.name, 'You must accept this to continue');
                    isValid = false;
                } else {
                    clearError(input.name);
                }
            } else {
                const value = input.value ? input.value.trim() : '';
                if (!value) {
                    showError(input.name, 'This field is required');
                    isValid = false;
                } else {
                    clearError(input.name);
                }
            }
        });

        // Step-specific validation
        if (currentStep === 1) {
            const businessName = document.getElementById('business_name')?.value?.trim() || '';
            const regNumber = document.getElementById('reg_number')?.value?.trim() || '';
            const address = document.getElementById('address')?.value?.trim() || '';
            const city = document.getElementById('city')?.value?.trim() || '';
            const region = document.getElementById('region')?.value?.trim() || '';
            
            if (!businessName || !validateBusinessName(businessName)) {
                isValid = false;
            }
            if (!regNumber || !validateRegistrationNumber(regNumber)) {
                isValid = false;
            }
            if (!address || address.length < 10) {
                showError('address', 'Business address must be at least 10 characters');
                isValid = false;
            } else {
                clearError('address');
            }
            if (!city || city.length < 2) {
                showError('city', 'City is required');
                isValid = false;
            } else {
                clearError('city');
            }
            if (!region) {
                showError('region', 'Region is required');
                isValid = false;
            } else {
                clearError('region');
            }
        } else if (currentStep === 2) {
            // Step 2 is informational only now, no validation needed
            isValid = true; // Always allow proceeding from step 2
        }

        if (!isValid) {
            console.log('Validation failed for step', currentStep);
            // Scroll to first error
            const firstError = stepElement.querySelector('.form-error.show, .form-input.error, .form-select.error, .form-textarea.error');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        return isValid;
    }

    function validateBusinessName(value) {
        const input = document.getElementById('business_name');
        const errorEl = document.getElementById('business_name_error');
        
        if (!value || value.trim().length < 2) {
            showError('business_name', 'Business name must be at least 2 characters');
            if (input) input.classList.add('error');
            return false;
        }
        
        if (value.length > 100) {
            showError('business_name', 'Business name must be less than 100 characters');
            if (input) input.classList.add('error');
            return false;
        }
        
        clearError('business_name');
        if (input) {
            input.classList.remove('error');
            input.classList.add('success');
        }
        return true;
    }

    function formatRegistrationNumber(input) {
        let value = input.value.replace(/[^A-Z0-9-]/gi, '');
        
        // Auto-format: CS-XXXXXXXXX
        if (value.length > 2 && !value.includes('-')) {
            value = value.substring(0, 2) + '-' + value.substring(2);
        }
        
        input.value = value.toUpperCase();
    }

    function validateRegistrationNumber(value) {
        const input = document.getElementById('reg_number');
        const errorEl = document.getElementById('reg_number_error');
        const successEl = document.getElementById('reg_number_success');
        
        const pattern = /^[A-Z]{2,3}-\d{6,9}$/;
        
        if (!value) {
            clearError('reg_number');
            if (successEl) successEl.classList.remove('show');
            return false;
        }
        
        if (!pattern.test(value)) {
            showError('reg_number', 'Format should be: CS-XXXXXXXXX');
            if (input) input.classList.add('error');
            if (successEl) successEl.classList.remove('show');
            return false;
        }
        
        clearError('reg_number');
        if (input) {
            input.classList.remove('error');
            input.classList.add('success');
        }
        if (successEl) successEl.classList.add('show');
        return true;
    }

    function validateEmail(value) {
        const input = document.getElementById('contact_email');
        const errorEl = document.getElementById('contact_email_error');
        const successEl = document.getElementById('contact_email_success');
        
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (!value) {
            clearError('contact_email');
            if (successEl) successEl.classList.remove('show');
            return false;
        }
        
        if (!emailPattern.test(value)) {
            showError('contact_email', 'Hmm, that doesn\'t look like a valid email');
            if (input) input.classList.add('error');
            if (successEl) successEl.classList.remove('show');
            return false;
        }
        
        clearError('contact_email');
        if (input) {
            input.classList.remove('error');
            input.classList.add('success');
        }
        if (successEl) successEl.classList.add('show');
        return true;
    }

    function formatPhoneNumber(input) {
        let value = input.value.replace(/\D/g, '');
        
        // Format: XX XXX XXXX
        if (value.length > 2) {
            value = value.substring(0, 2) + ' ' + value.substring(2);
        }
        if (value.length > 6) {
            value = value.substring(0, 6) + ' ' + value.substring(6, 10);
        }
        
        input.value = value;
    }

    function validatePhone(value) {
        const input = document.getElementById('contact_phone');
        const digits = value.replace(/\D/g, '');
        
        if (!value || digits.length < 9) {
            showError('contact_phone', 'Please enter a valid phone number');
            if (input) input.classList.add('error');
            return false;
        }
        
        clearError('contact_phone');
        if (input) {
            input.classList.remove('error');
            input.classList.add('success');
        }
        return true;
    }

    function showError(fieldName, message) {
        const errorEl = document.getElementById(`${fieldName}_error`);
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.classList.add('show');
        }
    }

    function clearError(fieldName) {
        const errorEl = document.getElementById(`${fieldName}_error`);
        if (errorEl) {
            errorEl.classList.remove('show');
        }
    }

    /**
     * File Uploads
     */
    function initFileUploads() {
        document.querySelectorAll('.upload-area').forEach(area => {
            const input = area.querySelector('.file-input');
            const uploadName = area.dataset.upload;
            
            if (!input) {
                console.error('File input not found in upload area:', uploadName);
                return;
            }
            
            // Make sure input is clickable and visible to pointer events
            input.style.pointerEvents = 'auto';
            input.style.cursor = 'pointer';
            input.style.zIndex = '10'; // Ensure it's above other elements
            
            // Click to upload - trigger input when clicking anywhere in the upload area
            area.addEventListener('click', function(e) {
                // Don't trigger if clicking on remove button or success message
                if (e.target.classList.contains('remove-file') || 
                    e.target.closest('.remove-file') ||
                    e.target.closest('.upload-success')) {
                    e.stopPropagation();
                    return;
                }
                
                // If clicking directly on the input, let it handle it naturally
                if (e.target === input || e.target === input.parentElement) {
                    return;
                }
                
                // Otherwise, programmatically trigger the input click
                console.log('Upload area clicked, triggering file input for:', uploadName);
                e.preventDefault();
                e.stopPropagation();
                setTimeout(() => {
                    input.click();
                }, 0);
            });
            
            // Also handle direct clicks on the input
            input.addEventListener('click', function(e) {
                console.log('File input clicked directly for:', uploadName);
                e.stopPropagation();
            });
            
            // Drag and drop
            area.addEventListener('dragover', function(e) {
                e.preventDefault();
                area.classList.add('drag-over');
            });
            
            area.addEventListener('dragleave', function() {
                area.classList.remove('drag-over');
            });
            
            area.addEventListener('drop', function(e) {
                e.preventDefault();
                area.classList.remove('drag-over');
                
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    // Create a new FileList-like object and assign to input
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(files[0]);
                    input.files = dataTransfer.files;
                    
                    handleFileUpload(input, files[0], uploadName);
                }
            });
            
            // File input change
            input.addEventListener('change', function(e) {
                console.log('File input changed:', uploadName, this.files);
                if (this.files && this.files.length > 0) {
                    console.log('File selected:', this.files[0].name, this.files[0].type);
                    handleFileUpload(this, this.files[0], uploadName);
                } else {
                    console.warn('No files selected');
                }
            });
        });
    }

    function handleFileUpload(input, file, uploadName) {
        console.log('handleFileUpload called:', uploadName, file.name, file.type, file.size);
        
        // Validate file
        const maxSize = 5 * 1024 * 1024; // 5MB
        const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
        
        // More flexible MIME type checking
        const fileExtension = file.name.split('.').pop().toLowerCase();
        const allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
        const isValidType = allowedTypes.includes(file.type) || allowedExtensions.includes(fileExtension);
        
        if (!isValidType) {
            console.error('Invalid file type:', file.type, fileExtension);
            showError(uploadName, 'Invalid file type. Please upload PDF, JPG, or PNG');
            return;
        }
        
        if (file.size > maxSize) {
            console.error('File too large:', file.size);
            showError(uploadName, 'File size must be less than 5MB');
            return;
        }
        
        clearError(uploadName);
        
        // Store file
        formData.files[uploadName] = file;
        console.log('File stored in formData.files:', uploadName);
        
        // Show upload success
        const uploadArea = input.closest('.upload-area');
        if (!uploadArea) {
            console.error('Upload area not found for input:', input);
            return;
        }
        
        const uploadContent = uploadArea.querySelector('.upload-content');
        const uploadSuccess = uploadArea.querySelector('.upload-success');
        
        if (!uploadContent || !uploadSuccess) {
            console.error('Upload content or success element not found');
            return;
        }
        
        const fileName = uploadSuccess.querySelector('.file-name');
        const fileSize = uploadSuccess.querySelector('.file-size');
        
        if (!fileName || !fileSize) {
            console.error('File name or size element not found');
            return;
        }
        
        if (uploadContent) {
            uploadContent.style.display = 'none';
        }
        if (uploadSuccess) {
            uploadSuccess.style.display = 'flex';
        }
        
        if (fileName) fileName.textContent = file.name;
        if (fileSize) fileSize.textContent = formatFileSize(file.size);
        
        console.log('Upload success UI updated - Content hidden, Success shown');
        
        // Update review summary
        updateReviewSummary();
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    function removeFile(uploadName) {
        const input = document.getElementById(uploadName);
        if (input) {
            input.value = '';
        }
        
        delete formData.files[uploadName];
        
        const uploadArea = input.closest('.upload-area');
        const uploadContent = uploadArea.querySelector('.upload-content');
        const uploadSuccess = uploadArea.querySelector('.upload-success');
        
        uploadContent.style.display = 'block';
        uploadSuccess.style.display = 'none';
        
        updateReviewSummary();
    }

    // Make removeFile global
    window.removeFile = removeFile;

    /**
     * Review Summary
     */
    function initReviewSummary() {
        document.querySelectorAll('.review-header').forEach(header => {
            header.addEventListener('click', function() {
                const card = this.closest('.review-card');
                card.classList.toggle('expanded');
            });
        });
    }

    function toggleReview(section) {
        const card = document.querySelector(`.review-card[data-section="${section}"]`);
        if (card) {
            card.classList.toggle('expanded');
        }
    }

    window.toggleReview = toggleReview;

    function updateReviewSummary() {
        // Business Information
        const businessName = document.getElementById('business_name')?.value || '-';
        const regNumber = document.getElementById('reg_number')?.value || '-';
        const address = document.getElementById('address')?.value || '-';
        const city = document.getElementById('city')?.value || '';
        const region = document.getElementById('region')?.value || '';
        
        document.getElementById('review_business_name').textContent = businessName;
        document.getElementById('review_reg_number').textContent = regNumber;
        document.getElementById('review_address').textContent = address + (city ? ', ' + city : '') + (region ? ', ' + region : '');
        
        // Contact Information (from user session)
        document.getElementById('review_contact_name').textContent = 'On file';
        document.getElementById('review_contact_email').textContent = 'On file';
        document.getElementById('review_contact_phone').textContent = 'On file';
        
        // Documents
        const docFields = ['business_reg', 'id_card', 'store_photo'];
        const docLabels = {
            'business_reg': 'review_doc_business_reg',
            'id_card': 'review_doc_id',
            'store_photo': 'review_doc_store'
        };
        
        docFields.forEach(field => {
            const reviewEl = document.getElementById(docLabels[field]);
            if (reviewEl) {
                if (formData.files[field]) {
                    reviewEl.style.display = 'flex';
                } else {
                    reviewEl.style.display = 'none';
                }
            }
        });
    }

    /**
     * Form Submission
     */
    document.getElementById('kycForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate all steps
        if (!validateCurrentStep()) {
            showStep(currentStep);
            return;
        }
        
        // Validate all files are uploaded
        const requiredFiles = ['business_reg', 'id_card'];
        let allFilesUploaded = true;
        
        requiredFiles.forEach(fileName => {
            if (!formData.files[fileName]) {
                showError(fileName, 'Please upload this document');
                allFilesUploaded = false;
            }
        });
        
        if (!allFilesUploaded) {
            currentStep = 3;
            showStep(3);
            updateProgressIndicator();
            return;
        }
        
        // Show loading state
        const submitBtn = document.getElementById('submitBtn');
        const btnText = submitBtn.querySelector('.btn-text');
        const btnLoading = submitBtn.querySelector('.btn-loading');
        
        submitBtn.disabled = true;
        btnText.style.display = 'none';
        btnLoading.style.display = 'inline-flex';
        
        // Create FormData
        const formDataObj = new FormData(this);
        
        // Append files from formData.files to FormData
        Object.keys(formData.files).forEach(fileName => {
            if (formData.files[fileName]) {
                formDataObj.append(fileName, formData.files[fileName]);
            }
        });
        
        // Ensure buildUrl is available
        if (typeof window.buildUrl !== 'function') {
            // Fallback: create buildUrl if not available
            const basePath = document.querySelector('meta[name="base-path"]')?.content || '/build_mate/';
            window.buildUrl = function(path) {
                path = path ? path.toString().replace(/^\/+/, '') : '';
                const base = basePath.endsWith('/') ? basePath : basePath + '/';
                return path ? base + path : base;
            };
        }
        
        // Submit via AJAX
        fetch(this.action, {
            method: 'POST',
            body: formDataObj,
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            console.log('Response status:', response.status, response.statusText);
            
            // Try to parse as JSON first
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json().then(data => {
                    console.log('JSON response:', data);
                    if (data.success) {
                        // Success - redirect to pending page
                        if (data.redirect) {
                            window.location.href = window.buildUrl(data.redirect);
                        } else {
                            window.location.href = window.buildUrl('/supplier/pending');
                        }
                    } else {
                        // Error from server
                        throw new Error(data.error || 'Submission failed');
                    }
                });
            }
            
            // Handle HTML responses (non-AJAX fallback)
            if (response.ok) {
                // If response is OK but not JSON, it might be a redirect
                if (response.redirected || response.url.includes('/supplier/pending')) {
                    window.location.href = window.buildUrl('/supplier/pending');
                    return;
                }
                return response.text().then(data => {
                    console.log('HTML response received');
                    // Check if response contains error message
                    if (data.includes('error') || data.includes('Error')) {
                        throw new Error('Server returned an error');
                    }
                    // Redirect to pending dashboard
                    window.location.href = window.buildUrl('/supplier/pending');
                });
            }
            
            // Handle error responses
            return response.text().then(errorText => {
                console.error('Error response:', errorText);
                throw new Error(`Server error: ${response.status} ${response.statusText}`);
            });
        })
        .catch(error => {
            console.error('Submission error:', error);
            submitBtn.disabled = false;
            btnText.style.display = 'inline';
            btnLoading.style.display = 'none';
            
            let errorMessage = 'There was an error submitting your application.';
            if (error.message) {
                errorMessage += '\n\n' + error.message;
            } else {
                errorMessage += ' Please try again.';
            }
            alert(errorMessage);
        });
    });

})();