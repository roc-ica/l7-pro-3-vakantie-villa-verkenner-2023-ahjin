<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Villa Beheer - Villa Verkenner</title>
    <link rel="stylesheet" href="/frontend/protected/styles/villas.css">
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
                        <label for="openbare-kamers">Openbare kamers</label>
                        <input type="number" id="openbare-kamers" placeholder="1" min="0">
                    </div>
                    <div class="form-group third">
                        <label for="prijs">Prijs</label>
                        <input type="text" id="prijs" placeholder="€ 0,00">
                    </div>
                </div>
                
                <button class="submit-btn">Voeg deze nieuwe villa toe</button>
            </div>
        </div>
        
        <!-- Villa list -->
        <div class="villas-list-section">
            <div class="list-header">
                <h2>Alle villa's</h2>
                <div class="search-box">
                    <input type="text" placeholder="Zoeken...">
                    <span class="search-icon">&#128269;</span>
                </div>
            </div>
            
            <p class="instruction">Click edit en verwijder inline.</p>
            
            <!-- Villa item -->
            <div class="villa-item">
                <div class="villa-photo">
                    <img src="https://images.unsplash.com/photo-1580587771525-78b9dba3b914?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1074&q=80" alt="Moderne villa met zwembad">
                </div>
                
                <div class="villa-details">
                    <div class="detail-group">
                        <label>Postcode</label>
                        <div class="editable-field">
                            <span>1071 JW</span>
                            <input type="text" value="1071 JW" class="hidden">
                        </div>
                    </div>
                    
                    <div class="detail-group">
                        <label>Straatnaam</label>
                        <div class="editable-field">
                            <span>Schaduwzijde 60</span>
                            <input type="text" value="Schaduwzijde 60" class="hidden">
                        </div>
                    </div>
                    
                    <div class="detail-group tags-group">
                        <label>Tags</label>
                        <div class="tags-display">
                            <div class="tag">Zwembad</div>
                            <div class="tag">Luxe</div>
                            <div class="tag">Uitzicht</div>
                            <button class="add-tag">+</button>
                        </div>
                    </div>
                    
                    <div class="detail-row">
                        <div class="detail-group small">
                            <label>Kamers</label>
                            <div class="editable-field">
                                <span>5</span>
                                <input type="number" value="5" min="1" class="hidden">
                            </div>
                        </div>
                        
                        <div class="detail-group small">
                            <label>Openbare ruimtes</label>
                            <div class="editable-field">
                                <span>2</span>
                                <input type="number" value="2" min="0" class="hidden">
                            </div>
                        </div>
                        
                        <div class="detail-group small">
                            <label>Prijs</label>
                            <div class="editable-field">
                                <span>€ 1.203.320,-</span>
                                <input type="text" value="1203320" class="hidden">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="villa-admin">
                    <div class="admin-icon">
                        <span class="admin-label">P</span>
                    </div>
                    <div class="admin-info">
                        <span class="admin-name">Pajer</span>
                        <span class="admin-time">6 days ago</span>
                        <div class="admin-message">Als je op een tag klikt wordt die verwijderd!</div>
                    </div>
                    <div class="admin-actions">
                        <button class="action-btn edit-btn">EDITEER</button>
                        <button class="action-btn delete-btn">VERWIJDER</button>
                    </div>
                </div>
            </div>
            
            <!-- Second villa item -->
            <div class="villa-item">
                <div class="villa-photo">
                    <img src="https://images.unsplash.com/photo-1564013799919-ab600027ffc6?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1470&q=80" alt="Luxe villa met tuin">
                </div>
                
                <div class="villa-details">
                    <div class="detail-group">
                        <label>Postcode</label>
                        <div class="editable-field">
                            <span>1082 AA</span>
                            <input type="text" value="1082 AA" class="hidden">
                        </div>
                    </div>
                    
                    <div class="detail-group">
                        <label>Straatnaam</label>
                        <div class="editable-field">
                            <span>Burgemeesterlaan 23</span>
                            <input type="text" value="Burgemeesterlaan 23" class="hidden">
                        </div>
                    </div>
                    
                    <div class="detail-group tags-group">
                        <label>Tags</label>
                        <div class="tags-display">
                            <div class="tag">Tuin</div>
                            <div class="tag">Modern</div>
                            <button class="add-tag">+</button>
                        </div>
                    </div>
                    
                    <div class="detail-row">
                        <div class="detail-group small">
                            <label>Kamers</label>
                            <div class="editable-field">
                                <span>4</span>
                                <input type="number" value="4" min="1" class="hidden">
                            </div>
                        </div>
                        
                        <div class="detail-group small">
                            <label>Openbare ruimtes</label>
                            <div class="editable-field">
                                <span>1</span>
                                <input type="number" value="1" min="0" class="hidden">
                            </div>
                        </div>
                        
                        <div class="detail-group small">
                            <label>Prijs</label>
                            <div class="editable-field">
                                <span>€ 845.000,-</span>
                                <input type="text" value="845000" class="hidden">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="villa-admin">
                    <div class="admin-icon">
                        <span class="admin-label">A</span>
                    </div>
                    <div class="admin-info">
                        <span class="admin-name">Admin</span>
                        <span class="admin-time">2 weeks ago</span>
                    </div>
                    <div class="admin-actions">
                        <button class="action-btn edit-btn">EDITEER</button>
                        <button class="action-btn delete-btn">VERWIJDER</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="/frontend/protected/utils/villa.js"></script>
</body>
</html> 