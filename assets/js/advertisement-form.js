// Modern Advertisement Form JavaScript

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('adForm');
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('mediaFiles');
    const previewContainer = document.getElementById('previewContainer');
    const submitBtn = document.getElementById('submitBtn');
    const titleInput = document.getElementById('adTitle');
    const descInput = document.getElementById('adDescription');
    const titleCounter = document.getElementById('titleCounter');
    const descCounter = document.getElementById('descCounter');
    
    let uploadedFiles = [];
    const maxFileSize = 10 * 1024 * 1024; // 10MB
    const allowedImageTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    const allowedVideoTypes = ['video/mp4', 'video/mov', 'video/quicktime', 'video/webm'];
    
    // Character counters
    function updateCounter(input, counter, max) {
        const length = input.value.length;
        counter.textContent = length;
        counter.parentElement.classList.remove('warning', 'error');
        if (length > max * 0.9) {
            counter.parentElement.classList.add('error');
        } else if (length > max * 0.75) {
            counter.parentElement.classList.add('warning');
        }
    }
    
    titleInput.addEventListener('input', () => updateCounter(titleInput, titleCounter, 255));
    descInput.addEventListener('input', () => updateCounter(descInput, descCounter, 1000));
    
    // Upload area click
    uploadArea.addEventListener('click', () => fileInput.click());
    
    // Drag and drop
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });
    
    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('dragover');
    });
    
    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        handleFiles(e.dataTransfer.files);
    });
    
    // File input change
    fileInput.addEventListener('change', (e) => {
        handleFiles(e.target.files);
    });
    
    // Handle file uploads
    function handleFiles(files) {
        Array.from(files).forEach(file => {
            // Validate file type
            const isValidImage = allowedImageTypes.includes(file.type);
            const isValidVideo = allowedVideoTypes.includes(file.type);
            
            if (!isValidImage && !isValidVideo) {
                alert(`File "${file.name}" is not a supported format. Please use images (JPG, PNG, GIF) or videos (MP4, MOV).`);
                return;
            }
            
            // Validate file size
            if (file.size > maxFileSize) {
                alert(`File "${file.name}" is too large. Maximum size is 10MB.`);
                return;
            }
            
            // Add to uploaded files
            uploadedFiles.push(file);
            
            // Create preview
            createPreview(file);
        });
        
        // Update file input
        updateFileInput();
    }
    
    // Create preview
    function createPreview(file) {
        const previewItem = document.createElement('div');
        previewItem.className = 'ad-preview-item';
        previewItem.dataset.filename = file.name;
        
        const isVideo = file.type.startsWith('video/');
        const mediaElement = isVideo ? document.createElement('video') : document.createElement('img');
        mediaElement.controls = isVideo;
        mediaElement.muted = isVideo;
        
        const reader = new FileReader();
        reader.onload = (e) => {
            mediaElement.src = e.target.result;
        };
        reader.readAsDataURL(file);
        
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'ad-preview-remove';
        removeBtn.innerHTML = '<i class="bi bi-x"></i>';
        removeBtn.onclick = () => removePreview(file.name);
        
        const info = document.createElement('div');
        info.className = 'ad-preview-info';
        info.textContent = file.name;
        
        previewItem.appendChild(mediaElement);
        previewItem.appendChild(removeBtn);
        previewItem.appendChild(info);
        previewContainer.appendChild(previewItem);
    }
    
    // Remove preview
    function removePreview(filename) {
        uploadedFiles = uploadedFiles.filter(f => f.name !== filename);
        const previewItem = previewContainer.querySelector(`[data-filename="${filename}"]`);
        if (previewItem) {
            previewItem.remove();
        }
        updateFileInput();
    }
    
    // Update file input with selected files
    function updateFileInput() {
        const dataTransfer = new DataTransfer();
        uploadedFiles.forEach(file => dataTransfer.items.add(file));
        fileInput.files = dataTransfer.files;
    }
    
    // Form submission
    form.addEventListener('submit', function(e) {
        // Validate product selection
        const productId = document.getElementById('productSelect').value;
        if (!productId) {
            e.preventDefault();
            alert('Please select a product');
            return false;
        }
        
        // Disable submit button and show loading state
        submitBtn.disabled = true;
        submitBtn.classList.add('ad-btn-loading');
        submitBtn.innerHTML = '<div class="ad-btn-spinner"></div> <span>Creating...</span>';
        
        // Let form submit normally (better for file uploads)
        // The form will handle the redirect on the server side
    });
    
    // Product selection change - auto-fill title if empty
    document.getElementById('productSelect').addEventListener('change', function() {
        if (!titleInput.value) {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                const productName = selectedOption.text.split(' - ')[0];
                titleInput.value = productName;
                updateCounter(titleInput, titleCounter, 255);
            }
        }
    });
    
    // Smooth animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    document.querySelectorAll('.ad-form-section').forEach(section => {
        section.style.opacity = '0';
        section.style.transform = 'translateY(20px)';
        section.style.transition = 'all 0.5s ease';
        observer.observe(section);
    });
});

