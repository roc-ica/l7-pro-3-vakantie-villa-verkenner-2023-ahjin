<?php

include_once __DIR__ . '/database.php';

class Filter {
    private $conn;
    private $filters = [];

    // Store filter criteria in an array
    public function __construct($filters = []) {
        $db = new Database();
        $this->conn = $db->getConnection();
        $this->filters = $filters; // Expecting an associative array of filters
    }

    private function buildSearchTermCondition($zoekterm, &$params) {
        if (empty($zoekterm)) {
            return "";
        }
        
        // Log the search term
        error_log("Search term in buildSearchTermCondition: " . $zoekterm);
        
        // Expanded search condition to include address fields
        $condition = "(v.titel LIKE :zoekterm OR v.beschrijving LIKE :zoekterm OR v.straat LIKE :zoekterm OR v.post_c LIKE :zoekterm OR v.plaatsnaam LIKE :zoekterm)";
        $params[':zoekterm'] = '%' . $zoekterm . '%';
        
        return $condition;
    }

    public function getFilteredVillas() {
        // Debug the filters
        error_log("Filter inputs: " . print_r($this->filters, true));
        
        if ($this->conn === null) {
            error_log("Filter class: Database connection failed.");
            return []; // Return empty array if connection fails
        }

        // Special case for direct title searches - prioritize name searches
        if (!empty($this->filters['zoekterm']) && strlen($this->filters['zoekterm']) > 3) {
            // First try a direct search with just the search term
            $directSearchSql = "SELECT DISTINCT v.*, 
                   (SELECT GROUP_CONCAT(fo.name SEPARATOR ', ') 
                    FROM feature_options fo
                    JOIN villa_feature_options vfo ON fo.id = vfo.option_id 
                    WHERE vfo.villa_id = v.id) AS features,
                   (SELECT GROUP_CONCAT(lo.name SEPARATOR ', ') 
                    FROM location_options lo
                    JOIN villa_location_options vlo ON lo.id = vlo.option_id 
                    WHERE vlo.villa_id = v.id) AS locations,
                   (SELECT image_path 
                    FROM villa_images 
                    WHERE villa_id = v.id AND is_hoofdfoto = 1 LIMIT 1) AS main_image,
                   (SELECT image_path 
                    FROM villa_images 
                    WHERE villa_id = v.id LIMIT 1) AS any_image
                FROM villas v
                WHERE (v.titel LIKE :search 
                       OR v.straat LIKE :search 
                       OR v.post_c LIKE :search 
                       OR v.plaatsnaam LIKE :search)";
            
            try {
                $directStmt = $this->conn->prepare($directSearchSql);
                $directStmt->execute([':search' => '%' . $this->filters['zoekterm'] . '%']);
                $directResults = $directStmt->fetchAll(PDO::FETCH_ASSOC);
                
                error_log("Direct search for '" . $this->filters['zoekterm'] . "' found " . count($directResults) . " results");
                
                // If we found exact matches, return them - prioritize direct name matches
                if (count($directResults) > 0) {
                    // Assign the correct image path
                    foreach ($directResults as &$row) {
                        $row['image'] = $row['main_image'] ?? $row['any_image'];
                        unset($row['main_image'], $row['any_image']); // Clean up temporary columns
                    }
                    unset($row); // Unset reference
                    
                    return $directResults;
                }
            } catch (PDOException $e) {
                error_log("Error in direct search query: " . $e->getMessage());
                // Continue with regular search if direct search fails
            }
        }

        // Check specifically for Biarodpas search
        if (!empty($this->filters['zoekterm']) && 
            stripos($this->filters['zoekterm'], 'biarodpas') !== false) {
            error_log("Special case: Searching for Biarodpas. Area filter may affect results.");
            
            // Check if we're filtering by area that would exclude Biarodpas
            if (!empty($this->filters['max_area']) && $this->filters['max_area'] < 1200) {
                error_log("Warning: max_area filter set to " . $this->filters['max_area'] . " which is less than Biarodpas (1200m²)");
                
                // Direct query to check Biarodpas villa
                $debugStmt = $this->conn->prepare("SELECT id, titel, oppervlakte FROM villas WHERE titel LIKE :term");
                $debugStmt->execute([':term' => '%Biarodpas%']);
                $debugResults = $debugStmt->fetchAll(PDO::FETCH_ASSOC);
                error_log("Biarodpas villa details: " . print_r($debugResults, true));
            }
        }

        // Base query - Selecting necessary columns including the new ones
        $sql = "SELECT DISTINCT v.*, 
                       (SELECT GROUP_CONCAT(fo.name SEPARATOR ', ') 
                        FROM feature_options fo
                        JOIN villa_feature_options vfo ON fo.id = vfo.option_id 
                        WHERE vfo.villa_id = v.id) AS features, -- Using feature_options
                       (SELECT GROUP_CONCAT(lo.name SEPARATOR ', ') 
                        FROM location_options lo
                        JOIN villa_location_options vlo ON lo.id = vlo.option_id 
                        WHERE vlo.villa_id = v.id) AS locations, -- Using location_options
                       (SELECT image_path 
                        FROM villa_images 
                        WHERE villa_id = v.id AND is_hoofdfoto = 1 LIMIT 1) AS main_image, -- Prefer main image
                       (SELECT image_path 
                        FROM villa_images 
                        WHERE villa_id = v.id LIMIT 1) AS any_image -- Fallback image
                FROM villas v";
        
        $joins = [];
        $whereClauses = [];
        $params = [];

        // --- Build WHERE clauses based on filters --- 

        // Price Range
        if (!empty($this->filters['min_price']) && $this->filters['min_price'] > 0) { // Avoid adding clause for 0
            $whereClauses[] = "v.prijs >= :min_price";
            $params[':min_price'] = $this->filters['min_price'];
        }
        if (!empty($this->filters['max_price']) && $this->filters['max_price'] > 0) {
            $whereClauses[] = "v.prijs <= :max_price";
            $params[':max_price'] = $this->filters['max_price'];
        }

        // Area Range
        if (!empty($this->filters['min_area']) && $this->filters['min_area'] > 0) {
            $whereClauses[] = "v.oppervlakte >= :min_area";
            $params[':min_area'] = $this->filters['min_area'];
        }
        if (!empty($this->filters['max_area']) && $this->filters['max_area'] > 0) {
            $whereClauses[] = "v.oppervlakte <= :max_area";
            $params[':max_area'] = $this->filters['max_area'];
        }

        // Rooms, Bedrooms, Bathrooms (Exact match or greater than/equal to? Assuming exact for now)
        if (!empty($this->filters['kamers']) && $this->filters['kamers'] > 0) {
            $whereClauses[] = "v.kamers = :kamers";
            $params[':kamers'] = $this->filters['kamers'];
        }
        if (!empty($this->filters['slaapkamers']) && $this->filters['slaapkamers'] > 0) {
            $whereClauses[] = "v.slaapkamers = :slaapkamers";
            $params[':slaapkamers'] = $this->filters['slaapkamers'];
        }
        if (!empty($this->filters['badkamers']) && $this->filters['badkamers'] > 0) {
            $whereClauses[] = "v.badkamers = :badkamers";
            $params[':badkamers'] = $this->filters['badkamers'];
        }

        // Search Term (in titel, straat, postcode, or plaatsnaam)
        if (!empty($this->filters['zoekterm'])) {
            $searchCondition = $this->buildSearchTermCondition($this->filters['zoekterm'], $params);
            if (!empty($searchCondition)) {
                $whereClauses[] = $searchCondition;
            }
        }

        // Feature Options (Eigenschappen) - Assuming checkbox values are option names
        if (!empty($this->filters['eigenschappen']) && is_array($this->filters['eigenschappen'])) {
            if (!in_array('features', $joins)) {
                $sql .= " JOIN villa_feature_options vfo_filter ON v.id = vfo_filter.villa_id";
                $sql .= " JOIN feature_options fo_filter ON vfo_filter.option_id = fo_filter.id";
                $joins[] = 'features'; 
            }
            $featurePlaceholders = [];
            $featureIndex = 0;
            foreach ($this->filters['eigenschappen'] as $featureName) {
                $placeholder = ':feature' . $featureIndex++;
                $featurePlaceholders[] = $placeholder;
                $params[$placeholder] = $featureName;
            }
            $whereClauses[] = "fo_filter.name IN (" . implode(',', $featurePlaceholders) . ")";
            // Ensure villas have ALL selected features
            $whereClauses[] = "v.id IN (
                SELECT vfo_sub.villa_id 
                FROM villa_feature_options vfo_sub
                JOIN feature_options fo_sub ON vfo_sub.option_id = fo_sub.id
                WHERE fo_sub.name IN (" . implode(',', $featurePlaceholders) . ")
                GROUP BY vfo_sub.villa_id
                HAVING COUNT(DISTINCT fo_sub.id) = :feature_count
            )";
            $params[':feature_count'] = count($this->filters['eigenschappen']);
        }
        
        // Location Options (Ligging) - Assuming checkbox values are option names
        if (!empty($this->filters['ligging']) && is_array($this->filters['ligging'])) {
            if (!in_array('locations', $joins)) {
                 $sql .= " JOIN villa_location_options vlo_filter ON v.id = vlo_filter.villa_id";
                 $sql .= " JOIN location_options lo_filter ON vlo_filter.option_id = lo_filter.id";
                 $joins[] = 'locations';
            }
            $locationPlaceholders = [];
            $locationIndex = 0;
            foreach ($this->filters['ligging'] as $locationName) {
                $placeholder = ':location' . $locationIndex++;
                $locationPlaceholders[] = $placeholder;
                $params[$placeholder] = $locationName;
            }
             // Ensure villas have ALL selected locations
            $whereClauses[] = "v.id IN (
                SELECT vlo_sub.villa_id 
                FROM villa_location_options vlo_sub
                JOIN location_options lo_sub ON vlo_sub.option_id = lo_sub.id
                WHERE lo_sub.name IN (" . implode(',', $locationPlaceholders) . ")
                GROUP BY vlo_sub.villa_id
                HAVING COUNT(DISTINCT lo_sub.id) = :location_count
            )";
            $params[':location_count'] = count($this->filters['ligging']);
        }

        // --- Combine WHERE clauses --- 
        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        // --- Add GROUP BY if options were filtered to avoid duplicates --- 
        if (in_array('features', $joins) || in_array('locations', $joins)) {
            $sql .= " GROUP BY v.id"; // Group by main villa table columns to ensure distinct villas
        }

        // Always add explicit ORDER BY to ensure consistent results
        $sql .= " ORDER BY v.id ASC";

        // Log the complete SQL query and parameters for debugging
        error_log("Generated SQL query: " . $sql);
        error_log("Query params: " . print_r($params, true));

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Debug the raw results
            error_log("Query returned " . count($results) . " results");
            if (count($results) === 0 && !empty($this->filters['zoekterm'])) {
                error_log("No results found for search term: " . $this->filters['zoekterm']);
                // Perform a direct query to check if the term exists at all
                $debugQuery = "SELECT id, titel FROM villas WHERE titel LIKE :term";
                $debugStmt = $this->conn->prepare($debugQuery);
                $debugStmt->execute([':term' => '%' . $this->filters['zoekterm'] . '%']);
                $debugResults = $debugStmt->fetchAll(PDO::FETCH_ASSOC);
                error_log("Direct title check found " . count($debugResults) . " matches: " . print_r($debugResults, true));
            }
            
            // Assign the correct image path (main or fallback)
            foreach ($results as &$row) {
                $row['image'] = $row['main_image'] ?? $row['any_image'];
                unset($row['main_image'], $row['any_image']); // Clean up temporary columns
            }
            unset($row); // Unset reference
            
            return $results;
            
        } catch (PDOException $e) {
            error_log("Error executing filtered villa query: " . $e->getMessage() . " SQL: " . $sql . " Params: " . print_r($params, true));
            return []; // Return empty array on error
        }
    }

    // Method to get available feature options (Eigenschappen)
    public function getFeatureOptions() {
         if ($this->conn === null) {
            error_log("Filter class: Database connection failed for getFeatureOptions.");
            return [];
        }
        try {
            $stmt = $this->conn->query("SELECT id, name FROM feature_options ORDER BY name");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
             error_log("Error fetching feature options: " . $e->getMessage());
            return [];
        }
    }

    // Method to get available location options (Ligging)
    public function getLocationOptions() {
        if ($this->conn === null) {
           error_log("Filter class: Database connection failed for getLocationOptions.");
           return [];
       }
       try {
           $stmt = $this->conn->query("SELECT id, name FROM location_options ORDER BY name");
           return $stmt->fetchAll(PDO::FETCH_ASSOC);
       } catch (PDOException $e) {
            error_log("Error fetching location options: " . $e->getMessage());
           return [];
       }
   }
}