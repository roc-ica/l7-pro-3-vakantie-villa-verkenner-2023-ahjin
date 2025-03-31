<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Villa's beheren - Villa Verkenner</title>
    <link rel="stylesheet" href="../protected/styles/villas.css">
    <link rel="stylesheet" href="../protected/styles/photo-upload.css">
    <link rel="stylesheet" href="../protected/styles/villa-list.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- SVG pattern definitions -->
    <svg width="0" height="0" style="position:absolute">
        <defs>
            <pattern id="photoUploadPattern" patternUnits="userSpaceOnUse" width="20" height="20" patternTransform="rotate(45)">
                <rect width="20" height="20" fill="#f5f5f5"/>
                <circle cx="5" cy="5" r="1" fill="#333" fill-opacity="0.1"/>
                <circle cx="15" cy="15" r="1" fill="#333" fill-opacity="0.1"/>
            </pattern>
            
            <pattern id="villaPhotoPattern" patternUnits="userSpaceOnUse" width="30" height="30">
                <rect width="30" height="30" fill="#eaf7fd"/>
                <line x1="0" y1="15" x2="30" y2="15" stroke="#3498db" stroke-width="0.5" stroke-opacity="0.2"/>
                <line x1="15" y1="0" x2="15" y2="30" stroke="#3498db" stroke-width="0.5" stroke-opacity="0.2"/>
            </pattern>
        </defs>
    </svg>

    <?php include 'components/header.php'; ?>
    
    <!-- Including the database -->
    <?php
    include_once '../../db/class/database.php';

    // Get all villas from the database
    $villas = get_villas();
    ?>
    
    <div class="container">
        <div class="titel-overzicht">
            <h1>Villa Beheer</h1>
            <p class="subtitle">Beheer villa's, foto's en details</p>
        </div>
        
        <!-- Upload section -->
        <div class="upload-section">
            <h2>Nieuwe villa toevoegen</h2>
            
            <div class="photo-upload-area">
                <h3>Maximaal 9 foto's</h3>
                <div class="photo-grid">
                    <div class="main-photo-slot">
                        <svg viewBox="0 0 100 100" class="photo-placeholder">
                            <rect width="100" height="100" fill="url(#photoUploadPattern)"/>
                            <path d="M35,45 L65,45 L65,35 L75,35 L75,45 L85,45 L85,55 L75,55 L75,65 L65,65 L65,55 L35,55 L35,65 L25,65 L25,55 L15,55 L15,45 L25,45 L25,35 L35,35 Z" fill="#aaa" fill-opacity="0.5"/>
                        </svg>
                    </div>
                    <div class="photo-slots">
                        <div class="photo-slot"></div>
                        <div class="photo-slot"></div>
                        <div class="photo-slot"></div>
                        <div class="photo-slot"></div>
                        <div class="photo-slot"></div>
                        <div class="photo-slot"></div>
                        <div class="photo-slot"></div>
                        <div class="photo-slot"></div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group half">
                        <label for="postcode">Postcode</label>
                        <input type="text" id="postcode" placeholder="1234 AB">
                    </div>
                    <div class="form-group half">
                        <label for="straatnaam">Straatnaam</label>
                        <input type="text" id="straatnaam" placeholder="Straatnaam">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group full">
                        <label for="tags">Tags</label>
                        <div class="tags-input">
                            <input type="text" id="tags" placeholder="Voeg tags toe...">
                            <button class="add-tag-btn">+</button>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group third">
                        <label for="kamers">Kamers</label>
                        <input type="number" id="kamers" placeholder="2" min="1">
                    </div>
                    <div class="form-group third">
                        <label for="badkamers">Badkamers</label>
                        <input type="number" id="badkamers" placeholder="1" min="0">
                    </div>
                    <div class="form-group third">
                        <label for="slaapkamers">Slaapkamers</label>
                        <input type="number" id="slaapkamers" placeholder="1" min="0">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group half">
                        <label for="oppervlakte">Oppervlakte (m²)</label>
                        <input type="number" id="oppervlakte" placeholder="100" min="1" step="0.1">
                    </div>
                    <div class="form-group half">
                        <label for="prijs">Prijs</label>
                        <input type="text" id="prijs" placeholder="€ 0,00">
                    </div>
                </div>
                
                <button class="submit-btn">Voeg deze nieuwe villa toe</button>
            </div>
        </div>
        
        <!-- Villa list -->
        <div class="villas-list-section">
            <h2>Bestaande villa's</h2>
            
            <?php if (empty($villas)): ?>
                <div class="no-villas-message">
                    <p>Er zijn nog geen villa's toegevoegd. Voeg een nieuwe villa toe om te beginnen.</p>
                </div>
            <?php else: ?>
                <?php foreach ($villas as $villa): ?>
                    <?php 
                        // Get the primary image for this villa
                        $villa_image = get_villa_primary_image($villa['id']);
                        $image_path = $villa_image ? $villa_image : '/frontend/assets/img/placeholder.jpg';
                        
                        // Get labels for this villa
                        $villa_labels = get_villa_labels($villa['id']);
                    ?>
                    <div class="villa-item" data-villa-id="<?= $villa['id'] ?>">
                        <div class="villa-image">
                            <img src="<?= $image_path ?>" alt="Villa afbeelding">
                        </div>
                        
                        <div class="villa-details">
                            <div class="detail-group">
                                <label>Postcode</label>
                                <div class="editable-field">
                                    <span><?= htmlspecialchars($villa['post_c']) ?></span>
                                    <input type="text" value="<?= htmlspecialchars($villa['post_c']) ?>" class="hidden">
                                </div>
                            </div>
                            
                            <div class="detail-group">
                                <label>Adres</label>
                                <div class="editable-field">
                                    <span><?= htmlspecialchars($villa['straat']) ?></span>
                                    <input type="text" value="<?= htmlspecialchars($villa['straat']) ?>" class="hidden">
                                </div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-group">
                                    <label>Kamers</label>
                                    <div class="editable-field">
                                        <span><?= htmlspecialchars($villa['kamers']) ?></span>
                                        <input type="number" value="<?= htmlspecialchars($villa['kamers']) ?>" class="hidden">
                                    </div>
                                </div>
                                
                                <div class="detail-group">
                                    <label>Badkamers</label>
                                    <div class="editable-field">
                                        <span><?= htmlspecialchars($villa['badkamers']) ?></span>
                                        <input type="number" value="<?= htmlspecialchars($villa['badkamers']) ?>" class="hidden">
                                    </div>
                                </div>
                                
                                <div class="detail-group">
                                    <label>Prijs</label>
                                    <div class="editable-field">
                                        <span>€ <?= number_format($villa['prijs'], 0, ',', '.') ?>,-</span>
                                        <input type="number" value="<?= htmlspecialchars($villa['prijs']) ?>" class="hidden">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tags-display">
                                <?php foreach ($villa_labels as $label): ?>
                                    <div class="tag" data-label-id="<?= $label['id'] ?>"><?= htmlspecialchars($label['naam']) ?></div>
                                <?php endforeach; ?>
                                <div class="add-tag">+</div>
                            </div>
                        </div>
                        
                        <div class="villa-actions">
                            <button class="edit-btn" data-villa-id="<?= $villa['id'] ?>">EDITEER</button>
                            <button class="delete-btn" data-villa-id="<?= $villa['id'] ?>">VERWIJDER</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="/frontend/protected/utils/add_villa_script.js"></script>
    <script>
        // Add event listeners for dynamic edit buttons, etc.
        document.addEventListener('DOMContentLoaded', function() {
            // Edit button functionality
            document.querySelectorAll('.edit-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const villaItem = this.closest('.villa-item');
                    const villaId = villaItem.getAttribute('data-villa-id');
                    
                    if (villaItem.classList.contains('editing')) {
                        // Save changes
                        villaItem.classList.remove('editing');
                        this.textContent = 'EDITEER';
                        
                        // Gather updated data
                        const updateData = {
                            id: villaId,
                            straat: villaItem.querySelector('.detail-group:nth-child(2) input').value,
                            post_c: villaItem.querySelector('.detail-group:nth-child(1) input').value,
                            kamers: parseInt(villaItem.querySelector('.detail-row .detail-group:nth-child(1) input').value) || 0,
                            badkamers: parseInt(villaItem.querySelector('.detail-row .detail-group:nth-child(2) input').value) || 0,
                            slaapkamers: 0, // This would need a field in the UI
                            oppervlakte: 0, // This would need a field in the UI
                            prijs: parseInt(villaItem.querySelector('.detail-row .detail-group:nth-child(3) input').value) || 0
                        };
                        
                        // Collect labels
                        const labels = [];
                        villaItem.querySelectorAll('.tags-display .tag').forEach(tag => {
                            labels.push(tag.textContent.trim());
                        });
                        updateData.labels = labels;
                        
                        // Send to the server with fetch API
                        fetch('/db/api/update_villa.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(updateData)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Update display
                                villaItem.querySelectorAll('.editable-field').forEach(field => {
                                    const input = field.querySelector('input');
                                    const display = field.querySelector('span');
                                    
                                    if (input.type === 'number' && field.closest('.detail-group').querySelector('label').textContent === 'Prijs') {
                                        display.textContent = '€ ' + parseInt(input.value).toLocaleString('nl-NL') + ',-';
                                    } else {
                                        display.textContent = input.value;
                                    }
                                });
                                
                                alert('Villa succesvol bijgewerkt!');
                            } else {
                                alert('Fout bij bijwerken: ' + (data.message || data.error));
                            }
                        })
                        .catch(error => {
                            console.error('Error updating villa:', error);
                            alert('Er is een fout opgetreden bij het bijwerken van de villa.');
                        });
                    } else {
                        // Enter edit mode
                        villaItem.classList.add('editing');
                        this.textContent = 'OPSLAAN';
                    }
                });
            });
            
            // Delete button functionality
            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const villaId = this.getAttribute('data-villa-id');
                    if (confirm('Weet je zeker dat je deze villa wilt verwijderen?')) {
                        // Send delete request to the server
                        fetch('/db/api/delete_villa.php?id=' + villaId, {
                            method: 'POST'
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Remove from the DOM
                                this.closest('.villa-item').remove();
                                alert('Villa succesvol verwijderd!');
                                
                                // If no villas left, show message
                                if (document.querySelectorAll('.villa-item').length === 0) {
                                    const message = document.createElement('div');
                                    message.className = 'no-villas-message';
                                    message.innerHTML = '<p>Er zijn nog geen villa\'s toegevoegd. Voeg een nieuwe villa toe om te beginnen.</p>';
                                    document.querySelector('.villas-list-section').appendChild(message);
                                }
                            } else {
                                alert('Fout bij verwijderen: ' + (data.message || data.error));
                            }
                        })
                        .catch(error => {
                            console.error('Error deleting villa:', error);
                            alert('Er is een fout opgetreden bij het verwijderen van de villa.');
                        });
                    }
                });
            });
            
            // Handle tag deletion
            document.querySelectorAll('.tag').forEach(tag => {
                tag.addEventListener('click', function() {
                    const labelId = this.getAttribute('data-label-id');
                    const villaId = this.closest('.villa-item').getAttribute('data-villa-id');
                    
                    if (confirm('Wilt u deze tag verwijderen?')) {
                        // In a real implementation, we'd send a request to remove this label
                        // For now, just remove from the DOM
                        this.remove();
                    }
                });
            });
            
            // Add tag functionality
            document.querySelectorAll('.add-tag').forEach(button => {
                button.addEventListener('click', function() {
                    const tagName = prompt('Voer een nieuwe tag in:');
                    if (tagName && tagName.trim()) {
                        const tagsDisplay = this.closest('.tags-display');
                        const newTag = document.createElement('div');
                        newTag.className = 'tag';
                        newTag.textContent = tagName.trim();
                        
                        // Add click to remove
                        newTag.addEventListener('click', function() {
                            if (confirm('Wilt u deze tag verwijderen?')) {
                                this.remove();
                            }
                        });
                        
                        // Insert before the add button
                        tagsDisplay.insertBefore(newTag, this);
                    }
                });
            });
            
            // Add CSS for edit mode
            const style = document.createElement('style');
            style.textContent = `
                .villa-item.editing .editable-field span {
                    display: none;
                }
                .villa-item.editing .editable-field input {
                    display: block;
                }
                .editable-field input.hidden {
                    display: none;
                }
            `;
            document.head.appendChild(style);
        });
    </script>
</body>
</html> 