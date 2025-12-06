<link rel="stylesheet" href="<?= \App\View::relAsset('assets/css/kyc-application.css') ?>">
<script src="<?= \App\View::relAsset('assets/js/kyc-form.js') ?>" defer></script>

<div class="kyc-application-form">
    <!-- Form Header -->
    <header class="form-header">
        <div class="container">
            <div class="header-content">
                <a href="<?= \App\View::relUrl('/') ?>" class="logo-link">
                    <i class="icon-hammer"></i> Build Mate
                </a>
                <div class="progress-indicator" id="progressIndicator">
                    <span class="progress-text">Step <span id="currentStep">1</span> of 4</span>
                    <div class="progress-dots">
                        <div class="dot active" data-step="1"></div>
                        <div class="dot" data-step="2"></div>
                        <div class="dot" data-step="3"></div>
                        <div class="dot" data-step="4"></div>
    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Form Container -->
    <div class="form-container">
        <div class="container">
            <div class="form-layout">
                <!-- Main Form Area (60%) -->
                <div class="form-main">
                    <form id="kycForm" method="POST" action="<?= \App\View::relUrl('/supplier/kyc/') ?>" enctype="multipart/form-data" class="multi-step-form">
            <?= \App\Csrf::field() ?>
                
                        <!-- Step 1: Business Information -->
                        <div class="form-step active" data-step="1" id="step1">
                            <div class="step-header">
                                <div class="step-progress">
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: 25%"></div>
                                    </div>
                                    <span class="step-number">Step 1 of 4</span>
                                </div>
                                <h2 class="step-title">
                                    <i class="bi bi-building"></i> Tell us about your business
                                </h2>
                                <p class="step-description">
                                    Let's start with the basics. We need this to verify your company and ensure Build Mate remains a trusted marketplace.
                                </p>
                            </div>

                            <div class="form-fields">
                                <!-- Business Name -->
                                <div class="form-group">
                                    <label for="business_name" class="form-label">
                                        Legal Business Name <span class="required">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-input" 
                                           id="business_name" 
                                           name="business_name" 
                                           value="<?= \App\View::e($supplier['business_name'] ?? '') ?>"
                                           placeholder="WATER WORKS LTD"
                                           required>
                                    <div class="form-helper">This should match your registration documents</div>
                                    <div class="form-error" id="business_name_error"></div>
                                </div>

                                <!-- Registration Number -->
                                <div class="form-group">
                                    <label for="reg_number" class="form-label">
                                        Company Registration Number <span class="required">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-input" 
                                           id="reg_number" 
                                           name="reg_number" 
                                           value="<?= \App\View::e($supplier['business_registration'] ?? '') ?>"
                                           placeholder="CS-123456789"
                                           required>
                                    <div class="form-helper">Format: CS-XXXXXXXXX</div>
                                    <div class="form-error" id="reg_number_error"></div>
                                    <div class="form-success" id="reg_number_success">
                                        <i class="bi bi-check-circle"></i> Format looks good
                                    </div>
                                </div>

                                <!-- Business Address -->
                                <div class="form-group">
                                    <label for="address" class="form-label">
                                        Business Address <span class="required">*</span>
                                    </label>
                                    <textarea class="form-input form-textarea" 
                                              id="address" 
                                              name="address" 
                                              rows="3"
                                              placeholder="Street address"
                                              required><?= \App\View::e($supplier['address'] ?? '') ?></textarea>
                                    <div class="form-error" id="address_error"></div>
                                </div>

                                <!-- City and Region -->
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="city" class="form-label">
                                            City <span class="required">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-input" 
                                               id="city" 
                                               name="city" 
                                               placeholder="Berekuso"
                                               required>
                                        <div class="form-error" id="city_error"></div>
                                    </div>
                                    <div class="form-group">
                                        <label for="region" class="form-label">
                                            Region <span class="required">*</span>
                                        </label>
                                        <select class="form-input form-select" id="region" name="region" required>
                                            <option value="">Select Region</option>
                                            <option value="Greater Accra">Greater Accra</option>
                                            <option value="Ashanti">Ashanti</option>
                                            <option value="Western">Western</option>
                                            <option value="Eastern">Eastern</option>
                                            <option value="Central">Central</option>
                                            <option value="Volta">Volta</option>
                                            <option value="Northern">Northern</option>
                                            <option value="Upper East">Upper East</option>
                                            <option value="Upper West">Upper West</option>
                                            <option value="Brong Ahafo">Brong Ahafo</option>
                                            <option value="Western North">Western North</option>
                                            <option value="Ahafo">Ahafo</option>
                                            <option value="Bono">Bono</option>
                                            <option value="Bono East">Bono East</option>
                                            <option value="Oti">Oti</option>
                                            <option value="Savannah">Savannah</option>
                                            <option value="North East">North East</option>
                                        </select>
                                        <div class="form-error" id="region_error"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="step-actions">
                                <button type="button" class="btn-primary" onclick="nextStep()">
                                    Continue to Contact Info <i class="bi bi-arrow-right"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 2: Contact Information -->
                        <div class="form-step" data-step="2" id="step2">
                            <div class="step-header">
                                <div class="step-progress">
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: 50%"></div>
                                    </div>
                                    <span class="step-number">Step 2 of 4</span>
                                </div>
                                <h2 class="step-title">
                                    <i class="bi bi-telephone"></i> How can we reach you?
                                </h2>
                                <p class="step-description">
                                    We'll use these details to contact you about your application and important account updates.
                                </p>
                            </div>

                            <div class="form-fields">
                                <!-- Note: Contact info is optional for now -->
                                <div class="form-group">
                                    <div class="info-box">
                                        <i class="bi bi-info-circle"></i>
                                        <p>Your contact information is already on file from your account registration. We'll use that to contact you about your application.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="step-actions">
                                <button type="button" class="btn-secondary" onclick="prevStep()">
                                    <i class="icon-arrow-left"></i> Back to Business Info
                                </button>
                                <button type="button" class="btn-primary" onclick="nextStep()">
                                    Continue to Documents <i class="bi bi-arrow-right"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 3: Document Uploads -->
                        <div class="form-step" data-step="3" id="step3">
                            <div class="step-header">
                                <div class="step-progress">
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: 75%"></div>
                                    </div>
                                    <span class="step-number">Step 3 of 4</span>
                                </div>
                                <h2 class="step-title">
                                    <i class="bi bi-file-earmark"></i> Verify your business
                                </h2>
                                <p class="step-description">
                                    Upload these documents to prove your business registration. All files are encrypted and secure.
                                </p>
                            </div>

                            <div class="form-fields">
                                <!-- Business Registration Certificate -->
                                <div class="form-group">
                                    <label class="form-label">
                                        Business Registration Certificate <span class="required">*</span>
                                    </label>
                                    <div class="upload-area" data-upload="business_reg">
                                        <input type="file" 
                                               class="file-input" 
                                               id="business_reg" 
                                               name="business_reg" 
                                               accept=".pdf,.jpg,.jpeg,.png"
                                               required>
                                        <div class="upload-content">
                                            <i class="bi bi-file-earmark-pdf upload-icon"></i>
                                            <p class="upload-text">
                                                <strong>Drag & drop your file here</strong><br>
                                                or click to browse
                                            </p>
                                            <p class="upload-hint">Accepted: PDF, JPG, PNG • Max size: 5MB</p>
                                        </div>
                                        <div class="upload-progress" style="display: none;">
                                            <div class="progress-bar-upload">
                                                <div class="progress-fill-upload"></div>
                                            </div>
                                            <span class="progress-text-upload">0%</span>
                                        </div>
                                        <div class="upload-success" style="display: none;">
                                            <i class="bi bi-check-circle"></i>
                                            <span class="file-name"></span>
                                            <span class="file-size"></span>
                                            <button type="button" class="remove-file" onclick="removeFile('business_reg')">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="form-error" id="business_reg_error"></div>
                                </div>

                                <!-- Store Photo (optional but we'll keep it) -->
                                <div class="form-group">
                                    <label class="form-label">
                                        Store Photo (Optional)
                                    </label>
                                    <div class="upload-area" data-upload="store_photo">
                                        <input type="file" 
                                               class="file-input" 
                                               id="store_photo" 
                                               name="store_photo" 
                                               accept=".jpg,.jpeg,.png">
                                        <div class="upload-content">
                                            <i class="bi bi-file-earmark-pdf upload-icon"></i>
                                            <p class="upload-text">
                                                <strong>Drag & drop your file here</strong><br>
                                                or click to browse
                                            </p>
                                            <p class="upload-hint">Accepted: PDF, JPG, PNG • Max size: 5MB</p>
                                        </div>
                                        <div class="upload-progress" style="display: none;"></div>
                                        <div class="upload-success" style="display: none;">
                                            <i class="bi bi-check-circle"></i>
                                            <span class="file-name"></span>
                                            <span class="file-size"></span>
                                            <button type="button" class="remove-file" onclick="removeFile('tin_doc')">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="form-error" id="tin_doc_error"></div>
                                </div>

                                <!-- Owner/Director ID -->
                                <div class="form-group">
                                    <label class="form-label">
                                        Owner/Director ID Card <span class="required">*</span>
                                    </label>
                                    <div class="upload-area" data-upload="id_card">
                                        <input type="file" 
                                               class="file-input" 
                                               id="id_card" 
                                               name="id_card" 
                                               accept=".pdf,.jpg,.jpeg,.png"
                                               required>
                                        <div class="upload-content">
                                            <i class="bi bi-file-earmark-image upload-icon"></i>
                                            <p class="upload-text">
                                                <strong>Drag & drop your file here</strong><br>
                                                or click to browse
                                            </p>
                                            <p class="upload-hint">Accepted: PDF, JPG, PNG • Max size: 5MB</p>
                                        </div>
                                        <div class="upload-progress" style="display: none;"></div>
                                        <div class="upload-success" style="display: none;">
                                            <i class="bi bi-check-circle"></i>
                                            <span class="file-name"></span>
                                            <span class="file-size"></span>
                                            <button type="button" class="remove-file" onclick="removeFile('id_card')">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="form-error" id="id_card_error"></div>
                                </div>
                            </div>

                            <div class="step-actions">
                                <button type="button" class="btn-secondary" onclick="prevStep()">
                                    <i class="icon-arrow-left"></i> Back to Contact Info
                                </button>
                                <button type="button" class="btn-primary" onclick="nextStep()">
                                    Continue to Review <i class="bi bi-arrow-right"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 4: Agreement & Submit -->
                        <div class="form-step" data-step="4" id="step4">
                            <div class="step-header">
                                <div class="step-progress">
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: 100%"></div>
                                    </div>
                                    <span class="step-number">Step 4 of 4</span>
                                </div>
                                <h2 class="step-title">
                                    <i class="bi bi-check-circle"></i> Almost there!
                                </h2>
                                <p class="step-description">
                                    Review your information and accept our terms to complete your application.
                                </p>
                            </div>

                            <div class="form-fields">
                                <!-- Review Summary -->
                                <div class="review-summary">
                                    <div class="review-card" data-section="business">
                                        <div class="review-header" onclick="toggleReview('business')">
                                            <h3>Business Information</h3>
                                            <i class="bi bi-chevron-down"></i>
                                        </div>
                                        <div class="review-content">
                                            <div class="review-item">
                                                <span class="review-label">Business Name:</span>
                                                <span class="review-value" id="review_business_name">-</span>
                                            </div>
                                            <div class="review-item">
                                                <span class="review-label">Registration Number:</span>
                                                <span class="review-value" id="review_reg_number">-</span>
                                            </div>
                                            <div class="review-item">
                                                <span class="review-label">Address:</span>
                                                <span class="review-value" id="review_address">-</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="review-card" data-section="contact">
                                        <div class="review-header" onclick="toggleReview('contact')">
                                            <h3>Contact Information</h3>
                                            <i class="bi bi-chevron-down"></i>
                                        </div>
                                        <div class="review-content">
                                            <div class="review-item">
                                                <span class="review-label">Contact Name:</span>
                                                <span class="review-value" id="review_contact_name">-</span>
                                            </div>
                                            <div class="review-item">
                                                <span class="review-label">Email:</span>
                                                <span class="review-value" id="review_contact_email">-</span>
                                            </div>
                                            <div class="review-item">
                                                <span class="review-label">Phone:</span>
                                                <span class="review-value" id="review_contact_phone">-</span>
                                            </div>
                                        </div>
                </div>
                
                                    <div class="review-card" data-section="documents">
                                        <div class="review-header" onclick="toggleReview('documents')">
                                            <h3>Documents Uploaded</h3>
                                            <i class="bi bi-chevron-down"></i>
                                        </div>
                                        <div class="review-content">
                                            <div class="review-item" id="review_doc_business_reg">
                                                <i class="bi bi-check-circle text-success"></i>
                                                <span>Business Registration Certificate</span>
                                            </div>
                                            <div class="review-item" id="review_doc_id">
                                                <i class="bi bi-check-circle text-success"></i>
                                                <span>Owner ID Card</span>
                                            </div>
                                            <div class="review-item" id="review_doc_store">
                                                <i class="bi bi-check-circle text-success"></i>
                                                <span>Store Photo</span>
                                            </div>
                                        </div>
                                    </div>
                </div>
                
                                <!-- Terms & Conditions -->
                                <div class="form-group">
                                    <div class="checkbox-group">
                                        <input type="checkbox" 
                                               class="form-checkbox" 
                                               id="terms_agreement" 
                                               name="terms_agreement" 
                                               required>
                                        <label for="terms_agreement" class="checkbox-label">
                                            I agree to Build Mate's <a href="<?= \App\View::relUrl('/terms') ?>" target="_blank">Terms of Service</a> and <a href="<?= \App\View::relUrl('/privacy') ?>" target="_blank">Privacy Policy</a>
                                            <span class="required">*</span>
                                        </label>
                                    </div>
                                    <div class="form-error" id="terms_agreement_error"></div>
                </div>
                
                                <div class="form-group">
                                    <div class="checkbox-group">
                                        <input type="checkbox" 
                                               class="form-checkbox" 
                                               id="info_accurate" 
                                               name="info_accurate" 
                                               required>
                                        <label for="info_accurate" class="checkbox-label">
                                            I confirm all information provided is accurate and up-to-date
                                            <span class="required">*</span>
                                        </label>
                                    </div>
                                    <div class="form-error" id="info_accurate_error"></div>
                                </div>
                </div>
                
                            <div class="step-actions">
                                <button type="button" class="btn-secondary" onclick="prevStep()">
                                    <i class="icon-arrow-left"></i> Back to Documents
                                </button>
                                <button type="submit" class="btn-primary btn-submit" id="submitBtn">
                                    <span class="btn-text">Submit Application for Review</span>
                                    <span class="btn-loading" style="display: none;">
                                        <i class="bi bi-hourglass-split"></i> Submitting...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Sidebar (40%) -->
                <aside class="form-sidebar">
                    <div class="sidebar-content">
                        <div class="sidebar-section">
                            <h3>
                                <i class="bi bi-shield-lock"></i> Why we need this information
                            </h3>
                            <ul class="sidebar-list">
                                <li>
                                    <i class="bi bi-check-circle"></i> Verify legitimate businesses
                                </li>
                                <li>
                                    <i class="bi bi-check-circle"></i> Prevent fraud on marketplace
                                </li>
                                <li>
                                    <i class="bi bi-check-circle"></i> Build buyer trust
                                </li>
                                <li>
                                    <i class="bi bi-check-circle"></i> Comply with regulations
                                </li>
                            </ul>
                        </div>

                        <div class="sidebar-divider"></div>

                        <div class="sidebar-section">
                            <h3>
                                <i class="bi bi-trophy"></i> What you get as verified
                            </h3>
                            <ul class="sidebar-list">
                                <li>
                                    <i class="icon-star"></i> Verified badge on products
                                </li>
                                <li>
                                    <i class="icon-star"></i> Priority in search results
                                </li>
                                <li>
                                    <i class="icon-star"></i> Access to bulk order tools
                                </li>
                                <li>
                                    <i class="icon-star"></i> Payment protection
                                </li>
                            </ul>
                        </div>

                        <div class="sidebar-divider"></div>

                        <div class="sidebar-section">
                            <h3>
                                <i class="bi bi-question-circle"></i> Need help?
                            </h3>
                            <a href="<?= \App\View::relUrl('/contact') ?>" class="btn-sidebar">
                                <i class="bi bi-chat-dots"></i> Chat with Support
                            </a>
                </div>
                
                        <div class="sidebar-divider"></div>

                        <div class="sidebar-section">
                            <h3>
                                <i class="bi bi-lock"></i> Your data is secure
                            </h3>
                            <div class="security-badges">
                                <span class="badge-item">256-bit</span>
                                <span class="badge-item">GDPR</span>
                                <span class="badge-item">Encrypted</span>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</div>
