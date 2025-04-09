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

        // Base query
        $sql = "SELECT DISTINCT v.*, 
                       (SELECT GROUP_CONCAT(l.naam SEPARATOR ', ') 
                        FROM labels l 
                        JOIN villa_labels vl ON l.id = vl.label_id 
                        WHERE vl.villa_id = v.id) AS tags,
                       (SELECT image_path 
                        FROM villa_images 
                        WHERE villa_id = v.id LIMIT 1) AS image
                FROM villas v";
        $joins = [];
        $whereClauses = [];
        $params = [];

        // --- Build WHERE clauses based on filters --- 

        // Price Range (assuming min_price and max_price)
        if (!empty($this->filters['min_price'])) {
            $whereClauses[] = "v.prijs >= :min_price";
            $params[':min_price'] = $this->filters['min_price'];
        }
        if (!empty($this->filters['max_price']) && $this->filters['max_price'] > 0) {
            $whereClauses[] = "v.prijs <= :max_price";
            $params[':max_price'] = $this->filters['max_price'];
        }

        // Area Range (assuming min_area and max_area)
        if (!empty($this->filters['min_area'])) {
            $whereClauses[] = "v.oppervlakte >= :min_area";
            $params[':min_area'] = $this->filters['min_area'];
        }
        if (!empty($this->filters['max_area']) && $this->filters['max_area'] > 0) {
            $whereClauses[] = "v.oppervlakte <= :max_area";
            $params[':max_area'] = $this->filters['max_area'];
        }

        // Rooms, Bedrooms, Bathrooms (exact match)
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

        // Search Term (in straat or post_c)
        if (!empty($this->filters['zoekterm'])) {
            $whereClauses[] = "(v.straat LIKE :zoekterm OR v.post_c LIKE :zoekterm)";
            $params[':zoekterm'] = '%' . $this->filters['zoekterm'] . '%';
        }

        // Labels (Amenities/Location) - Assuming checkbox values are label names
        $labelFilters = [];
        if (!empty($this->filters['faciliteiten'])) { // e.g., ['Zwembad', 'Privépark']
            $labelFilters = array_merge($labelFilters, $this->filters['faciliteiten']);
        }
        if (!empty($this->filters['ligging'])) { // e.g., ['Bij het bos', 'Aan het water']
            $labelFilters = array_merge($labelFilters, $this->filters['ligging']);
        }

        if (!empty($labelFilters)) {
            // We need to ensure villas have ALL selected labels.
            // Add join if not already added by other parts (though not strictly needed here yet)
            if (!in_array('labels', $joins)) {
                $sql .= " JOIN villa_labels vl_filter ON v.id = vl_filter.villa_id";
                $sql .= " JOIN labels l_filter ON vl_filter.label_id = l_filter.id";
                $joins[] = 'labels'; // Mark as joined
            }

            // Create placeholders for each label
            $labelPlaceholders = [];
            $labelIndex = 0;
            foreach ($labelFilters as $label) {
                $placeholder = ':label' . $labelIndex++;
                $labelPlaceholders[] = $placeholder;
                $params[$placeholder] = $label;
            }
            
            // Add condition: villa must have labels matching the names in the placeholders
            $whereClauses[] = "v.id IN (
                SELECT vl_sub.villa_id 
                FROM villa_labels vl_sub
                JOIN labels l_sub ON vl_sub.label_id = l_sub.id
                WHERE l_sub.naam IN (" . implode(',', $labelPlaceholders) . ")
                GROUP BY vl_sub.villa_id
                HAVING COUNT(DISTINCT l_sub.id) = :label_count
            )";
            $params[':label_count'] = count($labelFilters);

        }

        // Combine WHERE clauses
        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        // Add GROUP BY if labels were filtered to avoid duplicate villas from JOIN
         if (in_array('labels', $joins)) {
             $sql .= " GROUP BY v.id"; // Group by villa ID to ensure distinct villas
         }

        // Optional: Add ORDER BY or LIMIT clauses here if needed
        // $sql .= " ORDER BY v.prijs ASC";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error executing filtered villa query: " . $e->getMessage() . " SQL: " . $sql . " Params: " . print_r($params, true));
            return []; // Return empty array on error
        }
    }

    // Example method to get available labels (useful for admin forms)
    public function getAvailableLabels() {
         if ($this->conn === null) {
            error_log("Filter class: Database connection failed for getAvailableLabels.");
            return [];
        }
        try {
            $stmt = $this->conn->query("SELECT id, naam FROM labels ORDER BY naam");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
             error_log("Error fetching labels: " . $e->getMessage());
            return [];
        }
    }
}