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

    public function getFilteredVillas() {
        if ($this->conn === null) {
            error_log("Filter class: Database connection failed.");
            return []; // Return empty array if connection fails
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
            $whereClauses[] = "(v.titel LIKE :zoekterm OR v.straat LIKE :zoekterm OR v.post_c LIKE :zoekterm OR v.plaatsnaam LIKE :zoekterm)";
            $params[':zoekterm'] = '%' . $this->filters['zoekterm'] . '%';
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

        // --- Optional: Add ORDER BY or LIMIT clauses here if needed --- 
        // $sql .= " ORDER BY v.prijs ASC";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
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