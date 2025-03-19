document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.photo-upload-area');
    const submitBtn = form.querySelector('.submit-btn');
    const photoGrid = document.querySelector('.photo-grid');
    const mainPhotoSlot = document.querySelector('.main-photo-slot');
    const photoSlots = document.querySelectorAll('.photo-slot');
    
    // Add refresh button (temporary for testing)
    const refreshBtn = document.createElement('button');
    refreshBtn.className = 'refresh-btn';
    refreshBtn.innerHTML = 'Vernieuwen';
    refreshBtn.style.marginLeft = '10px';
    refreshBtn.addEventListener('click', function(e) {
        e.preventDefault();
        location.reload();
    });
    submitBtn.after(refreshBtn);
    
    // Add undo button to the form
    addUndoButton();
    
    // Initialize photo uploads
    initPhotoUploads();
    
    // Initialize price formatting
    initPriceFormatting();
    
    // Load any saved draft data
    loadDraftData();
    
    // Setup form autosave
    setupFormAutosave();
    
    // Handle tag/label buttons
    const addTagBtn = document.querySelector('.add-tag-btn');
    if (addTagBtn) {
        addTagBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const input = document.getElementById('tags');
            if (input && input.value.trim() !== '') {
                addTagToDisplay(input.value.trim());
                input.value = '';
                
                // Save draft after adding a tag
                saveDraftData();
            }
        });
    }
    
    // Function to initialize price formatting
    function initPriceFormatting() {
        const priceInput = document.getElementById('prijs');
        if (priceInput) {
            // Format on initial load if there's a value
            if (priceInput.value) {
                priceInput.value = formatPrice(priceInput.value);
            }

            // Format as user types
            priceInput.addEventListener('input', function(e) {
                // Store cursor position
                const cursorPos = this.selectionStart;
                const originalLength = this.value.length;
                
                // Remove non-numeric characters for processing
                const numericValue = this.value.replace(/[^\d]/g, '');
                
                // Format the number
                this.value = formatPrice(numericValue);
                
                // Adjust cursor position based on new length
                const newLength = this.value.length;
                const posDiff = newLength - originalLength;
                this.setSelectionRange(cursorPos + posDiff, cursorPos + posDiff);
            });

            // Select all text on focus
            priceInput.addEventListener('focus', function() {
                setTimeout(() => this.select(), 100);
            });
        }
    }
    
    // Format number as price with Euro symbol
    function formatPrice(value) {
        if (!value) return '';
        
        // Convert to number and format with thousand separators
        const numValue = parseInt(value);
        if (isNaN(numValue)) return '';
        
        return '€ ' + numValue.toLocaleString('nl-NL');
    }
    
    // Handle form submission
    submitBtn.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Gather form data
        const straat = document.getElementById('straatnaam').value;
        const postcode = document.getElementById('postcode').value;
        const kamers = parseInt(document.getElementById('kamers').value) || 0;
        const badkamers = parseInt(document.getElementById('badkamers').value) || 0;
        const slaapkamers = parseInt(document.getElementById('slaapkamers').value) || 0;
        const oppervlakte = parseFloat(document.getElementById('oppervlakte').value) || 0;
        const prijs = parseInt(document.getElementById('prijs').value.replace(/[^\d]/g, '')) || 0;
        
        // Get all tags/labels
        const labels = [];
        document.querySelectorAll('.form-tags .tag').forEach(tag => {
            labels.push(tag.textContent.trim());
        });
        
        // Validate data
        if (!straat || !postcode || kamers <= 0 || oppervlakte <= 0 || prijs <= 0) {
            alert('Vul alle verplichte velden in!');
            return;
        }

        // Check if we have uploaded images
        const uploadedImages = collectUploadedImages();
        
        // Create FormData for the file upload
        const formData = new FormData();
        formData.append('straat', straat);
        formData.append('post_c', postcode);
        formData.append('kamers', kamers);
        formData.append('badkamers', badkamers);
        formData.append('slaapkamers', slaapkamers);
        formData.append('oppervlakte', oppervlakte);
        formData.append('prijs', prijs);
        
        // Add labels to form data
        labels.forEach(label => {
            formData.append('labels[]', label);
        });
        
        // Add images to form data
        uploadedImages.forEach((file, index) => {
            if (file instanceof File) {
                formData.append(`images[${index}]`, file);
            }
        });
        
        // Create loading indicator
        const loadingIndicator = document.createElement('div');
        loadingIndicator.className = 'loading-indicator';
        loadingIndicator.innerHTML = '<div class="spinner"></div><p>Uploading villa data...</p>';
        document.body.appendChild(loadingIndicator);
        
        // Send data to API with file upload
        fetch('/db/api/insert_villa.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            // First, get the raw text of the response
            return response.text().then(text => {
                // Try to parse it as JSON
                try {
                    // If it starts with HTML tags, there's likely a PHP error
                    if (text.trim().startsWith('<')) {
                        console.error('Server returned HTML instead of JSON:', text);
                        throw new Error('Server returned HTML instead of JSON');
                    }
                    
                    // Try to parse as JSON
                    const data = JSON.parse(text);
                    
                    // Check for database errors
                    if (data.error === 'Database error') {
                        console.error('Database error:', data.message);
                        throw new Error('Database error: ' + data.message);
                    }
                    
                    return data;
                } catch (e) {
                    console.error('Failed to parse response as JSON:', text);
                    throw new Error('Invalid JSON response: ' + e.message);
                }
            });
        })
        .then(data => {
            // Remove loading indicator
            document.body.removeChild(loadingIndicator);
            
            if (data.success) {
                // Clear the draft data after successful submission
                clearDraftData();
                
                alert('Villa toegevoegd! ID: ' + data.villa_id);
                // Reload the page or clear the form
                location.reload();
            } else {
                alert('Fout bij toevoegen: ' + (data.message || data.error));
            }
        })
        .catch(error => {
            // Remove loading indicator
            if (document.body.contains(loadingIndicator)) {
                document.body.removeChild(loadingIndicator);
            }
            
            alert('Er is een fout opgetreden: ' + error);
            console.error('Error:', error);
        });
    });
    
    // Function to add a tag to the display
    function addTagToDisplay(tagName) {
        let tagsDisplay;
        
        // Create or find the tags display area
        if (!document.querySelector('.form-tags')) {
            tagsDisplay = document.createElement('div');
            tagsDisplay.className = 'tags-display form-tags';
            const tagsInput = document.querySelector('.tags-input');
            tagsInput.parentNode.insertAdjacentElement('afterend', tagsDisplay);
        } else {
            tagsDisplay = document.querySelector('.form-tags');
        }
        
        // Check if tag already exists
        const existingTags = Array.from(tagsDisplay.querySelectorAll('.tag')).map(tag => tag.textContent.toLowerCase());
        if (existingTags.includes(tagName.toLowerCase())) {
            return; // Skip duplicates
        }
        
        const newTag = document.createElement('div');
        newTag.className = 'tag';
        newTag.textContent = tagName;
        
        // Add click event to remove
        newTag.addEventListener('click', function() {
            this.remove();
            // Save draft after removing a tag
            saveDraftData();
        });
        
        // Add to display
        tagsDisplay.appendChild(newTag);
    }
    
    // Function to initialize photo uploads
    function initPhotoUploads() {
        // Create hidden file input
        const fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.multiple = true;
        fileInput.accept = 'image/*';
        fileInput.style.display = 'none';
        fileInput.id = 'hidden-file-input';
        document.body.appendChild(fileInput);
        
        // Add click listener to main photo slot
        mainPhotoSlot.addEventListener('click', function() {
            fileInput.click();
        });
        
        // Add click listeners to smaller photo slots
        photoSlots.forEach(slot => {
            slot.addEventListener('click', function() {
                fileInput.click();
            });
        });
        
        // Handle file selection
        fileInput.addEventListener('change', function(e) {
            if (this.files && this.files.length > 0) {
                handleFileUploads(Array.from(this.files));
                this.value = ''; // Reset to allow selecting the same file again
            }
        });
        
        // Add drag and drop support
        [mainPhotoSlot, ...photoSlots].forEach(slot => {
            slot.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('drag-over');
            });
            
            slot.addEventListener('dragleave', function() {
                this.classList.remove('drag-over');
            });
            
            slot.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('drag-over');
                
                if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                    handleFileUploads(Array.from(e.dataTransfer.files));
                }
            });
        });
    }
    
    // Function to handle file uploads
    function handleFileUploads(files) {
        // Check if we already have 9 photos
        const existingPhotos = document.querySelectorAll('.photo-preview');
        if (existingPhotos.length + files.length > 9) {
            alert('Je kunt maximaal 9 foto\'s uploaden.');
            files = files.slice(0, 9 - existingPhotos.length);
        }
        
        files.forEach(file => {
            if (!file.type.match('image.*')) {
                alert('Alleen afbeeldingen zijn toegestaan.');
                return;
            }
            
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const imgSrc = e.target.result;
                
                // Find the first empty slot
                let targetSlot;
                
                if (!mainPhotoSlot.querySelector('.photo-preview')) {
                    targetSlot = mainPhotoSlot;
                } else {
                    for (const slot of photoSlots) {
                        if (!slot.querySelector('.photo-preview')) {
                            targetSlot = slot;
                            break;
                        }
                    }
                }
                
                if (targetSlot) {
                    // Clear the slot
                    targetSlot.innerHTML = '';
                    
                    // Create the preview
                    const preview = document.createElement('div');
                    preview.className = 'photo-preview';
                    preview.style.backgroundImage = `url(${imgSrc})`;
                    preview.setAttribute('data-src', imgSrc);
                    
                    // Store the file object with the preview
                    preview.file = file;
                    
                    // Add delete button
                    const deleteBtn = document.createElement('button');
                    deleteBtn.className = 'delete-photo';
                    deleteBtn.innerHTML = '×';
                    deleteBtn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        preview.remove();
                        resetSlot(targetSlot);
                        // Save draft after removing an image
                        saveDraftData();
                    });
                    
                    preview.appendChild(deleteBtn);
                    targetSlot.appendChild(preview);
                    
                    // Save draft after adding an image
                    saveDraftData();
                } else {
                    alert('Alle slots zijn bezet. Verwijder een foto om een nieuwe toe te voegen.');
                }
            };
            
            reader.readAsDataURL(file);
        });
    }
    
    // Function to reset a photo slot
    function resetSlot(slot) {
        if (slot === mainPhotoSlot) {
            slot.innerHTML = `
                <svg viewBox="0 0 100 100" class="photo-placeholder">
                    <rect width="100" height="100" fill="url(#photoUploadPattern)"/>
                    <path d="M35,45 L65,45 L65,35 L75,35 L75,45 L85,45 L85,55 L75,55 L75,65 L65,65 L65,55 L35,55 L35,65 L25,65 L25,55 L15,55 L15,45 L25,45 L25,35 L35,35 Z" fill="#aaa" fill-opacity="0.5"/>
                </svg>
            `;
        } else {
            slot.innerHTML = '';
        }
    }
    
    // Function to collect all uploaded images for submission
    function collectUploadedImages() {
        const images = [];
        
        // Get main photo if it exists
        const mainPreview = mainPhotoSlot.querySelector('.photo-preview');
        if (mainPreview && mainPreview.file) {
            images.push(mainPreview.file);
        }
        
        // Get all other photos
        photoSlots.forEach(slot => {
            const preview = slot.querySelector('.photo-preview');
            if (preview && preview.file) {
                images.push(preview.file);
            }
        });
        
        return images;
    }
    
    // Add undo button to the form
    function addUndoButton() {
        // Check if the button already exists (to avoid duplication)
        if (document.querySelector('.undo-btn')) return;
        
        const formActions = document.createElement('div');
        formActions.className = 'form-actions';
        
        const undoBtn = document.createElement('button');
        undoBtn.className = 'undo-btn';
        undoBtn.innerHTML = 'Concept verwijderen';
        undoBtn.type = 'button';
        
        undoBtn.addEventListener('click', function() {
            if (confirm('Weet je zeker dat je alle ingevulde gegevens wilt verwijderen?')) {
                clearDraftData();
                location.reload(); // Force reload to clear all form fields and images
            }
        });
        
        formActions.appendChild(undoBtn);
        
        // Insert before the submit button
        submitBtn.parentNode.insertBefore(formActions, submitBtn);
        
        // Only show undo button if there's saved data
        if (!localStorage.getItem('villa_draft')) {
            undoBtn.style.display = 'none';
        }
    }
    
    // Save form data to localStorage
    function saveDraftData() {
        const draftData = {
            straat: document.getElementById('straatnaam').value,
            postcode: document.getElementById('postcode').value,
            kamers: document.getElementById('kamers').value,
            badkamers: document.getElementById('badkamers').value,
            slaapkamers: document.getElementById('slaapkamers').value,
            oppervlakte: document.getElementById('oppervlakte').value,
            prijs: document.getElementById('prijs').value,
            timestamp: new Date().getTime()
        };
        
        // Save tags
        const tags = [];
        document.querySelectorAll('.form-tags .tag').forEach(tag => {
            tags.push(tag.textContent.trim());
        });
        draftData.tags = tags;
        
        // Save image previews (data URLs)
        const imagePreviews = [];
        
        // Main photo slot
        const mainPreview = mainPhotoSlot.querySelector('.photo-preview');
        if (mainPreview) {
            const dataSrc = mainPreview.getAttribute('data-src');
            if (dataSrc) {
                imagePreviews.push({
                    slot: 'main',
                    src: dataSrc
                });
            }
        }
        
        // Other photo slots
        photoSlots.forEach((slot, index) => {
            const preview = slot.querySelector('.photo-preview');
            if (preview) {
                const dataSrc = preview.getAttribute('data-src');
                if (dataSrc) {
                    imagePreviews.push({
                        slot: index,
                        src: dataSrc
                    });
                }
            }
        });
        
        draftData.images = imagePreviews;
        
        try {
            localStorage.setItem('villa_draft', JSON.stringify(draftData));
            
            // Show the undo button since we now have saved data
            const undoBtn = document.querySelector('.undo-btn');
            if (undoBtn) {
                undoBtn.style.display = 'inline-block';
            }
        } catch (e) {
            console.error('Error saving draft data', e);
            // If localStorage is full (likely because of image data), we can try to save without images
            try {
                delete draftData.images;
                localStorage.setItem('villa_draft', JSON.stringify(draftData));
                console.warn('Saved draft without images due to storage limitations');
            } catch (e2) {
                console.error('Could not save draft data at all', e2);
            }
        }
    }
    
    // Load form data from localStorage
    function loadDraftData() {
        const savedData = localStorage.getItem('villa_draft');
        if (!savedData) return;
        
        try {
            const draftData = JSON.parse(savedData);
            
            // Check if draft is older than 24 hours (86400000 ms)
            if (new Date().getTime() - draftData.timestamp > 86400000) {
                clearDraftData();
                return;
            }
            
            // Fill form fields
            if (draftData.straat) document.getElementById('straatnaam').value = draftData.straat;
            if (draftData.postcode) document.getElementById('postcode').value = draftData.postcode;
            if (draftData.kamers) document.getElementById('kamers').value = draftData.kamers;
            if (draftData.badkamers) document.getElementById('badkamers').value = draftData.badkamers;
            if (draftData.slaapkamers) document.getElementById('slaapkamers').value = draftData.slaapkamers;
            if (draftData.oppervlakte) document.getElementById('oppervlakte').value = draftData.oppervlakte;
            if (draftData.prijs) document.getElementById('prijs').value = draftData.prijs;
            
            // Format price if needed
            if (draftData.prijs) {
                const priceInput = document.getElementById('prijs');
                priceInput.value = formatPrice(priceInput.value.replace(/[^\d]/g, ''));
            }
            
            // Restore tags
            if (draftData.tags && Array.isArray(draftData.tags)) {
                draftData.tags.forEach(tag => {
                    addTagToDisplay(tag);
                });
            }
            
            // Restore images
            if (draftData.images && Array.isArray(draftData.images)) {
                draftData.images.forEach(imageData => {
                    let targetSlot;
                    
                    // Determine target slot
                    if (imageData.slot === 'main') {
                        targetSlot = mainPhotoSlot;
                    } else if (typeof imageData.slot === 'number' && imageData.slot >= 0 && imageData.slot < photoSlots.length) {
                        targetSlot = photoSlots[imageData.slot];
                    }
                    
                    if (targetSlot && imageData.src) {
                        // Create preview
                        const preview = document.createElement('div');
                        preview.className = 'photo-preview';
                        preview.style.backgroundImage = `url(${imageData.src})`;
                        preview.setAttribute('data-src', imageData.src);
                        
                        // Clear the slot
                        targetSlot.innerHTML = '';
                        
                        // Add delete button
                        const deleteBtn = document.createElement('button');
                        deleteBtn.className = 'delete-photo';
                        deleteBtn.innerHTML = '×';
                        deleteBtn.addEventListener('click', function(e) {
                            e.stopPropagation();
                            preview.remove();
                            resetSlot(targetSlot);
                            saveDraftData();
                        });
                        
                        preview.appendChild(deleteBtn);
                        targetSlot.appendChild(preview);
                    }
                });
            }
            
            // Show a notification
            showDraftNotification();
        } catch (e) {
            console.error('Error loading draft data', e);
            clearDraftData();
        }
    }
    
    // Clear draft data
    function clearDraftData() {
        console.log('Clearing draft data');
        localStorage.removeItem('villa_draft');
        
        // Hide the undo button
        const undoBtn = document.querySelector('.undo-btn');
        if (undoBtn) {
            undoBtn.style.display = 'none';
        }
        
        // Remove any draft notification
        const notification = document.querySelector('.draft-notification');
        if (notification) {
            notification.remove();
        }
        
        // Clear form fields
        document.getElementById('straatnaam').value = '';
        document.getElementById('postcode').value = '';
        document.getElementById('kamers').value = '';
        document.getElementById('badkamers').value = '';
        document.getElementById('slaapkamers').value = '';
        document.getElementById('oppervlakte').value = '';
        document.getElementById('prijs').value = '';
        
        // Clear tags
        const tagsDisplay = document.querySelector('.form-tags');
        if (tagsDisplay) {
            tagsDisplay.innerHTML = '';
        }
        
        // Clear images
        resetSlot(mainPhotoSlot);
        photoSlots.forEach(slot => {
            resetSlot(slot);
        });
    }
    
    // Setup form autosave
    function setupFormAutosave() {
        // Save on input change with debounce
        let saveTimeout;
        const formInputs = form.querySelectorAll('input, textarea, select');
        
        formInputs.forEach(input => {
            input.addEventListener('input', function() {
                clearTimeout(saveTimeout);
                saveTimeout = setTimeout(saveDraftData, 1000);
            });
        });
        
        // Save when user is leaving the page
        window.addEventListener('beforeunload', function() {
            saveDraftData();
        });
    }
    
    // Show draft notification
    function showDraftNotification() {
        if (document.querySelector('.draft-notification')) return;
        
        const notification = document.createElement('div');
        notification.className = 'draft-notification';
        notification.innerHTML = 'Concept geladen! Je kunt verder gaan waar je gebleven was.';
        
        const closeBtn = document.createElement('button');
        closeBtn.innerHTML = '×';
        closeBtn.className = 'close-notification';
        closeBtn.addEventListener('click', function() {
            notification.remove();
        });
        
        notification.appendChild(closeBtn);
        document.querySelector('.upload-section').insertBefore(notification, form);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            if (notification.parentNode) {
                notification.classList.add('hiding');
                setTimeout(function() {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 500);
            }
        }, 5000);
    }
}); 