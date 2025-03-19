document.addEventListener('DOMContentLoaded', function() {
 
    /* =========================================
    VILLAS PAGE FUNCTIONALITY
    ========================================= */
    
    // Toggle edit mode for each villa item
    const editButtons = document.querySelectorAll('.edit-btn');
    if (editButtons.length > 0) {
        editButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const villaItem = this.closest('.villa-item');
                
                // Toggle the editing class
                villaItem.classList.toggle('editing');
                
                // Change button text based on state
                if (villaItem.classList.contains('editing')) {
                    this.textContent = 'OPSLAAN';
                    
                    // Show inputs, hide spans
                    villaItem.querySelectorAll('.editable-field').forEach(field => {
                        const span = field.querySelector('span');
                        const input = field.querySelector('input');
                        
                        span.classList.add('hidden');
                        input.classList.remove('hidden');
                    });
                } else {
                    this.textContent = 'EDITEER';
                    
                    // Hide inputs, show spans with new values
                    villaItem.querySelectorAll('.editable-field').forEach(field => {
                        const span = field.querySelector('span');
                        const input = field.querySelector('input');
                        
                        // Format price value if it's a price field
                        if (input.value.includes('€') || span.textContent.includes('€')) {
                            span.textContent = `€ ${parseFloat(input.value).toLocaleString('nl-NL')},- `;
                        } else {
                            span.textContent = input.value;
                        }
                        
                        span.classList.remove('hidden');
                        input.classList.add('hidden');
                    });
                }
            });
        });
    }
    
    // Delete villa functionality
    const deleteButtons = document.querySelectorAll('.delete-btn');
    if (deleteButtons.length > 0) {
        deleteButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const villaItem = this.closest('.villa-item');
                if (confirm('Weet je zeker dat je deze villa wilt verwijderen?')) {
                    villaItem.remove();
                }
            });
        });
    }
    
    // Tag removal in villa items
    const tags = document.querySelectorAll('.tag');
    if (tags.length > 0) {
        tags.forEach(tag => {
            tag.addEventListener('click', function() {
                if (this.closest('.villa-item') && this.closest('.villa-item').classList.contains('editing')) {
                    this.remove();
                }
            });
        });
    }
    
    // Villa search functionality
    const villaSearchBox = document.querySelector('.villas-list-section .search-box input');
    if (villaSearchBox) {
        villaSearchBox.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            document.querySelectorAll('.villa-item').forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
    
    // Add tag in edit mode for existing villas
    const addTagButtons = document.querySelectorAll('.add-tag');
    if (addTagButtons.length > 0) {
        addTagButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                if (this.closest('.villa-item') && this.closest('.villa-item').classList.contains('editing')) {
                    const tagName = prompt('Voer een nieuwe tag in:');
                    if (tagName && tagName.trim() !== '') {
                        const newTag = document.createElement('div');
                        newTag.className = 'tag';
                        newTag.textContent = tagName.trim();
                        
                        // Add click event to remove
                        newTag.addEventListener('click', function() {
                            if (this.closest('.villa-item').classList.contains('editing')) {
                                this.remove();
                            }
                        });
                        
                        // Insert before the add button
                        this.parentNode.insertBefore(newTag, this);
                    }
                }
            });
        });
    }
    
    // Add new tag in the add villa form
    const addTagBtn = document.querySelector('.add-tag-btn');
    if (addTagBtn) {
        addTagBtn.addEventListener('click', function() {
            const input = document.getElementById('tags');
            if (input && input.value.trim() !== '') {
                let tagsDisplay;
                
                // Create or find the tags display area
                if (!document.querySelector('.form-tags')) {
                    tagsDisplay = document.createElement('div');
                    tagsDisplay.className = 'tags-display form-tags';
                    input.parentNode.insertAdjacentElement('afterend', tagsDisplay);
                } else {
                    tagsDisplay = document.querySelector('.form-tags');
                }
                
                const newTag = document.createElement('div');
                newTag.className = 'tag';
                newTag.textContent = input.value.trim();
                
                // Add click event to remove
                newTag.addEventListener('click', function() {
                    this.remove();
                });
                
                // Add to display
                tagsDisplay.appendChild(newTag);
                
                // Clear input
                input.value = '';
            }
        });
    }
});